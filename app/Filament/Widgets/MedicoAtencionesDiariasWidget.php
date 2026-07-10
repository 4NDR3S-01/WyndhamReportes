<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MedicoAtencionesDiariasWidget extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected ?string $maxHeight = '300px';

    protected string $view = 'filament.widgets.skeleton-chart';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Atenciones mensuales (' . Carbon::today()->year . ')';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'pointRadius' => 3,
            'pointHoverRadius' => 6,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $hoy = Carbon::today();
        $inicio = $hoy->copy()->startOfYear();
        $fin = $hoy->copy()->endOfYear();

        // Conteo por día dentro del año actual (consulta agnóstica a la BD).
        $filas = MedicoParteDiario::query()
            ->whereDate('fecha', '>=', $inicio)
            ->whereDate('fecha', '<=', $fin)
            ->selectRaw('fecha, COUNT(*) as total')
            ->groupBy('fecha')
            ->get();

        // Agrupa los totales diarios por mes.
        $mapa = [];
        foreach ($filas as $fila) {
            $mes = (int) Carbon::parse($fila->fecha)->format('m');
            $mapa[$mes] = ($mapa[$mes] ?? 0) + (int) $fila->total;
        }

        // Serie mensual continua de enero al mes actual (los meses sin atenciones quedan en 0).
        // Como $hoy es la fecha de hoy, al cambiar de mes el nuevo se agrega automáticamente.
        $labels = [];
        $data = [];
        for ($mes = 1; $mes <= $hoy->month; $mes++) {
            $labels[] = Carbon::createFromDate($hoy->year, $mes, 1)->isoFormat('MMM');
            $data[] = $mapa[$mes] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Atenciones',
                    'data' => $data,
                    'borderColor' => '#0E7490',
                    'backgroundColor' => 'rgba(14, 116, 144, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}

