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
        'vacunas',
        'fichas_anteriores',
        'antecedentes',
        'telefono',
        'espirometria',
        'ecografia',
        'audiometria',
        'optometria',
        'visita_2021',
        'visita_2022',
        'visita_2023',
        'visita_2024',
        'visita_2025',
        'visita_2026',
        'tipo',
        'activo',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'edad'          => 'integer',
            'fecha_ingreso' => 'date',
            'espirometria'  => 'date',
            'ecografia'     => 'date',
            'audiometria'   => 'date',
            'optometria'    => 'date',
            'visita_2021'   => 'date',
            'visita_2022'   => 'date',
            'visita_2023'   => 'date',
            'visita_2024'   => 'date',
            'visita_2025'   => 'date',
            'visita_2026'   => 'date',
            'activo'        => 'boolean',
        ];
    }
}
