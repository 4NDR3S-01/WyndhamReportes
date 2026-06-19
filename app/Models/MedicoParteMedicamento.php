<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoParteMedicamento extends Model
{
    protected $table = 'medico_parte_medicamentos';

    protected $fillable = [
        'parte_diario_id',
        'producto_id',
        'movimiento_id',
        'campo_origen',
        'nombre_original',
        'cantidad',
        'procesado',
        'observacion',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'procesado' => 'boolean',
        ];
    }

    public function parteDiario(): BelongsTo
    {
        return $this->belongsTo(MedicoParteDiario::class, 'parte_diario_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MedicoProducto::class, 'producto_id');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(MedicoKardexMovimiento::class, 'movimiento_id');
    }
}
