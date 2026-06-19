<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoKardex extends Model
{
    protected $table = 'medico_kardex';

    protected $fillable = [
        'archivo_importado_id',
        'fecha_inicio',
        'fecha_fin',
        'tipo',
        'nombre',
        'saldo_anterior',
        'ingresos',
        'egresos',
        'total',
        'fecha_caducidad',
        'hash_unico',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'saldo_anterior' => 'float',
            'ingresos' => 'float',
            'egresos' => 'float',
            'total' => 'float',
        ];
    }

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(MedicoArchivoImportado::class, 'archivo_importado_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MedicoKardexMovimiento::class, 'kardex_id');
    }

    public function totalSalidas(): float
    {
        return (float) $this->movimientos()->where('tipo', 'salida')->sum('cantidad');
    }

    public function saldoActual(): float
    {
        $salidas = $this->totalSalidas();
        $ingresos = (float) $this->movimientos()->where('tipo', 'ingreso')->sum('cantidad');
        $ajustes = (float) $this->movimientos()->where('tipo', 'ajuste')->sum('cantidad');

        return (float) $this->total + $ingresos + $ajustes - $salidas;
    }

    public function scopeMedicinas($query)
    {
        return $query->where('tipo', 'medicina');
    }

    public static function generarHash(int $archivoId, int $numeroFila): string
    {
        return hash('sha256', implode('|', [$archivoId, $numeroFila]));
    }
}
