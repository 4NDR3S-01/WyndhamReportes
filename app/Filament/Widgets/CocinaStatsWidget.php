<?php

namespace App\Filament\Widgets;

use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CocinaStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalConsumos = CocinaConsumo::query()->count();
        $totalProductos = CocinaProducto::query()->count();

        $ultimaFecha = CocinaConsumo::query()->max('fecha') ?: now()->toDateString();

        $consumoUltimaFecha = (float) CocinaConsumo::query()
            ->whereDate('fecha', $ultimaFecha)
            ->sum('cantidad');

        $productosUltimaFecha = (int) CocinaConsumo::query()
            ->whereDate('fecha', $ultimaFecha)
            ->distinct('producto_id')
            ->count('producto_id');

        $fechasRegistradas = (int) CocinaConsumo::query()
            ->distinct('fecha')
            ->count('fecha');

        return [
            Stat::make('Consumos totales', number_format($totalConsumos, 0, ',', '.'))
                ->description('Filas procesadas')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Productos', number_format($totalProductos, 0, ',', '.'))
                ->description('Distintos en archivos')
                ->descriptionIcon('heroicon-o-tag')
                ->color('success'),

            Stat::make('Ultimo dia registrado', Carbon::parse($ultimaFecha)->format('d/m/Y'))
                ->description(number_format($consumoUltimaFecha, 2, ',', '.') . ' consumido')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Dias cubiertos', number_format($fechasRegistradas, 0, ',', '.'))
                ->description($productosUltimaFecha . ' productos el ultimo dia')
                ->descriptionIcon('heroicon-o-squares-2x2')
                ->color('danger'),
        ];
    }
}
