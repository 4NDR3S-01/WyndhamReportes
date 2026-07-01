<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoPacienteVisita extends Model
{
    protected $table = 'medico_paciente_visitas';

    protected $fillable = ['paciente_id', 'anio', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'date', 'anio' => 'integer'];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(MedicoPaciente::class, 'paciente_id');
    }
}
