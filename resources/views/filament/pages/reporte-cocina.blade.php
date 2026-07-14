<x-filament-panels::page>
    <x-hero-card title="Subir Datos" subtitle="Importa archivos Excel con el consumo del buffet" icon="heroicon-o-arrow-up-tray" color="ocean" />

    <div class="page-enter space-y-8">
        <section class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="card p-6 sm:p-8">
                <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Seleccionar documento</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Usa el archivo original exportado desde el sistema del hotel.</p>
                    </div>

                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        Max. 10 MB
                    </span>
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
                        <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/40">
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
                            class="btn-primary"
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
                <div class="card p-6">
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

                <div class="card p-6">
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
            <section class="card">
                <div class="card-header">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Vista previa del archivo</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $previewTotalFilas }} filas detectadas. Mostrando primeras {{ count($previewFilas) }}.</p>
                        </div>
                        <button type="button" wire:click="cerrarPreview" class="btn-outline">
                            Cerrar previa
                        </button>
                    </div>
                </div>

                <div class="scroll-thin overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead class="bg-ocean-50 dark:bg-ocean-950/30">
                            <tr>
                                @foreach ($previewEncabezados as $encabezado)
                                    <th class="table-header-cell">{{ $encabezado }}</th>
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
                                        <td class="table-cell text-gray-700 dark:text-gray-300">{{ $celda !== null ? $celda : '-' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="card">
            <div class="card-header">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Historial de archivos subidos</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Registro reciente de documentos cargados al modulo de cocina.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="chip bg-ocean-50 text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/40 dark:text-ocean-300 dark:ring-ocean-900">
                            {{ $this->historialArchivos->count() }} registros recientes
                        </span>
                        <button
                            type="button"
                            wire:click="solicitarEliminarTodo"
                            @if ($this->totalArchivos === 0) disabled @endif
                            class="btn-danger-ghost disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.56 0c.342.052.682.107 1.022.166m10.422 0a48.11 48.11 0 0 1-3.478-.397M4.772 5.79m6.114 0a.99.99 0 0 1 .51.858M6.159 5.79l-.51.858m0 0a48.108 48.108 0 0 0 3.478-.397m0 0L9.26 9" /></svg>
                            Eliminar todo
                        </button>
                    </div>
                </div>
            </div>

            @if ($this->historialArchivos->isNotEmpty())
                <div class="scroll-thin overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-950/40">
                                <tr>
                                    <th class="table-header-cell">Archivo</th>
                                    <th class="table-header-cell">Estado</th>
                                    <th class="table-header-cell">Usuario</th>
                                    <th class="table-header-cell">Tamano</th>
                                    <th class="table-header-cell">Fecha</th>
                                    <th class="table-header-cell">Resultado</th>
                                    <th class="table-header-cell text-right">Accion</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->historialArchivos as $archivoHistorial)
                                    <tr class="transition hover:bg-ocean-50/50 dark:hover:bg-ocean-950/20">
                                        <td class="table-cell">
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
                                        <td class="table-cell">
                                            <span class="chip bg-palm-50 text-palm-700 ring-1 ring-palm-100 dark:bg-palm-950/40 dark:text-palm-300 dark:ring-palm-900">
                                                {{ ucfirst($archivoHistorial->estado) }}
                                            </span>
                                        </td>
                                        <td class="table-cell text-gray-600 dark:text-gray-300">
                                            {{ $archivoHistorial->usuario?->name ?? 'Sistema' }}
                                        </td>
                                        <td class="table-cell text-gray-600 dark:text-gray-300">
                                            {{ $this->formatearTamano($archivoHistorial->tamano_bytes) }}
                                        </td>
                                        <td class="table-cell text-gray-600 dark:text-gray-300">
                                            {{ $archivoHistorial->fecha_subida?->format('d/m/Y H:i') ?? $archivoHistorial->created_at?->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="table-cell text-gray-600 dark:text-gray-300">
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
                                        <td class="table-cell text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if ($archivoHistorial->estado === 'recibido')
                                                    <button
                                                        type="button"
                                                        wire:click="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                        class="btn-outline"
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
                                                        class="btn-primary"
                                                    >
                                                        Importar
                                                    </button>
                                                @else
                                                    <span class="chip bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">Consolidado</span>
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

    @if ($modalEliminarTodoAbierto)
        <div class="modal-overlay" wire:click.self="cancelarEliminarTodo" x-data
             x-on:keydown.escape.window="$wire.cancelarEliminarTodo()"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="modal-panel w-[calc(100%-1rem)] sm:max-w-sm mx-auto" @click.stop>
                <div class="modal-accent" style="background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);"></div>
                <div class="bg-gradient-to-br from-red-50 via-white to-white px-5 py-5 text-center dark:from-red-950/30 dark:via-gray-900 dark:to-gray-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 ring-4 ring-red-50 dark:bg-red-950/30 dark:ring-red-950/10">
                        <svg class="h-7 w-7 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12.56 0c.342.052.682.107 1.022.166m10.422 0a48.11 48.11 0 0 1-3.478-.397M4.772 5.79m6.114 0a.99.99 0 0 1 .51.858M6.159 5.79l-.51.858m0 0a48.108 48.108 0 0 0 3.478-.397m0 0L9.26 9" /></svg>
                    </div>
                    <h4 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Eliminar todos los Excel</h4>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Se eliminarán <strong class="font-semibold text-gray-900 dark:text-white">{{ $this->totalArchivos }}</strong> archivos, sus consumos y errores asociados. Esta acción no se puede deshacer.
                    </p>
                </div>
                <div class="flex border-t border-gray-100 dark:border-gray-800">
                    <button wire:click="cancelarEliminarTodo"
                        class="flex-1 border-r border-gray-100 px-4 py-3.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-950">
                        Cancelar
                    </button>
                    <button wire:click="eliminarTodos"
                        class="flex-1 px-4 py-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20">
                        Eliminar todo
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
