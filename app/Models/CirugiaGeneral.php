<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CirugiaGeneral extends Model
{
    protected $table = 'cirugias_generales';
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
