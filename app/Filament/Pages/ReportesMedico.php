<?php

namespace App\Filament\Pages;

use App\Models\MedicoKardex;
use App\Models\MedicoKardexMovimiento;
use App\Models\MedicoParteDiario;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportesMedico extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedChartPie;

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes de medico';

    protected static ?string $slug = 'medico/reportes';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.reportes-medico';

    public ?string $desde = null;
    public ?string $hasta = null;

    public function mount(): void
    {
        $this->desde = MedicoParteDiario::query()->min('fecha') ?? now()->toDateString();
        $this->hasta = MedicoParteDiario::query()->max('fecha') ?? now()->toDateString();
    }

    public function descargarParteDiarioRango(): mixed
    {
        $this->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ]);

        $partes = MedicoParteDiario::query()
            ->whereDate('fecha', '>=', $this->desde)
            ->whereDate('fecha', '<=', $this->hasta)
            ->orderBy('fecha')
            ->orderBy('nombres')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle("{$this->desde} a {$this->hasta}");

        $headers = ['Fecha', 'Nombres', 'Edad', 'Area', 'Cargo', 'Certificados', 'Subsidio', 'Horas', 'Dias', 'Inicio Cert.', 'Fin Cert.', 'Medico', 'Causa', 'Diagnostico', 'Medicamento 1', 'Medicamento 2', 'Medicamento 3', 'Observacion'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue("{$col}1", $h);
        }
        $sheet->getStyle('A1:R1')->getFont()->setBold(true);

        $row = 2;
        foreach ($partes as $p) {
            $sheet->setCellValue("A{$row}", $p->fecha);
            $sheet->setCellValue("B{$row}", $p->nombres);
            $sheet->setCellValue("C{$row}", $p->edad);
            $sheet->setCellValue("D{$row}", $p->area);
            $sheet->setCellValue("E{$row}", $p->cargo);
            $sheet->setCellValue("F{$row}", $p->certificados);
            $sheet->setCellValue("G{$row}", $p->subsidio);
            $sheet->setCellValue("H{$row}", $p->horas_certificado);
            $sheet->setCellValue("I{$row}", $p->dias_certificado);
            $sheet->setCellValue("J{$row}", $p->fecha_inicio_certificado);
            $sheet->setCellValue("K{$row}", $p->fecha_fin_certificado);
            $sheet->setCellValue("L{$row}", $p->medico_certifica);
            $sheet->setCellValue("M{$row}", $p->causa);
            $sheet->setCellValue("N{$row}", $p->diagnostico);
            $sheet->setCellValue("O{$row}", $p->medicamento_1);
            $sheet->setCellValue("P{$row}", $p->medicamento_2);
            $sheet->setCellValue("Q{$row}", $p->medicamento_3);
            $sheet->setCellValue("R{$row}", $p->observacion);
            $row++;
        }

        foreach (range('A', 'R') as $c) {
            $sheet->getColumnDimension($c)->setWidth(18);
        }

        $path = storage_path("app/tmp/reporte-medico-{$this->desde}-{$this->hasta}.xlsx");
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, "parte-diario-{$this->desde}-a-{$this->hasta}.xlsx")->deleteFileAfterSend();
    }

    public function descargarKardex(): mixed
    {
        $items = MedicoKardex::query()
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KARDEX');

        $sheet->setCellValue('A1', 'Periodo');
        $sheet->setCellValue('B1', 'Tipo');
        $sheet->setCellValue('C1', 'Nombre');
        $sheet->setCellValue('D1', 'Saldo Anterior');
        $sheet->setCellValue('E1', 'Ingresos');
        $sheet->setCellValue('F1', 'Egresos');
        $sheet->setCellValue('G1', 'Total');
        $sheet->setCellValue('H1', 'Fecha Caducidad');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $item) {
            $sheet->setCellValue("A{$row}", "{$item->fecha_inicio} al {$item->fecha_fin}");
            $sheet->setCellValue("B{$row}", $item->tipo);
            $sheet->setCellValue("C{$row}", $item->nombre);
            $sheet->setCellValue("D{$row}", $item->saldo_anterior);
            $sheet->setCellValue("E{$row}", $item->ingresos);
            $sheet->setCellValue("F{$row}", $item->egresos);
            $sheet->setCellValue("G{$row}", $item->total);
            $sheet->setCellValue("H{$row}", $item->fecha_caducidad);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(30);

        $path = storage_path('app/tmp/kardex-medico.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'kardex-medico.xlsx')->deleteFileAfterSend();
    }

    public function descargarKardexConMovimientos(): mixed
    {
        $items = MedicoKardex::query()
            ->with('movimientos')
            ->where('tipo', 'medicina')
            ->orderBy('nombre')
            ->orderByDesc('fecha_fin')
            ->orderByDesc('id')
            ->get()
            ->unique('nombre')
            ->values();

        $movimientos = MedicoKardexMovimiento::query()
            ->with(['kardex', 'producto'])
            ->orderBy('fecha_movimiento')
            ->orderBy('id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $resumen = $spreadsheet->getActiveSheet();
        $resumen->setTitle('Resumen KARDEX');

        $headers = ['Medicamento', 'Saldo Anterior', 'Ingresos Excel', 'Egresos Excel', 'Total Ref.', 'Consumos', 'Saldo Actual', 'Periodo', 'Caducidad'];
        foreach ($headers as $i => $h) {
            $resumen->setCellValue(chr(65 + $i) . '1', $h);
        }
        $resumen->getStyle('A1:I1')->getFont()->setBold(true);

        $row = 2;
        foreach ($items as $item) {
            $resumen->setCellValue("A{$row}", $item->nombre);
            $resumen->setCellValue("B{$row}", $item->saldo_anterior);
            $resumen->setCellValue("C{$row}", $item->ingresos);
            $resumen->setCellValue("D{$row}", $item->egresos);
            $resumen->setCellValue("E{$row}", $item->total);
            $resumen->setCellValue("F{$row}", $item->totalSalidas());
            $resumen->setCellValue("G{$row}", $item->saldoActual());
            $resumen->setCellValue("H{$row}", "{$item->fecha_inicio} al {$item->fecha_fin}");
            $resumen->setCellValue("I{$row}", $item->fecha_caducidad);
            $row++;
        }

        foreach (range('A', 'I') as $c) {
            $resumen->getColumnDimension($c)->setWidth(18);
        }
        $resumen->getColumnDimension('A')->setWidth(35);
        $resumen->getColumnDimension('H')->setWidth(28);

        $detalle = $spreadsheet->createSheet();
        $detalle->setTitle('Movimientos');
        $detalleHeaders = ['Fecha', 'Medicamento', 'Tipo', 'Cantidad', 'Saldo Resultante', 'Responsable', 'Paciente', 'Archivo'];
        foreach ($detalleHeaders as $i => $h) {
            $detalle->setCellValue(chr(65 + $i) . '1', $h);
        }
        $detalle->getStyle('A1:H1')->getFont()->setBold(true);

        $row = 2;
        foreach ($movimientos as $mov) {
            $detalle->setCellValue("A{$row}", $mov->fecha_movimiento?->format('Y-m-d'));
            $detalle->setCellValue("B{$row}", $mov->producto?->nombre ?? $mov->observacion);
            $detalle->setCellValue("C{$row}", $mov->tipo);
            $detalle->setCellValue("D{$row}", $mov->cantidad);
            $detalle->setCellValue("E{$row}", $mov->origen);
            $detalle->setCellValue("F{$row}", $mov->personal_responsable);
            $detalle->setCellValue("G{$row}", $mov->kardex?->nombre);
            $detalle->setCellValue("H{$row}", $mov->observacion);
            $row++;
        }

        foreach (range('A', 'H') as $c) {
            $detalle->getColumnDimension($c)->setWidth(20);
        }
        $detalle->getColumnDimension('B')->setWidth(35);
        $detalle->getColumnDimension('F')->setWidth(28);
        $detalle->getColumnDimension('G')->setWidth(35);

        $path = storage_path('app/tmp/kardex-medico-movimientos.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'kardex-medico-movimientos.xlsx')->deleteFileAfterSend();
    }

    public function descargarDia(string $fecha): mixed
    {
        $partes = MedicoParteDiario::query()
            ->whereDate('fecha', $fecha)
            ->orderBy('nombres')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Parte diario ' . $fecha);

        $sheet->setCellValue('A1', 'Nombres');
        $sheet->setCellValue('B1', 'Area');
        $sheet->setCellValue('C1', 'Cargo');
        $sheet->setCellValue('D1', 'Causa');
        $sheet->setCellValue('E1', 'Diagnostico');
        $sheet->setCellValue('F1', 'Certificados');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $row = 2;
        foreach ($partes as $p) {
            $sheet->setCellValue("A{$row}", $p->nombres);
            $sheet->setCellValue("B{$row}", $p->area);
            $sheet->setCellValue("C{$row}", $p->cargo);
            $sheet->setCellValue("D{$row}", $p->causa);
            $sheet->setCellValue("E{$row}", $p->diagnostico);
            $sheet->setCellValue("F{$row}", $p->certificados);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(35);
        $sheet->getColumnDimension('F')->setWidth(15);

        $path = storage_path('app/tmp/reporte-medico-' . $fecha . '.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, "parte-diario-{$fecha}.xlsx")->deleteFileAfterSend();
    }

    public function descargarTodo(): mixed
    {
        $partes = MedicoParteDiario::query()
            ->orderBy('fecha')
            ->orderBy('nombres')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Parte diario completo');

        $sheet->setCellValue('A1', 'Fecha');
        $sheet->setCellValue('B1', 'Nombres');
        $sheet->setCellValue('C1', 'Area');
        $sheet->setCellValue('D1', 'Cargo');
        $sheet->setCellValue('E1', 'Causa');
        $sheet->setCellValue('F1', 'Diagnostico');
        $sheet->setCellValue('G1', 'Certificados');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $row = 2;
        foreach ($partes as $p) {
            $sheet->setCellValue("A{$row}", $p->fecha);
            $sheet->setCellValue("B{$row}", $p->nombres);
            $sheet->setCellValue("C{$row}", $p->area);
            $sheet->setCellValue("D{$row}", $p->cargo);
            $sheet->setCellValue("E{$row}", $p->causa);
            $sheet->setCellValue("F{$row}", $p->diagnostico);
            $sheet->setCellValue("G{$row}", $p->certificados);
            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);

        $path = storage_path('app/tmp/reporte-medico-general.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'parte-diario-general.xlsx')->deleteFileAfterSend();
    }

    public function getSemanasProperty(): Collection
    {
        $fechas = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%W', fecha) as semana, MIN(fecha) as inicio, MAX(fecha) as fin")
            ->groupBy('semana')
            ->orderBy('inicio')
            ->get();

        return $fechas->map(function ($f) {
            $atenciones = (int) MedicoParteDiario::query()
                ->whereBetween('fecha', [$f->inicio, $f->fin])
                ->count();
            $pacientes = (int) MedicoParteDiario::query()
                ->whereBetween('fecha', [$f->inicio, $f->fin])
                ->distinct('nombres')
                ->count('nombres');

            return (object) [
                'inicio' => $f->inicio,
                'fin' => $f->fin,
                'total' => $atenciones,
                'pacientes' => $pacientes,
            ];
        });
    }

    public function descargarKardexMensual(): mixed
    {
        $spreadsheet = new Spreadsheet();

        $meses = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, MIN(fecha) as inicio, MAX(fecha) as fin")
            ->groupBy('ym')->orderBy('ym')->get();

        $first = true;
        foreach ($meses as $i => $m) {
            if ($first) {
                $sheet = $spreadsheet->getActiveSheet();
                $first = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }
            $label = Carbon::parse($m->inicio)->translatedFormat('F Y');
            $sheet->setTitle(substr($label, 0, 31));

            $query = MedicoParteDiario::query()->whereBetween('fecha', [$m->inicio, $m->fin]);

            $sheet->setCellValue('A1', 'KARDEX — ' . $label);
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

            $sheet->setCellValue('A3', 'Total atenciones');
            $sheet->setCellValue('B3', (int) (clone $query)->count());
            $sheet->setCellValue('A4', 'Pacientes distintos');
            $sheet->setCellValue('B4', (int) (clone $query)->distinct('nombres')->count('nombres'));
            $sheet->setCellValue('A5', 'Dias con atencion');
            $sheet->setCellValue('B5', (int) (clone $query)->distinct('fecha')->count('fecha'));

            // Causas
            $sheet->setCellValue('A7', 'CAUSAS');
            $sheet->getStyle('A7')->getFont()->setBold(true);
            $sheet->setCellValue('A8', 'Causa');
            $sheet->setCellValue('B8', 'Cantidad');
            $row = 9;
            foreach ((clone $query)->selectRaw('causa, COUNT(*) as c')->whereNotNull('causa')->where('causa', '!=', '')->groupBy('causa')->orderByDesc('c')->get() as $c) {
                $sheet->setCellValue("A{$row}", $c->causa);
                $sheet->setCellValue("B{$row}", $c->c);
                $row++;
            }

            // Medicamentos
            $row += 1;
            $sheet->setCellValue("A{$row}", 'MEDICAMENTOS USADOS');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Medicamento');
            $sheet->setCellValue("B{$row}", 'Veces');
            $row++;
            $meds = collect();
            foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $col) {
                (clone $query)->whereNotNull($col)->where($col, '!=', '')->select($col)->get()
                    ->each(fn ($r) => $meds->push($r->$col));
            }
            foreach ($meds->countBy()->sortDesc() as $nombre => $count) {
                $sheet->setCellValue("A{$row}", $nombre);
                $sheet->setCellValue("B{$row}", $count);
                $row++;
            }

            // Areas
            $row += 1;
            $sheet->setCellValue("A{$row}", 'AREAS');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
            $sheet->setCellValue("A{$row}", 'Area');
            $sheet->setCellValue("B{$row}", 'Cantidad');
            $row++;
            foreach ((clone $query)->selectRaw('area, COUNT(*) as c')->whereNotNull('area')->where('area', '!=', '')->groupBy('area')->orderByDesc('c')->get() as $a) {
                $sheet->setCellValue("A{$row}", $a->area);
                $sheet->setCellValue("B{$row}", $a->c);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(40);
            $sheet->getColumnDimension('B')->setWidth(15);
        }

        $path = storage_path('app/tmp/kardex-mensual-medico.xlsx');
        @mkdir(dirname($path), 0777, true);
        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, 'kardex-mensual-medico.xlsx')->deleteFileAfterSend();
    }

    public function getKardexMensualDetalladoProperty(): Collection
    {
        if (MedicoParteDiario::query()->count() === 0) return collect();

        $meses = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, MIN(fecha) as inicio, MAX(fecha) as fin")
            ->groupBy('ym')->orderBy('ym', 'desc')->get();

        return $meses->map(function ($m) {
            $query = MedicoParteDiario::query()->whereBetween('fecha', [$m->inicio, $m->fin]);

            $causas = (clone $query)->selectRaw('causa, COUNT(*) as total')
                ->whereNotNull('causa')->where('causa', '!=', '')
                ->groupBy('causa')->orderByDesc('total')->get();

            $areas = (clone $query)->selectRaw('area, COUNT(*) as total')
                ->whereNotNull('area')->where('area', '!=', '')
                ->groupBy('area')->orderByDesc('total')->get();

            $meds = collect();
            foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $col) {
                (clone $query)->whereNotNull($col)->where($col, '!=', '')->select($col)->get()
                    ->each(fn ($r) => $meds->push($r->$col));
            }
            $medsCount = $meds->countBy()->sortDesc()->take(8);

            return (object) [
                'mes' => \Carbon\Carbon::parse($m->inicio)->translatedFormat('F Y'),
                'inicio' => $m->inicio,
                'fin' => $m->fin,
                'atenciones' => (int) (clone $query)->count(),
                'pacientes' => (int) (clone $query)->distinct('nombres')->count('nombres'),
                'dias' => (int) (clone $query)->distinct('fecha')->count('fecha'),
                'causas' => $causas,
                'areas' => $areas,
                'medicamentos' => $medsCount,
            ];
        });
    }

    public function getKardexMensualProperty(): Collection
    {
        if (MedicoParteDiario::query()->count() === 0) {
            return collect();
        }

        $meses = MedicoParteDiario::query()
            ->selectRaw("strftime('%Y-%m', fecha) as ym, MIN(fecha) as inicio, MAX(fecha) as fin")
            ->groupBy('ym')
            ->orderBy('ym', 'desc')
            ->get();

        return $meses->map(function ($m) {
            $query = MedicoParteDiario::query()->whereBetween('fecha', [$m->inicio, $m->fin]);

            $atenciones = (int) (clone $query)->count();
            $pacientes = (int) (clone $query)->distinct('nombres')->count('nombres');
            $conCert = (int) (clone $query)->whereNotNull('certificados')
                ->whereNotIn('certificados', ['SIN CERTIFICADO', ''])
                ->count();
            $sinCert = $atenciones - $conCert;

            $causaPrincipal = (clone $query)
                ->selectRaw('causa, COUNT(*) as total')
                ->whereNotNull('causa')->where('causa', '!=', '')
                ->groupBy('causa')->orderByDesc('total')->first();

            $areaPrincipal = (clone $query)
                ->selectRaw('area, COUNT(*) as total')
                ->whereNotNull('area')->where('area', '!=', '')
                ->groupBy('area')->orderByDesc('total')->first();

            $meds = collect();
            foreach (['medicamento_1', 'medicamento_2', 'medicamento_3'] as $col) {
                (clone $query)->whereNotNull($col)->where($col, '!=', '')
                    ->select($col)->get()
                    ->each(fn ($r) => $meds->push($r->$col));
            }
            $medsUsados = $meds->unique()->count();

            return (object) [
                'mes' => \Carbon\Carbon::parse($m->inicio)->format('F Y'),
                'inicio' => $m->inicio,
                'fin' => $m->fin,
                'atenciones' => $atenciones,
                'pacientes' => $pacientes,
                'conCertificado' => $conCert,
                'sinCertificado' => $sinCert,
                'causaPrincipal' => $causaPrincipal?->causa ?? '-',
                'areaPrincipal' => $areaPrincipal?->area ?? '-',
                'medicamentosUsados' => $medsUsados,
            ];
        });
    }

    public function getFechasDisponiblesProperty(): Collection
    {
        return MedicoParteDiario::query()
            ->distinct('fecha')
            ->orderBy('fecha', 'desc')
            ->pluck('fecha');
    }

    public function getKardexProperty(): Collection
    {
        return MedicoKardex::query()
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn ($item) => "{$item->fecha_inicio} al {$item->fecha_fin}");
    }

    public function getKardexMovimientosProperty(): Collection
    {
        return MedicoKardexMovimiento::query()
            ->with(['kardex'])
            ->latest('fecha_movimiento')
            ->latest('id')
            ->limit(50)
            ->get();
    }
}
