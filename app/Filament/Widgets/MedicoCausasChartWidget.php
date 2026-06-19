<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Filament\Widgets\ChartWidget;

class MedicoCausasChartWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

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
            ->selectRaw("causa, COUNT(*) as total")
            ->whereNotNull('causa')
            ->where('causa', '!=', '')
            ->groupBy('causa')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $colors = ['#ef4444', '#f59e0b', '#f97316', '#ec4899', '#8b5cf6', '#06b6d4', '#10b981', '#0ea5e9'];

        $datasets = [];
        foreach ($causas as $i => $c) {
            $datasets[] = [
                'label' => $c->causa,
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
