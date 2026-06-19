<?php

namespace App\Filament\Pages;

use App\Models\MedicoCatalogo;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class MedicoCatalogos extends Page
{
    protected static string|BackedEnum|null $navigationIcon = null;
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Base medica';
    protected static ?string $title = 'Base medica';
    protected static ?string $slug = 'medico/base-medica';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.medico-catalogos';
    protected Width|string|null $maxContentWidth = Width::Full;

    public string $tipo = 'area';
    public string $buscar = '';
    public string $estado = 'activos';
    public bool $modalAbierto = false;
    public bool $modalEliminarAbierto = false;
    public ?int $editandoId = null;
    public ?int $eliminandoId = null;
    public string $nombre = '';
    public ?string $descripcion = null;
    public bool $activo = true;

    public array $tipos = [
        'area' => 'Areas',
        'cargo' => 'Cargos',
        'causa' => 'Causas',
        'diagnostico' => 'Diagnosticos',
        'certificado_medico' => 'Certificados medicos',
        'subsidio' => 'Subsidios',
        'descanso_en' => 'Descanso en',
        'salida' => 'Salida',
        'medicacion' => 'Medicacion base',
        'cirugia_general' => 'Cirugia general',
        'flebologia_vascular' => 'Flebologia vascular',
        'atencion_medica_general' => 'Atencion medica general',
        'incidente' => 'Incidentes',
    ];

    public array $descripciones = [
        'area' => 'Departamentos y areas laborales usados en partes diarios.',
        'cargo' => 'Puestos de trabajo vinculados a colaboradores.',
        'causa' => 'Categorias principales de atencion medica.',
        'diagnostico' => 'Diagnosticos disponibles para registrar atenciones.',
        'certificado_medico' => 'Origen del certificado presentado por el colaborador.',
        'subsidio' => 'Valores usados para controlar subsidios.',
        'descanso_en' => 'Unidad del descanso medico: horas o dias.',
        'salida' => 'Resultado de la atencion o derivacion.',
        'medicacion' => 'Lista base importada desde la hoja BASE DE DATOS.',
        'cirugia_general' => 'Opciones clinicas relacionadas con cirugia general.',
        'flebologia_vascular' => 'Opciones clinicas relacionadas con flebologia vascular.',
        'atencion_medica_general' => 'Motivos frecuentes de atencion general.',
        'incidente' => 'Tipos de incidente registrados por el dispensario.',
    ];

    public function guardar(): void
    {
        $this->validate([
            'tipo' => ['required', 'string'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        MedicoCatalogo::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'tipo' => $this->tipo,
                'nombre' => trim($this->nombre),
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
            ],
        );

        $label = str($this->tipos[$this->tipo] ?? 'Registro')->singular()->lower()->ucfirst();

        $this->cerrarModal();
        Notification::make()->title("{$label} guardado")->success()->send();
    }

    public function abrirModalNuevo(): void
    {
        $this->limpiarFormulario();
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->limpiarFormulario();
    }

    public function editar(int $id): void
    {
        $item = MedicoCatalogo::query()->findOrFail($id);
        $this->editandoId = $item->id;
        $this->tipo = $item->tipo;
        $this->nombre = $item->nombre;
        $this->descripcion = $item->descripcion;
        $this->activo = $item->activo;
        $this->modalAbierto = true;
    }

    public function alternar(int $id): void
    {
        $item = MedicoCatalogo::query()->findOrFail($id);
        $item->update(['activo' => ! $item->activo]);
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

        $item = MedicoCatalogo::query()->findOrFail($this->eliminandoId);
        $label = str($this->tipos[$item->tipo] ?? 'Registro')->singular()->lower()->ucfirst();

        $item->delete();

        if ($this->editandoId === $this->eliminandoId) {
            $this->cerrarModal();
        }

        $this->cancelarEliminar();

        Notification::make()->title("{$label} eliminado")->success()->send();
    }

    public function getRegistroAEliminarProperty(): ?MedicoCatalogo
    {
        return $this->eliminandoId ? MedicoCatalogo::query()->find($this->eliminandoId) : null;
    }

    public function seleccionarTipo(string $tipo): void
    {
        if (! array_key_exists($tipo, $this->tipos)) {
            return;
        }

        $this->tipo = $tipo;
        $this->buscar = '';
        $this->estado = 'activos';
        $this->limpiarFormulario();
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->nombre = '';
        $this->descripcion = null;
        $this->activo = true;
    }

    public function getItemsProperty(): Collection
    {
        return MedicoCatalogo::query()
            ->where('tipo', $this->tipo)
            ->when($this->estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($this->estado === 'ocultos', fn ($query) => $query->where('activo', false))
            ->when($this->buscar !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->buscar . '%');
            }))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->limit(120)
            ->get();
    }

    public function getTotalSeccionProperty(): int
    {
        return (int) MedicoCatalogo::query()->where('tipo', $this->tipo)->count();
    }

    public function getActivosSeccionProperty(): int
    {
        return (int) MedicoCatalogo::query()->where('tipo', $this->tipo)->where('activo', true)->count();
    }

    public function getOcultosSeccionProperty(): int
    {
        return max(0, $this->totalSeccion - $this->activosSeccion);
    }

    public function getTotalFiltradoProperty(): int
    {
        return (int) MedicoCatalogo::query()
            ->where('tipo', $this->tipo)
            ->when($this->estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($this->estado === 'ocultos', fn ($query) => $query->where('activo', false))
            ->when($this->buscar !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('nombre', 'like', '%' . $this->buscar . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->buscar . '%');
            }))
            ->count();
    }

    public function getConteosPorTipoProperty(): array
    {
        return MedicoCatalogo::query()
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    public function getActivosPorTipoProperty(): array
    {
        return MedicoCatalogo::query()
            ->where('activo', true)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->map(fn ($total) => (int) $total)
            ->all();
    }
}
