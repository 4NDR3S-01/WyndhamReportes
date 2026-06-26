<?php

namespace App\Filament\Pages;

use App\Models\MedicoArchivoImportado;
use App\Services\Medico\ProcesadorArchivoMedico;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ReporteMedico extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static string|\UnitEnum|null $navigationGroup = 'Medico';

    protected static ?string $navigationLabel = 'Subir datos';

    protected static ?string $title = 'Subir datos de medico';

    protected static ?string $slug = 'medico/subir-datos';

    protected static ?int $navigationSort = 2;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.reporte-medico';

    public mixed $archivo = null;

    public ?string $nombreArchivoSubido = null;

    public ?string $rutaArchivoSubido = null;

    public ?int $archivoEnPreview = null;

    public array $previewEncabezados = [];

    public array $previewFilas = [];

    public int $previewTotalFilas = 0;

    public function subirDatos(): void
    {
        $this->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ], [
            'archivo.required' => 'Selecciona un archivo para importar.',
            'archivo.mimes' => 'El archivo debe ser Excel (.xlsx, .xls).',
            'archivo.max' => 'El archivo no debe superar los 10 MB.',
        ]);

        $nombreOriginal = $this->archivo->getClientOriginalName();
        $extension = $this->archivo->getClientOriginalExtension();
        $tamanoBytes = $this->archivo->getSize() ?: 0;
        $mimeType = $this->archivo->getMimeType();
        $base = pathinfo($nombreOriginal, PATHINFO_FILENAME);
        $nombreSeguro = now()->format('Ymd_His') . '_' . Str::slug($base) . '.' . $extension;

        $this->rutaArchivoSubido = $this->archivo->storeAs('importaciones/medico', $nombreSeguro);
        $this->nombreArchivoSubido = $nombreOriginal;

        MedicoArchivoImportado::query()->create([
            'usuario_id' => auth()->id(),
            'nombre_original' => $nombreOriginal,
            'nombre_guardado' => $nombreSeguro,
            'ruta' => $this->rutaArchivoSubido,
            'extension' => mb_strtolower($extension),
            'mime_type' => $mimeType,
            'tamano_bytes' => $tamanoBytes,
            'estado' => 'recibido',
            'fecha_subida' => now(),
        ]);

        $this->archivo = null;

        Notification::make()
            ->title('Archivo cargado correctamente')
            ->body('El archivo quedo guardado para la futura importacion.')
            ->success()
            ->send();
    }

    public function previsualizarArchivo(int $archivoId): void
    {
        $archivo = MedicoArchivoImportado::query()->findOrFail($archivoId);

        if (! Storage::exists($archivo->ruta)) {
            Notification::make()
                ->title('Archivo no encontrado')
                ->danger()
                ->send();
            return;
        }

        try {
            $rutaAbsoluta = Storage::path($archivo->ruta);
            $spreadsheet = IOFactory::load($rutaAbsoluta);

            $partesSheet = null;
            foreach ($spreadsheet->getSheetNames() as $n) {
                if (str_starts_with(mb_strtoupper(trim($n)), 'PARTES DIARIO')) {
                    $partesSheet = $n;
                    break;
                }
            }

            if (! $partesSheet) {
                Notification::make()->title('Hoja PARTES DIARIO no encontrada')->warning()->send();
                return;
            }

            $sheet = $spreadsheet->getSheetByName($partesSheet);

            $rows = $sheet->toArray(null, true, true, false);
            $rows = array_values(array_filter($rows, fn ($r) => array_filter($r, fn ($v) => $v !== null && $v !== '') !== []));

            $encabezados = [];
            $filasRaw = [];
            $encontrado = false;

            foreach ($rows as $row) {
                $normalized = array_map(fn ($v) => Str::ascii(Str::lower(trim((string) $v))), $row);
                if (! $encontrado && in_array('fecha', $normalized) && in_array('nombres', $normalized)) {
                    $encabezados = array_map(fn ($v) => trim((string) $v), $row);
                    $encontrado = true;
                    continue;
                }
                if ($encontrado) {
                    $filasRaw[] = $row;
                }
            }

            $preview = array_slice($filasRaw, 0, 20);
            $this->previewEncabezados = $encabezados;
            $this->previewFilas = $preview;
            $this->previewTotalFilas = count($filasRaw);
            $this->archivoEnPreview = $archivoId;
        } catch (\Throwable $e) {
            Notification::make()->title('Error al leer')->body($e->getMessage())->danger()->send();
        }
    }

    public function cerrarPreview(): void
    {
        $this->archivoEnPreview = null;
        $this->previewEncabezados = [];
        $this->previewFilas = [];
        $this->previewTotalFilas = 0;
    }

    public function procesarArchivo(int $archivoId, ProcesadorArchivoMedico $procesador): void
    {
        $archivo = MedicoArchivoImportado::query()->findOrFail($archivoId);

        if (str_starts_with($archivo->estado, 'procesado')) {
            Notification::make()
                ->title('Archivo ya procesado')
                ->body('Este documento ya fue consolidado previamente.')
                ->warning()
                ->send();
            return;
        }

        try {
            $resultado = $procesador->procesar($archivo);
        } catch (\Throwable $e) {
            $archivo->update(['estado' => 'error', 'observaciones' => $e->getMessage()]);
            Notification::make()->title('Error al procesar')->body($e->getMessage())->danger()->send();
            return;
        }

        $msg = "Partes diarios: {$resultado['partes_diarios']['importadas']} importados. ";
        $msg .= "Kardex: {$resultado['kardex']['importadas']} importados. ";
        $msg .= "Salidas: {$resultado['movimientos']['creados']} movimientos. ";
        $msg .= "Errores: {$resultado['errores']}. Duplicadas: {$resultado['duplicadas']}.";

        Notification::make()->title('Archivo procesado')->body($msg)->success()->send();
        $this->dispatch('$refresh');
    }

    public function getHistorialArchivosProperty(): Collection
    {
        return MedicoArchivoImportado::query()
            ->with('usuario')
            ->latest('fecha_subida')
            ->limit(8)
            ->get();
    }

    public function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        return $bytes . ' B';
    }
}
