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
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Partes diarios';
    protected static ?string $title = 'Partes diarios';
    protected static ?string $slug = 'medico/partes-diarios';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.medico-partes-diarios';

    public ?int $editandoId = null;
    public ?int $pacienteId = null;
    public string $buscar = '';
    public ?string $desde = null;
    public ?string $hasta = null;
    public ?string $areaFiltro = null;
    public ?string $causaFiltro = null;

    public string $fecha;
    public string $nombres = '';
    public ?int $edad = null;
    public ?string $area = null;
    public ?string $cargo = null;
    public ?string $certificados = 'SIN CERTIFICADO';
    public ?string $subsidio = null;
    public ?float $horas_certificado = null;
    public ?int $dias_certificado = null;
    public ?string $fecha_inicio_certificado = null;
    public ?string $fecha_fin_certificado = null;
    public ?string $medico_certifica = null;
    public ?string $causa = null;
    public ?string $diagnostico = null;
    public ?string $observacion = null;
    public array $medicamentos = [];

    public function mount(): void
    {
        $this->fecha = now()->toDateString();
        $this->desde = now()->startOfMonth()->toDateString();
        $this->hasta = now()->toDateString();
        $this->medicamentos = [['producto_id' => null, 'nombre_original' => '', 'cantidad' => 1]];
    }

    public function updatedPacienteId(): void
    {
        if (! $this->pacienteId) return;
        $p = MedicoPaciente::query()->find($this->pacienteId);
        if (! $p) return;

        $this->nombres = $p->nombres;
        $this->edad = $p->edad;
        $this->area = $p->area;
        $this->cargo = $p->cargo;
    }

    public function agregarMedicamento(): void
    {
        $this->medicamentos[] = ['producto_id' => null, 'nombre_original' => '', 'cantidad' => 1];
    }

    public function quitarMedicamento(int $index): void
    {
        unset($this->medicamentos[$index]);
        $this->medicamentos = array_values($this->medicamentos);
    }

    public function guardar(InventarioMedicoService $inventario): void
    {
        $this->validate([
            'fecha' => ['required', 'date'],
            'nombres' => ['required', 'string', 'max:255'],
        ]);

        $archivo = $inventario->archivoSistema();
        $meds = collect($this->medicamentos)->filter(fn ($m) => trim((string) ($m['nombre_original'] ?? '')) !== '' || ! empty($m['producto_id']))->values();
        $medNames = $meds->map(function ($m) {
            if (! empty($m['producto_id'])) {
                return MedicoProducto::query()->whereKey($m['producto_id'])->value('nombre');
            }

            return $m['nombre_original'] ?? null;
        })->values();

        $parte = MedicoParteDiario::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'archivo_importado_id' => $archivo->id,
                'fecha' => $this->fecha,
                'nombres' => trim($this->nombres),
                'edad' => $this->edad,
                'area' => $this->area,
                'cargo' => $this->cargo,
                'certificados' => $this->certificados,
                'subsidio' => $this->subsidio,
                'horas_certificado' => $this->horas_certificado,
                'dias_certificado' => $this->dias_certificado,
                'fecha_inicio_certificado' => $this->fecha_inicio_certificado,
                'fecha_fin_certificado' => $this->fecha_fin_certificado,
                'medico_certifica' => $this->medico_certifica,
                'causa' => $this->causa,
                'diagnostico' => $this->diagnostico,
                'medicamento_1' => $medNames->get(0),
                'medicamento_2' => $medNames->get(1),
                'medicamento_3' => $medNames->get(2),
                'observacion' => $this->observacion,
                'hash_unico' => $this->editandoId ? MedicoParteDiario::query()->whereKey($this->editandoId)->value('hash_unico') : hash('sha256', implode('|', ['manual', $archivo->id, microtime(true), $this->nombres])),
            ],
        );

        $inventario->sincronizarMedicacionParte($parte, $meds->map(function ($m) {
            $productoNombre = ! empty($m['producto_id']) ? MedicoProducto::query()->whereKey($m['producto_id'])->value('nombre') : null;
            return [
                'producto_id' => $m['producto_id'] ?: null,
                'nombre_original' => $m['nombre_original'] ?: $productoNombre,
                'cantidad' => (float) ($m['cantidad'] ?? 1),
            ];
        })->all());

        MedicoPaciente::query()->firstOrCreate(
            ['nombres' => trim($this->nombres)],
            ['edad' => $this->edad, 'area' => $this->area, 'cargo' => $this->cargo, 'tipo' => 'paciente', 'activo' => true],
        );

        $this->limpiarFormulario();
        Notification::make()->title('Parte diario guardado')->success()->send();
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
        $this->medicamentos = $p->medicamentos->map(fn ($m) => [
            'producto_id' => $m->producto_id,
            'nombre_original' => $m->nombre_original,
            'cantidad' => $m->cantidad,
        ])->values()->all() ?: [['producto_id' => null, 'nombre_original' => '', 'cantidad' => 1]];
    }

    public function eliminar(int $id, InventarioMedicoService $inventario): void
    {
        $parte = MedicoParteDiario::query()->findOrFail($id);
        $inventario->sincronizarMedicacionParte($parte, []);
        $parte->delete();
        Notification::make()->title('Parte diario eliminado')->success()->send();
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->pacienteId = null;
        $this->fecha = now()->toDateString();
        $this->nombres = '';
        $this->edad = null;
        $this->area = null;
        $this->cargo = null;
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
        $this->medicamentos = [['producto_id' => null, 'nombre_original' => '', 'cantidad' => 1]];
    }

    public function catalogo(string $tipo): Collection
    {
        return MedicoCatalogo::query()->where('tipo', $tipo)->where('activo', true)->orderBy('nombre')->pluck('nombre');
    }

    public function getPacientesProperty(): Collection
    {
        return MedicoPaciente::query()->where('activo', true)->orderBy('nombres')->limit(500)->get();
    }

    public function getProductosProperty(): Collection
    {
        return MedicoProducto::query()->where('activo', true)->orderBy('nombre')->get();
    }

    public function getPartesProperty(): Collection
    {
        return MedicoParteDiario::query()
            ->with('medicamentos.producto')
            ->when($this->desde, fn ($q) => $q->whereDate('fecha', '>=', $this->desde))
            ->when($this->hasta, fn ($q) => $q->whereDate('fecha', '<=', $this->hasta))
            ->when($this->buscar !== '', fn ($q) => $q->where('nombres', 'like', '%' . $this->buscar . '%')->orWhere('diagnostico', 'like', '%' . $this->buscar . '%'))
            ->when($this->areaFiltro, fn ($q) => $q->where('area', $this->areaFiltro))
            ->when($this->causaFiltro, fn ($q) => $q->where('causa', $this->causaFiltro))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(80)
            ->get();
    }
}
