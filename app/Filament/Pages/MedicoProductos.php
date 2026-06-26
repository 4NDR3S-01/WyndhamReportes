<?php

namespace App\Filament\Pages;

use App\Models\MedicoProducto;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MedicoProductos extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-beaker';
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Medicinas y equipos';
    protected static ?string $title = 'Medicinas y equipos';
    protected static ?string $slug = 'medico/productos';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.medico-productos';
    protected Width|string|null $maxContentWidth = Width::Full;

    public ?int $editandoId = null;
    public string $buscar = '';
    public string $tipoFiltro = 'todos';
    public string $estado = 'activos';
    public bool $modalProductoAbierto = false;
    public string $tipo = 'medicina';
    public string $nombre = '';
    public ?float $stock_minimo = 0;
    public ?string $fecha_caducidad = null;
    public bool $activo = true;
    public ?string $observaciones = null;

    public function guardar(): void
    {
        $this->validate(['nombre' => ['required', 'string', 'max:255']]);

        $producto = MedicoProducto::query()->updateOrCreate(
            ['id' => $this->editandoId],
            [
                'tipo' => $this->tipo,
                'nombre' => trim($this->nombre),
                'stock_minimo' => $this->stock_minimo ?: 0,
                'fecha_caducidad' => $this->fecha_caducidad,
                'activo' => $this->activo,
                'observaciones' => $this->observaciones,
            ],
        );

        $label = $producto->tipo === 'equipo' ? 'Equipo' : 'Medicina';

        $this->cerrarModalProducto();
        Notification::make()->title("{$label} guardado")->success()->send();
    }

    public function abrirModalProducto(): void
    {
        $this->limpiarFormulario();
        $this->modalProductoAbierto = true;
    }

    public function cerrarModalProducto(): void
    {
        $this->modalProductoAbierto = false;
        $this->limpiarFormulario();
    }

    public function editar(int $id): void
    {
        $p = MedicoProducto::query()->findOrFail($id);
        $this->editandoId = $p->id;
        $this->tipo = $p->tipo;
        $this->nombre = $p->nombre;
        $this->stock_minimo = $p->stock_minimo;
        $this->fecha_caducidad = $p->fecha_caducidad?->format('Y-m-d');
        $this->activo = $p->activo;
        $this->observaciones = $p->observaciones;
        $this->modalProductoAbierto = true;
    }

    public function alternar(int $id): void
    {
        $p = MedicoProducto::query()->findOrFail($id);
        $p->update(['activo' => ! $p->activo]);

        Notification::make()->title($p->activo ? 'Producto activado' : 'Producto desactivado')->success()->send();
    }

    public function eliminar(int $id): void
    {
        $producto = MedicoProducto::query()->findOrFail($id);

        DB::transaction(function () use ($producto) {
            $producto->movimientos()->update(['producto_id' => null]);
            $producto->delete();
        });

        Notification::make()->title('Producto eliminado')->success()->send();
    }

    public function limpiarFormulario(): void
    {
        $this->editandoId = null;
        $this->tipo = 'medicina';
        $this->nombre = '';
        $this->stock_minimo = 0;
        $this->fecha_caducidad = null;
        $this->activo = true;
        $this->observaciones = null;
    }

    public function getProductosProperty(): Collection
    {
        return MedicoProducto::query()
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
            ->when($this->tipoFiltro !== 'todos', fn ($q) => $q->where('tipo', $this->tipoFiltro))
            ->when($this->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($this->estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->limit(120)
            ->get();
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
            ->filter(fn (MedicoProducto $producto) => $producto->stock_minimo > 0 && $producto->saldoActual() <= $producto->stock_minimo)
            ->count();
    }

    public function getPorCaducarProperty(): int
    {
        return (int) MedicoProducto::query()
            ->where('activo', true)
            ->whereNotNull('fecha_caducidad')
            ->whereDate('fecha_caducidad', '<=', now()->addDays(60)->toDateString())
            ->count();
    }

    public function getTotalFiltradoProperty(): int
    {
        return (int) MedicoProducto::query()
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
            ->when($this->tipoFiltro !== 'todos', fn ($q) => $q->where('tipo', $this->tipoFiltro))
            ->when($this->estado === 'activos', fn ($q) => $q->where('activo', true))
            ->when($this->estado === 'inactivos', fn ($q) => $q->where('activo', false))
            ->count();
    }
}
