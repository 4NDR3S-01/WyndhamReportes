<?php

namespace App\Services\Medico;

use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoProducto;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Genera kardex mensual calculado en memoria (sin persistir).
 *
 * Las tablas medico_kardex_cierres / medico_kardex_cierre_items eran
 * snapshots redundantes que se regeneraban cada vez. Ahora el kardex
 * se calcula bajo demanda desde los movimientos reales.
 */
class KardexMensualService
{
    /**
     * Genera los items del kardex para un rango de fechas.
     *
     * @return array<int, array{producto_id:int, tipo:string, nombre:string,
     *   saldo_anterior:float, ingresos:float, egresos:float, total:float,
     *   fecha_caducidad:?string}>
     */
    public function generar(string $desde, string $hasta): array
    {
        $inicio = Carbon::parse($desde)->startOfDay();
        $fin = Carbon::parse($hasta)->startOfDay();

        if ($fin->lt($inicio)) {
            throw new \InvalidArgumentException('La fecha hasta debe ser mayor o igual a desde.');
        }

        $items = [];

        foreach (MedicoProducto::query()->orderBy('tipo')->orderBy('nombre')->get() as $producto) {
            $saldoBase = $this->saldoBaseProducto($producto, $inicio->toDateString());
            $movimientosAnteriores = $this->movimientosProductoHasta($producto, $inicio->copy()->subDay()->toDateString());
            $saldoAnterior = $saldoBase + $movimientosAnteriores;

            $ingresos = $this->sumaMovimientos($producto, $inicio->toDateString(), $fin->toDateString(), ['ingreso']);
            $ajustesPos = $this->sumaAjustes($producto, $inicio->toDateString(), $fin->toDateString(), positivo: true);
            $egresos = $this->sumaMovimientos($producto, $inicio->toDateString(), $fin->toDateString(), ['salida']);
            $ajustesNeg = abs($this->sumaAjustes($producto, $inicio->toDateString(), $fin->toDateString(), positivo: false));

            $totalIngresos = $ingresos + $ajustesPos;
            $totalEgresos = $egresos + $ajustesNeg;

            $items[] = [
                'producto_id'     => $producto->id,
                'tipo'            => $producto->tipo,
                'nombre'          => $producto->nombre,
                'saldo_anterior'  => $saldoAnterior,
                'ingresos'        => $totalIngresos,
                'egresos'         => $totalEgresos,
                'total'           => $saldoAnterior + $totalIngresos - $totalEgresos,
                'fecha_caducidad' => $producto->fecha_caducidad?->toDateString(),
            ];
        }

        return $items;
    }

    /**
     * Meses con movimientos (para el selector de historial).
     */
    public function mesesDisponibles(): Collection
    {
        return MedicoKardexMovimiento::query()
            ->selectRaw("strftime('%Y-%m', fecha_movimiento) as ym")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->pluck('ym')
            ->filter();
    }

    // ============================================================
    // Helpers internos
    // ============================================================

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

    private function movimientosProductoHasta(MedicoProducto $producto, string $hasta): float
    {
        $query = MedicoKardexMovimiento::query()
            ->where('producto_id', $producto->id)
            ->whereDate('fecha_movimiento', '<=', $hasta);

        return (float) (clone $query)->where('tipo', 'ingreso')->sum('cantidad')
             - (float) (clone $query)->where('tipo', 'salida')->sum('cantidad')
             + (float) (clone $query)->where('tipo', 'ajuste')->sum('cantidad');
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
