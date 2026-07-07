<?php

namespace App\Filament\Pages;

use App\Services\Medico\KardexMensualService;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MedicoKardexMensual extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static string|\UnitEnum|null $navigationGroup = 'Medico';
    protected static ?string $navigationLabel = 'Kardex mensual';
    protected static ?string $title = 'Kardex mensual';
    protected ?string $heading = '';
    protected static ?string $slug = 'medico/kardex-mensual';
    protected static ?int $navigationSort = 6;
    protected string $view = 'filament.pages.medico-kardex-mensual';

    // ============================================================
    // Estado del formulario
    // ============================================================
    public string $mes;
    public string $desde;
    public string $hasta;

    // ============================================================
    // Kardex generado (en memoria)
    // ============================================================
    /** @var array<int, array>|null */
    public ?array $items = null;
    public bool $cerrado = false;
    public ?string $kardexDesde = null;
    public ?string $kardexHasta = null;

    // ============================================================
    // Historial
    // ============================================================
    public ?string $historialSeleccionado = null;

    public function mount(): void
    {
        $this->mes = now()->format('Y-m');
        $this->aplicarMes();
    }

    public function aplicarMes(): void
    {
        $fecha = Carbon::parse($this->mes . '-01');
        $this->desde = $fecha->copy()->startOfMonth()->toDateString();
        $this->hasta = $fecha->copy()->endOfMonth()->toDateString();
    }

    public function generar(KardexMensualService $service): void
    {
        $this->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $this->items = $service->generar($this->desde, $this->hasta);
        $this->kardexDesde = $this->desde;
        $this->kardexHasta = $this->hasta;
        $this->cerrado = false;

        Notification::make()->title('Kardex generado — ' . count($this->items) . ' productos')->success()->send();
    }

    public function cerrar(): void
    {
        if (! $this->items) {
            Notification::make()->title('Primero genera un Kardex')->warning()->send();
            return;
        }
        $this->cerrado = true;
        Notification::make()->title('Kardex cerrado (no se modificará)')->success()->send();
    }

    public function reabrir(): void
    {
        $this->cerrado = false;
        Notification::make()->title('Kardex reabierto para edición')->warning()->send();
    }

    public function cargarHistorial(string $ym): void
    {
        $fecha = Carbon::parse($ym . '-01');
        $this->historialSeleccionado = $ym;
        $this->desde = $fecha->copy()->startOfMonth()->toDateString();
        $this->hasta = $fecha->copy()->endOfMonth()->toDateString();
        $this->mes = $ym;
    }

    public function exportar(): mixed
    {
        if (! $this->items) {
            Notification::make()->title('Primero genera un Kardex')->warning()->send();
            return null;
        }

        $inicioStr = Carbon::parse($this->kardexDesde)->format('d/m/Y');
        $finStr = Carbon::parse($this->kardexHasta)->format('d/m/Y');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KARDEX');
        $sheet->fromArray(['DISPENSARIO MEDICO'], null, 'A1');
        $sheet->fromArray(['KARDEX ARQUEO MENSUAL'], null, 'A2');
        $sheet->fromArray(["DEL {$inicioStr} AL {$finStr}"], null, 'A3');

        $headers = ['MEDICINAS / EQUIPOS', 'SALDO ANTERIOR', 'INGRESOS', 'EGRESOS', 'TOTAL', 'FECHA DE CADUCIDAD', 'FECHA INICIO', 'FECHA FIN'];
        $sheet->fromArray($headers, null, 'A5');
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        $row = 6;
        foreach ($this->items as $item) {
            $sheet->setCellValue("A{$row}", $item['nombre']);
            $sheet->setCellValue("B{$row}", $item['saldo_anterior']);
            $sheet->setCellValue("C{$row}", $item['ingresos']);
            $sheet->setCellValue("D{$row}", $item['egresos']);
            $sheet->setCellValue("E{$row}", $item['total']);
            $sheet->setCellValue("F{$row}", $item['fecha_caducidad'] ?? '');
            $sheet->setCellValue("G{$row}", $this->kardexDesde);
            $sheet->setCellValue("H{$row}", $this->kardexHasta);
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setWidth($column === 'A' ? 34 : 18);
        }

        $filename = 'kardex-medico-' . Carbon::parse($this->kardexDesde)->format('Ymd') . '-' . Carbon::parse($this->kardexHasta)->format('Ymd') . '.xlsx';
        $path = storage_path("app/tmp/{$filename}");
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend();
    }

    // ============================================================
    // Computed
    // ============================================================

    public function getMesesDisponiblesProperty(): Collection
    {
        return app(KardexMensualService::class)->mesesDisponibles();
    }

    public function getItemsCountProperty(): int
    {
        return count($this->items ?? []);
    }

    public function getTotalIngresosProperty(): float
    {
        return collect($this->items ?? [])->sum('ingresos');
    }

    public function getTotalEgresosProperty(): float
    {
        return collect($this->items ?? [])->sum('egresos');
    }

    public function getTotalSaldoProperty(): float
    {
        return collect($this->items ?? [])->sum('total');
    }
}
