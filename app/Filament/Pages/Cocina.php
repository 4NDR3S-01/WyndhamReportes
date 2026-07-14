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

    public ?int $huespedesObjetivo = null;

    /** @var int|null ID del documento fuente activo (el dashboard depende solo de él) */
    public ?int $archivoSeleccionadoId = null;

    public bool $modalArchivoAbierto = false;

    /** @var mixed Archivo en proceso de subida desde el dashboard */
    public mixed $archivo = null;

    public function mount(): void
    {
        $ultimo = CocinaArchivoImportado::query()->latest('fecha_subida')->first();
        $this->archivoSeleccionadoId = $ultimo?->id;

        $max = $this->maxFecha;
        $this->fechaSeleccionada = $max ? \Carbon\Carbon::parse($max)->format('Y-m-d') : null;
        $this->fechaReferencia = $this->fechaSeleccionada;
    }

    public function updatedFechaSeleccionada(): void {}

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

        $this->modalArchivoAbierto = false;
        $this->dispatch('cocina-archivo-cambio', id: $nuevo->id);
        $this->dispatch('$refresh');

        Notification::make()
            ->title('Archivo cargado y procesado')
            ->body("Filas importadas: {$resultado['importadas']}. Errores: {$resultado['errores']}. Duplicadas: {$resultado['duplicadas']}.")
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
        if (! $this->fechaReferencia || ! $this->huespedesReferencia || ! $this->huespedesObjetivo || $this->total === 0) {
            return collect();
        }

        $factor = $this->huespedesObjetivo / max(1, $this->huespedesReferencia);

        return $this->consumoQuery()
            ->with('producto')
            ->whereDate('fecha', $this->fechaReferencia)
            ->select('producto_id', 'unidad_medida')
            ->selectRaw('SUM(cantidad) as total_cantidad')
            ->groupBy('producto_id', 'unidad_medida')
            ->orderByDesc('total_cantidad')
            ->get()
            ->map(function ($c) use ($factor) {
                $u = mb_strtolower(trim($c->unidad_medida ?? 'unidad'));
                $esEntero = (str_contains($u, 'unidad') || str_contains($u, 'porcion')) && fmod($c->total_cantidad, 1) == 0;
                $sugerido = $c->total_cantidad * $factor;
                $sugerido = $esEntero ? ceil($sugerido) : round($sugerido, 2);

                return (object) [
                    'nombre' => $c->producto?->nombre ?? 'Sin nombre',
                    'unidad' => $u,
                    'consumoBase' => $c->total_cantidad,
                    'sugerido' => $sugerido,
                    'esEntero' => $esEntero,
                ];
            })
            ->filter(fn ($r) => $r->sugerido > 0)
            ->values();
    }
}
