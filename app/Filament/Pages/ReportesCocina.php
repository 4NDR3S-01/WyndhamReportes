<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportesCocina extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string|\UnitEnum|null $navigationGroup = 'Cocina';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes de cocina';

    protected ?string $heading = '';

    protected static ?string $slug = 'cocina/reportes';

    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.pages.reportes-cocina';

    public ?string $desde = null;

    public ?string $hasta = null;

    public function mount(): void
    {
        $this->desde = \App\Models\CocinaConsumo::query()->min('fecha') ?? now()->toDateString();
        $this->hasta = \App\Models\CocinaConsumo::query()->max('fecha') ?? now()->toDateString();
    }

    public function descargarDia(string $fecha): mixed
    {
        $consumos = \App\Models\CocinaConsumo::query()
            ->with('producto')
            ->whereDate('fecha', $fecha)
            ->selectRaw('producto_id, SUM(cantidad) as total')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consumo ' . $fecha);

        $sheet->setCellValue('A1', 'Producto');
        $sheet->setCellValue('B1', 'Unidad');
        $sheet->setCellValue('C1', 'Cantidad');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $row = 2;
        foreach ($consumos as $c) {
            $sheet->setCellValue("A{$row}", $c->producto?->nombre ?? '');
            $sheet->setCellValue("B{$row}", $c->producto?->unidad_medida ?? '');
            $sheet->setCellValue("C{$row}", (float) $c->total);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);

        $path = storage_path('app/tmp/reporte-cocina-' . $fecha . '.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, "consumo-{$fecha}.xlsx")->deleteFileAfterSend();
    }

    public function descargarTodo(): mixed
    {
        $consumos = \App\Models\CocinaConsumo::query()
            ->with('producto')
            ->selectRaw('fecha, producto_id, SUM(cantidad) as total')
            ->groupBy('fecha', 'producto_id')
            ->orderBy('fecha')
            ->orderByDesc('total')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consumo general');

        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Producto');
        $sheet->setCellValue('C1', 'Unidad');
        $sheet->setCellValue('D1', 'Cantidad');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $row = 2;
        foreach ($consumos as $c) {
            $sheet->setCellValue("A{$row}", $c->fecha);
            $sheet->setCellValue("B{$row}", $c->producto?->nombre ?? '');
            $sheet->setCellValue("C{$row}", $c->producto?->unidad_medida ?? '');
            $sheet->setCellValue("D{$row}", (float) $c->total);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        $path = storage_path('app/tmp/reporte-cocina-general.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'consumo-general.xlsx')->deleteFileAfterSend();
    }

    public function descargarRango(): mixed
    {
        $this->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $consumos = \App\Models\CocinaConsumo::query()
            ->with('producto')
            ->whereDate('fecha', '>=', $this->desde)
            ->whereDate('fecha', '<=', $this->hasta)
            ->selectRaw('fecha, producto_id, SUM(cantidad) as total')
            ->groupBy('fecha', 'producto_id')
            ->orderBy('fecha')
            ->orderByDesc('total')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("Consumo {$this->desde} a {$this->hasta}");

        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Producto');
        $sheet->setCellValue('C1', 'Unidad');
        $sheet->setCellValue('D1', 'Cantidad');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        $row = 2;
        foreach ($consumos as $c) {
            $sheet->setCellValue("A{$row}", $c->fecha);
            $sheet->setCellValue("B{$row}", $c->producto?->nombre ?? '');
            $sheet->setCellValue("C{$row}", $c->producto?->unidad_medida ?? '');
            $sheet->setCellValue("D{$row}", (float) $c->total);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);

        $path = storage_path('app/tmp/reporte-cocina-rango.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, "consumo-{$this->desde}-a-{$this->hasta}.xlsx")->deleteFileAfterSend();
    }

    public function getFechasDisponiblesProperty(): \Illuminate\Support\Collection
    {
        return \App\Models\CocinaConsumo::query()
            ->distinct('fecha')
            ->orderBy('fecha', 'desc')
            ->pluck('fecha');
    }

    public function getErroresImportacionProperty(): \Illuminate\Support\Collection
    {
        return \App\Models\CocinaImportacionError::query()
            ->with('archivoImportado')
            ->latest()
            ->limit(20)
            ->get();
    }

    public function getSemanasProperty(): \Illuminate\Support\Collection
    {
        $fechas = \App\Models\CocinaConsumo::query()
            ->selectRaw('strftime(\'%Y-%W\', fecha) as semana, MIN(fecha) as inicio, MAX(fecha) as fin')
            ->groupBy('semana')
            ->orderBy('inicio')
            ->get();

        return $fechas->map(function ($f) {
            $registros = (int) \App\Models\CocinaConsumo::query()
                ->whereBetween('fecha', [$f->inicio, $f->fin])
                ->count();
            $productos = (int) \App\Models\CocinaConsumo::query()
                ->whereBetween('fecha', [$f->inicio, $f->fin])
                ->distinct('producto_id')
                ->count('producto_id');

            return (object) [
                'inicio' => $f->inicio,
                'fin' => $f->fin,
                'total' => $registros,
                'productos' => $productos,
            ];
        });
    }
}
