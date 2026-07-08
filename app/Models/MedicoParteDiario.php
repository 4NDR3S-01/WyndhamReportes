<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoParteDiario extends Model
{
    protected $table = 'medico_partes_diarios';

    protected $fillable = [
        'fecha',
        'nombres',
        'edad',
        'area_id',
        'cargo_id',
        'tipo_paciente',
        'habitacion',
        'turno',
        'tipo_certificado',
        'entidad_certificado_id',
        'horas_certificado',
        'dias_certificado',
        'fecha_inicio_certificado',
        'fecha_fin_certificado',
        'medico_certifica',
        'causa_id',
        'diagnostico_id',
        'tipo_salida',
        'incidente',
        'observacion',
        'hash_unico',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_inicio_certificado' => 'date',
            'fecha_fin_certificado' => 'date',
            'horas_certificado' => 'decimal:2',
            'dias_certificado' => 'integer',
            'edad' => 'integer',
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

    public function entidadCertificado(): BelongsTo
    {
        return $this->belongsTo(EntidadCertificado::class, 'entidad_certificado_id');
    }

    public function causa(): BelongsTo
    {
        return $this->belongsTo(Causa::class, 'causa_id');
    }

    public function diagnostico(): BelongsTo
    {
        return $this->belongsTo(Diagnostico::class, 'diagnostico_id');
    }

    public function medicamentos(): HasMany
    {
        return $this->hasMany(MedicoParteMedicamento::class, 'parte_diario_id');
    }
}
