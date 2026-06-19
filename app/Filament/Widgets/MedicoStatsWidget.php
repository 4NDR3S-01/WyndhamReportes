<?php

namespace App\Filament\Widgets;

use App\Models\MedicoKardex;
use App\Models\MedicoParteDiario;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MedicoStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalAtenciones = MedicoParteDiario::query()->count();
        $totalPacientes = (int) MedicoParteDiario::query()->distinct('nombres')->count('nombres');
        $totalKardex = MedicoKardex::query()->count();

        $ultimaFecha = MedicoParteDiario::query()->max('fecha') ?: now()->toDateString();
        $atencionesHoy = (int) MedicoParteDiario::query()->whereDate('fecha', $ultimaFecha)->count();
        $diasCubiertos = (int) MedicoParteDiario::query()->distinct('fecha')->count('fecha');

        return [
            Stat::make('Atenciones totales', number_format($totalAtenciones, 0, ',', '.'))
                ->description('Consultas registradas')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Pacientes', number_format($totalPacientes, 0, ',', '.'))
                ->description('Personas distintas atendidas')
                ->descriptionIcon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Ultimo dia registrado', Carbon::parse($ultimaFecha)->format('d/m/Y'))
                ->description($atencionesHoy . ' atenciones')
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('warning'),

            Stat::make('Dias cubiertos', number_format($diasCubiertos, 0, ',', '.'))
                ->description($totalKardex . ' items en kardex')
                ->descriptionIcon('heroicon-o-squares-2x2')
                ->color('danger'),
        ];
    }
}
