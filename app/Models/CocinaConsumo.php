<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CocinaConsumo extends Model
{
    protected $table = 'cocina_consumos';

    protected $fillable = [
        'archivo_importado_id',
        'producto_id',
        'fecha',
        'servicio',
        'concepto',
        'unidad_medida',
        'cantidad',
        'valor',
        'hash_unico',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'cantidad' => 'decimal:3',
            'valor' => 'decimal:2',
        ];
    }

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(CocinaArchivoImportado::class, 'archivo_importado_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(CocinaProducto::class, 'producto_id');
    }

    public static function generarHash(string $fecha, int $productoId, string $servicio, ?string $concepto): string
    {
        return hash('sha256', implode('|', [$fecha, $productoId, mb_strtolower($servicio), mb_strtolower(trim($concepto ?? ''))]));
    }
}
