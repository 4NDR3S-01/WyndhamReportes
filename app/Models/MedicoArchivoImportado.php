<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoArchivoImportado extends Model
{
    protected $table = 'medico_archivos_importados';

    protected $fillable = [
        'usuario_id',
        'nombre_original',
        'nombre_guardado',
        'ruta',
        'extension',
        'mime_type',
        'tamano_bytes',
        'estado',
        'total_filas',
        'filas_importadas',
        'filas_con_error',
        'observaciones',
        'fecha_subida',
        'fecha_procesado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_subida' => 'datetime',
            'fecha_procesado' => 'datetime',
            'tamano_bytes' => 'integer',
            'total_filas' => 'integer',
            'filas_importadas' => 'integer',
            'filas_con_error' => 'integer',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function partesDiarios(): HasMany
    {
        return $this->hasMany(MedicoParteDiario::class, 'archivo_importado_id');
    }

    public function kardex(): HasMany
    {
        return $this->hasMany(MedicoKardex::class, 'archivo_importado_id');
    }

    public function errores(): HasMany
    {
        return $this->hasMany(MedicoImportacionError::class, 'archivo_importado_id');
    }
}
