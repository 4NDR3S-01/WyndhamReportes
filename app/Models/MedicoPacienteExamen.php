<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicoPacienteExamen extends Model
{
    protected $table = 'medico_paciente_examenes';

    protected $fillable = ['paciente_id', 'tipo', 'fecha'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(MedicoPaciente::class, 'paciente_id');
    }
}
