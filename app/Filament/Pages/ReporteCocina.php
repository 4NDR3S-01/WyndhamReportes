<?php

namespace App\Filament\Pages;

use App\Models\CocinaArchivoImportado;
use App\Models\CocinaConsumo;
use App\Models\CocinaImportacionError;
use App\Services\Cocina\ProcesadorArchivoConsumo;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ReporteCocina extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static string|\UnitEnum|null $navigationGroup = 'Cocina';

    protected static ?string $navigationLabel = 'Subir datos';

    protected static ?string $title = 'Subir datos de cocina';

    protected ?string $heading = '';

    protected static ?string $slug = 'cocina/subir-datos';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.reporte-cocina';

    public mixed $archivo = null;

    public ?string $nombreArchivoSubido = null;

    public ?string $rutaArchivoSubido = null;

    public ?int $archivoEnPreview = null;

    public bool $modalEliminarTodoAbierto = false;

    public ?int $archivoAEliminar = null;

    public ?string $nombreArchivoAEliminar = null;

    /** @var array<int, string> */
    public array $previewEncabezados = [];

    /** @var array<int, array<int, mixed>> */
    public array $previewFilas = [];

    public int $previewTotalFilas = 0;

    private function guardarArchivo()
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

        $this->rutaArchivoSubido = $this->archivo->storeAs('importaciones/cocina', $nombreSeguro);
        $this->nombreArchivoSubido = $nombreOriginal;

        $archivoRecord = CocinaArchivoImportado::query()->create([
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
            ->body('El archivo quedo guardado para la futura importacion de datos.')
            ->success()
            ->send();

        return $archivoRecord;
    }

    public function subirDatos(): void
    {
        $this->guardarArchivo();
    }

    public function subirYPrevisualizar(): void
    {
        $archivoRecord = $this->guardarArchivo();
        if ($archivoRecord) {
            $this->previsualizarArchivo($archivoRecord->id);
        }
    }

    public function previsualizarArchivo(int $archivoId): void
    {
        $archivo = CocinaArchivoImportado::query()->findOrFail($archivoId);

        if (! Storage::exists($archivo->ruta)) {
            Notification::make()
                ->title('Archivo no encontrado')
                ->body('El archivo fisico no se encuentra en el almacenamiento.')
                ->danger()
                ->send();

            return;
        }

        try {
            $rutaAbsoluta = Storage::path($archivo->ruta);
            $spreadsheet = IOFactory::load($rutaAbsoluta);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, false);
            $rows = array_values(array_filter($rows, fn (array $row): bool => array_filter($row, fn ($val): bool => $val !== null && $val !== '') !== []));

            $encabezados = [];
            $filasRaw = [];
            $encontradoEncabezado = false;

            foreach ($rows as $row) {
                $normalized = array_map(fn ($val): string => $this->normalizarEncabezadoStr((string) $val), $row);

                if (! $encontradoEncabezado) {
                    if (in_array('fecha', $normalized, true) && in_array('cantidad', $normalized, true)) {
                        $encabezados = array_map(fn ($val): string => trim((string) $val), $row);
                        $encontradoEncabezado = true;
                    }
                    continue;
                }

                $filasRaw[] = array_map(fn ($val) => $val, $row);
            }

            $preview = array_slice($filasRaw, 0, 20);

            $this->previewEncabezados = $encabezados;
            $this->previewFilas = $preview;
            $this->previewTotalFilas = count($filasRaw);
            $this->archivoEnPreview = $archivoId;
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Error al leer el archivo')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function cerrarPreview(): void
    {
        $this->archivoEnPreview = null;
        $this->previewEncabezados = [];
        $this->previewFilas = [];
        $this->previewTotalFilas = 0;
    }

    private function normalizarEncabezadoStr(string $valor): string
    {
        $valor = Str::ascii(Str::lower(trim($valor)));

        return preg_replace('/[^a-z0-9]/', '', $valor) ?? '';
    }

    public function procesarArchivo(int $archivoId, ProcesadorArchivoConsumo $procesador): void
    {
        $archivo = CocinaArchivoImportado::query()->findOrFail($archivoId);

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
        } catch (\Throwable $exception) {
            $archivo->update([
                'estado' => 'error',
                'observaciones' => $exception->getMessage(),
            ]);

            Notification::make()
                ->title('No se pudo procesar el archivo')
                ->body($exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $resumen = $resultado['errores'] > 0
            ? " Filas importadas: {$resultado['importadas']}. Errores: {$resultado['errores']}{$resultado['resumen']}. Duplicadas: {$resultado['duplicadas']}."
            : " Filas importadas: {$resultado['importadas']}. Errores: {$resultado['errores']}. Duplicadas: {$resultado['duplicadas']}.";

        Notification::make()
            ->title('Archivo procesado')
            ->body($resumen)
            ->success()
            ->send();

        $this->dispatch('$refresh');
    }

    public function getTotalArchivosProperty(): int
    {
        return (int) CocinaArchivoImportado::query()->count();
    }

    public function solicitarEliminarTodo(): void
    {
        if ($this->totalArchivos === 0) {
            return;
        }

        $this->modalEliminarTodoAbierto = true;
    }

    public function cancelarEliminarTodo(): void
    {
        $this->modalEliminarTodoAbierto = false;
    }

    public function eliminarTodos(): void
    {
        if (! $this->modalEliminarTodoAbierto) {
            return;
        }

        $archivos = CocinaArchivoImportado::query()->get();

        foreach ($archivos as $archivo) {
            if ($archivo->ruta && Storage::exists($archivo->ruta)) {
                Storage::delete($archivo->ruta);
            }
        }

        // Borrado en cascada DB-agnóstico: los consumos y errores dependen
        // del archivo, por lo que se eliminan primero.
        CocinaConsumo::query()->delete();
        CocinaImportacionError::query()->delete();
        CocinaArchivoImportado::query()->delete();

        $this->modalEliminarTodoAbierto = false;
        $this->cerrarPreview();

        Notification::make()
            ->title('Archivos eliminados')
            ->body('Se eliminaron todos los Excel, sus consumos y errores asociados.')
            ->success()
            ->send();
    }

    public function solicitarEliminarArchivo(int $archivoId): void
    {
        $archivo = CocinaArchivoImportado::query()->find($archivoId);

        if (! $archivo) {
            Notification::make()
                ->title('Archivo no encontrado')
                ->danger()
                ->send();

            return;
        }

        $this->archivoAEliminar = $archivoId;
        $this->nombreArchivoAEliminar = $archivo->nombre_original;
    }

    public function cancelarEliminarArchivo(): void
    {
        $this->archivoAEliminar = null;
        $this->nombreArchivoAEliminar = null;
    }

    public function eliminarArchivo(): void
    {
        if ($this->archivoAEliminar === null) {
            return;
        }

        $archivo = CocinaArchivoImportado::query()->find($this->archivoAEliminar);

        if (! $archivo) {
            Notification::make()
                ->title('Archivo no encontrado')
                ->danger()
                ->send();

            $this->cancelarEliminarArchivo();

            return;
        }

        $nombreOriginal = $archivo->nombre_original;

        // Eliminar archivo físico
        if ($archivo->ruta && Storage::exists($archivo->ruta)) {
            Storage::delete($archivo->ruta);
        }

        // Eliminar consumos y errores asociados
        $archivo->consumos()->delete();
        $archivo->errores()->delete();
        $archivo->delete();

        $this->cancelarEliminarArchivo();
        $this->cerrarPreview();

        Notification::make()
            ->title('Archivo eliminado')
            ->body("Se eliminaron el archivo «{$nombreOriginal}» y sus datos asociados.")
            ->success()
            ->send();
    }

    public function getHistorialArchivosProperty(): Collection
    {
        return CocinaArchivoImportado::query()
            ->with('usuario')
            ->latest('fecha_subida')
            ->limit(8)
            ->get();
    }

    public function formatearTamano(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }
}
