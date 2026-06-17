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
        $diasRaw = CocinaConsumo::query()
            ->selectRaw('DISTINCT fecha')
            ->orderBy('fecha')
            ->pluck('fecha');

        $dias = $diasRaw->map(fn ($f) => Carbon::parse($f)->format('Y-m-d'));
        $labels = $diasRaw->map(fn ($f) => Carbon::parse($f)->format('d/m'))->toArray();

        if ($this->filter && $this->filter !== 'todos') {
            $producto = CocinaProducto::query()->find($this->filter);

            $consumos = CocinaConsumo::query()
                ->where('producto_id', $this->filter)
                ->selectRaw("fecha, SUM(cantidad) as total")
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get()
                ->keyBy(fn ($c) => Carbon::parse($c->fecha)->format('Y-m-d'));

            $data = $dias->map(fn ($f) => $consumos->has($f) ? (float) $consumos[$f]->total : null)->values()->toArray();

            return [
                'datasets' => [
                    [
                        'label' => $producto?->nombre ?? 'Producto seleccionado',
                        'data' => $data,
                        'borderColor' => '#0ea5e9',
                        'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                        'fill' => true,
                        'tension' => 0.3,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        $topIds = CocinaConsumo::query()
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('producto_id');

        if ($topIds->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => $labels,
            ];
        }

        $productos = CocinaProducto::query()->whereIn('id', $topIds)->get()->keyBy('id');
        $colors = ['#0ea5e9', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6'];

        $datasets = [];
        foreach ($topIds as $i => $pid) {
            $p = $productos->get($pid);

            $consumos = CocinaConsumo::query()
                ->where('producto_id', $pid)
                ->selectRaw("fecha, SUM(cantidad) as total")
                ->groupBy('fecha')
                ->orderBy('fecha')
                ->get()
                ->keyBy(fn ($c) => Carbon::parse($c->fecha)->format('Y-m-d'));

            $data = $dias->map(fn ($f) => $consumos->has($f) ? (float) $consumos[$f]->total : null)->values()->toArray();

            $datasets[] = [
                'label' => $p?->nombre ?? 'Producto ' . $pid,
                'data' => $data,
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => 'transparent',
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
}
