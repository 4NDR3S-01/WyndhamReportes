<x-filament-panels::page>
    <x-hero-card
        title="Dashboard de Cocina"
        icon="heroicon-o-presentation-chart-line"
        color="ocean"
        :subtitle="$this->archivoSeleccionado
            ? 'Documento: ' . \Illuminate\Support\Str::limit($this->archivoSeleccionado->nombre_original, 40) . ' · ' .
              ($this->minFecha ? \Carbon\Carbon::parse($this->minFecha)->format('d/m/Y') : '—') . ' — ' .
              ($this->maxFecha ? \Carbon\Carbon::parse($this->maxFecha)->format('d/m/Y') : '—') . ' · ' .
              number_format($this->total, 0, ',', '.') . ' registros'
            : 'Sin documento seleccionado'"
    />

    {{-- Barra de documento fuente activo --}}
    <div class="page-enter">
        <section class="card">
            <div class="card-header">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Documento activo</span>
                    @if ($this->archivoSeleccionado)
                        <span class="chip bg-ocean-50 text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/50 dark:text-ocean-300 dark:ring-ocean-900">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            {{ \Illuminate\Support\Str::limit($this->archivoSeleccionado->nombre_original, 48) }}
                        </span>
                        <span class="chip-sm bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $this->archivoSeleccionado->fecha_subida->format('d/m/Y H:i') }}
                        </span>
                    @else
                        <span class="chip bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">Sin documento</span>
                    @endif
                </div>
                <button wire:click="abrirModalArchivo" class="btn-outline shrink-0">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    Cambiar documento
                </button>
            </div>
        </section>
    </div>

    {{-- Stats KPI --}}
    <div class="page-enter mb-8 grid gap-6 sm:grid-cols-4">
        <x-stat-card
            title="Consumos totales"
            :value="number_format($this->total, 0, ',', '.')"
            description="Filas del documento"
            icon="heroicon-o-document-text"
            color="brand"
        />
        <x-stat-card
            title="Productos"
            :value="number_format($this->productosCount, 0, ',', '.')"
            description="Distintos en el documento"
            icon="heroicon-o-tag"
            color="palm"
        />
        <x-stat-card
            title="Producto top"
            :value="$this->productoTop ? \Illuminate\Support\Str::limit($this->productoTop->nombre, 16) : '—'"
            :description="$this->productoTop
                ? $this->formatoSegunUnidad($this->productoTop->total, $this->productoTop->unidad) . ' consumidos'
                : 'Sin datos'"
            icon="heroicon-o-trophy"
            color="coral"
        />
        <x-stat-card
            title="Dias cubiertos"
            :value="number_format($this->fechasRegistradas, 0, ',', '.')"
            :description="number_format($this->productosUltimaFecha, 0, ',', '.') . ' productos el ultimo dia'"
            icon="heroicon-o-squares-2x2"
            color="ocean"
        />
    </div>

    <div class="page-enter space-y-4">
        @if ($this->total === 0)
            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Sin datos todavia</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Sube un archivo con <strong>Cambiar documento → Subir nuevo</strong> o selecciona otro documento ya cargado.
                </p>
            </div>
        @endif

        @if ($this->total > 0 && $this->fechasDisponibles->isNotEmpty())
            <section class="card">
                <div class="card-header">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Consumo del dia</span>
                    <x-cocina.date-picker
                        field="fechaSeleccionada"
                        label="Fecha"
                        :available="$this->fechasDisponiblesRaw"
                    />
                </div>

                @if ($this->fechaSeleccionada && count($this->consumoDelDia) > 0)
                    <div class="border-t border-gray-100 px-5 pb-4 dark:border-gray-800">
                        @foreach ($this->consumoDelDia as $u => $items)
                            <div class="mt-2 first:mt-0">
                                <div class="mb-2">
                                    <span class="chip bg-ocean-50 text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/50 dark:text-ocean-300 dark:ring-ocean-900">
                                        {{ match($u) { 'kilo' => 'Kilos', 'litro' => 'Litros', 'porcion' => 'Porciones', 'gramo' => 'Gramos', default => 'Unidades' } }}
                                        &middot; {{ $this->formatoSegunUnidad($this->totalesPorUnidad[$u] ?? 0, $u) }}
                                    </span>
                                </div>
                                <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                    @foreach ($items as $item)
                                        <div class="flex items-center justify-between rounded-xl border border-gray-100 bg-gray-50/70 px-3 py-2 text-sm dark:border-gray-800 dark:bg-gray-950/40">
                                            <span class="truncate font-medium text-gray-950 dark:text-white">{{ $item->producto?->nombre ?? 'Sin nombre' }}</span>
                                            <span class="ml-2 shrink-0 font-semibold text-gray-700 dark:text-gray-300">{{ $this->formatoSegunUnidad($item->total_cantidad, $u) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($this->total > 0)
            <details class="card group" open>
                <summary class="card-header flex cursor-pointer items-center justify-between gap-2 text-xs font-semibold uppercase tracking-wide text-ocean-700 dark:text-ocean-300 select-none">
                    Recomendacion de produccion
                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="border-t border-ocean-100 px-5 pb-4 dark:border-ocean-900">
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Fecha referencia</label>
                            <x-cocina.date-picker
                                field="fechaReferencia"
                                label="Fecha referencia"
                                align="left"
                                :available="$this->fechasDisponiblesRaw"
                            />
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Huespedes ese dia</label>
                            <input type="number" min="1" wire:model.live.debounce.400ms="huespedesReferencia" placeholder="Ej. 120" class="input mt-1">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Huespedes esperados</label>
                            <input type="number" min="1" wire:model.live.debounce.400ms="huespedesObjetivo" placeholder="Ej. 150" class="input mt-1">
                        </div>
                    </div>

                    @if ($this->recomendacion->isNotEmpty())
                        <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($this->recomendacion as $rec)
                                <div class="flex items-center justify-between rounded-xl border border-ocean-100 bg-ocean-50/60 px-3 py-2 text-sm dark:border-ocean-900 dark:bg-ocean-950/30">
                                    <span class="truncate font-medium text-gray-950 dark:text-white">{{ $rec->nombre }}</span>
                                    <span class="ml-2 shrink-0 font-semibold text-ocean-700 dark:text-ocean-300">{{ $this->formatoValor($rec->sugerido, $rec->esEntero) }} <span class="text-xs font-normal">{{ $rec->unidad }}</span></span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </details>
        @endif
    </div>

    {{-- Modal: selector de documento fuente --}}
    @if ($this->modalArchivoAbierto)
        <div
            class="modal-overlay"
            wire:click.self="cerrarModalArchivo"
            x-data="{ subir: false }"
            x-on:keydown.escape.window="subir = false; $wire.cerrarModalArchivo()"
        >
            <div class="modal-panel-lg">
                <div class="modal-accent-ocean"></div>

                <div class="flex items-center justify-between px-5 py-4">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Seleccionar documento</h3>
                    <button wire:click="cerrarModalArchivo" class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div x-show="!subir">
                    <div class="scroll-thin max-h-80 space-y-1.5 overflow-y-auto px-3 pb-2">
                        @forelse ($this->archivosDisponibles as $a)
                            <button
                                wire:click="seleccionarArchivo({{ $a->id }})"
                                class="flex w-full items-center gap-3 rounded-xl border px-3.5 py-3 text-left transition
                                    @if ($this->archivoSeleccionadoId === $a->id)
                                        border-ocean-300 bg-ocean-50/60 ring-1 ring-ocean-200 dark:border-ocean-700 dark:bg-ocean-950/30
                                    @else
                                        border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50
                                    @endif"
                            >
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-ocean-50 text-ocean-600 dark:bg-ocean-950/50 dark:text-ocean-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $a->nombre_original }}</span>
                                    <span class="mt-0.5 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ $a->fecha_subida->format('d/m/Y H:i') }}</span>
                                        <span class="chip-sm bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $a->estado)) }}</span>
                                    </span>
                                </span>
                                @if ($this->archivoSeleccionadoId === $a->id)
                                    <svg class="h-5 w-5 shrink-0 text-ocean-600 dark:text-ocean-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @endif
                            </button>
                        @empty
                            <p class="py-8 text-center text-sm text-gray-400">No hay documentos subidos todavia.</p>
                        @endforelse
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-5 py-3.5 dark:border-gray-800">
                        <p class="text-xs text-gray-400">El dashboard muestra solo este documento.</p>
                        <button type="button" x-on:click="subir = true" class="btn-primary">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            Subir nuevo
                        </button>
                    </div>
                </div>

                <div x-show="subir" x-cloak>
                    <div class="space-y-4 px-5 py-4">
                        <div>
                            <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Archivo Excel</label>
                            <input type="file" wire:model="archivo" accept=".xlsx,.xls,.csv" class="input mt-1.5">
                            @error('archivo') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                            <p class="mt-1.5 text-xs text-gray-400">Excel (.xlsx, .xls) o CSV · maximo 10 MB. Se procesara de inmediato.</p>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-1">
                            <button type="button" x-on:click="subir = false" class="btn-outline">Volver</button>
                            <button type="button" wire:click="subirDesdeDashboard" class="btn-primary">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                Cargar y procesar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
