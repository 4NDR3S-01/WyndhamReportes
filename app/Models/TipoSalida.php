<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoSalida extends Model
{
    protected $table = 'tipos_salida';
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
