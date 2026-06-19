<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicoKardexCierre extends Model
{
    protected $table = 'medico_kardex_cierres';

    protected $fillable = [
        'periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'generado_por',
        'cerrado_en',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'cerrado_en' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicoKardexCierreItem::class, 'cierre_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}
