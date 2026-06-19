<?php

namespace App\Services\Medico;

use App\Models\MedicoKardex;
use App\Models\MedicoKardexCierre;
use App\Models\MedicoKardexCierreItem;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoProducto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KardexMensualService
{
    public function generar(string $desde, string $hasta, bool $cerrar = false): MedicoKardexCierre
    {
        $inicio = Carbon::parse($desde)->startOfDay();
        $fin = Carbon::parse($hasta)->startOfDay();

        if ($fin->lt($inicio)) {
            throw new \InvalidArgumentException('La fecha hasta debe ser mayor o igual a desde.');
        }

        return DB::transaction(function () use ($inicio, $fin, $cerrar): MedicoKardexCierre {
            $cierre = MedicoKardexCierre::query()->firstOrCreate(
                ['fecha_inicio' => $inicio->toDateString(), 'fecha_fin' => $fin->toDateString()],
                ['periodo' => $inicio->format('Y-m'), 'generado_por' => auth()->id(), 'estado' => 'abierto'],
            );

            if ($cierre->estado === 'cerrado' && ! $cerrar) {
                return $cierre->load('items');
            }

            $cierre->items()->delete();

            foreach (MedicoProducto::query()->orderBy('tipo')->orderBy('nombre')->get() as $producto) {
                $saldoBase = $this->saldoBaseProducto($producto, $inicio->toDateString());
                $movimientosAntes = $this->movimientosProductoHasta($producto, null, $inicio->copy()->subDay()->toDateString());
                $saldoAnterior = $saldoBase + $movimientosAntes;

                $ingresos = $this->sumaMovimientos($producto, $inicio->toDateString(), $fin->toDateString(), ['ingreso']);
                $ajustesPositivos = $this->sumaAjustes($producto, $inicio->toDateString(), $fin->toDateString(), positivo: true);
                $egresos = $this->sumaMovimientos($producto, $inicio->toDateString(), $fin->toDateString(), ['salida']);
                $ajustesNegativos = abs($this->sumaAjustes($producto, $inicio->toDateString(), $fin->toDateString(), positivo: false));
                $totalIngresos = $ingresos + $ajustesPositivos;
                $totalEgresos = $egresos + $ajustesNegativos;

                MedicoKardexCierreItem::create([
                    'cierre_id' => $cierre->id,
                    'producto_id' => $producto->id,
                    'tipo' => $producto->tipo,
                    'nombre' => $producto->nombre,
                    'saldo_anterior' => $saldoAnterior,
                    'ingresos' => $totalIngresos,
                    'egresos' => $totalEgresos,
                    'total' => $saldoAnterior + $totalIngresos - $totalEgresos,
                    'fecha_caducidad' => $producto->fecha_caducidad,
                ]);
            }

            $cierre->update([
                'periodo' => $inicio->format('Y-m'),
                'generado_por' => auth()->id(),
                'estado' => $cerrar ? 'cerrado' : 'abierto',
                'cerrado_en' => $cerrar ? now() : null,
            ]);

            return $cierre->load('items');
        });
    }

    public function mesesDisponibles(): Collection
    {
        return MedicoKardexMovimiento::query()
            ->selectRaw("strftime('%Y-%m', fecha_movimiento) as ym")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->pluck('ym')
            ->filter();
    }

    private function saldoBaseProducto(MedicoProducto $producto, string $antesDe): float
    {
        $kardex = MedicoKardex::query()
            ->where('nombre', $producto->nombre)
            ->whereDate('fecha_fin', '<', $antesDe)
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->first();

        if (! $kardex) {
            $kardex = MedicoKardex::query()
                ->where('nombre', $producto->nombre)
                ->orderBy('fecha_inicio')
                ->orderBy('id')
                ->first();
        }

        return (float) ($kardex?->total ?? 0);
    }

    private function movimientosProductoHasta(MedicoProducto $producto, ?string $desde, string $hasta): float
    {
        $query = MedicoKardexMovimiento::query()
            ->where('producto_id', $producto->id)
            ->whereDate('fecha_movimiento', '<=', $hasta);

        if ($desde) {
            $query->whereDate('fecha_movimiento', '>=', $desde);
        }

        $ingresos = (clone $query)->where('tipo', 'ingreso')->sum('cantidad');
        $salidas = (clone $query)->where('tipo', 'salida')->sum('cantidad');
        $ajustes = (clone $query)->where('tipo', 'ajuste')->sum('cantidad');

        return (float) $ingresos - (float) $salidas + (float) $ajustes;
    }

    private function sumaMovimientos(MedicoProducto $producto, string $desde, string $hasta, array $tipos): float
    {
        return (float) MedicoKardexMovimiento::query()
            ->where('producto_id', $producto->id)
            ->whereIn('tipo', $tipos)
            ->whereBetween('fecha_movimiento', [$desde, $hasta])
            ->sum('cantidad');
    }

    private function sumaAjustes(MedicoProducto $producto, string $desde, string $hasta, bool $positivo): float
    {
        $query = MedicoKardexMovimiento::query()
            ->where('producto_id', $producto->id)
            ->where('tipo', 'ajuste')
            ->whereBetween('fecha_movimiento', [$desde, $hasta]);

        $positivo ? $query->where('cantidad', '>', 0) : $query->where('cantidad', '<', 0);

        return (float) $query->sum('cantidad');
    }
}
