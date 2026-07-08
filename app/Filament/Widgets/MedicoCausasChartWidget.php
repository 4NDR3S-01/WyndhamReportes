<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Filament\Widgets\ChartWidget;

class MedicoCausasChartWidget extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.skeleton-chart';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Causas mas frecuentes';
    }

    protected function getData(): array
    {
        $causas = MedicoParteDiario::query()
            ->selectRaw("causa_id, COUNT(*) as total")
            ->whereNotNull('causa_id')
            ->groupBy('causa_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('causa')
            ->get();

        $colors = ['#ef4444', '#A68064', '#D9704A', '#ee7e62', '#3B4C82', '#2f9bb3', '#4D7C5E', '#0E7490'];

        $datasets = [];
        foreach ($causas as $i => $c) {
            $datasets[] = [
                'label' => $c->causa?->nombre ?? 'Sin Causa',
                'data' => [(int) $c->total],
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

