<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoKardexCierreItem extends Model
{
    protected $table = 'medico_kardex_cierre_items';

    protected $fillable = [
        'cierre_id',
        'producto_id',
        'tipo',
        'nombre',
        'saldo_anterior',
        'ingresos',
        'egresos',
        'total',
        'fecha_caducidad',
    ];

    protected function casts(): array
    {
        return [
            'saldo_anterior' => 'float',
            'ingresos' => 'float',
            'egresos' => 'float',
            'total' => 'float',
            'fecha_caducidad' => 'date',
        ];
    }

    public function cierre(): BelongsTo
    {
        return $this->belongsTo(MedicoKardexCierre::class, 'cierre_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MedicoProducto::class, 'producto_id');
    }
}
