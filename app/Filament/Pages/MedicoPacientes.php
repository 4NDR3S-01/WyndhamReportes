<?php

namespace App\Filament\Pages;

use App\Models\MedicoPaciente;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MedicoPacientes extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Pacientes y colaboradores';

    protected static ?string $title = 'Pacientes y colaboradores';

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

    public ?string $area = null;

    public ?string $cargo = null;

    public ?string $fecha_ingreso = null;

    public ?string $patologias = null;

    public ?string $vacunas = null;

    public ?string $fichas_anteriores = null;

    public ?string $antecedentes = null;

    public ?string $telefono = null;

    // Exámenes ocupacionales
    public ?string $espirometria = null;

    public ?string $ecografia = null;

    public ?string $audiometria = null;

    public ?string $optometria = null;

    // Visitas anuales
    public ?string $visita_2021 = null;

    public ?string $visita_2022 = null;

    public ?string $visita_2023 = null;

    public ?string $visita_2024 = null;

    public ?string $visita_2025 = null;

    public ?string $visita_2026 = null;

    public string $tipo = 'colaborador';

    public bool $activo = true;

    public ?string $observaciones = null;

    // === FILTERS ===
    public string $buscar = '';

    public ?string $areaFiltro = null;

    public ?string $tipoFiltro = null;

    public ?string $estadoFiltro = null; // 'activo' | 'inactivo' | null (todos)

    // === PAGINATION ===
    public int $pagina = 1;

    public int $porPagina = 20;

    public function mount(): void
    {
        //
    }

    public function updated(string $property): void
    {
        $resetPagina = ['buscar', 'areaFiltro', 'tipoFiltro', 'estadoFiltro'];
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
        $validated = $this->validate([
            'nombres'   => ['required', 'string', 'max:255'],
            'cedula'    => ['nullable', 'string', 'max:20'],
            'edad'      => ['nullable', 'integer', 'min:0', 'max:150'],
            'tipo'      => ['required', 'in:colaborador,aspirante,externo,paciente,huesped'],
            'telefono'  => ['nullable', 'string', 'max:50'],
        ]);

        MedicoPaciente::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'cedula'             => $this->cedula !== '' ? $this->cedula : null,
                'nombres'            => trim($this->nombres),
                'edad'               => $this->edad,
                'area'               => $this->area,
                'cargo'              => $this->cargo,
                'fecha_ingreso'      => $this->fecha_ingreso,
                'patologias'         => $this->patologias,
                'vacunas'            => $this->vacunas,
                'fichas_anteriores'  => $this->fichas_anteriores,
                'antecedentes'       => $this->antecedentes,
                'telefono'           => $this->telefono,
                'espirometria'       => $this->espirometria,
                'ecografia'          => $this->ecografia,
                'audiometria'        => $this->audiometria,
                'optometria'         => $this->optometria,
                'visita_2021'        => $this->visita_2021,
                'visita_2022'        => $this->visita_2022,
                'visita_2023'        => $this->visita_2023,
                'visita_2024'        => $this->visita_2024,
                'visita_2025'        => $this->visita_2025,
                'visita_2026'        => $this->visita_2026,
                'tipo'               => $this->tipo,
                'activo'             => $this->activo,
                'observaciones'      => $this->observaciones,
            ],
        );

        $this->cerrarModal();
        Notification::make()->title('Paciente guardado correctamente')->success()->send();
    }

    public function editar(int $id): void
    {
        $p = MedicoPaciente::query()->findOrFail($id);

        $this->editandoId        = $p->id;
        $this->cedula            = $p->cedula ?? '';
        $this->nombres           = $p->nombres;
        $this->edad              = $p->edad;
        $this->area              = $p->area;
        $this->cargo             = $p->cargo;
        $this->fecha_ingreso     = $p->fecha_ingreso?->format('Y-m-d');
        $this->patologias        = $p->patologias;
        $this->vacunas           = $p->vacunas;
        $this->fichas_anteriores = $p->fichas_anteriores;
        $this->antecedentes      = $p->antecedentes;
        $this->telefono          = $p->telefono;
        $this->espirometria      = $p->espirometria?->format('Y-m-d');
        $this->ecografia         = $p->ecografia?->format('Y-m-d');
        $this->audiometria       = $p->audiometria?->format('Y-m-d');
        $this->optometria        = $p->optometria?->format('Y-m-d');
        $this->visita_2021       = $p->visita_2021?->format('Y-m-d');
        $this->visita_2022       = $p->visita_2022?->format('Y-m-d');
        $this->visita_2023       = $p->visita_2023?->format('Y-m-d');
        $this->visita_2024       = $p->visita_2024?->format('Y-m-d');
        $this->visita_2025       = $p->visita_2025?->format('Y-m-d');
        $this->visita_2026       = $p->visita_2026?->format('Y-m-d');
        $this->tipo              = $p->tipo;
        $this->activo            = $p->activo;
        $this->observaciones     = $p->observaciones;
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
        $this->area              = null;
        $this->cargo             = null;
        $this->fecha_ingreso     = null;
        $this->patologias        = null;
        $this->vacunas           = null;
        $this->fichas_anteriores = null;
        $this->antecedentes      = null;
        $this->telefono          = null;
        $this->espirometria      = null;
        $this->ecografia         = null;
        $this->audiometria       = null;
        $this->optometria        = null;
        $this->visita_2021       = null;
        $this->visita_2022       = null;
        $this->visita_2023       = null;
        $this->visita_2024       = null;
        $this->visita_2025       = null;
        $this->visita_2026       = null;
        $this->tipo              = 'colaborador';
        $this->activo            = true;
        $this->observaciones     = null;
    }

    // === COMPUTED PROPERTIES ===

    private function queryPacientes(): \Illuminate\Database\Eloquent\Builder
    {
        return MedicoPaciente::query()
            ->when($this->buscar !== '', function ($q) {
                $q->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->buscar . '%')
                      ->orWhere('cedula', 'like', '%' . $this->buscar . '%')
                      ->orWhere('area', 'like', '%' . $this->buscar . '%')
                      ->orWhere('cargo', 'like', '%' . $this->buscar . '%');
                });
            })
            ->when($this->areaFiltro, fn ($q) => $q->where('area', $this->areaFiltro))
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

        return MedicoPaciente::query()->find($this->pacienteDetalleId);
    }

    public function getExamenesEstadoProperty(): array
    {
        $p = $this->pacienteDetalle;
        if (! $p) return [];

        $hoy = now();
        $result = [];
        $examenes = [
            'espirometria' => 'Espirometría',
            'ecografia'    => 'Ecografía',
            'audiometria'  => 'Audiometría',
            'optometria'   => 'Optometría',
        ];

        foreach ($examenes as $campo => $nombre) {
            $fecha = $p->{$campo};
            if (! $fecha) {
                $result[] = ['nombre' => $nombre, 'estado' => 'pendiente', 'fecha' => null];
            } elseif ($fecha->lt($hoy->copy()->subYear())) {
                $result[] = ['nombre' => $nombre, 'estado' => 'vencido', 'fecha' => $fecha->format('d/m/Y')];
            } else {
                $result[] = ['nombre' => $nombre, 'estado' => 'vigente', 'fecha' => $fecha->format('d/m/Y')];
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
            'conExamenes'  => (int) (clone $base)->where(function ($q) {
                $q->whereNotNull('espirometria')
                  ->orWhereNotNull('ecografia')
                  ->orWhereNotNull('audiometria')
                  ->orWhereNotNull('optometria');
            })->count(),
        ];
    }

    public function getAreasUnicasProperty(): Collection
    {
        return MedicoPaciente::query()
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct('area')
            ->orderBy('area')
            ->pluck('area');
    }

    public function getFiltrosActivosProperty(): int
    {
        $count = 0;
        if ($this->areaFiltro) $count++;
        if ($this->tipoFiltro) $count++;
        if ($this->estadoFiltro) $count++;
        if ($this->buscar !== '') $count++;
        return $count;
    }
}
