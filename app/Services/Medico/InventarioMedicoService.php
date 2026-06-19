<?php

namespace App\Services\Medico;

use App\Models\MedicoArchivoImportado;
use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoParteDiario;
use App\Models\MedicoParteMedicamento;
use App\Models\MedicoProducto;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventarioMedicoService
{
    public function sincronizarMedicacionParte(MedicoParteDiario $parte, array $medicamentos): void
    {
        DB::transaction(function () use ($parte, $medicamentos): void {
            $parte->load('medicamentos.movimiento');

            foreach ($parte->medicamentos as $linea) {
                if ($linea->movimiento) {
                    $linea->movimiento->delete();
                }
                $linea->delete();
            }

            $index = 1;
            foreach ($medicamentos as $medicamento) {
                $nombre = trim((string) ($medicamento['nombre_original'] ?? ''));
                $cantidad = (float) ($medicamento['cantidad'] ?? 1);

                if ($nombre === '' || $cantidad <= 0) {
                    continue;
                }

                $producto = isset($medicamento['producto_id']) && $medicamento['producto_id']
                    ? MedicoProducto::query()->find($medicamento['producto_id'])
                    : MedicoProducto::resolverPorNombre($nombre);

                $linea = MedicoParteMedicamento::create([
                    'parte_diario_id' => $parte->id,
                    'producto_id' => $producto?->id,
                    'campo_origen' => 'medicamento_' . $index,
                    'nombre_original' => $nombre,
                    'cantidad' => $cantidad,
                    'procesado' => false,
                    'observacion' => $medicamento['observacion'] ?? null,
                ]);

                if ($producto) {
                    $movimiento = $this->registrarMovimientoProducto(
                        producto: $producto,
                        tipo: 'salida',
                        cantidad: $cantidad,
                        fecha: (string) $parte->fecha?->format('Y-m-d'),
                        responsable: $parte->medico_certifica ?: 'Departamento medico',
                        origen: 'parte_diario',
                        parte: $parte,
                        observacion: 'Salida automatica por parte diario #' . $parte->id,
                        campoMedicamento: 'linea_' . $linea->id,
                        archivo: $parte->archivoImportado,
                    );

                    $linea->update([
                        'movimiento_id' => $movimiento->id,
                        'procesado' => true,
                    ]);
                }

                $index++;
            }
        });
    }

    public function registrarMovimientoProducto(
        MedicoProducto $producto,
        string $tipo,
        float $cantidad,
        string $fecha,
        ?string $responsable = null,
        string $origen = 'manual',
        ?MedicoParteDiario $parte = null,
        ?string $observacion = null,
        ?string $campoMedicamento = null,
        ?MedicoArchivoImportado $archivo = null,
    ): MedicoKardexMovimiento {
        $kardex = $this->asegurarKardexProducto($producto, $archivo);
        $hash = $parte
            ? MedicoKardexMovimiento::generarHash($parte->id, $campoMedicamento ?: $producto->nombre, $producto->nombre)
            : hash('sha256', implode('|', ['manual', $producto->id, $tipo, $cantidad, $fecha, microtime(true), random_int(1, PHP_INT_MAX)]));

        $cantidadMovimiento = abs($cantidad);
        $saldo = $producto->saldoActual();
        $saldoResultante = match ($tipo) {
            'salida' => $saldo - $cantidadMovimiento,
            'ajuste' => $saldo + $cantidad,
            default => $saldo + $cantidadMovimiento,
        };

        return MedicoKardexMovimiento::create([
            'kardex_id' => $kardex->id,
            'producto_id' => $producto->id,
            'parte_diario_id' => $parte?->id,
            'archivo_importado_id' => $archivo?->id,
            'medicamento_nombre' => $producto->nombre,
            'campo_medicamento' => $campoMedicamento,
            'cantidad' => $tipo === 'ajuste' ? $cantidad : $cantidadMovimiento,
            'tipo' => $tipo,
            'origen' => $origen,
            'saldo_resultante' => $saldoResultante,
            'fecha_movimiento' => $fecha,
            'personal_responsable' => $responsable,
            'observacion' => $observacion,
            'hash_unico' => $hash,
        ]);
    }

    public function asegurarKardexProducto(MedicoProducto $producto, ?MedicoArchivoImportado $archivo = null): MedicoKardex
    {
        $existente = MedicoKardex::query()
            ->where('nombre', $producto->nombre)
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->first();

        if ($existente) {
            return $existente;
        }

        $archivo ??= $this->archivoSistema();

        return MedicoKardex::create([
            'archivo_importado_id' => $archivo->id,
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin' => now()->endOfMonth()->toDateString(),
            'tipo' => $producto->tipo,
            'nombre' => $producto->nombre,
            'saldo_anterior' => 0,
            'ingresos' => 0,
            'egresos' => 0,
            'total' => 0,
            'fecha_caducidad' => $producto->fecha_caducidad?->format('Y-m-d'),
            'hash_unico' => hash('sha256', implode('|', ['producto', $producto->id, now()->timestamp])),
        ]);
    }

    public function archivoSistema(): MedicoArchivoImportado
    {
        $usuarioId = auth()->id() ?: User::query()->value('id');

        if (! $usuarioId) {
            throw new \RuntimeException('No existe un usuario para registrar movimientos medicos.');
        }

        return MedicoArchivoImportado::firstOrCreate(
            ['nombre_guardado' => 'panel-medico-manual'],
            [
                'usuario_id' => $usuarioId,
                'nombre_original' => 'Panel medico',
                'ruta' => 'panel-medico',
                'extension' => 'panel',
                'mime_type' => null,
                'tamano_bytes' => 0,
                'estado' => 'manual',
                'fecha_subida' => now(),
            ],
        );
    }
}
