<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CocinaProducto extends Model
{
    protected $table = 'cocina_productos';

    protected $fillable = ['codigo', 'nombre', 'unidad_medida', 'grupo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function consumos(): HasMany
    {
        return $this->hasMany(CocinaConsumo::class, 'producto_id');
    }
}
