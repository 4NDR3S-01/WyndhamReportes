<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoImportacionError extends Model
{
    protected $table = 'medico_importacion_errores';

    protected $fillable = [
        'archivo_importado_id',
        'fila',
        'columna',
        'valor',
        'mensaje',
    ];

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(MedicoArchivoImportado::class, 'archivo_importado_id');
    }
}
