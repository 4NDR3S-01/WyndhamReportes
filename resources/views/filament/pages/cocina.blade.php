<x-filament-panels::page>
    <div class="flex items-center justify-between gap-4 pb-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Cocina</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if ($this->minFecha) {{ \Carbon\Carbon::parse($this->minFecha)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($this->maxFecha)->format('d/m/Y') }} @else Sin datos @endif
                &middot; {{ number_format($this->total, 0, ',', '.') }} registros &middot; {{ $this->archivos }} archivo{{ $this->archivos !== 1 ? 's' : '' }}
            </p>
        </div>
    </div>

    @if ($this->total === 0)
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Sin datos todavia</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sube un archivo Excel desde <strong>Subir datos</strong> y presiona <strong>Importar</strong>.</p>
        </div>
    @endif

    @if ($this->total > 0 && $this->fechasDisponibles->isNotEmpty())
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-2 px-5 py-3">
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Consumo del dia</span>
                <select wire:model.live="fechaSeleccionada" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @foreach ($this->fechasDisponibles as $f)
                        @php $fd = \Carbon\Carbon::parse($f)->format('Y-m-d'); @endphp
                        <option value="{{ $fd }}">{{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}</option>
                    @endforeach
                </select>
            </div>

            @if ($this->fechaSeleccionada && count($this->consumoDelDia) > 0)
                <div class="border-t border-gray-100 px-5 pb-4 dark:border-gray-800">
                    @foreach ($this->consumoDelDia as $u => $items)
                        <div class="mt-2 first:mt-0">
                            <div class="mb-2">
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-700 ring-1 ring-sky-100 dark:bg-sky-950/50 dark:text-sky-300 dark:ring-sky-900">
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
        </div>
    @endif

    @if ($this->total > 0)
        <details class="mb-4 group rounded-2xl border border-sky-200 bg-white shadow-sm dark:border-sky-900 dark:bg-gray-900" open>
            <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300 select-none">
                Recomendacion de produccion
                <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="border-t border-sky-100 px-5 pb-4 dark:border-sky-900">
                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Fecha referencia</label>
                        <select wire:model.live="fechaReferencia" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @foreach ($this->fechasDisponibles as $f)
                                @php $fd = \Carbon\Carbon::parse($f)->format('Y-m-d'); @endphp
                                <option value="{{ $fd }}">{{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Huespedes ese dia</label>
                        <input type="number" min="1" wire:model.live.debounce.400ms="huespedesReferencia" placeholder="Ej. 120" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-700 dark:text-gray-300">Huespedes esperados</label>
                        <input type="number" min="1" wire:model.live.debounce.400ms="huespedesObjetivo" placeholder="Ej. 150" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    </div>
                </div>

                @if ($this->recomendacion->isNotEmpty())
                    <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($this->recomendacion as $rec)
                            <div class="flex items-center justify-between rounded-xl border border-sky-100 bg-sky-50/60 px-3 py-2 text-sm dark:border-sky-900 dark:bg-sky-950/30">
                                <span class="truncate font-medium text-gray-950 dark:text-white">{{ $rec->nombre }}</span>
                                <span class="ml-2 shrink-0 font-semibold text-sky-700 dark:text-sky-300">{{ $this->formatoValor($rec->sugerido, $rec->esEntero) }} <span class="text-xs font-normal">{{ $rec->unidad }}</span></span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </details>
    @endif
</x-filament-panels::page>
