<?php

namespace App\Filament\Pages;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use App\Models\CocinaProducto;
use App\Services\Cocina\ProcesadorArchivoConsumo;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cocina extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static string|\UnitEnum|null $navigationGroup = 'Cocina';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard de Cocina';

    protected ?string $heading = '';

    protected static ?string $slug = 'cocina';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.cocina';

    public ?string $fechaSeleccionada = null;

    public ?string $fechaReferencia = null;

    public ?int $huespedesReferencia = null;

    /** @var int|null ID del documento fuente activo (el dashboard depende solo de él) */
    public ?int $archivoSeleccionadoId = null;

    public bool $modalArchivoAbierto = false;

    /** @var mixed Archivo en proceso de subida desde el dashboard */
    public mixed $archivo = null;

    /** @var array Fechas disponibles (Y-m-d) del documento activo, para el calendario reactivo */
    public array $fechasDisponiblesRaw = [];

    public function mount(): void
    {
        $ultimo = CocinaArchivoImportado::query()->latest('fecha_subida')->first();
        $this->archivoSeleccionadoId = $ultimo?->id;

        $this->syncFechasDisponibles();

        $max = $this->maxFecha;
        $this->fechaSeleccionada = $max ? \Carbon\Carbon::parse($max)->format('Y-m-d') : null;
        $this->fechaReferencia = $this->fechaSeleccionada;
    }

    public function updatedFechaSeleccionada(): void {}

    public function setFecha(string $campo, ?string $valor): void
    {
        if (! in_array($campo, ['fechaSeleccionada', 'fechaReferencia'], true)) {
            return;
        }

        // Ambas secciones comparten la misma fecha: cambiar una sincroniza la otra.
        $this->fechaSeleccionada = $valor;
        $this->fechaReferencia = $valor;
        $this->dispatch('$refresh');
    }

    /** Sincroniza las fechas disponibles (Y-m-d) del documento activo para el calendario reactivo. */
    protected function syncFechasDisponibles(): void
    {
        $this->fechasDisponiblesRaw = $this->fechasDisponibles
            ->map(fn ($f) => \Carbon\Carbon::parse($f)->format('Y-m-d'))
            ->values()
            ->all();
    }

    // ── Selector de documento fuente ──
    public function abrirModalArchivo(): void
    {
        $this->modalArchivoAbierto = true;
    }

    public function cerrarModalArchivo(): void
    {
        $this->modalArchivoAbierto = false;
    }

    public function seleccionarArchivo(int $id): void
    {
        $this->archivoSeleccionadoId = $id;

        $max = $this->maxFecha;
        $this->fechaSeleccionada = $max ? \Carbon\Carbon::parse($max)->format('Y-m-d') : null;
        $this->fechaReferencia = $this->fechaSeleccionada;

        $this->syncFechasDisponibles();

        $this->modalArchivoAbierto = false;
        $this->dispatch('cocina-archivo-cambio', id: $id);
        $this->dispatch('$refresh');
    }

    public function subirDesdeDashboard(): void
    {
        $this->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'archivo.required' => 'Selecciona un archivo para importar.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls) o CSV.',
            'archivo.max' => 'El archivo no debe superar los 10 MB.',
        ]);

        $nombreOriginal = $this->archivo->getClientOriginalName();
        $extension = $this->archivo->getClientOriginalExtension();
        $tamanoBytes = $this->archivo->getSize() ?: 0;
        $mimeType = $this->archivo->getMimeType();
        $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
        $nombreSeguro = now()->format('Ymd_His') . '_' . Str::slug($base) . '.' . $extension;

        $ruta = $this->archivo->storeAs('importaciones/cocina', $nombreSeguro);
        $this->archivo = null;

        $nuevo = CocinaArchivoImportado::query()->create([
            'usuario_id' => auth()->id(),
            'nombre_original' => $nombreOriginal,
            'nombre_guardado' => $nombreSeguro,
            'ruta' => $ruta,
            'extension' => mb_strtolower($extension),
            'mime_type' => $mimeType,
            'tamano_bytes' => $tamanoBytes,
            'estado' => 'recibido',
            'fecha_subida' => now(),
        ]);

        try {
            $procesador = app(ProcesadorArchivoConsumo::class);
            $resultado = $procesador->procesar($nuevo);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('No se pudo procesar el archivo')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->archivoSeleccionadoId = $nuevo->id;

        $max = $this->maxFecha;
        $this->fechaSeleccionada = $max ? \Carbon\Carbon::parse($max)->format('Y-m-d') : null;
        $this->fechaReferencia = $this->fechaSeleccionada;

        $this->syncFechasDisponibles();

        $this->modalArchivoAbierto = false;
        $this->dispatch('cocina-archivo-cambio', id: $nuevo->id);
        $this->dispatch('$refresh');

        $resumen = $resultado['errores'] > 0
            ? " Filas importadas: {$resultado['importadas']}. Errores: {$resultado['errores']}{$resultado['resumen']}. Duplicadas: {$resultado['duplicadas']}."
            : " Filas importadas: {$resultado['importadas']}. Errores: {$resultado['errores']}. Duplicadas: {$resultado['duplicadas']}.";

        Notification::make()
            ->title('Archivo cargado y procesado')
            ->body($resumen)
            ->success()
            ->send();
    }

    public static function getNavigationItemActiveRoutePattern(): string|array
    {
        return static::getRouteName();
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\CocinaProductosChartWidget::class,
            \App\Filament\Widgets\CocinaConsumoDiarioWidget::class,
        ];
    }

    // ── Query base: solo el documento fuente activo (o todo si no hay selección) ──
    protected function consumoQuery(): Builder
    {
        $query = CocinaConsumo::query();

        if ($this->archivoSeleccionadoId) {
            $query->where('archivo_importado_id', $this->archivoSeleccionadoId);
        }

        return $query;
    }

    public function getArchivoSeleccionadoProperty(): ?CocinaArchivoImportado
    {
        if (! $this->archivoSeleccionadoId) {
            return null;
        }

        return CocinaArchivoImportado::query()->find($this->archivoSeleccionadoId);
    }

    public function getArchivosDisponiblesProperty(): Collection
    {
        return CocinaArchivoImportado::query()
            ->orderByDesc('fecha_subida')
            ->get();
    }

    // ── Datos para stat cards inline ──
    public function getRegistrosUltimaFechaProperty(): int
    {
        return (int) $this->consumoQuery()
            ->whereDate('fecha', $this->maxFecha)
            ->count();
    }

    public function getProductosUltimaFechaProperty(): int
    {
        return (int) $this->consumoQuery()
            ->whereDate('fecha', $this->maxFecha)
            ->distinct('producto_id')
            ->count('producto_id');
    }

    public function getFechasRegistradasProperty(): int
    {
        return (int) $this->consumoQuery()
            ->distinct('fecha')
            ->count('fecha');
    }

    public function getMinFechaProperty(): ?string
    {
        return $this->consumoQuery()->min('fecha');
    }

    public function getMaxFechaProperty(): ?string
    {
        return $this->consumoQuery()->max('fecha');
    }

    public function getTotalProperty(): int
    {
        return (int) $this->consumoQuery()->count();
    }

    public function getProductosCountProperty(): int
    {
        return (int) $this->consumoQuery()
            ->distinct('producto_id')
            ->count('producto_id');
    }

    public function getArchivosProperty(): int
    {
        return (int) CocinaArchivoImportado::query()->count();
    }

    public function getProductoTopProperty(): ?object
    {
        if ($this->total === 0) {
            return null;
        }

        $fila = $this->consumoQuery()
            ->select('producto_id')
            ->selectRaw('SUM(cantidad) as total')
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->first();

        if (! $fila) {
            return null;
        }

        $producto = CocinaProducto::query()->find($fila->producto_id);

        return (object) [
            'nombre' => $producto?->nombre ?? 'Sin nombre',
            'total' => (float) $fila->total,
            'unidad' => mb_strtolower(trim($producto?->unidad_medida ?? 'unidad')),
        ];
    }

    public function getFechasDisponiblesProperty(): Collection
    {
        return $this->consumoQuery()
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
        $filas = $this->consumoQuery()
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

        return CocinaProducto::query()
            ->whereHas('consumos', function (Builder $q): void {
                if ($this->archivoSeleccionadoId) {
                    $q->where('archivo_importado_id', $this->archivoSeleccionadoId);
                }
            })
            ->get()
            ->map(function (CocinaProducto $p) {
                $totalC = (float) $p->consumos()
                    ->when($this->archivoSeleccionadoId, fn (Builder $q) => $q->where('archivo_importado_id', $this->archivoSeleccionadoId))
                    ->sum('cantidad');
                $dias = (int) $p->consumos()
                    ->when($this->archivoSeleccionadoId, fn (Builder $q) => $q->where('archivo_importado_id', $this->archivoSeleccionadoId))
                    ->distinct('fecha')
                    ->count('fecha');
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
        if (! $this->fechaReferencia || ! $this->huespedesReferencia || $this->total === 0) {
            return collect();
        }

        $huespedes = max(1, $this->huespedesReferencia);

        return $this->consumoQuery()
            ->with('producto')
            ->whereDate('fecha', $this->fechaReferencia)
            ->select('producto_id', 'unidad_medida')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->groupBy('producto_id', 'unidad_medida')
            ->orderByDesc('total_cantidad')
            ->get()
            ->map(function ($c) use ($huespedes) {
                $u = mb_strtolower(trim($c->unidad_medida ?? 'unidad'));
                $esEntero = (str_contains($u, 'unidad') || str_contains($u, 'porcion')) && fmod($c->total_cantidad, 1) == 0;
                $porHuesped = $c->total_cantidad / $huespedes;
                // Unidades/porciones: redondeo hacia arriba (no se puede servir media unidad).
                // Kilos, litros, gramos: 2 decimales, suficiente para cocina.
                $porHuesped = $esEntero ? ceil($porHuesped) : round($porHuesped, 2);

                return (object) [
                    'nombre' => $c->producto?->nombre ?? 'Sin nombre',
                    'unidad' => $u,
                    'consumoBase' => $c->total_cantidad,
                    'sugerido' => $porHuesped,
                    'esEntero' => $esEntero,
                ];
            })
            ->filter(fn ($r) => $r->sugerido > 0)
            ->values();
    }

    // ── Exportación de recomendación ──

    public function exportarRecomendacionExcel(): void
    {
        $recomendacion = $this->recomendacion;

        if ($recomendacion->isEmpty()) {
            Notification::make()
                ->title('Sin datos para exportar')
                ->body('Completa los campos de fecha y huéspedes para generar la recomendación.')
                ->warning()
                ->send();

            return;
        }

        $spreadsheet = $this->generarSpreadsheetRecomendacion();

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'recomendacion_cocina_' . uniqid() . '.xlsx';
        $path = "{$dir}/{$filename}";

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);

        $this->redirect(route('descargar.temp', ['file' => $filename]));
    }

    public function exportarRecomendacionPdf(): void
    {
        $recomendacion = $this->recomendacion;

        if ($recomendacion->isEmpty()) {
            Notification::make()
                ->title('Sin datos para exportar')
                ->body('Completa los campos de fecha y huéspedes para generar la recomendación.')
                ->warning()
                ->send();

            return;
        }

        $dir = storage_path('app/temp');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Si Dompdf está disponible, genera PDF real
        if (class_exists(\Dompdf\Dompdf::class)) {
            $spreadsheet = $this->generarSpreadsheetRecomendacion();
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf($spreadsheet);
            $filename = 'recomendacion_cocina_' . uniqid() . '.pdf';
            $path = "{$dir}/{$filename}";
            $writer->save($path);

            $this->redirect(route('descargar.temp', ['file' => $filename]));

            return;
        }

        // Fallback: reporte HTML imprimible (sin dependencia externa)
        $html = view('exports.recomendacion-cocina', [
            'recomendacion' => $recomendacion,
            'fechaReferencia' => $this->fechaReferencia,
            'huespedesReferencia' => $this->huespedesReferencia,
            'archivo' => $this->archivoSeleccionado?->nombre_original,
        ])->render();

        $filename = 'recomendacion_cocina_' . uniqid() . '.html';
        $path = "{$dir}/{$filename}";
        file_put_contents($path, $html);

        $this->redirect(route('ver.temp', ['file' => $filename]));
    }

    protected function generarSpreadsheetRecomendacion(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Recomendacion');

        // Título
        $sheet->setCellValue('A1', 'Recomendación de Producción — Wyndham Manta');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        // Subtítulo con parámetros
        $fecha = \Carbon\Carbon::parse($this->fechaReferencia)->format('d/m/Y');
        $info = "Fecha: {$fecha}  |  Huéspedes: {$this->huespedesReferencia}";
        if ($this->archivoSeleccionado) {
            $info .= '  |  Documento: ' . $this->archivoSeleccionado->nombre_original;
        }
        $sheet->setCellValue('A2', $info);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setARGB('FF666666');

        // Encabezados (fila 4)
        $encabezados = ['Producto', 'Unidad', 'Consumo Base', 'Por Persona'];
        $col = 'A';
        foreach ($encabezados as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $headerStyle = $sheet->getStyle('A4:D4');
        $headerStyle->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $headerStyle->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF0E7490');
        $headerStyle->getAlignment()->setHorizontal('center');

        // Datos
        $row = 5;
        foreach ($this->recomendacion as $rec) {
            $sheet->setCellValue("A{$row}", $rec->nombre);
            $sheet->setCellValue("B{$row}", $rec->unidad);
            $sheet->setCellValueExplicit("C{$row}", (string) $rec->consumoBase, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) $rec->sugerido, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setVertical('center');
            $row++;
        }

        // Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(38);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(18);

        // Bordes en la tabla
        $lastDataRow = $row - 1;
        $sheet->getStyle("A4:D{$lastDataRow}")->getBorders()
            ->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Fila de timestamp
        $sheet->setCellValue('A' . ($lastDataRow + 2), 'Generado el ' . now()->format('d/m/Y H:i'));
        $sheet->mergeCells('A' . ($lastDataRow + 2) . ':D' . ($lastDataRow + 2));
        $sheet->getStyle('A' . ($lastDataRow + 2))->getFont()->setSize(9)->getColor()->setARGB('FF999999');

        return $spreadsheet;
    }
}
