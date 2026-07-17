<?php

namespace App\Filament\Widgets;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\On;

class CocinaConsumoDiarioWidget extends ChartWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.skeleton-chart';

    /** @var int|null Documento fuente activo en el dashboard */
    public ?int $archivoId = null;

    #[On('cocina-archivo-cambio')]
    public function onArchivoCambio($id = null): void
    {
        $this->archivoId = $id;
    }

    private function archivoId(): ?int
    {
        return $this->archivoId
            ?? CocinaArchivoImportado::query()->latest('fecha_subida')->value('id');
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): ?string
    {
        return 'Evolución del consumo diario';
    }

    public function getDescription(): ?string
    {
        return 'Total consumido por día, desglosado por producto en el documento seleccionado.';
    }

    protected function getFilters(): ?array
    {
        $aid = $this->archivoId();

        $productos = CocinaProducto::query()
            ->whereHas('consumos', function ($q) use ($aid): void {
                if ($aid) {
                    $q->where('archivo_importado_id', $aid);
                }
            })
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->toArray();

        return ['todos' => 'Todos los productos'] + $productos;
    }

    private function buildMapaConsumo(callable $queryModifier): array
    {
        $query = CocinaConsumo::query();

        if ($this->archivoId()) {
            $query->where('archivo_importado_id', $this->archivoId());
        }

        $queryModifier($query);

        $mapa = [];
        foreach ($query->selectRaw('fecha, SUM(cantidad) as total')->groupBy('fecha')->get() as $c) {
            $mapa[Carbon::parse($c->fecha)->format('Y-m-d')] = (float) $c->total;
        }

        return $mapa;
    }

    protected function getData(): array
    {
        $aid = $this->archivoId();

        $diasRaw = CocinaConsumo::query();

        if ($aid) {
            $diasRaw->where('archivo_importado_id', $aid);
        }

        $diasRaw = $diasRaw
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

        // Filtro: un solo producto -> una barra por dia (sin huecos).
        if ($this->filter && $this->filter !== 'todos') {
            $producto = CocinaProducto::query()->find($this->filter);
            $mapa = $this->buildMapaConsumo(fn ($q) => $q->where('producto_id', $this->filter));

            $data = [];
            foreach ($dias as $f) {
                $data[] = $mapa[$f] ?? 0;
            }

            return [
                'datasets' => [
                    [
                        'label' => $producto?->nombre ?? 'Producto seleccionado',
                        'data' => $data,
                        'backgroundColor' => '#0E749040',
                        'borderColor' => '#0E7490',
                        'fill' => true,
                        'tension' => 0.3,
                        'pointRadius' => 0,
                        'pointHitRadius' => 8,
                    ],
                ],
                'labels' => $labels,
            ];
        }

        // Todos: barras apiladas de los 5 productos mas consumidos.
        $topIds = CocinaConsumo::query();

        if ($aid) {
            $topIds->where('archivo_importado_id', $aid);
        }

        $topIds = $topIds
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('producto_id');

        if ($topIds->isEmpty()) {
            return ['datasets' => [], 'labels' => $labels];
        }

        $productos = CocinaProducto::query()->whereIn('id', $topIds)->get()->keyBy('id');
        $colors = ['#0E7490', '#A68064', '#4D7C5E', '#D9704A', '#3B4C82'];

        $datasets = [];
        foreach ($topIds as $i => $pid) {
            $p = $productos->get($pid);
            $mapa = $this->buildMapaConsumo(fn ($q) => $q->where('producto_id', $pid));

            $data = [];
            foreach ($dias as $f) {
                $data[] = $mapa[$f] ?? 0;
            }

            $datasets[] = [
                'label' => $p?->nombre ?? 'Producto ' . $pid,
                'data' => $data,
                'backgroundColor' => $colors[$i % count($colors)] . '40',
                'borderColor' => $colors[$i % count($colors)],
                'fill' => true,
                'tension' => 0.3,
                'pointRadius' => 0,
                'pointHitRadius' => 8,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'interaction' => [
                'mode' => 'nearest',
                'axis' => 'x',
                'intersect' => false,
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => ['boxWidth' => 12, 'usePointStyle' => true],
                ],
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],
            'scales' => [
                'x' => [
                    'stacked' => true,
                    'grid' => ['display' => false],
                    'title' => ['display' => true, 'text' => 'Fecha'],
                    'ticks' => ['maxRotation' => 0, 'autoSkip' => true, 'maxTicksLimit' => 14],
                ],
                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,
                    'title' => ['display' => true, 'text' => 'Cantidad'],
                    'ticks' => ['precision' => 0],
                ],
            ],
        ];
    }
}
