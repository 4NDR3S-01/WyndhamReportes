<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoParteDiario extends Model
{
    protected $table = 'medico_partes_diarios';

    protected $fillable = [
        'archivo_importado_id',
        'fecha',
        'nombres',
        'edad',
        'area',
        'cargo',
        'tipo_paciente',
        'habitacion',
        'turno',
        'certificados',
        'subsidio',
        'horas_certificado',
        'dias_certificado',
        'fecha_inicio_certificado',
        'fecha_fin_certificado',
        'medico_certifica',
        'causa',
        'diagnostico',
        'medicamento_1',
        'medicamento_2',
        'medicamento_3',
        'observacion',
        'hash_unico',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'fecha_inicio_certificado' => 'date',
            'fecha_fin_certificado' => 'date',
            'horas_certificado' => 'float',
            'dias_certificado' => 'integer',
            'edad' => 'integer',
        ];
    }

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(MedicoArchivoImportado::class, 'archivo_importado_id');
    }

    public function medicamentos(): HasMany
    {
        return $this->hasMany(MedicoParteMedicamento::class, 'parte_diario_id');
    }

    public static function generarHash(int $archivoId, int $numeroFila): string
    {
        return hash('sha256', implode('|', [$archivoId, $numeroFila]));
    }
}
