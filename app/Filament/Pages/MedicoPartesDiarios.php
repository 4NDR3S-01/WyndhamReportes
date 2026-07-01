<?php

namespace App\Filament\Pages;

use App\Models\MedicoCatalogo;
use App\Models\MedicoPaciente;
use App\Models\MedicoParteDiario;
use App\Models\MedicoProducto;
use App\Services\Medico\InventarioMedicoService;
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

    public ?string $area = null;

    public ?string $cargo = null;

    public string $tipoPaciente = 'colaborador'; // 'colaborador' | 'huesped'

    public ?string $habitacion = null;

    public ?string $turno = null;

    // Certificado
    public ?string $certificados = 'SIN CERTIFICADO';

    public ?string $subsidio = null;

    public ?float $horas_certificado = null;

    public ?int $dias_certificado = null;

    public ?string $fecha_inicio_certificado = null;

    public ?string $fecha_fin_certificado = null;

    public ?string $medico_certifica = null;

    // Atención
    public ?string $causa = null;

    public ?string $diagnostico = null;

    public ?string $observacion = null;

    // Medicación (array de líneas)
    public array $medicamentos = [];

    // === FILTROS LISTA ===
    public string $buscar = '';

    public ?string $desde = null;

    public ?string $hasta = null;

    public ?string $areaFiltro = null;

    public ?string $causaFiltro = null;

    public ?string $tipoPacienteFiltro = null;

    public ?string $estadoFiltro = null; // 'con_certificado' | 'sin_certificado'

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
        $this->medicamentos = [['producto_id' => null, 'cantidad' => 1, 'observacion' => null]];
    }

    public function updated(string $property): void
    {
        $resetPagina = ['buscar', 'desde', 'hasta', 'areaFiltro', 'causaFiltro', 'tipoPacienteFiltro', 'estadoFiltro', 'mostrarSoloHoy'];
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
        $p = MedicoPaciente::query()->find($this->pacienteId);
        if (! $p) {
            return;
        }

        $this->nombres = $p->nombres;
        $this->edad = $p->edad;
        $this->area = $p->area;
        $this->cargo = $p->cargo;
        $this->tipoPaciente = $p->tipo;
    }

    public function updatedBuscarPaciente(): void
    {
        $this->pacienteId = null;
    }

    public function updatedTipoPaciente(): void
    {
        if ($this->tipoPaciente === 'huesped') {
            $this->area = 'HUÉSPED';
            $this->cargo = null;
        }
    }

    public function agregarMedicamento(): void
    {
        $this->medicamentos[] = ['producto_id' => null, 'cantidad' => 1, 'observacion' => null];
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

    public function guardar(InventarioMedicoService $inventario): void
    {
        $this->validate([
            'fecha'           => ['required', 'date'],
            'nombres'         => ['required', 'string', 'max:255'],
            'tipoPaciente'    => ['required', 'in:colaborador,huesped'],
            'habitacion'      => ['nullable', 'string', 'max:20', 'required_if:tipoPaciente,huesped'],
            'turno'           => ['nullable', 'in:mañana,tarde,noche'],
            'causa'           => ['required', 'string', 'max:255'],
            'diagnostico'     => ['nullable', 'string'],
            'medicamentos.*.producto_id' => ['required', 'integer', 'exists:medico_productos,id'],
            'medicamentos.*.cantidad'    => ['required', 'numeric', 'min:0.01'],
        ], [
            'medicamentos.*.producto_id.required' => 'Seleccione un producto en cada línea de medicación.',
            'medicamentos.*.cantidad.min'         => 'La cantidad debe ser mayor a 0.',
            'habitacion.required_if'              => 'La habitación es obligatoria para huéspedes.',
        ]);

        $archivo = $inventario->archivoSistema();
        $meds = collect($this->medicamentos)
            ->filter(fn ($m) => ! empty($m['producto_id']))
            ->values();

        if ($meds->isEmpty()) {
            $this->addError('medicamentos.0.producto_id', 'Debe agregar al menos un medicamento.');

            return;
        }

        $medNames = $meds->map(fn ($m) => MedicoProducto::query()->whereKey($m['producto_id'])->value('nombre'))->values();

        $parte = MedicoParteDiario::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'archivo_importado_id'   => $archivo->id,
                'fecha'                  => $this->fecha,
                'nombres'                => trim($this->nombres),
                'edad'                   => $this->edad,
                'area'                   => $this->area,
                'cargo'                  => $this->cargo,
                'tipo_paciente'          => $this->tipoPaciente,
                'habitacion'             => $this->habitacion,
                'turno'                  => $this->turno,
                'certificados'           => $this->certificados,
                'subsidio'               => $this->subsidio,
                'horas_certificado'      => $this->horas_certificado,
                'dias_certificado'       => $this->dias_certificado,
                'fecha_inicio_certificado' => $this->fecha_inicio_certificado,
                'fecha_fin_certificado'    => $this->fecha_fin_certificado,
                'medico_certifica'       => $this->medico_certifica,
                'causa'                  => $this->causa,
                'diagnostico'            => $this->diagnostico,
                'medicamento_1'          => $medNames->get(0),
                'medicamento_2'          => $medNames->get(1),
                'medicamento_3'          => $medNames->get(2),
                'observacion'            => $this->observacion,
                'hash_unico'             => $this->editandoId
                    ? MedicoParteDiario::query()->whereKey($this->editandoId)->value('hash_unico')
                    : hash('sha256', implode('|', ['manual', $archivo->id, microtime(true), $this->nombres])),
            ],
        );

        $inventario->sincronizarMedicacionParte($parte, $meds->map(function ($m) {
            $productoNombre = MedicoProducto::query()->whereKey($m['producto_id'])->value('nombre');

            return [
                'producto_id'    => $m['producto_id'],
                'nombre_original'=> $productoNombre,
                'cantidad'       => (float) ($m['cantidad'] ?? 1),
                'observacion'    => $m['observacion'] ?? null,
            ];
        })->all());

        // Upsert paciente
        MedicoPaciente::query()->firstOrCreate(
            ['nombres' => trim($this->nombres)],
            [
                'edad'       => $this->edad,
                'area'       => $this->area,
                'cargo'      => $this->cargo,
                'tipo'       => $this->tipoPaciente,
                'activo'     => true,
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
        $this->area = $p->area;
        $this->cargo = $p->cargo;
        $this->tipoPaciente = $p->tipo_paciente ?? 'colaborador';
        $this->habitacion = $p->habitacion;
        $this->turno = $p->turno;
        $this->certificados = $p->certificados;
        $this->subsidio = $p->subsidio;
        $this->horas_certificado = $p->horas_certificado;
        $this->dias_certificado = $p->dias_certificado;
        $this->fecha_inicio_certificado = $p->fecha_inicio_certificado?->format('Y-m-d');
        $this->fecha_fin_certificado = $p->fecha_fin_certificado?->format('Y-m-d');
        $this->medico_certifica = $p->medico_certifica;
        $this->causa = $p->causa;
        $this->diagnostico = $p->diagnostico;
        $this->observacion = $p->observacion;
        $this->medicamentos = $p->medicamentos->map(function ($m) {
            $productoId = $m->producto_id;
            if (! $productoId && $m->nombre_original) {
                $productoId = MedicoProducto::resolverPorNombre($m->nombre_original)?->id;
            }

            return [
                'producto_id' => $productoId,
                'cantidad'    => $m->cantidad,
                'observacion' => $m->observacion,
            ];
        })->values()->all() ?: [['producto_id' => null, 'cantidad' => 1, 'observacion' => null]];
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

    public function confirmarEliminar(InventarioMedicoService $inventario): void
    {
        if (! $this->eliminandoId) {
            return;
        }

        $parte = MedicoParteDiario::query()->findOrFail($this->eliminandoId);
        $inventario->sincronizarMedicacionParte($parte, []);
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
        $this->area = null;
        $this->cargo = null;
        $this->tipoPaciente = 'colaborador';
        $this->habitacion = null;
        $this->turno = null;
        $this->certificados = 'SIN CERTIFICADO';
        $this->subsidio = null;
        $this->horas_certificado = null;
        $this->dias_certificado = null;
        $this->fecha_inicio_certificado = null;
        $this->fecha_fin_certificado = null;
        $this->medico_certifica = null;
        $this->causa = null;
        $this->diagnostico = null;
        $this->observacion = null;
        $this->medicamentos = [['producto_id' => null, 'cantidad' => 1, 'observacion' => null]];
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

    public function catalogo(string $tipo): Collection
    {
        return MedicoCatalogo::query()->where('tipo', $tipo)->where('activo', true)->orderBy('nombre')->pluck('nombre');
    }

    // === COMPUTED PROPERTIES ===

    public function getPacientesProperty(): Collection
    {
        return MedicoPaciente::query()
            ->where('activo', true)
            ->when($this->buscarPaciente !== '', fn ($q) => $q->where('nombres', 'like', '%' . $this->buscarPaciente . '%'))
            ->orderBy('nombres')
            ->limit(20)
            ->get(['id', 'nombres', 'edad', 'area', 'cargo', 'tipo',
                   'patologias', 'vacunas', 'telefono', 'fecha_ingreso',
                   'espirometria', 'ecografia', 'audiometria', 'optometria',
                   'fichas_anteriores',
                   'visita_2021', 'visita_2022', 'visita_2023',
                   'visita_2024', 'visita_2025', 'visita_2026']);
    }

    public function getPacienteSeleccionadoProperty(): ?MedicoPaciente
    {
        if (! $this->pacienteId) {
            return null;
        }

        return MedicoPaciente::query()->find($this->pacienteId);
    }

    public function getExamenesPendientesProperty(): array
    {
        if (! $this->pacienteId) {
            return [];
        }

        $p = $this->pacienteSeleccionado;
        if (! $p) return [];

        $pendientes = [];
        $hoy = now();

        // Check if exams are due (more than 1 year old or never done)
        $examenes = [
            'espirometria' => 'Espirometria',
            'ecografia'    => 'Ecografia',
            'audiometria'  => 'Audiometria',
            'optometria'   => 'Optometria',
        ];

        foreach ($examenes as $campo => $nombre) {
            $fecha = $p->{$campo};
            if (! $fecha) {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'pendiente', 'fecha' => null];
            } elseif ($fecha->lt($hoy->copy()->subYear())) {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vencido', 'fecha' => $fecha->format('d/m/Y')];
            } else {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vigente', 'fecha' => $fecha->format('d/m/Y')];
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
            $campo = "visita_{$anio}";
            $fecha = $this->pacienteSeleccionado->{$campo};
            $visitas[$anio] = $fecha?->format('d/m/Y');
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
            ->get(['fecha', 'causa', 'diagnostico']);
    }

    public function getTieneCertificadoProperty(): bool
    {
        return $this->editandoId && $this->certificados && $this->certificados !== 'SIN CERTIFICADO';
    }

    public function getFiltrosActivosProperty(): int
    {
        $count = 0;
        if ($this->areaFiltro) $count++;
        if ($this->causaFiltro) $count++;
        if ($this->tipoPacienteFiltro) $count++;
        if ($this->estadoFiltro) $count++;
        if ($this->buscar !== '') $count++;
        if (! $this->mostrarSoloHoy) $count++;
        return $count;
    }

    public function getProductosProperty(): Collection
    {
        return MedicoProducto::query()
            ->where('activo', true)
            ->where('tipo', 'medicina')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'stock_minimo'])
            ->map(function ($p) {
                $saldo = $p->saldoActual();
                $bajo = $p->stock_minimo > 0 && $saldo <= $p->stock_minimo;

                return (object) [
                    'id'        => $p->id,
                    'nombre'    => $p->nombre,
                    'stock'     => $saldo,
                    'minimo'    => $p->stock_minimo,
                    'bajoStock' => $bajo,
                ];
            });
    }

    private function queryPartes(): \Illuminate\Database\Eloquent\Builder
    {
        $query = MedicoParteDiario::query()->with('medicamentos.producto');

        if ($this->mostrarSoloHoy) {
            $query->whereDate('fecha', now()->toDateString());
        } else {
            $query->when($this->desde, fn ($q) => $q->whereDate('fecha', '>=', $this->desde))
                  ->when($this->hasta, fn ($q) => $q->whereDate('fecha', '<=', $this->hasta));
        }

        return $query->when($this->buscar !== '', fn ($q) => $q->where('nombres', 'like', '%' . $this->buscar . '%')
            ->orWhere('diagnostico', 'like', '%' . $this->buscar . '%')
            ->orWhere('habitacion', 'like', '%' . $this->buscar . '%'))
            ->when($this->areaFiltro, fn ($q) => $q->where('area', $this->areaFiltro))
            ->when($this->causaFiltro, fn ($q) => $q->where('causa', $this->causaFiltro))
            ->when($this->tipoPacienteFiltro, fn ($q) => $q->where('tipo_paciente', $this->tipoPacienteFiltro))
            ->when($this->estadoFiltro === 'con_certificado', fn ($q) => $q->whereNotNull('certificados')->where('certificados', '!=', 'SIN CERTIFICADO')->where('certificados', '!=', ''))
            ->when($this->estadoFiltro === 'sin_certificado', fn ($q) => $q->where(function ($q) {
                $q->whereNull('certificados')->orWhere('certificados', '')->orWhere('certificados', 'SIN CERTIFICADO');
            }))
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
            'conCert'   => (int) (clone $base)->whereNotNull('certificados')->where('certificados', '!=', '')->where('certificados', '!=', 'SIN CERTIFICADO')->count(),
            'areas'     => (int) (clone $base)->whereNotNull('area')->where('area', '!=', '')->distinct('area')->count('area'),
        ];
    }
}