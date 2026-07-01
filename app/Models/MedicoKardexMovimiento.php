<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoKardexMovimiento extends Model
{
    protected $table = 'medico_kardex_movimientos';
    protected $fillable = [
        'kardex_id', 'producto_id', 'tipo', 'origen', 'cantidad',
        'fecha_movimiento', 'personal_responsable', 'observacion',
    ];
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_movimiento' => 'date',
        ];
    }
    public function kardex(): BelongsTo { return $this->belongsTo(MedicoKardex::class); }
    public function producto(): BelongsTo { return $this->belongsTo(MedicoProducto::class); }
}
