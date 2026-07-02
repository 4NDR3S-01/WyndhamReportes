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

        $colors = ['#0E7490', '#A68064', '#4D7C5E', '#ef4444', '#3B4C82', '#ee7e62', '#2f9bb3', '#D9704A', '#6a9e7b', '#3B4C82'];

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
