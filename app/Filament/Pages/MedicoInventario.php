<?php

namespace App\Filament\Pages;

use App\Models\Medicamento;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoProducto;
use App\Services\Medico\InventarioMedicoService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MedicoInventario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedArchiveBox;
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Inventario';
    protected static ?string $title = 'Inventario médico';
    protected ?string $heading = '';
    protected static ?string $slug = 'medico/inventario';
    protected static ?int $navigationSort = 5;
    protected string $view = 'filament.pages.medico-inventario';

    // ============================================================
    // MOVIMIENTO — estado del formulario de movimiento
    // ============================================================
    public ?int $producto_id = null;
    public ?int $productoSeleccionadoId = null; // para panel detalle
    public string $tipo = 'ingreso';
    public float $cantidad = 1;
    public string $fecha_movimiento;
    public ?string $responsable = null;
    public ?string $observacion = null;

    // ============================================================
    // PRODUCTO — estado del formulario de producto (modal)
    // ============================================================
    public bool $modalProductoAbierto = false;
    public ?int $editandoId = null;
    public string $productoTipo = 'medicina';
    public string $productoNombre = '';
    public ?int $productoMedicamentoId = null;
    public ?float $productoStockMinimo = 0;
    public ?string $productoFechaCaducidad = null;
    public bool $productoActivo = true;
    public ?string $productoObservaciones = null;

    // ============================================================
    // FILTROS
    // ============================================================
    public string $buscar = '';
    public string $tipoFiltro = 'todos';   // todos|medicina|equipo
    public string $estadoFiltro = 'activos'; // activos|inactivos|todos

    public function mount(): void
    {
        $this->fecha_movimiento = now()->toDateString();
    }

    // ============================================================
    // MOVIMIENTOS
    // ============================================================

    public function guardarMovimiento(InventarioMedicoService $service): void
    {
        $this->validate([
            'producto_id' => ['required', 'integer'],
            'tipo' => ['required', 'in:ingreso,salida,ajuste'],
            'cantidad' => ['required', 'numeric', 'not_in:0'],
            'fecha_movimiento' => ['required', 'date'],
        ]);

        $producto = MedicoProducto::query()->findOrFail($this->producto_id);
        $service->registrarMovimientoProducto($producto, $this->tipo, $this->cantidad, $this->fecha_movimiento, $this->responsable, 'manual', null, $this->observacion);

        $this->cantidad = 1;
        $this->observacion = null;
        Notification::make()->title('Movimiento registrado')->success()->send();
    }

    public function seleccionarProducto(int $id): void
    {
        $this->productoSeleccionadoId = $id;
        $this->producto_id = $id;
    }

    // ============================================================
    // PRODUCTOS (CRUD)
    // ============================================================

    public function abrirModalProducto(): void
    {
        $this->limpiarFormularioProducto();
        $this->modalProductoAbierto = true;
    }

    public function cerrarModalProducto(): void
    {
        $this->modalProductoAbierto = false;
        $this->limpiarFormularioProducto();
    }

    public function editarProducto(int $id): void
    {
        $p = MedicoProducto::query()->findOrFail($id);
        $this->editandoId = $p->id;
        $this->productoTipo = $p->tipo;
        $this->productoNombre = $p->nombre;
        $this->productoMedicamentoId = $p->medicamento_id;
        $this->productoStockMinimo = $p->stock_minimo;
        $this->productoFechaCaducidad = $p->fecha_caducidad?->format('Y-m-d');
        $this->productoActivo = $p->activo;
        $this->productoObservaciones = $p->observaciones;
        $this->modalProductoAbierto = true;
    }

    public function guardarProducto(): void
    {
        $this->validate(['productoNombre' => ['required', 'string', 'max:255']]);

        MedicoProducto::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'tipo' => $this->productoTipo,
                'nombre' => trim($this->productoNombre),
                'medicamento_id' => $this->productoMedicamentoId,
                'stock_minimo' => $this->productoStockMinimo ?: 0,
                'fecha_caducidad' => $this->productoFechaCaducidad,
                'activo' => $this->productoActivo,
                'observaciones' => $this->productoObservaciones,
            ],
        );

        $label = $this->productoTipo === 'equipo' ? 'Equipo' : 'Medicina';
        $this->cerrarModalProducto();
        Notification::make()->title("{$label} guardado")->success()->send();
    }

    public function alternarProducto(int $id): void
    {
        $p = MedicoProducto::query()->findOrFail($id);
        $p->update(['activo' => ! $p->activo]);
        Notification::make()->title($p->activo ? 'Producto activado' : 'Producto desactivado')->success()->send();
    }

    public function eliminarProducto(int $id): void
    {
        $producto = MedicoProducto::query()->findOrFail($id);
        DB::transaction(function () use ($producto) {
            $producto->movimientos()->update(['producto_id' => null]);
            $producto->delete();
        });
        Notification::make()->title('Producto eliminado')->success()->send();
    }

    public function limpiarFormularioProducto(): void
    {
        $this->editandoId = null;
        $this->productoTipo = 'medicina';
        $this->productoNombre = '';
        $this->productoMedicamentoId = null;
        $this->productoStockMinimo = 0;
        $this->productoFechaCaducidad = null;
        $this->productoActivo = true;
        $this->productoObservaciones = null;
    }

    // ============================================================
    // COMPUTED
    // ============================================================

    public function getProductosProperty(): Collection
    {
        return MedicoProducto::query()->where('activo', true)->orderBy('nombre')->get();
    }

    public function getResumenProperty(): Collection
    {
        return MedicoProducto::query()
            ->with(['movimientos', 'medicamento'])
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
            ->when($this->tipoFiltro !== 'todos', fn ($q) => $q->where('tipo', $this->tipoFiltro))
            ->when($this->estadoFiltro === 'activos', fn ($q) => $q->where('activo', true))
            ->when($this->estadoFiltro === 'inactivos', fn ($q) => $q->where('activo', false))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->limit(100)
            ->get();
    }

    public function getMovimientosProperty(): Collection
    {
        return MedicoKardexMovimiento::query()
            ->with(['producto', 'parteDiario'])
            ->latest('fecha_movimiento')
            ->latest('id')
            ->limit(80)
            ->get();
    }

    public function getProductoSeleccionadoProperty(): ?MedicoProducto
    {
        if (! $this->productoSeleccionadoId) {
            return null;
        }
        return MedicoProducto::query()
            ->with(['medicamento', 'movimientos' => fn ($q) => $q->latest()->limit(20)])
            ->find($this->productoSeleccionadoId);
    }

    public function getMedicamentosCatalogProperty(): Collection
    {
        return Medicamento::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    public function getTotalProductosProperty(): int
    {
        return (int) MedicoProducto::query()->count();
    }

    public function getTotalMedicinasProperty(): int
    {
        return (int) MedicoProducto::query()->where('tipo', 'medicina')->count();
    }

    public function getTotalEquiposProperty(): int
    {
        return (int) MedicoProducto::query()->where('tipo', 'equipo')->count();
    }

    public function getStockBajoProperty(): int
    {
        return MedicoProducto::query()
            ->where('activo', true)
            ->get()
            ->filter(fn (MedicoProducto $p) => $p->stock_minimo > 0 && $p->saldoActual() <= $p->stock_minimo)
            ->count();
    }
}
