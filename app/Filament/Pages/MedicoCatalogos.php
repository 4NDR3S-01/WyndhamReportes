<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MedicoCatalogos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCircleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Base médica';

    protected static ?string $title = 'Base médica';

    protected ?string $heading = '';

    protected static ?string $slug = 'medico/base-medica';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.medico-catalogos';

    public string $tipo = 'area';

    public string $buscar = '';

    public string $estado = 'activos';

    public bool $modalAbierto = false;

    public bool $modalEliminarAbierto = false;

    public ?int $editandoId = null;

    public ?int $eliminandoId = null;

    public string $nombre = '';

    public bool $activo = true;

    // === PAGINATION ===
    public int $pagina = 1;

    public int $porPagina = 30;

    // === TIPO → MODELO + ETIQUETA ===

    public array $tipos = [
        'area' => 'Áreas',
        'cargo' => 'Cargos',
        'causa' => 'Causas',
        'diagnostico' => 'Diagnósticos',
        'entidad_certificado' => 'Entidades certificado',
    ];

    public array $descripciones = [
        'area' => 'Departamentos y áreas laborales usados en partes diarios.',
        'cargo' => 'Puestos de trabajo vinculados a colaboradores.',
        'causa' => 'Categorías principales de atención médica.',
        'diagnostico' => 'Diagnósticos disponibles para registrar atenciones.',
        'entidad_certificado' => 'Origen del certificado presentado por el colaborador.',
    ];

    protected function getModelClass(string $tipo): string
    {
        return match ($tipo) {
            'area' => Area::class,
            'cargo' => Cargo::class,
            'causa' => Causa::class,
            'diagnostico' => Diagnostico::class,
            'entidad_certificado' => EntidadCertificado::class,
        };
    }

    // === CRUD ===

    public function guardar(): void
    {
        $this->validate([
            'tipo' => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $modelClass = $this->getModelClass($this->tipo);

        $modelClass::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'nombre' => mb_strtoupper(trim($this->nombre)),
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
        $modelClass = $this->getModelClass($this->tipo);
        $item = $modelClass::query()->findOrFail($id);
        $this->editandoId = $item->id;
        $this->nombre = $item->nombre;
        $this->activo = $item->activo;
        $this->modalAbierto = true;
    }

    public function alternar(int $id): void
    {
        $modelClass = $this->getModelClass($this->tipo);
        $item = $modelClass::query()->findOrFail($id);
        $nuevoEstado = ! $item->activo;
        $item->update(['activo' => $nuevoEstado]);
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

        $modelClass = $this->getModelClass($this->tipo);
        $item = $modelClass::query()->findOrFail($this->eliminandoId);
        $label = str($this->tipos[$this->tipo] ?? 'Registro')->singular()->lower()->ucfirst();

        $item->delete();

        if ($this->editandoId === $this->eliminandoId) {
            $this->cerrarModal();
        }

        $this->cancelarEliminar();

        Notification::make()->title("{$label} eliminado")->success()->send();
    }

    public function getRegistroAEliminarProperty(): ?Model
    {
        if (! $this->eliminandoId) {
            return null;
        }
        $modelClass = $this->getModelClass($this->tipo);

        return $modelClass::query()->find($this->eliminandoId);
    }

    public function seleccionarTipo(string $tipo): void
    {
        if (! array_key_exists($tipo, $this->tipos)) {
            return;
        }

        $this->tipo = $tipo;
        $this->buscar = '';
        $this->estado = 'activos';
        $this->pagina = 1;
        $this->limpiarFormulario();
    }

    public function irPagina(int $n): void
    {
        $this->pagina = max(1, min($n, $this->totalPaginas));
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['buscar', 'estado'], true)) {
            $this->pagina = 1;
        }
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->nombre = '';
        $this->activo = true;
    }

    // === COMPUTED PROPERTIES ===

    public function getItemsProperty(): Collection
    {
        $modelClass = $this->getModelClass($this->tipo);

        return $modelClass::query()
            ->when($this->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($this->estado === 'ocultos', fn ($q) => $q->where('activo', false))
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%'.$this->buscar.'%'))
            ->orderByDesc('activo')
            ->orderBy('nombre')
            ->skip(($this->pagina - 1) * $this->porPagina)
            ->take($this->porPagina)
            ->get();
    }

    public function getTotalPaginasProperty(): int
    {
        return (int) ceil($this->totalFiltrado / $this->porPagina);
    }

    public function getTotalSeccionProperty(): int
    {
        $modelClass = $this->getModelClass($this->tipo);

        return (int) $modelClass::query()->count();
    }

    public function getActivosSeccionProperty(): int
    {
        $modelClass = $this->getModelClass($this->tipo);

        return (int) $modelClass::query()->where('activo', true)->count();
    }

    public function getOcultosSeccionProperty(): int
    {
        return max(0, $this->totalSeccion - $this->activosSeccion);
    }

    public function getTotalFiltradoProperty(): int
    {
        $modelClass = $this->getModelClass($this->tipo);

        return (int) $modelClass::query()
            ->when($this->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($this->estado === 'ocultos', fn ($q) => $q->where('activo', false))
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%'.$this->buscar.'%'))
            ->count();
    }

    public function getConteosPorTipoProperty(): array
    {
        $result = [];

        foreach ($this->tipos as $key => $_) {
            try {
                $modelClass = $this->getModelClass($key);
                $result[$key] = (int) $modelClass::query()->count();
            } catch (\Throwable) {
                $result[$key] = 0;
            }
        }

        return $result;
    }

    public function getActivosPorTipoProperty(): array
    {
        $result = [];

        foreach ($this->tipos as $key => $_) {
            try {
                $modelClass = $this->getModelClass($key);
                $result[$key] = (int) $modelClass::query()->where('activo', true)->count();
            } catch (\Throwable) {
                $result[$key] = 0;
            }
        }

        return $result;
    }
}
