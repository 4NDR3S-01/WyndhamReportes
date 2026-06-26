<?php

namespace App\Filament\Pages;

use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoProducto;
use App\Services\Medico\InventarioMedicoService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class MedicoInventario extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedArchiveBox;
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Inventario';
    protected static ?string $title = 'Inventario medico';
    protected static ?string $slug = 'medico/inventario';
    protected static ?int $navigationSort = 7;
    protected string $view = 'filament.pages.medico-inventario';

    public ?int $producto_id = null;
    public string $tipo = 'ingreso';
    public float $cantidad = 1;
    public string $fecha_movimiento;
    public ?string $responsable = null;
    public ?string $observacion = null;
    public string $buscar = '';

    public function mount(): void
    {
        $this->fecha_movimiento = now()->toDateString();
    }

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

    public function getProductosProperty(): Collection
    {
        return MedicoProducto::query()->where('activo', true)->orderBy('nombre')->get();
    }

    public function getResumenProperty(): Collection
    {
        return MedicoProducto::query()
            ->with('movimientos')
            ->when($this->buscar !== '', fn ($q) => $q->where('nombre', 'like', '%' . $this->buscar . '%'))
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
}
