<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoCatalogo extends Model
{
    protected $table = 'medico_catalogos';

    protected $fillable = [
        'tipo',
        'nombre',
        'descripcion',
        'metadata',
        'activo',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'activo' => 'boolean',
            'orden' => 'integer',
        ];
    }
}
