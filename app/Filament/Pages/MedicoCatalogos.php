<?php

namespace App\Filament\Pages;

use App\Models\Area;
use App\Models\Cargo;
use App\Models\Causa;
use App\Models\Diagnostico;
use App\Models\EntidadCertificado;
use App\Models\Incidente;
use App\Models\Medicamento;
use App\Models\MedicoProducto;
use App\Models\TipoCertificado;
use App\Models\TipoDescanso;
use App\Models\TipoSalida;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class MedicoCatalogos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedCircleStack;
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
        'area'                   => 'Áreas',
        'cargo'                  => 'Cargos',
        'causa'                  => 'Causas',
        'diagnostico'            => 'Diagnósticos',
        'tipo_certificado'       => 'Tipos de certificado',
        'entidad_certificado'    => 'Entidades certificado',
        'tipo_descanso'          => 'Tipos de descanso',
        'tipo_salida'            => 'Tipos de salida',
        'medicamento'            => 'Medicamentos y Productos',
        'incidente'              => 'Incidentes',
    ];

    public array $descripciones = [
        'area'                  => 'Departamentos y áreas laborales usados en partes diarios.',
        'cargo'                 => 'Puestos de trabajo vinculados a colaboradores.',
        'causa'                 => 'Categorías principales de atención médica.',
        'diagnostico'           => 'Diagnósticos disponibles para registrar atenciones.',
        'tipo_certificado'      => 'Tipos de certificado (subsidio, reposo, etc.).',
        'entidad_certificado'   => 'Origen del certificado presentado por el colaborador.',
        'tipo_descanso'         => 'Unidad del descanso médico: horas o días.',
        'tipo_salida'           => 'Resultado de la atención o derivación.',
        'medicamento'           => 'Lista base importada desde la hoja BASE DE DATOS.',
        'incidente'             => 'Tipos de incidente registrados por el dispensario.',
    ];

    protected function getModelClass(string $tipo): string
    {
        return match ($tipo) {
            'area'                => Area::class,
            'cargo'               => Cargo::class,
            'causa'               => Causa::class,
            'diagnostico'         => Diagnostico::class,
            'tipo_certificado'    => TipoCertificado::class,
            'entidad_certificado' => EntidadCertificado::class,
            'tipo_descanso'       => TipoDescanso::class,
            'tipo_salida'         => TipoSalida::class,
            'medicamento'         => Medicamento::class,
            'incidente'           => Incidente::class,
        };
    }

    // === CRUD ===

    public function guardar(): void
    {
        $this->validate([
            'tipo'   => ['required', 'string', 'max:50'],
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $modelClass = $this->getModelClass($this->tipo);

        $registro = $modelClass::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'nombre' => mb_strtoupper(trim($this->nombre)),
                'activo' => $this->activo,
            ],
        );

        // ── SINCRONIZAR: medicamento → inventario ──
        if ($this->tipo === 'medicamento') {
            $this->sincronizarMedicamentoAInventario($registro);
        }

        $label = str($this->tipos[$this->tipo] ?? 'Registro')->singular()->lower()->ucfirst();

        $this->cerrarModal();
        Notification::make()->title("{$label} guardado")->success()->send();
    }

    /**
     * Crea o vincula un MedicoProducto para un medicamento del catálogo.
     */
    private function sincronizarMedicamentoAInventario(Model $medicamento): void
    {
        $producto = MedicoProducto::resolverPorNombre($medicamento->nombre);

        if (! $producto) {
            // Crear producto nuevo vinculado
            MedicoProducto::query()->create([
                'medicamento_id' => $medicamento->id,
                'tipo'           => 'medicina',
                'nombre'         => $medicamento->nombre,
                'stock_minimo'   => 0,
                'activo'         => $medicamento->activo,
            ]);
        } elseif (! $producto->medicamento_id) {
            // Producto existe sin vínculo → vincular
            $producto->update([
                'medicamento_id' => $medicamento->id,
                'activo'         => $medicamento->activo,
            ]);
        } elseif ($producto->medicamento_id !== $medicamento->id) {
            // Producto vinculado a OTRO medicamento → crear alias y vincular este también
            // (caso raro: nombres iguales pero IDs diferentes)
            $producto->update(['medicamento_id' => $medicamento->id]);
        }
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

        // Sincronizar estado a productos vinculados (medicamentos → inventario)
        if ($this->tipo === 'medicamento' && $item instanceof Medicamento) {
            MedicoProducto::query()
                ->where('medicamento_id', $item->id)
                ->update(['activo' => $nuevoEstado]);
        }
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
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
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
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
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
