<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CocinaImportacionError extends Model
{
    protected $table = 'cocina_importacion_errores';

    protected $fillable = ['archivo_importado_id', 'fila', 'columna', 'valor', 'mensaje'];

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(CocinaArchivoImportado::class, 'archivo_importado_id');
    }
}
