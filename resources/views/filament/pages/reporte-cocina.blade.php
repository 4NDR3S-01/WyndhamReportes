<x-filament-panels::page>
    <x-hero-card title="Subir Datos" subtitle="Importa archivos Excel con el consumo del buffet" icon="heroicon-o-arrow-up-tray" color="ocean" />

    <div class="page-enter space-y-8">
        {{-- Upload + Sidebar --}}
        <section class="grid items-start gap-6 xl:grid-cols-[1fr_360px]">
            {{-- Left column --}}
            <div class="space-y-6 min-w-0">
                {{-- Upload zone --}}
                <div class="card p-5 sm:p-6 h-fit">
                    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-950 dark:text-white">Seleccionar documento</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Usa el archivo original exportado desde el sistema del hotel.</p>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-ocean-50 px-3.5 py-1.5 text-xs font-semibold text-ocean-700 ring-1 ring-ocean-200 dark:bg-ocean-950/40 dark:text-ocean-300 dark:ring-ocean-900">
                            <x-heroicon-o-document-text class="h-3.5 w-3.5" />
                            Max. 10 MB
                        </span>
                    </div>

                    <form wire:submit.prevent="subirDatos" class="flex flex-col gap-5">
                        <label for="archivo"
                            class="group relative block cursor-pointer rounded-3xl border-2 border-dashed border-ocean-200 bg-gradient-to-b from-ocean-50/60 to-white p-8 sm:p-10 text-center transition-all duration-200 hover:scale-[1.01] hover:border-ocean-400 hover:shadow-lg hover:shadow-ocean-500/5 active:scale-[0.99] dark:border-ocean-900 dark:from-ocean-950/30 dark:to-gray-900 dark:hover:border-ocean-700"
                            x-data
                            x-on:dragover.prevent="$el.classList.add('border-ocean-500', 'bg-ocean-50', 'dark:bg-ocean-950/40')"
                            x-on:dragleave.prevent="$el.classList.remove('border-ocean-500', 'bg-ocean-50', 'dark:bg-ocean-950/40')"
                            x-on:drop.prevent="$el.classList.remove('border-ocean-500', 'bg-ocean-50', 'dark:bg-ocean-950/40')"
                        >
                            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-ocean-600 shadow-sm ring-1 ring-ocean-100 transition-transform duration-200 group-hover:scale-110 group-hover:ring-ocean-300 dark:bg-gray-900 dark:text-ocean-300 dark:ring-ocean-900">
                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 16V4m0 0 4 4m-4-4-4 4M4 16.5V18a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-1.5" />
                                </svg>
                            </div>

                            <div class="mt-5">
                                <p class="text-lg font-bold text-gray-950 dark:text-white">
                                    Haz clic o arrastra el archivo aquí
                                </p>
                                <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400">
                                    Formatos aceptados: <span class="font-semibold text-ocean-600 dark:text-ocean-400">.xlsx</span>, <span class="font-semibold text-ocean-600 dark:text-ocean-400">.xls</span>, <span class="font-semibold text-ocean-600 dark:text-ocean-400">.csv</span>
                                </p>
                            </div>

                            <input
                                id="archivo"
                                type="file"
                                wire:model="archivo"
                                accept=".xlsx,.xls,.csv"
                                class="sr-only"
                            >
                        </label>

                        {{-- Loading indicator --}}
                        <div wire:loading wire:target="archivo"
                            class="flex items-center gap-3 rounded-2xl border border-ocean-200 bg-ocean-50 p-4 dark:border-ocean-900 dark:bg-ocean-950/30">
                            <svg class="h-5 w-5 animate-spin text-ocean-600 dark:text-ocean-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            <span class="text-sm font-medium text-ocean-700 dark:text-ocean-300">Cargando archivo temporalmente...</span>
                        </div>

                        {{-- Selected file card --}}
                        @if ($archivo)
                            <div class="flex items-start gap-4 rounded-2xl border border-ocean-100 bg-ocean-50/50 p-4 dark:border-ocean-900 dark:bg-ocean-950/20">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-ocean-600 shadow-sm ring-1 ring-ocean-200 dark:bg-gray-900 dark:text-ocean-300 dark:ring-ocean-900">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-950 dark:text-white">{{ $archivo->getClientOriginalName() }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                        @php
                                            $selectedSize = $archivo->getSize() ?: 0;
                                        @endphp
                                        <span class="font-medium text-ocean-600 dark:text-ocean-400">{{ $this->formatearTamano($selectedSize) }}</span>
                                        <span class="mx-1.5">&middot;</span>
                                        <span class="uppercase">{{ $archivo->getClientOriginalExtension() }}</span>
                                    </p>
                                </div>
                                <button type="button" wire:click="$set('archivo', null)" class="btn-danger-ghost shrink-0 px-2 py-1" title="Quitar archivo">
                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                    <span class="sr-only">Quitar archivo</span>
                                </button>
                            </div>
                        @endif

                        {{-- Validation error --}}
                        @error('archivo')
                            <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30">
                                <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-red-500" />
                                <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $message }}</p>
                            </div>
                        @enderror

                        {{-- Submit footer --}}
                        <div class="flex flex-col gap-3 border-t border-gray-100 pt-5 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                                <x-heroicon-o-lock-closed class="h-3.5 w-3.5 shrink-0" />
                                Al subirlo, el archivo queda almacenado de forma interna para su procesamiento posterior.
                            </p>

                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="subirDatos,archivo"
                                class="btn-primary"
                            >
                                <span wire:loading.remove wire:target="subirDatos">
                                    <x-heroicon-o-cloud-arrow-up class="h-4 w-4" />
                                    Guardar archivo
                                </span>
                                <span wire:loading wire:target="subirDatos">
                                    <svg class="-ml-1 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                    </svg>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Success banner after upload --}}
                    @if ($nombreArchivoSubido)
                        <div class="mt-6 rounded-3xl border border-palm-200 bg-palm-50 p-5 dark:border-palm-900 dark:bg-palm-950/30">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-palm-600 text-white">
                                    <x-heroicon-o-check class="h-5 w-5" />
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

                {{-- File preview --}}
                @if ($archivoEnPreview && count($previewEncabezados) > 0)
                    <section class="card">
                        <div class="card-header">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean-100 text-ocean-600 dark:bg-ocean-900/60 dark:text-ocean-400">
                                        <x-heroicon-o-eye class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Vista previa del archivo</h3>
                                        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $previewTotalFilas }} filas detectadas. Mostrando primeras {{ count($previewFilas) }}.</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="cerrarPreview" class="btn-outline">
                                    <x-heroicon-o-x-mark class="h-4 w-4" />
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
                                        <tr class="table-row {{ $bgClass }}">
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

                {{-- File history --}}
                <section class="card">
                    <div class="card-header">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-ocean-100 text-ocean-600 dark:bg-ocean-900/60 dark:text-ocean-400">
                                    <x-heroicon-o-archive-box class="h-5 w-5" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">Historial de archivos subidos</h3>
                                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Registro reciente de documentos cargados al modulo de cocina.</p>
                                </div>
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
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                    Eliminar todo
                                </button>
                            </div>
                        </div>
                    </div>

                    @if ($this->historialArchivos->isNotEmpty())
                        <div class="scroll-thin overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-950/40">
                                    <tr>
                                        <th class="table-header-cell">Archivo</th>
                                        <th class="table-header-cell">Estado</th>
                                        <th class="table-header-cell hidden sm:table-cell">Usuario</th>
                                        <th class="table-header-cell hidden md:table-cell">Tamano</th>
                                        <th class="table-header-cell hidden md:table-cell">Fecha</th>
                                        <th class="table-header-cell">Resultado</th>
                                        <th class="table-header-cell text-right">Accion</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @foreach ($this->historialArchivos as $archivoHistorial)
                                        <tr class="table-row">
                                            <td class="table-cell">
                                                <div class="flex items-start gap-3">
                                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-ocean-100 text-xs font-bold uppercase text-ocean-700 dark:bg-ocean-950 dark:text-ocean-300">
                                                        {{ $archivoHistorial->extension ?: 'doc' }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-semibold text-gray-950 dark:text-white max-w-[160px] sm:max-w-xs">{{ $archivoHistorial->nombre_original }}</p>
                                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 truncate max-w-[160px] sm:max-w-xs">{{ $archivoHistorial->ruta }}</p>
                                                        {{-- Mobile-only meta row --}}
                                                        <div class="mt-1.5 flex items-center gap-2 text-xs text-gray-400 sm:hidden">
                                                            <span>{{ $archivoHistorial->usuario?->name ?? 'Sistema' }}</span>
                                                            <span>&middot;</span>
                                                            <span>{{ $this->formatearTamano($archivoHistorial->tamano_bytes) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="table-cell">
                                                @php
                                                    $estado = $archivoHistorial->estado;
                                                    $chipColor = match (true) {
                                                        str_starts_with($estado, 'procesado') => 'bg-palm-50 text-palm-700 ring-palm-200 dark:bg-palm-950/40 dark:text-palm-300 dark:ring-palm-900',
                                                        $estado === 'recibido' => 'bg-ocean-50 text-ocean-700 ring-ocean-200 dark:bg-ocean-950/40 dark:text-ocean-300 dark:ring-ocean-900',
                                                        $estado === 'error' => 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-950/40 dark:text-red-300 dark:ring-red-900',
                                                        default => 'bg-gray-100 text-gray-600 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700',
                                                    };
                                                    $estadoLabel = match (true) {
                                                        str_starts_with($estado, 'procesado') => 'Procesado',
                                                        $estado === 'recibido' => 'Recibido',
                                                        $estado === 'error' => 'Error',
                                                        default => ucfirst($estado),
                                                    };
                                                @endphp
                                                <span class="chip {{ $chipColor }}">{{ $estadoLabel }}</span>
                                            </td>
                                            <td class="table-cell hidden text-gray-600 dark:text-gray-300 sm:table-cell">
                                                {{ $archivoHistorial->usuario?->name ?? 'Sistema' }}
                                            </td>
                                            <td class="table-cell hidden text-gray-600 dark:text-gray-300 md:table-cell">
                                                {{ $this->formatearTamano($archivoHistorial->tamano_bytes) }}
                                            </td>
                                            <td class="table-cell hidden text-gray-600 dark:text-gray-300 md:table-cell whitespace-nowrap">
                                                {{ $archivoHistorial->fecha_subida?->format('d/m/Y H:i') ?? $archivoHistorial->created_at?->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="table-cell text-gray-600 dark:text-gray-300">
                                                @if (str_starts_with($archivoHistorial->estado, 'procesado'))
                                                    <div class="space-y-0.5">
                                                        <p class="font-semibold text-gray-950 dark:text-white">{{ number_format($archivoHistorial->filas_importadas, 0, ',', '.') }} importadas</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($archivoHistorial->filas_con_error, 0, ',', '.') }} con error / {{ number_format($archivoHistorial->total_filas, 0, ',', '.') }} filas</p>
                                                    </div>
                                                @elseif ($archivoHistorial->estado === 'error')
                                                    <div class="flex items-center gap-1.5">
                                                        <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0 text-red-500" />
                                                        <p class="max-w-[120px] truncate text-sm text-red-600 dark:text-red-400">{{ $archivoHistorial->observaciones ?: 'Error de procesamiento' }}</p>
                                                    </div>
                                                @else
                                                    <span class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                                                        <x-heroicon-o-clock class="h-3.5 w-3.5" />
                                                        Pendiente de procesar
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="table-cell text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @if ($archivoHistorial->estado === 'recibido')
                                                        <button
                                                            type="button"
                                                            wire:click="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                            wire:loading.attr="disabled"
                                                            wire:target="previsualizarArchivo({{ $archivoHistorial->id }})"
                                                            class="btn-outline"
                                                        >
                                                            <x-heroicon-o-eye class="h-4 w-4" />
                                                            <span class="hidden sm:inline">Ver previa</span>
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
                                                            <x-heroicon-o-arrow-up-tray class="h-4 w-4" />
                                                            <span class="hidden sm:inline">Importar</span>
                                                        </button>
                                                    @else
                                                        <span class="chip bg-gray-100 text-gray-500 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700">
                                                            <x-heroicon-o-check class="h-3 w-3" />
                                                            Consolidado
                                                        </span>
                                                    @endif

                                                    <button
                                                        type="button"
                                                        wire:click="solicitarEliminarArchivo({{ $archivoHistorial->id }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="solicitarEliminarArchivo({{ $archivoHistorial->id }})"
                                                        class="btn-danger-ghost"
                                                        title="Eliminar archivo"
                                                    >
                                                        <x-heroicon-o-trash class="h-4 w-4" />
                                                        <span class="sr-only">Eliminar</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-12 text-center sm:px-8">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <x-heroicon-o-document-text class="h-7 w-7" />
                            </div>
                            <h4 class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">Aun no hay archivos registrados</h4>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cuando subas un documento, aparecera aqui con su fecha, usuario y estado.</p>
                        </div>
                    @endif
                </section>
            </div>

            {{-- Sidebar --}}
            <aside class="space-y-6 xl:sticky xl:top-6">
                {{-- Requirements --}}
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5 dark:border-gray-800">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-ocean-100 text-ocean-700 ring-1 ring-ocean-200 dark:bg-ocean-900/60 dark:text-ocean-300 dark:ring-ocean-800">
                            <x-heroicon-o-list-bullet class="h-4 w-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-950 dark:text-white">Requisitos del archivo</h3>
                    </div>
                    <div class="space-y-3 p-5">
                        <div class="flex items-start gap-3 rounded-xl bg-ocean-50/50 p-3 dark:bg-ocean-950/20">
                            <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-ocean-500" />
                            <p class="text-sm text-gray-700 dark:text-gray-300">Debe provenir del reporte de consumo del sistema actual.</p>
                        </div>
                        <div class="flex items-start gap-3 rounded-xl bg-ocean-50/50 p-3 dark:bg-ocean-950/20">
                            <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-ocean-500" />
                            <p class="text-sm text-gray-700 dark:text-gray-300">Formatos permitidos: <span class="font-semibold text-ocean-600 dark:text-ocean-400">Excel</span> o <span class="font-semibold text-ocean-600 dark:text-ocean-400">CSV</span>.</p>
                        </div>
                        <div class="flex items-start gap-3 rounded-xl bg-ocean-50/50 p-3 dark:bg-ocean-950/20">
                            <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0 text-ocean-500" />
                            <p class="text-sm text-gray-700 dark:text-gray-300">Columnas esperadas: <span class="font-semibold">fecha</span>, <span class="font-semibold">articulo</span>, <span class="font-semibold">presentacion</span>, <span class="font-semibold">cantidad</span> y <span class="font-semibold">concepto</span>.</p>
                        </div>
                    </div>
                </div>

                {{-- Last processing stats --}}
                @php
                    $ultimo = \App\Models\CocinaArchivoImportado::query()
                        ->whereIn('estado', ['procesado', 'procesado_con_errores'])
                        ->latest('fecha_procesado')
                        ->first();
                @endphp
                <div class="card overflow-hidden">
                    <div class="flex items-center gap-2.5 border-b border-gray-50 px-5 py-3.5 dark:border-gray-800">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-ocean-100 text-ocean-700 ring-1 ring-ocean-200 dark:bg-ocean-900/60 dark:text-ocean-300 dark:ring-ocean-800">
                            <x-heroicon-o-clock class="h-4 w-4" />
                        </div>
                        <h3 class="text-sm font-bold text-gray-950 dark:text-white">Ultimo procesamiento</h3>
                    </div>
                    @if ($ultimo)
                        <div class="space-y-4 p-5">
                            {{-- File name --}}
                            <div class="flex items-center gap-3">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-ocean-100 text-ocean-600 dark:bg-ocean-900/50 dark:text-ocean-400">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Archivo</p>
                                    <p class="truncate text-sm font-semibold text-gray-950 dark:text-white">{{ $ultimo->nombre_original }}</p>
                                </div>
                            </div>

                            {{-- Stats grid --}}
                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-palm-100 bg-palm-50/30 p-3.5 dark:border-palm-900 dark:bg-palm-950/20">
                                    <p class="text-xs font-bold uppercase tracking-widest text-palm-600 dark:text-palm-400">Importadas</p>
                                    <p class="mt-1 text-2xl font-black tracking-tight tabular-nums text-gray-950 dark:text-white">
                                        {{ number_format($ultimo->filas_importadas, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-red-100 bg-red-50/30 p-3.5 dark:border-red-900 dark:bg-red-950/20">
                                    <p class="text-xs font-bold uppercase tracking-widest text-red-500 dark:text-red-400">Con error</p>
                                    <p class="mt-1 text-2xl font-black tracking-tight tabular-nums text-gray-950 dark:text-white {{ $ultimo->filas_con_error > 0 ? 'text-red-600 dark:text-red-400' : '' }}">
                                        {{ number_format($ultimo->filas_con_error, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Date --}}
                            <div class="flex items-center gap-2 border-t border-gray-50 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                                {{ $ultimo->fecha_procesado?->format('d/m/Y H:i') }}
                            </div>
                        </div>
                    @else
                        <div class="px-5 py-10 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-ocean-50 text-ocean-400 dark:bg-ocean-950/30 dark:text-ocean-500">
                                <x-heroicon-o-inbox class="h-6 w-6" />
                            </div>
                            <p class="mt-4 text-sm font-semibold text-gray-950 dark:text-white">Sin procesamientos</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-[200px] mx-auto leading-relaxed">Sube un archivo y procesalo para ver los resultados aqui.</p>
                        </div>
                    @endif
                </div>
            </aside>
        </section>

    </div>

    {{-- Delete all modal --}}
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
                        <x-heroicon-o-trash class="h-7 w-7 text-red-500 dark:text-red-400" />
                    </div>
                    <h4 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Eliminar todos los Excel</h4>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Se eliminaran <strong class="font-semibold text-gray-900 dark:text-white">{{ $this->totalArchivos }}</strong> archivos, sus consumos y errores asociados. Esta accion no se puede deshacer.
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

    {{-- Delete single file modal --}}
    @if ($archivoAEliminar)
        <div class="modal-overlay" wire:click.self="cancelarEliminarArchivo" x-data
             x-on:keydown.escape.window="$wire.cancelarEliminarArchivo()"
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
                        <x-heroicon-o-trash class="h-7 w-7 text-red-500 dark:text-red-400" />
                    </div>
                    <h4 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Eliminar Excel</h4>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                        Se eliminara el archivo <strong class="font-semibold text-gray-900 dark:text-white">{{ $nombreArchivoAEliminar }}</strong> y todos sus consumos y errores asociados. Esta accion no se puede deshacer.
                    </p>
                </div>
                <div class="flex border-t border-gray-100 dark:border-gray-800">
                    <button wire:click="cancelarEliminarArchivo"
                        class="flex-1 border-r border-gray-100 px-4 py-3.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-950">
                        Cancelar
                    </button>
                    <button wire:click="eliminarArchivo"
                        class="flex-1 px-4 py-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20">
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
