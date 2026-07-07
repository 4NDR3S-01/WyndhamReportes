<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use App\Models\Medicamento;
use App\Models\MedicoPaciente;
use App\Models\MedicoPacienteExamen;
use App\Models\MedicoPacienteVisita;
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

    protected ?string $heading = '';

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

    public string $cedula = '';

    public ?int $edad = null;

    public ?int $area_id = null;

    public ?int $cargo_id = null;

    public string $tipoPaciente = 'colaborador';

    public ?string $habitacion = null;

    public ?string $turno = null;

    // === MODAL ATENCIÓN ===
    public bool $modalAtencionAbierto = false;

    public string $tipoAtencion = 'general';

    // === QUICK-CREATE MODAL (form completo de paciente) ===
    public bool $quickCreateAbierto = false;

    public string $qNombres = '';
    public string $qCedula = '';
    public string $qTipo = 'colaborador';
    public ?int $qEdad = null;
    public ?int $qAreaId = null;
    public ?int $qCargoId = null;
    public ?string $qTelefono = null;
    public ?string $qFechaIngreso = null;
    public bool $qActivo = true;
    public ?string $qPatologias = null;
    public ?string $qVacunas = null;
    public ?string $qFichasAnteriores = null;
    public ?string $qAntecedentes = null;
    public ?string $qObservaciones = null;
    public array $qExamenesFechas = [
        'espirometria' => null,
        'ecografia'    => null,
        'audiometria'  => null,
        'optometria'   => null,
    ];
    public array $qVisitasFechas = [
        2021 => null, 2022 => null, 2023 => null,
        2024 => null, 2025 => null, 2026 => null,
    ];

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
        $this->cedula = $p->cedula ?? '';
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

    // === MODAL ATENCIÓN ===

    public function abrirModalAtencion(): void
    {
        $this->limpiarFormulario();
        $this->modalAtencionAbierto = true;
    }

    public function cerrarModalAtencion(): void
    {
        $this->modalAtencionAbierto = false;
        $this->limpiarFormulario();
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

    // === QUICK-CREATE PACIENTE (form completo) ===

    public function abrirQuickCreate(): void
    {
        // Pre-rellenar el nombre con lo que el usuario ya escribió en el buscador
        $this->qNombres    = mb_strtoupper(trim($this->buscarPaciente ?: ''));
        $this->qCedula     = '';
        $this->qTipo       = 'colaborador';
        $this->qEdad       = null;
        $this->qAreaId     = null;
        $this->qCargoId    = null;
        $this->qTelefono   = null;
        $this->qFechaIngreso = null;
        $this->qActivo     = true;
        $this->qPatologias = null;
        $this->qVacunas    = null;
        $this->qFichasAnteriores = null;
        $this->qAntecedentes = null;
        $this->qObservaciones = null;
        $this->qExamenesFechas = array_fill_keys(['espirometria','ecografia','audiometria','optometria'], null);
        $this->qVisitasFechas  = array_fill_keys(range(2021, 2026), null);
        $this->quickCreateAbierto = true;
        $this->buscarPaciente = '';
    }

    public function cerrarQuickCreate(): void
    {
        $this->quickCreateAbierto = false;
    }

    public function quickGuardarPaciente(): void
    {
        $this->validate([
            'qNombres' => ['required', 'string', 'max:255'],
            'qTipo'    => ['required', 'in:colaborador,aspirante,externo,paciente,huesped'],
            'qCedula'  => ['nullable', 'string', 'max:20'],
            'qEdad'    => ['nullable', 'integer', 'min:0', 'max:150'],
        ]);

        $nombreNormalizado = mb_strtoupper(trim($this->qNombres));

        $paciente = MedicoPaciente::query()->create([
            'cedula'            => $this->qCedula !== '' ? $this->qCedula : null,
            'nombres'           => $nombreNormalizado,
            'tipo'              => $this->qTipo,
            'edad'              => $this->qEdad,
            'area_id'           => $this->qAreaId,
            'cargo_id'          => $this->qCargoId,
            'telefono'          => $this->qTelefono,
            'fecha_ingreso'     => $this->qFechaIngreso,
            'patologias'        => $this->qPatologias,
            'vacunas'           => $this->qVacunas,
            'fichas_anteriores' => $this->qFichasAnteriores,
            'antecedentes'      => $this->qAntecedentes,
            'observaciones'     => $this->qObservaciones,
            'activo'            => $this->qActivo,
        ]);

        // Guardar exámenes ocupacionales
        foreach ($this->qExamenesFechas as $tipo => $fecha) {
            if ($fecha) {
                MedicoPacienteExamen::query()->updateOrCreate(
                    ['paciente_id' => $paciente->id, 'tipo' => $tipo],
                    ['fecha' => $fecha],
                );
            }
        }

        // Guardar visitas anuales
        foreach ($this->qVisitasFechas as $anio => $fecha) {
            if ($fecha) {
                MedicoPacienteVisita::query()->updateOrCreate(
                    ['paciente_id' => $paciente->id, 'anio' => $anio],
                    ['fecha' => $fecha],
                );
            }
        }

        // Autoseleccionar en el formulario de atención
        $this->pacienteId  = $paciente->id;
        $this->nombres     = $paciente->nombres;
        $this->cedula      = $paciente->cedula ?? '';
        $this->tipoPaciente = in_array($paciente->tipo, ['huesped']) ? 'huesped' : 'colaborador';
        $this->edad        = $paciente->edad;
        $this->area_id     = $paciente->area_id;
        $this->cargo_id    = $paciente->cargo_id;

        $this->cerrarQuickCreate();
        Notification::make()->title("«{$nombreNormalizado}» creado y seleccionado")->success()->send();
    }

    public function guardar(): void
    {
        $this->validate([
            'fecha'           => ['required', 'date'],
            'nombres'         => ['required', 'string', 'max:255'],
            'cedula'          => ['nullable', 'string', 'max:20'],
            'tipoPaciente'    => ['required', 'in:colaborador,aspirante,externo,paciente,huesped'],
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
                'nombres'                  => mb_strtoupper(trim($this->nombres)),
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

        // Vincular o crear paciente — usar pacienteId si ya se seleccionó
        $nombreNormalizado = mb_strtoupper(trim($this->nombres));
        if ($this->pacienteId && MedicoPaciente::query()->whereKey($this->pacienteId)->exists()) {
            // Actualizar datos del paciente existente
            MedicoPaciente::query()->whereKey($this->pacienteId)->update([
                'edad'    => $this->edad ?? null,
                'area_id' => $this->area_id,
                'cargo_id'=> $this->cargo_id,
                'tipo'    => $this->tipoPaciente,
            ]);
        } elseif ($this->tipoPaciente !== 'colaborador') {
            // Para huéspedes/externos/aspirantes/pacientes: crear si no existe
            // Buscar por nombre exacto o cédula
            $paciente = null;
            if ($this->cedula !== '') {
                $paciente = MedicoPaciente::query()->where('cedula', $this->cedula)->first();
            }
            if (! $paciente) {
                $paciente = MedicoPaciente::query()->where('nombres', $nombreNormalizado)->first();
            }
            if (! $paciente) {
                MedicoPaciente::query()->create([
                    'cedula'  => $this->cedula !== '' ? $this->cedula : null,
                    'nombres' => $nombreNormalizado,
                    'edad'    => $this->edad,
                    'area_id' => $this->area_id,
                    'cargo_id'=> $this->cargo_id,
                    'tipo'    => $this->tipoPaciente,
                    'activo'  => true,
                ]);
            }
        }

        $this->modalAtencionAbierto = false;
        $this->limpiarFormulario();
        Notification::make()->title('Atención guardada correctamente')->success()->send();
    }

    public function editar(int $id): void
    {
        $p = MedicoParteDiario::query()->with('medicamentos')->findOrFail($id);
        $this->editandoId = $p->id;
        $this->fecha = $p->fecha instanceof \Carbon\Carbon ? $p->fecha->format('Y-m-d') : now()->toDateString();
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
        $this->fecha_inicio_certificado = $p->fecha_inicio_certificado instanceof \Carbon\Carbon ? $p->fecha_inicio_certificado->format('Y-m-d') : $p->fecha_inicio_certificado;
        $this->fecha_fin_certificado = $p->fecha_fin_certificado instanceof \Carbon\Carbon ? $p->fecha_fin_certificado->format('Y-m-d') : $p->fecha_fin_certificado;
        $this->medico_certifica = $p->medico_certifica;
        $this->causa_id = $p->causa_id;
        $this->diagnostico_id = $p->diagnostico_id;
        $this->observacion = $p->observacion;
        $this->medicamentos = $p->medicamentos->map(fn ($m) => [
            'medicamento_id' => $m->medicamento_id,
            'cantidad'       => $m->cantidad,
        ])->values()->all() ?: [['medicamento_id' => null, 'cantidad' => 1]];

        $this->modalAtencionAbierto = true;
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
        $this->cedula = '';
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

    public function toggleTurno(string $turno): void
    {
        $this->turno = $this->turno === $turno ? null : $turno;
    }

    public function cambiarModo(string $modo): void
    {
        if ($modo === 'hoy') {
            $this->mostrarSoloHoy = true;
            $this->desde = now()->toDateString();
            $this->hasta = now()->toDateString();
        } else {
            $this->mostrarSoloHoy = false;
            $this->desde = now()->startOfMonth()->toDateString();
            $this->hasta = now()->toDateString();
        }
        $this->pagina = 1;
    }

    // === COMPUTED PROPERTIES ===

    public function getPacientesProperty(): Collection
    {
        return MedicoPaciente::query()->with(['area', 'cargo', 'examenes', 'visitas'])
            ->when($this->buscarPaciente !== '', fn ($q) => $q->where(function ($q) {
                $q->where('nombres', 'like', '%' . $this->buscarPaciente . '%')
                  ->orWhere('cedula', 'like', '%' . $this->buscarPaciente . '%')
                  ->orWhereHas('area', fn ($a) => $a->where('nombre', 'like', '%' . $this->buscarPaciente . '%'));
            }))
            ->orderByDesc('activo')
            ->orderBy('nombres')
            ->limit(20)
            ->get(['id', 'nombres', 'cedula', 'edad', 'area_id', 'cargo_id', 'tipo',
                   'activo', 'patologias', 'vacunas', 'telefono', 'fecha_ingreso', 'antecedentes',
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
            } elseif ($ex->fecha instanceof \Carbon\Carbon && $ex->fecha->lt($hoy->copy()->subYear())) {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vencido', 'fecha' => $ex->fecha->format('d/m/Y')];
            } else {
                $pendientes[] = ['nombre' => $nombre, 'estado' => 'vigente', 'fecha' => $ex->fecha instanceof \Carbon\Carbon ? $ex->fecha->format('d/m/Y') : $ex->fecha];
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
            $visitas[$anio] = $vis?->fecha instanceof \Carbon\Carbon ? $vis->fecha->format('d/m/Y') : $vis?->fecha;
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
        // No pre-cargar — la lista es muy larga. Se busca via buscarDiagnosticos() bajo demanda.
        return collect();
    }

    /**
     * Búsqueda server-side de diagnósticos (invocada desde Alpine via $wire.call).
     */
    public function buscarDiagnosticos(string $q = ''): array
    {
        if (mb_strlen(trim($q)) < 2) {
            return [];
        }

        return Diagnostico::query()
            ->where('activo', true)
            ->where('nombre', 'like', '%' . trim($q) . '%')
            ->orderBy('nombre')
            ->limit(50)
            ->get(['id', 'nombre'])
            ->toArray();
    }

    /**
     * Obtener un diagnóstico por ID (para inicializar el combobox en edición).
     */
    public function buscarDiagnosticoPorId(?int $id): ?array
    {
        if (! $id) {
            return null;
        }
        $d = Diagnostico::query()->find($id);
        return $d ? ['id' => $d->id, 'nombre' => $d->nombre] : null;
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

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function queryPartes()
    {
        $query = MedicoParteDiario::query()->with(['area', 'cargo', 'causa', 'diagnostico',
            'entidadCertificado', 'tipoCertificado', 'medicamentos.medicamento']);

        // Filtro de fecha
        if ($this->mostrarSoloHoy) {
            $query->whereDate('fecha', now()->toDateString());
        } else {
            if ($this->desde) {
                $query->whereDate('fecha', '>=', $this->desde);
            }
            if ($this->hasta) {
                $query->whereDate('fecha', '<=', $this->hasta);
            }
        }

        // Búsqueda por texto
        if ($this->buscar !== '') {
            $query->where(function ($q) {
                $q->where('nombres', 'like', '%' . $this->buscar . '%')
                  ->orWhere('observacion', 'like', '%' . $this->buscar . '%')
                  ->orWhere('habitacion', 'like', '%' . $this->buscar . '%');
            });
        }

        // Filtros adicionales
        if ($this->areaFiltroId) {
            $query->where('area_id', $this->areaFiltroId);
        }
        if ($this->causaFiltroId) {
            $query->where('causa_id', $this->causaFiltroId);
        }
        if ($this->tipoPacienteFiltro) {
            $query->where('tipo_paciente', $this->tipoPacienteFiltro);
        }
        if ($this->estadoFiltro === 'con_certificado') {
            $query->whereNotNull('entidad_certificado_id');
        }
        if ($this->estadoFiltro === 'sin_certificado') {
            $query->whereNull('entidad_certificado_id');
        }

        return $query->orderByDesc('fecha')->orderByDesc('id');
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
