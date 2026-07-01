<?php

namespace App\Filament\Pages;

use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoParteDiario;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class Medico extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard de Medico';

    protected ?string $heading = '';

    protected static ?string $slug = 'medico';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.medico';

    public ?string $fechaSeleccionada = null;
    public ?string $mesSeleccionado = null;

    public int $totalAtenciones = 0;
    public int $totalPacientes = 0;
    public int $totalKardex = 0;
    public ?string $ultimaFechaStr = null;
    public int $atencionesHoy = 0;
    public int $diasCubiertos = 0;

    public function mount(): void
    {
        $max = MedicoParteDiario::query()->max('fecha');
        $this->fechaSeleccionada = $max ? Carbon::parse($max)->format('Y-m-d') : null;
        $this->mesSeleccionado = $max ? Carbon::parse($max)->format('Y-m') : null;

        $this->totalAtenciones = MedicoParteDiario::query()->count();
        $this->totalPacientes = (int) MedicoParteDiario::query()->distinct('nombres')->count('nombres');
        $this->totalKardex = MedicoKardex::query()->count();
        $this->ultimaFechaStr = $max ?: now()->toDateString();
        $this->atencionesHoy = (int) MedicoParteDiario::query()->whereDate('fecha', $this->ultimaFechaStr)->count();
        $this->diasCubiertos = (int) MedicoParteDiario::query()->distinct('fecha')->count('fecha');
    }

    public function updatedFechaSeleccionada(): void {}
    public function updatedMesSeleccionado(): void {}

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return static::getRouteName();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // El widget de estadísticas se maquetará directamente en blade.
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\MedicoKardexMensualWidget::class,
            \App\Filament\Widgets\MedicoAtencionesDiariasWidget::class,
            \App\Filament\Widgets\MedicoCausasChartWidget::class,
            \App\Filament\Widgets\MedicoAreasChartWidget::class,
        ];
    }

    public function getMinFechaProperty(): ?string
    {
        return MedicoParteDiario::query()->min('fecha');
    }

    public function getMaxFechaProperty(): ?string
    {
        return MedicoParteDiario::query()->max('fecha');
    }

    public function getTotalProperty(): int
    {
        return (int) MedicoParteDiario::query()->count();
    }

    public function getArchivosProperty(): int
    {
        return (int) \App\Models\MedicoArchivoImportado::query()->count();
    }

    public function getFechasDisponiblesProperty(): Collection
    {
        return MedicoParteDiario::query()
            ->distinct('fecha')
            ->orderBy('fecha', 'desc')
            ->pluck('fecha');
    }

    public function getMesesDisponiblesProperty(): Collection
    {
        return MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, MIN(fecha) as inicio")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->get()
            ->map(fn ($m) => (object) [
                'ym' => $m->ym,
                'label' => Carbon::parse($m->inicio)->translatedFormat('F Y'),
            ]);
    }

    public function getResumenDelMesProperty(): object
    {
        if (! $this->mesSeleccionado) {
            return (object) ['atenciones' => 0, 'pacientes' => 0, 'dias' => 0, 'conCertificado' => 0, 'medicamentos' => 0];
        }

        [$y, $m] = explode('-', $this->mesSeleccionado);
        $inicio = "{$y}-{$m}-01";
        $fin = date('Y-m-t', strtotime($inicio));

        $query = MedicoParteDiario::query()->whereBetween('fecha', [$inicio, $fin]);

        $atenciones = (int) (clone $query)->count();
        $pacientes = (int) (clone $query)->distinct('nombres')->count('nombres');
        $dias = (int) (clone $query)->distinct('fecha')->count('fecha');
        $conCert = (int) (clone $query)->whereNotNull('certificados')
            ->whereNotIn('certificados', ['SIN CERTIFICADO', ''])->count();

        $meds = collect();
        foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $col) {
            (clone $query)->whereNotNull($col)->where($col, '!=', '')
                ->select($col)->get()
                ->each(fn ($r) => $meds->push($r->$col));
        }

        return (object) [
            'atenciones' => $atenciones,
            'pacientes' => $pacientes,
            'dias' => $dias,
            'conCertificado' => $conCert,
            'medicamentos' => $meds->unique()->count(),
        ];
    }

    public function getResumenDelDiaProperty(): object
    {
        if (! $this->fechaSeleccionada) {
            return (object) ['atenciones' => 0, 'areas' => 0, 'conCertificado' => 0, 'sinCertificado' => 0];
        }

        $atenciones = $this->atencionesDelDia;

        return (object) [
            'atenciones' => $atenciones->count(),
            'areas' => $atenciones->pluck('area')->unique()->filter()->count(),
            'conCertificado' => $atenciones->filter(fn ($a) => $a->certificados && $a->certificados !== 'SIN CERTIFICADO')->count(),
            'sinCertificado' => $atenciones->filter(fn ($a) => ! $a->certificados || $a->certificados === 'SIN CERTIFICADO')->count(),
        ];
    }

    public function getAtencionesDelDiaProperty(): Collection
    {
        if (! $this->fechaSeleccionada || $this->total === 0) {
            return collect();
        }

        return MedicoParteDiario::query()
            ->whereDate('fecha', $this->fechaSeleccionada)
            ->orderBy('area')
            ->orderBy('nombres')
            ->get();
    }

    public function getAtencionesPorAreaProperty(): array
    {
        $agrupado = [];
        foreach ($this->atencionesDelDia as $at) {
            $area = $at->area ?: 'Sin area';
            $agrupado[$area][] = $at;
        }
        return $agrupado;
    }

    public function getMedicamentosMasUsadosProperty(): Collection
    {
        if ($this->total === 0) return collect();

        $todos = collect();
        foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $col) {
            MedicoParteDiario::query()
                ->whereNotNull($col)->where($col, '!=', '')
                ->select($col)->get()
                ->each(fn ($p) => $todos->push($p->$col));
        }

        return $todos->countBy()->sortDesc()->take(10)
            ->map(fn ($c, $n) => (object) ['nombre' => $n, 'total' => $c])->values();
    }

    public function getKardexAlertasProperty(): Collection
    {
        return $this->kardexActual
            ->filter(fn (MedicoKardex $k) => $k->saldoActual() <= 2)
            ->sortBy(fn (MedicoKardex $k) => $k->saldoActual())
            ->values();
    }

    public function getKardexActualProperty(): Collection
    {
        return MedicoKardex::query()
            ->with('movimientos')
            ->where('tipo', 'medicina')
            ->orderBy('nombre')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->get()
            ->unique('nombre')
            ->values();
    }

    public function getEquiposProperty(): Collection
    {
        return MedicoKardex::query()
            ->where('tipo', 'equipo')
            ->orderBy('nombre')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->get()
            ->unique('nombre')
            ->values();
    }

    public function getMovimientosRecientesProperty(): Collection
    {
        return MedicoKardexMovimiento::query()
            ->with(['kardex', 'parteDiario'])
            ->latest('fecha_movimiento')
            ->latest('id')
            ->limit(20)
            ->get();
    }
}
