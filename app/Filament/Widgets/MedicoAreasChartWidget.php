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
            ->selectRaw("area_id, COUNT(*) as total")
            ->whereNotNull('area_id')
            ->groupBy('area_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('area')
            ->get();

        $colors = ['#4D7C5E', '#0E7490', '#A68064', '#ef4444', '#3B4C82', '#ee7e62', '#2f9bb3', '#D9704A'];

        $datasets = [];
        foreach ($areas as $i => $a) {
            $datasets[] = [
                'label' => $a->area?->nombre ?? 'Sin Area',
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
