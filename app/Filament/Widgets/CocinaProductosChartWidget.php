<?php

namespace App\Filament\Widgets;

use App\Models\CocinaConsumo;
use Filament\Widgets\ChartWidget;

class CocinaProductosChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Productos mas consumidos';
    }

    protected function getData(): array
    {
        $productos = CocinaConsumo::query()
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->with('producto')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $colors = ['#0ea5e9', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#84cc16', '#6366f1'];

        $datasets = [];
        foreach ($productos as $i => $item) {
            $nombre = $item->producto?->nombre ?? 'Sin nombre';
            $unidad = mb_strtolower($item->producto?->unidad_medida ?? '-');

            $datasets[] = [
                'label' => $nombre . ' (' . $unidad . ')',
                'data' => [(float) $item->total],
                'backgroundColor' => $colors[$i % count($colors)],
                'borderColor' => $colors[$i % count($colors)],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => ['Total consumido'],
        ];
    }
}
