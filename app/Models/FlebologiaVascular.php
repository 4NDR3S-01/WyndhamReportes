<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FlebologiaVascular extends Model
{
    protected $table = 'flebologias_vasculares';
    protected $fillable = ['nombre', 'activo'];
    protected function casts(): array { return ['activo' => 'boolean']; }
}
