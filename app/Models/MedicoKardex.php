<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicoKardex extends Model
{
    protected $table = 'medico_kardex';
    protected $fillable = [
        'fecha_inicio', 'fecha_fin', 'tipo',
        'nombre', 'saldo_anterior', 'ingresos', 'egresos', 'total',
        'fecha_caducidad', 'hash_unico',
    ];
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date', 'fecha_fin' => 'date',
            'saldo_anterior' => 'decimal:2', 'ingresos' => 'decimal:2',
            'egresos' => 'decimal:2', 'total' => 'decimal:2',
        ];
    }
}
