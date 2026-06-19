<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Filament\Widgets\ChartWidget;

class MedicoAreasChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Areas con mas atenciones';
    }

    protected function getData(): array
    {
        $areas = MedicoParteDiario::query()
            ->selectRaw("area, COUNT(*) as total")
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->groupBy('area')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $colors = ['#10b981', '#0ea5e9', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316'];

        $datasets = [];
        foreach ($areas as $i => $a) {
            $datasets[] = [
                'label' => $a->area,
                'data' => [(int) $a->total],
                'backgroundColor' => $colors[$i % count($colors)],
                'borderColor' => $colors[$i % count($colors)],
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => ['Atenciones'],
        ];
    }
}
