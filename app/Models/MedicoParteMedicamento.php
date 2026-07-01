<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoParteMedicamento extends Model
{
    protected $table = 'medico_parte_medicamentos';

    protected $fillable = [
        'parte_diario_id',
        'medicamento_id',
        'nombre_original',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
        ];
    }

    public function parteDiario(): BelongsTo
    {
        return $this->belongsTo(MedicoParteDiario::class, 'parte_diario_id');
    }

    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(Medicamento::class, 'medicamento_id');
    }
}
