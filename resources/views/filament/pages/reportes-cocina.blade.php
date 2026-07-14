<x-filament-panels::page>
    <x-hero-card title="Reportes" subtitle="Exporta datos de consumo en formato Excel" icon="heroicon-o-document-chart-bar" color="tide" />

    @if ($this->fechasDisponibles->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400">Importa un archivo Excel para generar reportes.</p>
        </div>
    @endif

    <div class="page-enter grid gap-4 lg:grid-cols-2">
        @if ($this->fechasDisponibles->isNotEmpty())
            <section class="card">
                <div class="card-header">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Descargar por rango</h3>
                </div>
                <div class="space-y-3 p-5">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
                            <input type="date" wire:model="desde" class="input mt-1">
                            @error('desde') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
                            <input type="date" wire:model="hasta" class="input mt-1">
                            @error('hasta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <a href="#" wire:click.prevent="descargarRango" class="btn-primary w-full">
                        <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                        Descargar Excel
                    </a>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">Descargar por dia</h3>
                </div>
                <div class="space-y-3 p-5">
                    @foreach ($this->fechasDisponibles->take(6) as $f)
                        @php $fd = \Carbon\Carbon::parse($f)->format('Y-m-d'); @endphp
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-medium text-gray-950 dark:text-white">{{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}</span>
                            <a href="#" wire:click.prevent="descargarDia('{{ $fd }}')" class="btn-primary">
                                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                Excel
                            </a>
                        </div>
                    @endforeach
                    <div class="pt-2">
                        <a href="#" wire:click.prevent="descargarTodo" class="btn-outline w-full">
                            Descargar todas las fechas
                        </a>
                    </div>
                </div>
            </section>
        @endif
    </div>

    @if ($this->semanas->isNotEmpty())
        <section class="card mt-4">
            <div class="card-header">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Comparativo semanal</h3>
            </div>
            <div class="scroll-thin overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-950/40">
                        <tr>
                            <th class="table-header-cell">Semana</th>
                            <th class="table-header-cell text-right">Productos</th>
                            <th class="table-header-cell text-right">Registros</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->semanas as $semana)
                            <tr class="table-row">
                                <td class="table-cell text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($semana->inicio)->format('d/m') }} – {{ \Carbon\Carbon::parse($semana->fin)->format('d/m/Y') }}
                                </td>
                                <td class="table-cell text-right text-gray-700 dark:text-gray-300">{{ $semana->productos }}</td>
                                <td class="table-cell text-right font-semibold text-gray-950 dark:text-white">{{ number_format($semana->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if ($this->erroresImportacion->isNotEmpty())
        <section class="card mt-4">
            <div class="card-header">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Errores de importacion</h3>
            </div>
            <div class="scroll-thin overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-coral-50 dark:bg-coral-950/30">
                        <tr>
                            <th class="table-header-cell text-coral-700 dark:text-coral-300">Archivo</th>
                            <th class="table-header-cell text-coral-700 dark:text-coral-300">Fila</th>
                            <th class="table-header-cell text-coral-700 dark:text-coral-300">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->erroresImportacion as $error)
                            <tr class="table-row">
                                <td class="table-cell text-gray-700 dark:text-gray-300">{{ $error->archivoImportado?->nombre_original }}</td>
                                <td class="table-cell text-gray-700 dark:text-gray-300">{{ $error->fila }}</td>
                                <td class="table-cell text-gray-700 dark:text-gray-300">{{ $error->mensaje }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-filament-panels::page>
