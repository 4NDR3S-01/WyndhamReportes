<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoProductoAlias extends Model
{
    protected $table = 'medico_producto_aliases';

    protected $fillable = [
        'producto_id',
        'alias',
        'alias_normalizado',
    ];

    public function producto(): BelongsTo
    {
        return $this->belongsTo(MedicoProducto::class, 'producto_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $alias): void {
            $alias->alias_normalizado = MedicoProducto::normalizarNombre($alias->alias);
        });
    }
}
