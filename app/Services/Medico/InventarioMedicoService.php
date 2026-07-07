<?php

namespace App\Services\Medico;

use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoParteDiario;
use App\Models\MedicoProducto;

class InventarioMedicoService
{
    public function registrarMovimientoProducto(
        MedicoProducto $producto,
        string $tipo,
        float $cantidad,
        string $fecha,
        ?string $responsable = null,
        string $origen = 'manual',
        ?MedicoParteDiario $parte = null,
        ?string $observacion = null,
    ): MedicoKardexMovimiento {
        $kardex = $this->asegurarKardexProducto($producto);

        return MedicoKardexMovimiento::query()->create([
            'kardex_id'            => $kardex->id,
            'producto_id'          => $producto->id,
            'parte_diario_id'      => $parte?->id,
            'tipo'                 => $tipo,
            'origen'               => $origen,
            'cantidad'             => $cantidad,
            'fecha_movimiento'     => $fecha,
            'personal_responsable' => $responsable,
            'observacion'          => $observacion,
        ]);
    }

    public function asegurarKardexProducto(MedicoProducto $producto): MedicoKardex
    {
        $existente = MedicoKardex::query()
            ->where('nombre', $producto->nombre)
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->first();

        if ($existente) {
            return $existente;
        }

        return MedicoKardex::query()->create([
            'fecha_inicio'    => now()->startOfMonth()->toDateString(),
            'fecha_fin'       => now()->endOfMonth()->toDateString(),
            'tipo'            => $producto->tipo,
            'nombre'          => $producto->nombre,
            'saldo_anterior'  => 0,
            'ingresos'        => 0,
            'egresos'         => 0,
            'total'           => 0,
            'fecha_caducidad' => $producto->fecha_caducidad?->format('Y-m-d'),
            'hash_unico'      => hash('sha256', implode('|', ['producto', $producto->id, now()->timestamp])),
        ]);
    }
}
