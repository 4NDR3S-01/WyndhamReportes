<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoKardexMovimiento extends Model
{
    protected $table = 'medico_kardex_movimientos';

    protected $fillable = [
        'kardex_id',
        'producto_id',
        'parte_diario_id',
        'archivo_importado_id',
        'medicamento_nombre',
        'campo_medicamento',
        'cantidad',
        'tipo',
        'origen',
        'saldo_resultante',
        'fecha_movimiento',
        'personal_responsable',
        'observacion',
        'hash_unico',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'saldo_resultante' => 'float',
            'fecha_movimiento' => 'date',
        ];
    }

    public function kardex(): BelongsTo
    {
        return $this->belongsTo(MedicoKardex::class, 'kardex_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MedicoProducto::class, 'producto_id');
    }

    public function parteDiario(): BelongsTo
    {
        return $this->belongsTo(MedicoParteDiario::class, 'parte_diario_id');
    }

    public function archivoImportado(): BelongsTo
    {
        return $this->belongsTo(MedicoArchivoImportado::class, 'archivo_importado_id');
    }

    public static function generarHash(int $parteDiarioId, string $campoMedicamento, string $medicamentoNombre): string
    {
        return hash('sha256', implode('|', [$parteDiarioId, $campoMedicamento, mb_strtolower(trim($medicamentoNombre))]));
    }
}
