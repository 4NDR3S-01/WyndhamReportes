<?php

namespace App\Filament\Pages;

use App\Models\MedicoKardexCierre;
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
    protected static ?string $slug = 'medico/kardex-mensual';
    protected static ?int $navigationSort = 8;
    protected string $view = 'filament.pages.medico-kardex-mensual';

    public string $mes;
    public string $desde;
    public string $hasta;
    public ?int $cierreId = null;

    public function mount(): void
    {
        $this->mes = now()->format('Y-m');
        $this->aplicarMes();
        $this->cierreId = MedicoKardexCierre::query()->latest('id')->value('id');
    }

    public function aplicarMes(): void
    {
        $fecha = Carbon::parse($this->mes . '-01');
        $this->desde = $fecha->copy()->startOfMonth()->toDateString();
        $this->hasta = $fecha->copy()->endOfMonth()->toDateString();
    }

    public function generar(KardexMensualService $service): void
    {
        $this->validate(['desde' => ['required', 'date'], 'hasta' => ['required', 'date', 'after_or_equal:desde']]);
        $cierre = $service->generar($this->desde, $this->hasta, cerrar: false);
        $this->cierreId = $cierre->id;
        Notification::make()->title('Kardex generado')->success()->send();
    }

    public function cerrar(KardexMensualService $service): void
    {
        $this->validate(['desde' => ['required', 'date'], 'hasta' => ['required', 'date', 'after_or_equal:desde']]);
        $cierre = $service->generar($this->desde, $this->hasta, cerrar: true);
        $this->cierreId = $cierre->id;
        Notification::make()->title('Kardex cerrado')->success()->send();
    }

    public function reabrir(int $id): void
    {
        MedicoKardexCierre::query()->findOrFail($id)->update(['estado' => 'abierto', 'cerrado_en' => null]);
        Notification::make()->title('Kardex reabierto')->warning()->send();
    }

    public function exportar(): mixed
    {
        $cierre = $this->cierre;
        if (! $cierre) {
            Notification::make()->title('Primero genera un Kardex')->warning()->send();
            return null;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KARDEX');
        $sheet->setCellValue('A1', 'DISPENSARIO MEDICO');
        $sheet->setCellValue('A2', 'KARDEX ARQUEO MENSUAL');
        $sheet->setCellValue('A3', 'DEL ' . $cierre->fecha_inicio->format('d/m/Y') . ' AL ' . $cierre->fecha_fin->format('d/m/Y'));

        $headers = ['MEDICINAS / EQUIPOS', 'SALDO ANTERIOR', 'INGRESOS', 'EGRESOS', 'TOTAL', 'FECHA DE CADUCIDAD', 'FECHA INICIO MES', 'FECHA FIN MES'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue(chr(65 + $i) . '5', $header);
        }
        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        $row = 6;
        foreach ($cierre->items as $item) {
            $sheet->setCellValue("A{$row}", $item->nombre);
            $sheet->setCellValue("B{$row}", $item->saldo_anterior);
            $sheet->setCellValue("C{$row}", $item->ingresos);
            $sheet->setCellValue("D{$row}", $item->egresos);
            $sheet->setCellValue("E{$row}", $item->total);
            $sheet->setCellValue("F{$row}", $item->fecha_caducidad?->format('Y-m-d'));
            $sheet->setCellValue("G{$row}", $cierre->fecha_inicio->format('Y-m-d'));
            $sheet->setCellValue("H{$row}", $cierre->fecha_fin->format('Y-m-d'));
            $row++;
        }

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setWidth($column === 'A' ? 34 : 18);
        }

        $path = storage_path("app/tmp/kardex-medico-{$cierre->fecha_inicio->format('Ymd')}-{$cierre->fecha_fin->format('Ymd')}.xlsx");
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, basename($path))->deleteFileAfterSend();
    }

    public function getCierresProperty(): Collection
    {
        return MedicoKardexCierre::query()->withCount('items')->latest('fecha_inicio')->limit(20)->get();
    }

    public function getCierreProperty(): ?MedicoKardexCierre
    {
        return $this->cierreId ? MedicoKardexCierre::query()->with('items')->find($this->cierreId) : null;
    }
}
