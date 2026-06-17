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

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad consumida',
                    'data' => $productos->pluck('total')->toArray(),
                    'backgroundColor' => '#0ea5e9',
                    'borderColor' => '#0284c7',
                ],
            ],
            'labels' => $productos->map(fn ($item) =>
                ($item->producto?->nombre ?? 'Sin nombre')
                . ' (' . mb_strtolower($item->producto?->unidad_medida ?? '-') . ')'
            )->toArray(),
        ];
    }
}
