<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MedicoKardexCierre extends Model
{
    protected $table = 'medico_kardex_cierres';
    protected $fillable = [
        'periodo', 'fecha_inicio', 'fecha_fin', 'estado',
        'generado_por', 'cerrado_en', 'observaciones',
    ];
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date', 'fecha_fin' => 'date',
            'cerrado_en' => 'datetime',
        ];
    }
}
