<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoPaciente extends Model
{
    protected $table = 'medico_pacientes';

    protected $fillable = [
        'cedula',
        'nombres',
        'edad',
        'area_id',
        'cargo_id',
        'fecha_ingreso',
        'patologias',
        'vacunas',
        'fichas_anteriores',
        'antecedentes',
        'telefono',
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

    // === RELACIONES ===

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(MedicoPacienteExamen::class, 'paciente_id');
    }

    public function visitas(): HasMany
    {
        return $this->hasMany(MedicoPacienteVisita::class, 'paciente_id');
    }
}
