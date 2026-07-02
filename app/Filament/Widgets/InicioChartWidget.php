<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class InicioChartWidget extends ChartWidget
{
    protected ?string $heading = 'Actividad del Sistema (Últimos 7 días)';
    protected ?string $maxHeight = '300px';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Importación de Archivos',
                    'data' => [4, 7, 2, 8, 12, 5, 9],
                    'borderColor' => '#3b82f6', // Bright Blue
                    'backgroundColor' => 'rgba(11, 59, 96, 0.1)',
                ],
                [
                    'label' => 'Consumos Reportados',
                    'data' => [12, 19, 15, 22, 30, 24, 28],
                    'borderColor' => '#4D7C5E', // Emerald
                    'backgroundColor' => 'rgba(77, 124, 94, 0.1)',
                ],
            ],
            'labels' => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
