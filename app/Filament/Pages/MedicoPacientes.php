<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\MedicoPaciente;
use App\Models\MedicoPacienteExamen;
use App\Models\MedicoPacienteVisita;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MedicoPacientes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $title = 'Pacientes';

    protected ?string $heading = '';

    protected static ?string $slug = 'medico/pacientes';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.medico-pacientes';

    // === MODAL STATE ===
    public bool $modalAbierto = false;

    public bool $modalEliminarAbierto = false;

    public ?int $eliminandoId = null;

    // === FORM STATE ===
    public ?int $editandoId = null;

    public ?int $pacienteDetalleId = null;

    public string $cedula = '';

    public string $nombres = '';

    public ?int $edad = null;

    public ?int $area_id = null;

    public ?int $cargo_id = null;

    public ?string $fecha_ingreso = null;

    public ?string $patologias = null;

    public ?string $vacunas = null;

    public ?string $fichas_anteriores = null;

    public ?string $antecedentes = null;

    public ?string $telefono = null;

    // Exámenes ocupacionales como fechas por tipo
    public array $examenesFechas = [
        'espirometria' => null,
        'ecografia' => null,
        'audiometria' => null,
        'optometria' => null,
    ];

    // Visitas anuales como fechas por año
    public array $visitasFechas = [
        2021 => null,
        2022 => null,
        2023 => null,
        2024 => null,
        2025 => null,
        2026 => null,
    ];

    public string $tipo = 'colaborador';

    public bool $activo = true;

    public ?string $observaciones = null;

    // === FILTERS ===
    public string $buscar = '';

    public ?int $areaFiltroId = null;

    public ?string $tipoFiltro = null;

    public ?string $estadoFiltro = null;

    // === PAGINATION ===
    public int $pagina = 1;

    public int $porPagina = 20;

    public function mount(): void
    {
        //
    }

    public function updated(string $property): void
    {
        $resetPagina = ['buscar', 'areaFiltroId', 'tipoFiltro', 'estadoFiltro'];
        if (in_array($property, $resetPagina, true)) {
            $this->pagina = 1;
        }
    }

    public function irPagina(int $n): void
    {
        $this->pagina = max(1, min($n, $this->totalPaginas));
    }

    // === MODAL ===

    public function abrirModal(?int $id = null): void
    {
        if ($id) {
            $this->editar($id);
        } else {
            $this->limpiarFormulario();
        }
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->limpiarFormulario();
    }

    // === CRUD ===

    public function verDetalle(int $id): void
    {
        $this->pacienteDetalleId = $this->pacienteDetalleId === $id ? null : $id;
    }

    public function guardar(): void
    {
        $this->validate([
            'nombres' => ['required', 'string', 'max:255'],
            'cedula'  => ['nullable', 'string', 'max:20'],
            'edad'    => ['nullable', 'integer', 'min:0', 'max:150'],
            'tipo'    => ['required', 'in:colaborador,aspirante,externo,paciente,huesped'],
            'telefono'=> ['nullable', 'string', 'max:50'],
        ]);

        $paciente = MedicoPaciente::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'cedula'            => $this->cedula !== '' ? $this->cedula : null,
                'nombres'           => trim($this->nombres),
                'edad'              => $this->edad,
                'area_id'           => $this->area_id,
                'cargo_id'          => $this->cargo_id,
                'fecha_ingreso'     => $this->fecha_ingreso,
                'patologias'        => $this->patologias,
                'vacunas'           => $this->vacunas,
                'fichas_anteriores' => $this->fichas_anteriores,
                'antecedentes'      => $this->antecedentes,
                'telefono'          => $this->telefono,
                'tipo'              => $this->tipo,
                'activo'            => $this->activo,
                'observaciones'     => $this->observaciones,
            ],
        );

        // Exámenes ocupacionales
        $tiposExamen = ['espirometria', 'ecografia', 'audiometria', 'optometria'];
        foreach ($tiposExamen as $tipo) {
            $fecha = $this->examenesFechas[$tipo] ?? null;
            if ($fecha) {
                MedicoPacienteExamen::query()->updateOrCreate(
                    ['paciente_id' => $paciente->id, 'tipo' => $tipo],
                    ['fecha' => $fecha],
                );
            } else {
                MedicoPacienteExamen::query()->where('paciente_id', $paciente->id)->where('tipo', $tipo)->delete();
            }
        }

        // Visitas anuales
        foreach ($this->visitasFechas as $anio => $fecha) {
            if ($fecha) {
                MedicoPacienteVisita::query()->updateOrCreate(
                    ['paciente_id' => $paciente->id, 'anio' => $anio],
                    ['fecha' => $fecha],
                );
            } else {
                MedicoPacienteVisita::query()->where('paciente_id', $paciente->id)->where('anio', $anio)->delete();
            }
        }

        $this->cerrarModal();
        Notification::make()->title('Paciente guardado correctamente')->success()->send();
    }

    public function editar(int $id): void
    {
        $p = MedicoPaciente::query()->with(['examenes', 'visitas'])->findOrFail($id);

        $this->editandoId        = $p->id;
        $this->cedula            = $p->cedula ?? '';
        $this->nombres           = $p->nombres;
        $this->edad              = $p->edad;
        $this->area_id           = $p->area_id;
        $this->cargo_id          = $p->cargo_id;
        $this->fecha_ingreso     = $p->fecha_ingreso?->format('Y-m-d');
        $this->patologias        = $p->patologias;
        $this->vacunas           = $p->vacunas;
        $this->fichas_anteriores = $p->fichas_anteriores;
        $this->antecedentes      = $p->antecedentes;
        $this->telefono          = $p->telefono;

        // Exámenes desde relación
        $this->examenesFechas = [
            'espirometria' => null,
            'ecografia'    => null,
            'audiometria'  => null,
            'optometria'   => null,
        ];
        foreach ($p->examenes as $ex) {
            $this->examenesFechas[$ex->tipo] = $ex->fecha?->format('Y-m-d');
        }

        // Visitas desde relación
        $this->visitasFechas = array_fill_keys(range(2021, 2026), null);
        foreach ($p->visitas as $vis) {
            if ($vis->anio >= 2021 && $vis->anio <= 2026) {
                $this->visitasFechas[$vis->anio] = $vis->fecha?->format('Y-m-d');
            }
        }

        $this->tipo          = $p->tipo;
        $this->activo        = $p->activo;
        $this->observaciones = $p->observaciones;
    }

    public function alternar(int $id): void
    {
        $p = MedicoPaciente::query()->findOrFail($id);
        $p->update(['activo' => ! $p->activo]);
        Notification::make()
            ->title($p->activo ? 'Paciente activado' : 'Paciente desactivado')
            ->success()
            ->send();
    }

    public function solicitarEliminar(int $id): void
    {
        $this->eliminandoId = $id;
        $this->modalEliminarAbierto = true;
    }

    public function cancelarEliminar(): void
    {
        $this->modalEliminarAbierto = false;
        $this->eliminandoId = null;
    }

    public function confirmarEliminar(): void
    {
        if (! $this->eliminandoId) {
            return;
        }

        $paciente = MedicoPaciente::query()->findOrFail($this->eliminandoId);
        $nombre = $paciente->nombres;
        $paciente->delete();

        $this->cancelarEliminar();
        Notification::make()->title("«{$nombre}» eliminado correctamente")->success()->send();
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId        = null;
        $this->cedula            = '';
        $this->nombres           = '';
        $this->edad              = null;
        $this->area_id           = null;
        $this->cargo_id          = null;
        $this->fecha_ingreso     = null;
        $this->patologias        = null;
        $this->vacunas           = null;
        $this->fichas_anteriores = null;
        $this->antecedentes      = null;
        $this->telefono          = null;
        $this->examenesFechas = [
            'espirometria' => null,
            'ecografia'    => null,
            'audiometria'  => null,
            'optometria'   => null,
        ];
        $this->visitasFechas = array_fill_keys(range(2021, 2026), null);
        $this->tipo              = 'colaborador';
        $this->activo            = true;
        $this->observaciones     = null;
    }

    // === COMPUTED PROPERTIES ===

    private function queryPacientes(): \Illuminate\Database\Eloquent\Builder
    {
        return MedicoPaciente::query()->with(['area', 'cargo', 'examenes', 'visitas'])
            ->when($this->buscar !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->buscar . '%')
                      ->orWhere('cedula', 'like', '%' . $this->buscar . '%')
                      ->orWhereHas('area', fn ($a) => $a->where('nombre', 'like', '%' . $this->buscar . '%'))
                      ->orWhereHas('cargo', fn ($c) => $c->where('nombre', 'like', '%' . $this->buscar . '%'));
                });
            })
            ->when($this->areaFiltroId, fn ($q) => $q->where('area_id', $this->areaFiltroId))
            ->when($this->tipoFiltro, fn ($q) => $q->where('tipo', $this->tipoFiltro))
            ->when($this->estadoFiltro === 'activo', fn ($q) => $q->where('activo', true))
            ->when($this->estadoFiltro === 'inactivo', fn ($q) => $q->where('activo', false))
            ->orderByDesc('activo')
            ->orderBy('nombres');
    }

    public function getPacientesProperty(): Collection
    {
        return $this->queryPacientes()
            ->skip(($this->pagina - 1) * $this->porPagina)
            ->take($this->porPagina)
            ->get();
    }

    public function getTotalPacientesProperty(): int
    {
        return (int) $this->queryPacientes()->count();
    }

    public function getTotalPaginasProperty(): int
    {
        return (int) ceil($this->totalPacientes / $this->porPagina);
    }

    public function getPacienteDetalleProperty(): ?MedicoPaciente
    {
        if (! $this->pacienteDetalleId) {
            return null;
        }
        return MedicoPaciente::query()->with(['area', 'cargo', 'examenes', 'visitas'])->find($this->pacienteDetalleId);
    }

    public function getExamenesEstadoProperty(): array
    {
        $p = $this->pacienteDetalle;
        if (! $p) return [];

        $hoy = now();
        $result = [];
        $tipos = ['espirometria' => 'Espirometría', 'ecografia' => 'Ecografía',
                  'audiometria' => 'Audiometría', 'optometria' => 'Optometría'];

        foreach ($tipos as $tipo => $nombre) {
            $examen = $p->examenes->firstWhere('tipo', $tipo);
            if (! $examen || ! $examen->fecha) {
                $result[] = ['nombre' => $nombre, 'estado' => 'pendiente', 'fecha' => null];
            } elseif ($examen->fecha->lt($hoy->copy()->subYear())) {
                $result[] = ['nombre' => $nombre, 'estado' => 'vencido', 'fecha' => $examen->fecha->format('d/m/Y')];
            } else {
                $result[] = ['nombre' => $nombre, 'estado' => 'vigente', 'fecha' => $examen->fecha->format('d/m/Y')];
            }
        }
        return $result;
    }

    public function getEstadisticasProperty(): array
    {
        $base = MedicoPaciente::query();

        return [
            'total'        => (int) (clone $base)->count(),
            'activos'      => (int) (clone $base)->where('activo', true)->count(),
            'colaboradores'=> (int) (clone $base)->where('tipo', 'colaborador')->count(),
            'conPatologia' => (int) (clone $base)->whereNotNull('patologias')->where('patologias', '!=', '')->count(),
            'conTelefono'  => (int) (clone $base)->whereNotNull('telefono')->where('telefono', '!=', '')->count(),
            'conExamenes'  => (int) MedicoPacienteExamen::query()->distinct('paciente_id')->count('paciente_id'),
        ];
    }

    public function getAreasParaFiltroProperty(): Collection
    {
        return Area::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getAreasParaSelectProperty(): Collection
    {
        return Area::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getCargosParaSelectProperty(): Collection
    {
        return Cargo::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getFiltrosActivosProperty(): int
    {
        $count = 0;
        if ($this->areaFiltroId) $count++;
        if ($this->tipoFiltro) $count++;
        if ($this->estadoFiltro) $count++;
        if ($this->buscar !== '') $count++;
        return $count;
    }
}
