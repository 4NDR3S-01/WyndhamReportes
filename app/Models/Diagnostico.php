<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
