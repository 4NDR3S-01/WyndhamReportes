<x-filament-panels::page>
    <x-hero-card title="Reportes" subtitle="Exporta datos de consumo en formato Excel" icon="heroicon-o-document-chart-bar" color="tide" />

    @if ($this->fechasDisponibles->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Importa un archivo Excel para generar reportes.</p>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        @if ($this->fechasDisponibles->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Descargar por rango</h3>
                </div>
                <div class="space-y-3 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
                            <input type="date" wire:model="desde" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('desde') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
                            <input type="date" wire:model="hasta" class="mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @error('hasta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <a href="#" wire:click.prevent="descargarRango" class="inline-flex w-full items-center justify-center rounded-lg bg-ocean-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-ocean-700">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                        Descargar Excel
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Descargar por dia</h3>
                </div>
                <div class="space-y-3 p-5">
                    @foreach ($this->fechasDisponibles->take(6) as $f)
                        @php $fd = \Carbon\Carbon::parse($f)->format('Y-m-d'); @endphp
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">{{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}</span>
                            <a href="#" wire:click.prevent="descargarDia('{{ $fd }}')" class="inline-flex items-center rounded-lg bg-ocean-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-ocean-700">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                Excel
                            </a>
                        </div>
                    @endforeach
                    <div class="pt-2">
                        <a href="#" wire:click.prevent="descargarTodo" class="inline-flex w-full items-center justify-center rounded-lg border border-ocean-200 bg-ocean-50 px-3 py-2 text-xs font-semibold text-ocean-700 transition hover:bg-ocean-100 dark:border-ocean-900 dark:bg-ocean-950/30 dark:text-ocean-300 dark:hover:bg-ocean-950/50">
                            Descargar todas las fechas
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if ($this->semanas->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Comparativo semanal</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-950/40">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Semana</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Productos</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Registros</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->semanas as $semana)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                        {{ \Carbon\Carbon::parse($semana->inicio)->format('d/m') }} – {{ \Carbon\Carbon::parse($semana->fin)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ $semana->productos }}</td>
                                    <td class="px-5 py-3 text-right text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($semana->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    @if ($this->erroresImportacion->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-coral-100 bg-white shadow-sm dark:border-coral-900/40 dark:bg-gray-900">
            <div class="border-b border-coral-100 px-5 py-4 dark:border-coral-900/40">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Errores de importacion</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-coral-100 bg-coral-50 dark:border-coral-900/40 dark:bg-coral-950/30">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Archivo</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Fila</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->erroresImportacion as $error)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->archivoImportado?->nombre_original }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->fila }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->mensaje }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
