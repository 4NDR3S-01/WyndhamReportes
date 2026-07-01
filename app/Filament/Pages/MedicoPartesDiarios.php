<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use App\Models\Medicamento;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoParteMedicamento;
use App\Models\TipoCertificado;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MedicoPartesDiarios extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Partes diarios';

    protected static ?string $title = 'Partes diarios';

    protected static ?string $slug = 'medico/partes-diarios';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.medico-partes-diarios';

    // === FORMULARIO ===
    public ?int $editandoId = null;

    public ?int $pacienteId = null;

    public string $buscarPaciente = '';

    // Datos del paciente
    public string $fecha = '';

    public string $nombres = '';

    public ?int $edad = null;

    public ?int $area_id = null;

    public ?int $cargo_id = null;

    public string $tipoPaciente = 'colaborador';

    public ?string $habitacion = null;

    public ?string $turno = null;

    // Certificado
    public ?int $entidad_certificado_id = null;

    public ?int $tipo_certificado_id = null;

    public ?float $horas_certificado = null;

    public ?int $dias_certificado = null;

    public ?string $fecha_inicio_certificado = null;

    public ?string $fecha_fin_certificado = null;

    public ?string $medico_certifica = null;

    // Atención
    public ?int $causa_id = null;

    public ?int $diagnostico_id = null;

    public ?string $observacion = null;

    // Medicación (array de líneas: medicamento_id, cantidad)
    public array $medicamentos = [];

    // === FILTROS LISTA ===
    public string $buscar = '';

    public ?string $desde = null;

    public ?string $hasta = null;

    public ?int $areaFiltroId = null;

    public ?int $causaFiltroId = null;

    public ?string $tipoPacienteFiltro = null;

    public ?string $estadoFiltro = null;

    // === UI STATE ===
    public bool $mostrarSoloHoy = true;

    public bool $modalEliminarAbierto = false;

    public ?int $eliminandoId = null;

    public int $pagina = 1;

    public int $porPagina = 15;

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->desde = now()->startOfMonth()->toDateString();
        $this->hasta = now()->toDateString();
        $this->medicamentos = [['medicamento_id' => null, 'cantidad' => 1]];
    }

    public function updated(string $property): void
    {
        $resetPagina = ['buscar', 'desde', 'hasta', 'areaFiltroId', 'causaFiltroId', 'tipoPacienteFiltro', 'estadoFiltro', 'mostrarSoloHoy'];
        if (in_array($property, $resetPagina, true)) {
            $this->pagina = 1;
        }
    }

    public function irPagina(int $n): void
    {
        $this->pagina = max(1, min($n, $this->totalPaginas));
    }

    public function updatedPacienteId(): void
    {
        if (! $this->pacienteId) {
            return;
        }
        $p = MedicoPaciente::query()->with(['area', 'cargo'])->find($this->pacienteId);
        if (! $p) {
            return;
        }

        $this->nombres = $p->nombres;
        $this->edad = $p->edad;
        $this->area_id = $p->area_id;
        $this->cargo_id = $p->cargo_id;
        $this->tipoPaciente = $p->tipo;
    }

    public function updatedBuscarPaciente(): void
    {
        $this->pacienteId = null;
    }

    public function updatedTipoPaciente(): void
    {
        if ($this->tipoPaciente === 'huesped') {
            $this->area_id = null;
            $this->cargo_id = null;
        }
    }

    public function agregarMedicamento(): void
    {
        $this->medicamentos[] = ['medicamento_id' => null, 'cantidad' => 1];
    }

    public function quitarMedicamento(int $index): void
    {
        if (count($this->medicamentos) <= 1) {
            Notification::make()->title('Debe haber al menos un medicamento')->warning()->send();
            return;
        }
        unset($this->medicamentos[$index]);
        $this->medicamentos = array_values($this->medicamentos);
    }

    public function guardar(): void
    {
        $this->validate([
            'fecha'           => ['required', 'date'],
            'nombres'         => ['required', 'string', 'max:255'],
            'tipoPaciente'    => ['required', 'in:colaborador,huesped'],
            'habitacion'      => ['nullable', 'string', 'max:20', 'required_if:tipoPaciente,huesped'],
            'turno'           => ['nullable', 'in:mañana,tarde,noche'],
            'causa_id'        => ['required', 'integer', 'exists:causas,id'],
            'medicamentos.*.medicamento_id' => ['nullable', 'integer'],
            'medicamentos.*.cantidad' => ['required', 'numeric', 'min:0.01'],
        ], [
            'medicamentos.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'habitacion.required_if'      => 'La habitación es obligatoria para huéspedes.',
        ]);

        $meds = collect($this->medicamentos)
            ->filter(fn ($m) => ! empty($m['medicamento_id']))
            ->values();

        if ($meds->isEmpty()) {
            $this->addError('medicamentos.0.medicamento_id', 'Debe agregar al menos un medicamento.');
            return;
        }

        $parte = MedicoParteDiario::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'fecha'                    => $this->fecha,
                'nombres'                  => trim($this->nombres),
                'edad'                     => $this->edad,
                'area_id'                  => $this->area_id,
                'cargo_id'                 => $this->cargo_id,
                'tipo_paciente'            => $this->tipoPaciente,
                'habitacion'               => $this->habitacion,
                'turno'                    => $this->turno,
                'tipo_certificado_id'      => $this->tipo_certificado_id,
                'entidad_certificado_id'   => $this->entidad_certificado_id,
                'horas_certificado'        => $this->horas_certificado,
                'dias_certificado'         => $this->dias_certificado,
                'fecha_inicio_certificado' => $this->fecha_inicio_certificado,
                'fecha_fin_certificado'    => $this->fecha_fin_certificado,
                'medico_certifica'         => $this->medico_certifica,
                'causa_id'                 => $this->causa_id,
                'diagnostico_id'           => $this->diagnostico_id,
                'observacion'              => $this->observacion,
                'hash_unico'               => $this->editandoId
                    ? MedicoParteDiario::query()->whereKey($this->editandoId)->value('hash_unico')
                    : hash('sha256', implode('|', ['manual', microtime(true), $this->nombres])),
            ],
        );

        // Medicamentos → medico_parte_medicamentos
        $parte->medicamentos()->delete();
        foreach ($meds as $m) {
            $med = Medicamento::query()->find($m['medicamento_id']);
            MedicoParteMedicamento::query()->create([
                'parte_diario_id' => $parte->id,
                'medicamento_id'  => $m['medicamento_id'],
                'nombre_original' => $med?->nombre ?? 'Desconocido',
                'cantidad'        => (float) ($m['cantidad'] ?? 1),
            ]);
        }

        // Upsert paciente
        MedicoPaciente::query()->firstOrCreate(
            ['nombres' => trim($this->nombres)],
            [
                'edad'   => $this->edad,
                'area_id'=> $this->area_id,
                'cargo_id'=> $this->cargo_id,
                'tipo'   => $this->tipoPaciente,
                'activo' => true,
            ],
        );

        $this->limpiarFormulario();
        Notification::make()->title('Atención guardada correctamente')->success()->send();
    }

    public function editar(int $id): void
    {
        $p = MedicoParteDiario::query()->with('medicamentos')->findOrFail($id);
        $this->editandoId = $p->id;
        $this->fecha = $p->fecha?->format('Y-m-d') ?? now()->toDateString();
        $this->nombres = $p->nombres;
        $this->edad = $p->edad;
        $this->area_id = $p->area_id;
        $this->cargo_id = $p->cargo_id;
        $this->tipoPaciente = $p->tipo_paciente ?? 'colaborador';
        $this->habitacion = $p->habitacion;
        $this->turno = $p->turno;
        $this->entidad_certificado_id = $p->entidad_certificado_id;
        $this->tipo_certificado_id = $p->tipo_certificado_id;
        $this->horas_certificado = $p->horas_certificado;
        $this->dias_certificado = $p->dias_certificado;
        $this->fecha_inicio_certificado = $p->fecha_inicio_certificado?->format('Y-m-d');
        $this->fecha_fin_certificado = $p->fecha_fin_certificado?->format('Y-m-d');
        $this->medico_certifica = $p->medico_certifica;
        $this->causa_id = $p->causa_id;
        $this->diagnostico_id = $p->diagnostico_id;
        $this->observacion = $p->observacion;
        $this->medicamentos = $p->medicamentos->map(fn ($m) => [
            'medicamento_id' => $m->medicamento_id,
            'cantidad'       => $m->cantidad,
        ])->values()->all() ?: [['medicamento_id' => null, 'cantidad' => 1]];
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

        $parte = MedicoParteDiario::query()->findOrFail($this->eliminandoId);
        $parte->medicamentos()->delete();
        $parte->delete();

        $this->cancelarEliminar();
        Notification::make()->title('Atención eliminada')->success()->send();
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->pacienteId = null;
        $this->buscarPaciente = '';
        $this->fecha = now()->toDateString();
        $this->nombres = '';
        $this->edad = null;
        $this->area_id = null;
        $this->cargo_id = null;
        $this->tipoPaciente = 'colaborador';
        $this->habitacion = null;
        $this->turno = null;
        $this->entidad_certificado_id = null;
        $this->tipo_certificado_id = null;
        $this->horas_certificado = null;
        $this->dias_certificado = null;
        $this->fecha_inicio_certificado = null;
        $this->fecha_fin_certificado = null;
        $this->medico_certifica = null;
        $this->causa_id = null;
        $this->diagnostico_id = null;
        $this->observacion = null;
        $this->medicamentos = [['medicamento_id' => null, 'cantidad' => 1]];
    }

    public function toggleSoloHoy(): void
    {
        $this->mostrarSoloHoy = ! $this->mostrarSoloHoy;
        if ($this->mostrarSoloHoy) {
            $this->desde = now()->toDateString();
            $this->hasta = now()->toDateString();
        } else {
            $this->desde = now()->startOfMonth()->toDateString();
            $this->hasta = now()->toDateString();
        }
    }

    // === COMPUTED PROPERTIES ===

    public function getPacientesProperty(): Collection
    {
        return MedicoPaciente::query()->with(['area', 'cargo', 'examenes', 'visitas'])
            ->where('activo', true)
            ->when($this->buscarPaciente !== '', fn ($q) => $q->where('nombres', 'like', '%' . $this->buscarPaciente . '%'))
            ->orderBy('nombres')
            ->limit(20)
            ->get(['id', 'nombres', 'edad', 'area_id', 'cargo_id', 'tipo',
                   'patologias', 'vacunas', 'telefono', 'fecha_ingreso', 'antecedentes',
                   'fichas_anteriores']);
    }

    public function getPacienteSeleccionadoProperty(): ?MedicoPaciente
    {
        if (! $this->pacienteId) {
            return null;
        }
        return MedicoPaciente::query()->with(['area', 'cargo', 'examenes', 'visitas'])->find($this->pacienteId);
    }

    public function getExamenesPendientesProperty(): array
    {
        if (! $this->pacienteId || ! $this->pacienteSeleccionado) {
            return [];
        }

        $p = $this->pacienteSeleccionado;
        $pendientes = [];
        $hoy = now();

        $tipos = ['espirometria' => 'Espirometria', 'ecografia' => 'Ecografia',
                  'audiometria' => 'Audiometria', 'optometria' => 'Optometria'];

        foreach ($tipos as $tipo => $nombre) {
            $ex = $p->examenes->firstWhere('tipo', $tipo);
            if (! $ex || ! $ex->fecha) {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'pendiente', 'fecha' => null];
            } elseif ($ex->fecha->lt($hoy->copy()->subYear())) {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vencido', 'fecha' => $ex->fecha->format('d/m/Y')];
            } else {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vigente', 'fecha' => $ex->fecha->format('d/m/Y')];
            }
        }

        return $pendientes;
    }

    public function getVisitasAnualesProperty(): array
    {
        if (! $this->pacienteId || ! $this->pacienteSeleccionado) {
            return [];
        }

        $visitas = [];
        foreach (range(2021, 2026) as $anio) {
            $vis = $this->pacienteSeleccionado->visitas->firstWhere('anio', $anio);
            $visitas[$anio] = $vis?->fecha?->format('d/m/Y');
        }
        return $visitas;
    }

    public function getUltimasVisitasProperty(): Collection
    {
        if (! $this->pacienteId) {
            return collect();
        }

        return MedicoParteDiario::query()
            ->where('nombres', $this->pacienteSeleccionado?->nombres)
            ->latest('fecha')
            ->latest('id')
            ->limit(5)
            ->get(['fecha', 'causa_id', 'diagnostico_id'])
            ->map(function ($p) {
                $p->causa_nombre = $p->causa?->nombre;
                $p->diagnostico_nombre = $p->diagnostico?->nombre;
                return $p;
            });
    }

    public function getTieneCertificadoProperty(): bool
    {
        return $this->editandoId && $this->entidad_certificado_id;
    }

    public function getFiltrosActivosProperty(): int
    {
        $count = 0;
        if ($this->areaFiltroId) $count++;
        if ($this->causaFiltroId) $count++;
        if ($this->tipoPacienteFiltro) $count++;
        if ($this->estadoFiltro) $count++;
        if ($this->buscar !== '') $count++;
        if (! $this->mostrarSoloHoy) $count++;
        return $count;
    }

    public function getAreaCatalogProperty(): Collection
    {
        return Area::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getCargoCatalogProperty(): Collection
    {
        return Cargo::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getCausaCatalogProperty(): Collection
    {
        return Causa::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getDiagnosticoCatalogProperty(): Collection
    {
        return Diagnostico::query()->where('activo', true)->orderBy('nombre')->limit(200)->get(['id', 'nombre']);
    }

    public function getEntidadCatalogProperty(): Collection
    {
        return EntidadCertificado::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getTipoCertCatalogProperty(): Collection
    {
        return TipoCertificado::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    public function getMedicamentosCatalogProperty(): Collection
    {
        return Medicamento::query()->where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function queryPartes(): \Illuminate\Database\Eloquent\Builder
    {
        $query = MedicoParteDiario::query()->with(['area', 'cargo', 'causa', 'diagnostico',
            'entidadCertificado', 'tipoCertificado', 'medicamentos.medicamento']);

        if ($this->mostrarSoloHoy) {
            $query->whereDate('fecha', now()->toDateString());
        } else {
            $query->when($this->desde, fn ($q) => $q->whereDate('fecha', '>=', $this->desde))
                  ->when($this->hasta, fn ($q) => $q->whereDate('fecha', '<=', $this->hasta));
        }

        return $query->when($this->buscar !== '', fn ($q) => $q->where(function ($q) {
                $q->where('nombres', 'like', '%' . $this->buscar . '%')
                  ->orWhere('observacion', 'like', '%' . $this->buscar . '%')
                  ->orWhere('habitacion', 'like', '%' . $this->buscar . '%');
            }))
            ->when($this->areaFiltroId, fn ($q) => $q->where('area_id', $this->areaFiltroId))
            ->when($this->causaFiltroId, fn ($q) => $q->where('causa_id', $this->causaFiltroId))
            ->when($this->tipoPacienteFiltro, fn ($q) => $q->where('tipo_paciente', $this->tipoPacienteFiltro))
            ->when($this->estadoFiltro === 'con_certificado', fn ($q) => $q->whereNotNull('entidad_certificado_id'))
            ->when($this->estadoFiltro === 'sin_certificado', fn ($q) => $q->whereNull('entidad_certificado_id'))
            ->orderByDesc('fecha')
            ->orderByDesc('id');
    }

    public function getPartesProperty(): Collection
    {
        return $this->queryPartes()
            ->skip(($this->pagina - 1) * $this->porPagina)
            ->take($this->porPagina)
            ->get();
    }

    public function getTotalPartesProperty(): int
    {
        return (int) $this->queryPartes()->count();
    }

    public function getTotalPaginasProperty(): int
    {
        return (int) ceil($this->totalPartes / $this->porPagina);
    }

    public function getEstadisticasHoyProperty(): array
    {
        $hoy = now()->toDateString();
        $base = MedicoParteDiario::query()->whereDate('fecha', $hoy);

        return [
            'total'     => (int) (clone $base)->count(),
            'huespedes' => (int) (clone $base)->where('tipo_paciente', 'huesped')->count(),
            'colabs'    => (int) (clone $base)->where('tipo_paciente', 'colaborador')->count(),
            'conCert'   => (int) (clone $base)->whereNotNull('entidad_certificado_id')->count(),
            'areas'     => (int) (clone $base)->whereNotNull('area_id')->distinct('area_id')->count('area_id'),
        ];
    }
}
