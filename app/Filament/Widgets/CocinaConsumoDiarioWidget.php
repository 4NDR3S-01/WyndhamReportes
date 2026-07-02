<?php

namespace App\Filament\Widgets;

use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class CocinaConsumoDiarioWidget extends ChartWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Consumo diario';
    }

    protected function getFilters(): ?array
    {
        $productos = CocinaProducto::query()
            ->whereHas('consumos')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();

        return ['todos' => 'Todos los productos'] + $productos;
    }

    private function buildMapaConsumo(callable $queryModifier): array
    {
        $query = CocinaConsumo::query();
        $queryModifier($query);

        $mapa = [];
        foreach ($query->selectRaw("fecha, SUM(cantidad) as total")->groupBy('fecha')->get() as $c) {
            $mapa[Carbon::parse($c->fecha)->format('Y-m-d')] = (float) $c->total;
        }

        return $mapa;
    }

    protected function getData(): array
    {
        $diasRaw = CocinaConsumo::query()
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

        if ($this->filter && $this->filter !== 'todos') {
            $producto = CocinaProducto::query()->find($this->filter);
            $mapa = $this->buildMapaConsumo(fn ($q) => $q->where('producto_id', $this->filter));

            $data = [];
            foreach ($dias as $f) {
                $data[] = $mapa[$f] ?? null;
            }

            return [
                'datasets' => [
                    [
                        'label' => $producto?->nombre ?? 'Producto seleccionado',
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

        $topIds = CocinaConsumo::query()
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('producto_id');

        if ($topIds->isEmpty()) {
            return ['datasets' => [], 'labels' => $labels];
        }

        $productos = CocinaProducto::query()->whereIn('id', $topIds)->get()->keyBy('id');
        $colors = ['#0E7490', '#A68064', '#4D7C5E', '#ef4444', '#3B4C82'];

        $datasets = [];
        foreach ($topIds as $i => $pid) {
            $p = $productos->get($pid);
            $mapa = $this->buildMapaConsumo(fn ($q) => $q->where('producto_id', $pid));

            $data = [];
            foreach ($dias as $f) {
                $data[] = $mapa[$f] ?? null;
            }

            $datasets[] = [
                'label' => $p?->nombre ?? 'Producto ' . $pid,
                'data' => $data,
                'borderColor' => $colors[$i % count($colors)],
                'backgroundColor' => 'transparent',
                'fill' => false,
                'tension' => 0.3,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }
}
