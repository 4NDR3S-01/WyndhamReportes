<?php

namespace App\Filament\Widgets;

use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CocinaConsumoDiarioWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Consumo diario';
    }

    protected function getFilters(): ?array
    {
        $productos = CocinaProducto::query()
            ->whereHas('consumos')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();

        return ['todos' => 'Todos los productos'] + $productos;
    }

    protected function getData(): array
    {
        $query = CocinaConsumo::query();

        if ($this->filter && $this->filter !== 'todos') {
            $query->where('producto_id', $this->filter);
        }

        $consumos = $query
            ->selectRaw('fecha, SUM(cantidad) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $label = 'Consumo total';

        if ($this->filter && $this->filter !== 'todos') {
            $producto = CocinaProducto::query()->find($this->filter);
            $label = $producto?->nombre ?? 'Producto seleccionado';
        }

        return [
            'datasets' => [
                [
                    'label' => $label,
                    'data' => $consumos->pluck('total')->toArray(),
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $consumos->map(fn ($c) => Carbon::parse($c->fecha)->format('d/m'))->toArray(),
        ];
    }
}
