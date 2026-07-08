<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteMedicamento;
use Filament\Widgets\ChartWidget;

class MedicoMedicamentosChartWidget extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.skeleton-chart';

    protected function getType(): string
    {
        return 'bar';
    }

    public function getHeading(): ?string
    {
        return 'Medicamentos mas usados';
    }

    protected function getData(): array
    {
        $medicamentos = MedicoParteMedicamento::query()
            ->selectRaw('medicamento_id, SUM(cantidad) as total')
            ->whereNotNull('medicamento_id')
            ->groupBy('medicamento_id')
            ->orderByDesc('total')
            ->limit(8)
            ->with('medicamento')
            ->get();

        if ($medicamentos->isEmpty()) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $labels = [];
        $data = [];
        foreach ($medicamentos as $m) {
            $labels[] = $m->medicamento?->nombre ?? 'Desconocido';
            $data[] = (float) $m->total;
        }

        $colors = ['#0E7490', '#3B4C82', '#4D7C5E', '#A68064', '#D9704A', '#ee7e62', '#2f9bb3', '#ef4444'];

        return [
            'datasets' => [
                [
                    'label' => 'Unidades dispensadas',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                    'ticks' => [
                        'font' => [
                            'size' => 11,
                        ],
                    ],
                ],
            ],
        ];
    }
}

