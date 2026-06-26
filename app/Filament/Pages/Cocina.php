<?php

namespace App\Filament\Pages;

use App\Models\CocinaConsumo;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Cocina extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Cocina';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard de Cocina';

    protected static ?string $slug = 'cocina';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.cocina';

    public ?string $fechaSeleccionada = null;

    public ?string $fechaReferencia = null;

    public ?int $huespedesReferencia = null;

    public ?int $huespedesObjetivo = null;

    public function mount(): void
    {
        $max = CocinaConsumo::query()->max('fecha');
        $this->fechaSeleccionada = $max ? \Carbon\Carbon::parse($max)->format('Y-m-d') : null;
        $this->fechaReferencia = $this->fechaSeleccionada;
    }

    public function updatedFechaSeleccionada(): void {}

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return static::getRouteName();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CocinaStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\CocinaProductosChartWidget::class,
            \App\Filament\Widgets\CocinaConsumoDiarioWidget::class,
        ];
    }

    public function getMinFechaProperty(): ?string
    {
        return CocinaConsumo::query()->min('fecha');
    }

    public function getMaxFechaProperty(): ?string
    {
        return CocinaConsumo::query()->max('fecha');
    }

    public function getTotalProperty(): int
    {
        return (int) CocinaConsumo::query()->count();
    }

    public function getProductosCountProperty(): int
    {
        return (int) \App\Models\CocinaProducto::query()->count();
    }

    public function getArchivosProperty(): int
    {
        return (int) \App\Models\CocinaArchivoImportado::query()->count();
    }

    public function getFechasDisponiblesProperty(): Collection
    {
        return CocinaConsumo::query()
            ->distinct('fecha')
            ->orderBy('fecha', 'desc')
            ->pluck('fecha');
    }

    public function getConsumoDelDiaProperty(): array
    {
        if (! $this->fechaSeleccionada || $this->total === 0) {
            return [];
        }

        $agrupado = [];
        $filas = CocinaConsumo::query()
            ->with('producto')
            ->whereDate('fecha', $this->fechaSeleccionada)
            ->select('producto_id')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->groupBy('producto_id')
            ->orderByDesc('total_cantidad')
            ->get();

        foreach ($filas as $fila) {
            $u = mb_strtolower(trim($fila->producto?->unidad_medida ?? 'unidad'));
            // Normalizar la clave de unidad
            $clave = match (true) {
                str_contains($u, 'kilo') => 'kilo',
                str_contains($u, 'litro') => 'litro',
                str_contains($u, 'porcion') => 'porcion',
                str_contains($u, 'gramo') => 'gramo',
                default => 'unidad',
            };
            $agrupado[$clave][] = $fila;
        }

        return $agrupado;
    }

    public function getTotalesPorUnidadProperty(): array
    {
        $totales = [];
        foreach ($this->consumoDelDia as $u => $items) {
            $totales[$u] = array_sum(array_map(fn ($i) => $i->total_cantidad, $items));
        }

        return $totales;
    }

    public function getAnalisisProperty(): Collection
    {
        if ($this->total === 0) {
            return collect();
        }

        return \App\Models\CocinaProducto::query()
            ->whereHas('consumos')
            ->get()
            ->map(function (\App\Models\CocinaProducto $p) {
                $totalC = (float) $p->consumos()->sum('cantidad');
                $dias = (int) $p->consumos()->distinct('fecha')->count('fecha');
                $promedio = $dias > 0 ? $totalC / $dias : 0;

                $u = mb_strtolower(trim($p->unidad_medida ?? 'unidad'));
                $debeRedondear = (str_contains($u, 'unidad') || str_contains($u, 'porcion')) && fmod($totalC, 1) == 0;
                $totalC = $debeRedondear ? round($totalC) : round($totalC, 3);
                $promedio = $debeRedondear ? round($promedio) : round($promedio, 3);

                return (object) [
                    'nombre' => $p->nombre,
                    'unidad' => $p->unidad_medida ?: '-',
                    'total' => $totalC,
                    'dias' => $dias,
                    'promedio' => $promedio,
                    'esEntero' => $debeRedondear,
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();
    }

    public function formatoValor(float $valor, bool $esEntero = false): string
    {
        $formateado = number_format($esEntero ? round($valor) : $valor, $esEntero ? 0 : 3, ',', '.');

        if (! $esEntero) {
            $formateado = rtrim(rtrim($formateado, '0'), ',');
        }

        return $formateado;
    }

    public function formatoSegunUnidad(float $valor, string $unidadClave): string
    {
        $esEntero = ($unidadClave === 'unidad' || $unidadClave === 'porcion') && fmod($valor, 1) == 0;

        return number_format($valor, $esEntero ? 0 : 2, ',', '.');
    }

    public function getRecomendacionProperty(): Collection
    {
        if (! $this->fechaReferencia || ! $this->huespedesReferencia || ! $this->huespedesObjetivo || $this->total === 0) {
            return collect();
        }

        $factor = $this->huespedesObjetivo / max(1, $this->huespedesReferencia);

        return CocinaConsumo::query()
            ->with('producto')
            ->whereDate('fecha', $this->fechaReferencia)
            ->select('producto_id', 'unidad_medida')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->groupBy('producto_id', 'unidad_medida')
            ->orderByDesc('total_cantidad')
            ->get()
            ->map(function ($c) use ($factor) {
                $u = mb_strtolower(trim($c->unidad_medida ?? 'unidad'));
                $esEntero = (str_contains($u, 'unidad') || str_contains($u, 'porcion')) && fmod($c->total_cantidad, 1) == 0;
                $sugerido = $c->total_cantidad * $factor;
                $sugerido = $esEntero ? ceil($sugerido) : round($sugerido, 2);

                return (object) [
                    'nombre' => $c->producto?->nombre ?? 'Sin nombre',
                    'unidad' => $u,
                    'consumoBase' => $c->total_cantidad,
                    'sugerido' => $sugerido,
                    'esEntero' => $esEntero,
                ];
            })
            ->filter(fn ($r) => $r->sugerido > 0)
            ->values();
    }
}
