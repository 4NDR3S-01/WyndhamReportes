<?php

namespace App\Filament\Pages;

use App\Models\MedicoPaciente;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MedicoPacientes extends Page
{
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Pacientes y colaboradores';
    protected static ?string $title = 'Pacientes y colaboradores';
    protected static ?string $slug = 'medico/pacientes';
    protected static ?int $navigationSort = 3;
    protected string $view = 'filament.pages.medico-pacientes';

    public ?int $editandoId = null;
    public string $buscar = '';
    public ?string $cedula = null;
    public string $nombres = '';
    public ?int $edad = null;
    public ?string $area = null;
    public ?string $cargo = null;
    public ?string $fecha_ingreso = null;
    public ?string $patologias = null;
    public string $tipo = 'colaborador';
    public bool $activo = true;
    public ?string $observaciones = null;

    public function guardar(): void
    {
        $this->validate(['nombres' => ['required', 'string', 'max:255']]);

        MedicoPaciente::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'cedula' => $this->cedula,
                'nombres' => trim($this->nombres),
                'edad' => $this->edad,
                'area' => $this->area,
                'cargo' => $this->cargo,
                'fecha_ingreso' => $this->fecha_ingreso,
                'patologias' => $this->patologias,
                'tipo' => $this->tipo,
                'activo' => $this->activo,
                'observaciones' => $this->observaciones,
            ],
        );

        $this->limpiarFormulario();
        Notification::make()->title('Paciente guardado')->success()->send();
    }

    public function editar(int $id): void
    {
        $p = MedicoPaciente::query()->findOrFail($id);
        $this->editandoId = $p->id;
        $this->cedula = $p->cedula;
        $this->nombres = $p->nombres;
        $this->edad = $p->edad;
        $this->area = $p->area;
        $this->cargo = $p->cargo;
        $this->fecha_ingreso = $p->fecha_ingreso?->format('Y-m-d');
        $this->patologias = $p->patologias;
        $this->tipo = $p->tipo;
        $this->activo = $p->activo;
        $this->observaciones = $p->observaciones;
    }

    public function alternar(int $id): void
    {
        $p = MedicoPaciente::query()->findOrFail($id);
        $p->update(['activo' => ! $p->activo]);
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->cedula = null;
        $this->nombres = '';
        $this->edad = null;
        $this->area = null;
        $this->cargo = null;
        $this->fecha_ingreso = null;
        $this->patologias = null;
        $this->tipo = 'colaborador';
        $this->activo = true;
        $this->observaciones = null;
    }

    public function getPacientesProperty(): Collection
    {
        return MedicoPaciente::query()
            ->when($this->buscar !== '', fn ($q) => $q->where('nombres', 'like', '%' . $this->buscar . '%')->orWhere('cedula', 'like', '%' . $this->buscar . '%'))
            ->orderByDesc('activo')
            ->orderBy('nombres')
            ->limit(80)
            ->get();
    }
}
