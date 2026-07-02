<x-filament-panels::page>
    <x-hero-card title="Subir Datos" subtitle="Importa archivos Excel con el consumo del buffet" icon="heroicon-o-arrow-up-tray" color="ocean" />

    <div class="space-y-8">
        <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Seleccionar documento</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Usa el archivo original exportado desde el sistema del hotel.</p>
                    </div>

                    <div class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        Max. 10 MB
                    </div>
                </div>

                <form wire:submit.prevent="subirDatos" class="space-y-6">
                    <label for="archivo" class="group relative block cursor-pointer rounded-3xl border-2 border-dashed border-ocean-200 bg-ocean-50/40 p-8 text-center transition hover:border-ocean-400 hover:bg-ocean-50 dark:border-ocean-900 dark:bg-ocean-950/20 dark:hover:border-ocean-700">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-ocean-600 shadow-sm ring-1 ring-ocean-100 dark:bg-gray-900 dark:text-ocean-300 dark:ring-ocean-900">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0 4 4m-4-4-4 4M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5" />
                            </svg>
                        </div>

                        <div class="mt-5">
                            <p class="text-base font-semibold text-gray-950 dark:text-white">Haz clic para seleccionar el archivo</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Excel o CSV: .xlsx, .xls, .csv</p>
                        </div>

                        <input
                            id="archivo"
                            type="file"
                            wire:model="archivo"
                            accept=".xlsx,.xls,.csv"
                            class="sr-only"
                        >
                    </label>

                    <div wire:loading wire:target="archivo" class="rounded-2xl border border-ocean-200 bg-ocean-50 p-4 text-sm font-medium text-ocean-700 dark:border-ocean-900 dark:bg-ocean-950/30 dark:text-ocean-300">
                        Cargando archivo temporalmente...
                    </div>

                    @if ($archivo)
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Archivo seleccionado</p>
                            <p class="mt-1 truncate text-sm font-medium text-gray-950 dark:text-white">{{ $archivo->getClientOriginalName() }}</p>
                        </div>
                    @endif

                    @error('archivo')
                        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="flex flex-col gap-3 border-t border-gray-100 pt-6 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                            Al subirlo, el archivo queda almacenado de forma interna para su procesamiento posterior.
                        </p>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="subirDatos,archivo"
                            class="inline-flex items-center justify-center rounded-xl bg-ocean-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-ocean-700 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="subirDatos">Guardar archivo</span>
                            <span wire:loading wire:target="subirDatos">Guardando...</span>
                        </button>
                    </div>
                </form>

                @if ($nombreArchivoSubido)
                    <div class="mt-6 rounded-3xl border border-palm-200 bg-palm-50 p-5 dark:border-palm-900 dark:bg-palm-950/30">
                        <div class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-palm-600 text-white">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m5 13 4 4L19 7" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-palm-900 dark:text-palm-200">Archivo guardado correctamente</p>
                                <p class="mt-1 truncate text-sm text-palm-800 dark:text-palm-300">{{ $nombreArchivoSubido }}</p>
                                <p class="mt-1 break-all text-xs text-palm-700/80 dark:text-palm-300/80">{{ $rutaArchivoSubido }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Requisitos del archivo</h3>
                    <div class="mt-5 space-y-4">
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-ocean-500"></span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Debe provenir del reporte de consumo del sistema actual.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-ocean-500"></span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Formatos permitidos: Excel o CSV.</p>
                        </div>
                        <div class="flex gap-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-ocean-500"></span>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Columnas esperadas: fecha, articulo, presentacion, cantidad y concepto.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Ultimo procesamiento</h3>
                    @php
                        $ultimo = \App\Models\CocinaArchivoImportado::query()
                            ->whereIn('estado', ['procesado', 'procesado_con_errores'])
                            ->latest('fecha_procesado')
                            ->first();
                    @endphp
                    @if ($ultimo)
                        <div class="mt-4 space-y-3 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Archivo</span>
                                <span class="truncate font-medium text-gray-950 dark:text-white">{{ $ultimo->nombre_original }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Importadas</span>
                                <span class="font-semibold text-palm-600 dark:text-palm-400">{{ number_format($ultimo->filas_importadas, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Con error</span>
                                <span class="font-semibold text-red-600 dark:text-red-400">{{ number_format($ultimo->filas_con_error, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-gray-500 dark:text-gray-400">Fecha</span>
                                <span class="text-gray-700 dark:text-gray-300">{{ $ultimo->fecha_procesado?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Aun no se ha procesado ningun archivo.</p>
                    @endif
                </div>
            </aside>
        </section>

        @if ($archivoEnPreview && count($previewEncabezados) > 0)
            <section class="rounded-3xl border border-ocean-200 bg-white shadow-sm dark:border-ocean-900 dark:bg-gray-900">
                <div class="border-b border-ocean-100 px-6 py-5 dark:border-ocean-900 sm:px-8">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Vista previa del archivo</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $previewTotalFilas }} filas detectadas. Mostrando primeras {{ count($previewFilas) }}.</p>
                        </div>
                        <button type="button" wire:click="cerrarPreview" class="inline-flex items-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            Cerrar previa
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead class="bg-ocean-50 dark:bg-ocean-950/30">
                            <tr>
                                @foreach ($previewEncabezados as $encabezado)
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-ocean-700 dark:text-ocean-300">{{ $encabezado }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($previewFilas as $index => $fila)
                                @php
                                    $bgClass = $index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-950/40';
                                @endphp
                                <tr class="transition hover:bg-ocean-50/30 dark:hover:bg-ocean-950/10 {{ $bgClass }}">
                                    @foreach ($fila as $celda)
                                        <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $celda !== null ? $celda : '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 sm:px-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Historial de archivos subidos</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registro reciente de documentos cargados al modulo de cocina.</p>
                    </div>

                    <span class="inline-flex w-fit rounded-full bg-ocean-50 px-3 py-1 text-xs font-semibold text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/40 dark:text-ocean-300 dark:ring-ocean-900">
                        {{ $this->historialArchivos->count() }} registros recientes
                    </span>
                </div>
            </div>

            @if ($this->historialArchivos->isNotEmpty())
                <div class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-950/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Archivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Usuario</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tamano</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Fecha</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Resultado</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Accion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->historialArchivos as $archivoHistorial)
                                    <tr class="transition hover:bg-ocean-50/50 dark:hover:bg-ocean-950/20">
                                        <td class="px-6 py-4">
                                            <div class="flex items-start gap-3">
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean-100 text-xs font-bold uppercase text-ocean-700 dark:bg-ocean-950 dark:text-ocean-300">
                                                    {{ $archivoHistorial->extension ?: 'doc' }}
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $archivoHistorial->nombre_original }}</p>
                                                    <p class="mt-1 max-w-md truncate text-xs text-gray-500 dark:text-gray-400">{{ $archivoHistorial->ruta }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex rounded-full bg-palm-50 px-2.5 py-1 text-xs font-semibold text-palm-700 ring-1 ring-palm-100 dark:bg-palm-950/40 dark:text-palm-300 dark:ring-palm-900">
                                                {{ ucfirst($archivoHistorial->estado) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $archivoHistorial->usuario?->name ?? 'Sistema' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $this->formatearTamano($archivoHistorial->tamano_bytes) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            {{ $archivoHistorial->fecha_subida?->format('d/m/Y H:i') ?? $archivoHistorial->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                            @if (str_starts_with($archivoHistorial->estado, 'procesado'))
                                                <div class="space-y-1">
                                                    <p class="font-medium text-gray-950 dark:text-white">{{ number_format($archivoHistorial->filas_importadas, 0, ',', '.') }} importadas</p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($archivoHistorial->filas_con_error, 0, ',', '.') }} con error / {{ number_format($archivoHistorial->total_filas, 0, ',', '.') }} filas</p>
                                                </div>
                                            @elseif ($archivoHistorial->estado === 'error')
                                                <p class="max-w-xs truncate text-sm text-red-600 dark:text-red-400">{{ $archivoHistorial->observaciones ?: 'Error de procesamiento' }}</p>
                                            @else
                                                <span class="text-sm text-gray-500 dark:text-gray-400">Pendiente de procesar</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if ($archivoHistorial->estado === 'recibido')
                                                    <button
                                                        type="button"
                                                        wire:click="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                        class="inline-flex items-center justify-center rounded-xl border border-ocean-200 bg-white px-3 py-2 text-xs font-semibold text-ocean-700 shadow-sm transition hover:bg-ocean-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-ocean-900 dark:bg-gray-900 dark:text-ocean-300 dark:hover:bg-ocean-950/40"
                                                    >
                                                        Ver previa
                                                    </button>
                                                @endif

                                                @if (! str_starts_with($archivoHistorial->estado, 'procesado'))
                                                    <button
                                                        type="button"
                                                        wire:click="procesarArchivo({{ $archivoHistorial->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="procesarArchivo({{ $archivoHistorial->id }})"
                                                        class="inline-flex items-center justify-center rounded-xl bg-ocean-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-ocean-700 disabled:cursor-not-allowed disabled:opacity-60"
                                                    >
                                                        Importar
                                                    </button>
                                                @else
                                                    <span class="inline-flex rounded-xl bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">Consolidado</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="px-6 py-12 text-center sm:px-8">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 8h10M7 12h7m-7 4h10M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" />
                        </svg>
                    </div>
                    <h4 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">Aun no hay archivos registrados</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cuando subas un documento, aparecera aqui con su fecha, usuario y estado.</p>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
