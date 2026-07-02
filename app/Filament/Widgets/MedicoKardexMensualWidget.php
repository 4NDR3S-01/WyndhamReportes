<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MedicoKardexMensualWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'KARDEX Mensual — Atenciones';
    }

    protected function getData(): array
    {
        if (MedicoParteDiario::query()->count() === 0) {
            return ['datasets' => [], 'labels' => []];
        }

        $meses = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, MIN(fecha) as inicio, COUNT(*) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        $labels = [];
        $data = [];
        foreach ($meses as $m) {
            $labels[] = Carbon::parse($m->inicio)->format('M y');
            $data[] = (int) $m->total;
        }

        $pacientes = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, COUNT(DISTINCT nombres) as total")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->keyBy('ym');

        $dataPacientes = [];
        foreach ($meses as $m) {
            $dataPacientes[] = isset($pacientes[$m->ym]) ? (int) $pacientes[$m->ym]->total : null;
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
                [
                    'label' => 'Pacientes distintos',
                    'data' => $dataPacientes,
                    'borderColor' => '#3B4C82',
                    'backgroundColor' => 'transparent',
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }
}
