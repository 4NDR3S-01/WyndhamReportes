<?php

namespace App\Filament\Widgets;

use App\Models\MedicoParteDiario;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class MedicoAtencionesDiariasWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Atenciones diarias';
    }

    protected function getData(): array
    {
        $diasRaw = MedicoParteDiario::query()
            ->selectRaw('DISTINCT fecha')
            ->orderBy('fecha')
            ->pluck('fecha');

        $labels = [];
        $dias = [];
        foreach ($diasRaw as $f) {
            $key = Carbon::parse($f)->format('Y-m-d');
            $dias[] = $key;
            $labels[] = Carbon::parse($f)->format('d/m');
        }

        $mapa = [];
        foreach (MedicoParteDiario::query()->selectRaw('fecha, COUNT(*) as total')->groupBy('fecha')->get() as $c) {
            $mapa[Carbon::parse($c->fecha)->format('Y-m-d')] = (int) $c->total;
        }

        $data = [];
        foreach ($dias as $f) {
            $data[] = $mapa[$f] ?? null;
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
