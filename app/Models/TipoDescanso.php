<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TipoDescanso extends Model
{
    protected $table = 'tipos_descanso';
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
