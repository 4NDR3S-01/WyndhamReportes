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
            ->select('fecha')
            ->orderBy('fecha')
            ->get()
            ->groupBy(fn ($p) => Carbon::parse($p->fecha)->format('Y-m'))
            ->map(fn ($grupo, $ym) => (object) [
                'ym'     => $ym,
                'inicio' => $grupo->min('fecha'),
                'total'  => $grupo->count(),
            ])
            ->sortBy('ym')
            ->values();

        $labels = [];
        $data = [];
        foreach ($meses as $m) {
            $labels[] = Carbon::parse($m->inicio)->format('M y');
            $data[] = (int) $m->total;
        }

        $pacientes = MedicoParteDiario::query()
            ->select('fecha', 'nombres')
            ->orderBy('fecha')
            ->get()
            ->groupBy(fn ($p) => Carbon::parse($p->fecha)->format('Y-m'))
            ->map(fn ($grupo) => $grupo->unique('nombres')->count());

        $dataPacientes = [];
        foreach ($meses as $m) {
            $dataPacientes[] = $pacientes[$m->ym] ?? null;
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
