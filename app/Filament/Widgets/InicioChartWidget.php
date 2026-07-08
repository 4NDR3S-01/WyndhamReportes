<?php

namespace App\Filament\Widgets;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class InicioChartWidget extends ChartWidget
{
    protected ?string $heading = 'Actividad del Sistema (Últimos 7 días)';
    protected ?string $maxHeight = '280px';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    protected function getData(): array
    {
        $hoy = Carbon::today();
        $labels = [];
        $archivos = [];
        $consumos = [];

        for ($i = 6; $i >= 0; $i--) {
            $dia = $hoy->copy()->subDays($i);
            $labels[] = $dia->isoFormat('ddd');

            $archivos[] = CocinaArchivoImportado::query()
                ->whereDate('created_at', $dia)
                ->count();

            $consumos[] = CocinaConsumo::query()
                ->whereDate('fecha', $dia)
                ->count();
        }

        $tieneDatos = array_sum($archivos) > 0 || array_sum($consumos) > 0;

        if (! $tieneDatos) {
            return [
                'datasets' => [],
                'labels' => $labels,
            ];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Archivos importados',
                    'data' => $archivos,
                    'borderColor' => '#0B3B60',
                    'backgroundColor' => 'rgba(11, 59, 96, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth' => 2,
                ],
                [
                    'label' => 'Consumos cargados',
                    'data' => $consumos,
                    'borderColor' => '#4D7C5E',
                    'backgroundColor' => 'rgba(77, 124, 94, 0.08)',
                    'fill' => true,
                    'tension' => 0.35,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
