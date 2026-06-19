<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicoPaciente extends Model
{
    protected $table = 'medico_pacientes';

    protected $fillable = [
        'cedula',
        'nombres',
        'edad',
        'area',
        'cargo',
        'fecha_ingreso',
        'patologias',
        'tipo',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'edad' => 'integer',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
        ];
    }
}
