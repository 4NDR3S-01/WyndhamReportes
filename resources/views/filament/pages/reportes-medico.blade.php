<x-filament-panels::page>
    <x-hero-card title="Reportes Médicos y Kardex" subtitle="Genera cierres mensuales, exporta datos y analiza el histórico de atenciones" icon="heroicon-o-document-arrow-down" color="tide" />

    <div class="page-enter space-y-6">

        {{-- ============================================================
        EMPTY STATE (sin datos)
        ============================================================ --}}
        @if ($this->fechasDisponibles->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
                <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">No hay datos registrados. Importa un archivo Excel desde la secci&oacute;n correspondiente para generar reportes.</p>
            </div>
        @endif

        @if ($this->fechasDisponibles->isNotEmpty())

        {{-- ============================================================
        STATS STRIP
        ============================================================ --}}
        @php $mesesCount = max(1, $this->kardexMensualDetallado->count()); @endphp
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-stat-card title="Atenciones registradas" :value="number_format($this->kardexMensualDetallado->sum('atenciones'), 0, ',', '.')" icon="heroicon-o-document-text" color="tide" />
            <x-stat-card title="Meses con datos" :value="$mesesCount" icon="heroicon-o-calendar-days" color="ocean" />
            <x-stat-card title="Total productos" :value="number_format(\App\Models\MedicoProducto::count(), 0, ',', '.')" icon="heroicon-o-cube" color="palm" />
            <x-stat-card title="Pacientes únicos" :value="number_format($this->kardexMensualDetallado->sum('pacientes'), 0, ',', '.')" icon="heroicon-o-user-group" color="coral" />
        </div>

        {{-- ============================================================
        SECCIÓN 1: Generar Kardex Mensual
        ============================================================ --}}
        <section class="card overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Generar Kardex Mensual</h3>
                    <p class="text-xs text-gray-400">Cierre por mes calendario o rango personalizado</p>
                </div>
                @if ($items)
                    <div class="flex gap-2">
                        <button wire:click="exportar" class="btn-outline !rounded-lg !px-3 !py-2 !text-xs">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Exportar Excel
                        </button>
                        @if($cerrado)
                            <button wire:click="reabrir" class="btn-ghost !rounded-lg !px-3 !py-2 !text-xs">Reabrir</button>
                        @else
                            <button wire:click="cerrar" class="btn-outline !rounded-lg !px-3 !py-2 !text-xs">Cerrar</button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Form --}}
            <div class="flex flex-wrap items-end gap-3 border-b border-gray-50 px-5 py-4 dark:border-gray-800">
                <div class="min-w-[160px] flex-1">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Mes</label>
                    <input type="month" wire:model="mes" wire:change="aplicarMes"
                        class="input-sm w-full">
                </div>
                <div class="min-w-[130px] flex-1">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Desde</label>
                    <input type="date" wire:model="desde" class="input-sm w-full">
                    @error('desde') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="min-w-[130px] flex-1">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Hasta</label>
                    <input type="date" wire:model="hasta" class="input-sm w-full">
                    @error('hasta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <button wire:click="generar" class="btn-primary !rounded-lg !px-4 !py-2 text-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Generar
                </button>
            </div>

            {{-- Result table --}}
            @if ($items)
                <div class="scroll-thin overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="table-header-cell">Producto</th>
                                <th class="table-header-cell text-right">Saldo anterior</th>
                                <th class="table-header-cell text-right">Ingresos</th>
                                <th class="table-header-cell text-right">Egresos</th>
                                <th class="table-header-cell text-right">Total</th>
                                <th class="table-header-cell">Caducidad</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @foreach ($items as $item)
                            <tr class="table-row">
                                <td class="table-cell">
                                    <p class="font-semibold text-gray-900 dark:text-white">{{ $item['nombre'] }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $item['tipo'] === 'medicina' ? 'Medicina' : 'Insumo' }}</p>
                                </td>
                                <td class="table-cell text-right tabular-nums text-gray-600 dark:text-gray-400">
                                    {{ number_format($item['saldo_anterior'], 2, ',', '.') }}
                                </td>
                                <td class="table-cell text-right tabular-nums font-semibold text-palm-600 dark:text-palm-400">
                                    {{ number_format($item['ingresos'], 2, ',', '.') }}
                                </td>
                                <td class="table-cell text-right tabular-nums font-semibold text-red-500">
                                    {{ number_format($item['egresos'], 2, ',', '.') }}
                                </td>
                                <td class="table-cell text-right tabular-nums font-bold text-gray-900 dark:text-white">
                                    {{ number_format($item['total'], 2, ',', '.') }}
                                </td>
                                <td class="table-cell">
                                    @if($item['fecha_caducidad'])
                                        <span class="chip bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($item['fecha_caducidad'])->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">&mdash;</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Footer totals --}}
                <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-3 dark:border-gray-800 dark:bg-gray-950/30">
                    <div class="flex flex-wrap gap-4 text-xs text-gray-500">
                        <span>Total items: <strong class="text-gray-900 dark:text-white">{{ $this->itemsCount }}</strong></span>
                        <span>Ingresos: <strong class="text-palm-600">${{ number_format($this->totalIngresos, 2, ',', '.') }}</strong></span>
                        <span>Egresos: <strong class="text-red-500">${{ number_format($this->totalEgresos, 2, ',', '.') }}</strong></span>
                        <span>Saldo total: <strong class="text-gray-900 dark:text-white">${{ number_format($this->totalSaldo, 2, ',', '.') }}</strong></span>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-tide-50 text-tide-400 dark:bg-tide-950/20">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">A&uacute;n no hay kardex generado</p>
                    <p class="mt-1 text-xs text-gray-400">Selecciona un mes y haz clic en "Generar" para calcular el cierre</p>
                </div>
            @endif

            {{-- Historial de meses --}}
            @if ($this->mesesDisponibles->isNotEmpty())
                <div class="border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="h-4 w-4 text-tide-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Historial de meses</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->mesesDisponibles as $ym)
                            @php
                                $fecha = \Carbon\Carbon::parse($ym . '-01');
                                $isActive = $historialSeleccionado === $ym;
                            @endphp
                            <button wire:click="cargarHistorial('{{ $ym }}')"
                                class="rounded-lg border px-3.5 py-1.5 text-xs font-semibold transition-all duration-150
                                    {{ $isActive
                                        ? 'border-tide-400 bg-tide-50 text-tide-700 dark:border-tide-600 dark:bg-tide-950/20 dark:text-tide-400'
                                        : 'border-gray-200 bg-white text-gray-600 hover:border-tide-300 hover:bg-tide-50/50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-tide-700 dark:hover:bg-tide-950/10' }}">
                                {{ ucfirst($fecha->isoFormat('MMMM YYYY')) }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- ============================================================
        SECCIÓN 2: Exportar Reportes (download cards)
        ============================================================ --}}
        <section class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Exportar Reportes</h3>
                <span class="text-xs text-gray-400">Descarga en formato Excel</span>
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-5">
                <a href="#" wire:click.prevent="descargarKardexMensual"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-tide-200 bg-tide-50/60 p-5 text-center shadow-sm transition hover:bg-tide-100 dark:border-tide-900 dark:bg-tide-950/30 dark:hover:bg-tide-950/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-tide-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-tide-700 dark:text-tide-300">KARDEX Mensual</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Excel multi-hoja por mes</span>
                </a>

                <a href="#" wire:click.prevent="descargarParteDiarioRango"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-ocean-200 bg-ocean-50/60 p-5 text-center shadow-sm transition hover:bg-ocean-100 dark:border-ocean-900 dark:bg-ocean-950/30 dark:hover:bg-ocean-950/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-ocean-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-ocean-700 dark:text-ocean-300">Parte Diario</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Excel por rango de fechas</span>
                </a>

                <a href="#" wire:click.prevent="descargarKardex"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-palm-200 bg-palm-50/60 p-5 text-center shadow-sm transition hover:bg-palm-100 dark:border-palm-900 dark:bg-palm-950/30 dark:hover:bg-palm-950/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-palm-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-palm-700 dark:text-palm-300">KARDEX Inventario</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Medicinas e insumos</span>
                </a>

                <a href="#" wire:click.prevent="descargarKardexConMovimientos"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-coral-200 bg-coral-50/60 p-5 text-center shadow-sm transition hover:bg-coral-100 dark:border-coral-900 dark:bg-coral-950/30 dark:hover:bg-coral-950/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-coral-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-coral-700 dark:text-coral-300">KARDEX Movimientos</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Saldo real y salidas</span>
                </a>

                <a href="#" wire:click.prevent="descargarTodo"
                    class="flex flex-col items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 text-center shadow-sm transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-950/30 dark:hover:bg-gray-950/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                    </div>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Todo</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400">Parte diario completo</span>
                </a>
            </div>
        </section>

        {{-- ============================================================
        ANÁLISIS — secciones colapsables
        ============================================================ --}}
        {{-- Selector de rango para Parte Diario --}}
        <details class="card group">
            <summary class="card-header cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-ocean-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Filtrar por rango de fechas</h3>
                </div>
                <span class="text-xs text-gray-400">Parte diario por rango personalizado</span>
            </summary>
            <div class="flex flex-wrap items-end gap-3 border-t border-gray-50 px-5 py-4 dark:border-gray-800">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
                    <input type="date" wire:model="desde" class="mt-1 block rounded-lg border-gray-300 text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
                    <input type="date" wire:model="hasta" class="mt-1 block rounded-lg border-gray-300 text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                </div>
                <a href="#" wire:click.prevent="descargarParteDiarioRango" class="inline-flex items-center rounded-lg bg-ocean-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-ocean-700">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                    Descargar
                </a>
            </div>
        </details>

        {{-- Kardex Mensual Detallado --}}
        @if ($this->kardexMensualDetallado->isNotEmpty())
        <details class="card group" open>
            <summary class="card-header cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-tide-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">KARDEX Mensual Detallado</h3>
                </div>
                <span class="chip bg-tide-100 text-tide-700 dark:bg-tide-900/30 dark:text-tide-400">{{ $this->kardexMensualDetallado->count() }}</span>
            </summary>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($this->kardexMensualDetallado as $mes)
                    <details class="group">
                        <summary class="flex cursor-pointer items-center gap-3 px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 select-none hover:bg-gray-50 dark:hover:bg-gray-950/50">
                            <svg class="h-4 w-4 shrink-0 text-tide-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            <span class="flex-1 text-base text-gray-900 dark:text-white">{{ $mes->mes }}</span>
                            <div class="flex items-center gap-3 text-xs font-normal text-gray-500 dark:text-gray-400">
                                <span>{{ $mes->atenciones }} atenciones</span>
                                <span class="hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ $mes->pacientes }} pacientes</span>
                                <span class="hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ $mes->dias }} d&iacute;as</span>
                            </div>
                        </summary>
                        <div class="border-t border-gray-100 px-5 pb-5 dark:border-gray-800">
                            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-950/30">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Causas</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->causas as $c)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $c->causa }}</span>
                                                <span class="shrink-0 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $c->total }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-tide-100 bg-tide-50/40 p-4 dark:border-tide-900 dark:bg-tide-950/20">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-tide-600 dark:text-tide-400">Medicamentos</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->medicamentos as $nombre => $count)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $nombre }}</span>
                                                <span class="shrink-0 rounded-full bg-tide-100 px-2 py-0.5 text-xs font-semibold text-tide-700 dark:bg-tide-900 dark:text-tide-300">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-950/30">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">&Aacute;reas</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->areas as $a)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $a->area }}</span>
                                                <span class="shrink-0 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $a->total }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </details>
        @endif

        {{-- Comparativo Semanal --}}
        @if ($this->semanas->isNotEmpty())
        <details class="card group" open>
            <summary class="card-header cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-gray-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Comparativo Semanal</h3>
                </div>
                <span class="text-xs text-gray-400">{{ $this->semanas->count() }} semanas</span>
            </summary>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-950/40">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Semana</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pacientes</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atenciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->semanas as $semana)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($semana->inicio)->format('d/m') }} &ndash; {{ \Carbon\Carbon::parse($semana->fin)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ $semana->pacientes }}</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($semana->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
        @endif

        {{-- Kardex Inventario --}}
        @if ($this->kardex->isNotEmpty())
        <details class="card group">
            <summary class="card-header cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-palm-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">KARDEX Inventario</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">Importado desde el Excel original</span>
                    <a href="#" wire:click.prevent="descargarKardex" class="inline-flex items-center rounded-lg bg-palm-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-palm-700">
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                        Descargar
                    </a>
                </div>
            </summary>
            <div class="space-y-3 p-5">
                @foreach ($this->kardex as $periodo => $items)
                    <details class="group rounded-xl border border-gray-100 dark:border-gray-800">
                        <summary class="flex cursor-pointer items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 select-none hover:bg-gray-50 dark:hover:bg-gray-950/50">
                            <svg class="h-3.5 w-3.5 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            {{ $periodo }}
                            <span class="text-xs text-gray-400">({{ $items->count() }} items)</span>
                        </summary>
                        <div class="overflow-x-auto px-4 pb-3">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Saldo Ant.</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Ingresos</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Egresos</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Total</th>
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Caducidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                            <td class="py-1.5 pr-3 font-medium text-gray-900 dark:text-white">{{ $item->nombre }}</td>
                                            <td class="py-1.5 pr-3 text-gray-500 dark:text-gray-400">{{ $item->tipo === 'insumo' ? 'Insumo' : 'Medicina' }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->saldo_anterior }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->ingresos }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->egresos }}</td>
                                            <td class="py-1.5 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $item->total }}</td>
                                            <td class="py-1.5 pr-3 text-gray-600 dark:text-gray-400">{{ $item->fecha_caducidad ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        </details>
        @endif

        {{-- Movimientos Kardex --}}
        @if ($this->kardexMovimientos->isNotEmpty())
        <details class="card group">
            <summary class="card-header cursor-pointer select-none">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 shrink-0 text-coral-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Movimientos de KARDEX</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-400">&Uacute;ltimas salidas generadas desde partes diarios</span>
                    <a href="#" wire:click.prevent="descargarKardexConMovimientos" class="inline-flex items-center rounded-lg bg-coral-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-coral-700">
                        Descargar Excel
                    </a>
                </div>
            </summary>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-coral-100 bg-coral-50 dark:border-coral-900 dark:bg-coral-950/30">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Fecha</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Producto</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Tipo</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Cantidad</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Origen</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-coral-700 dark:text-coral-300">Paciente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->kardexMovimientos as $mov)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-950 dark:text-white">{{ $mov->producto?->nombre ?? $mov->kardex?->nombre ?? '&mdash;' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="chip-sm {{ $mov->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700' : ($mov->tipo === 'salida' ? 'bg-red-100 text-red-700' : 'bg-sand-100 text-sand-700') }}">
                                        {{ $mov->tipo }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-950 dark:text-white">{{ $mov->cantidad }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $mov->origen === 'parte_diario' ? 'Consulta' : 'Manual' }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $mov->parteDiario?->nombres ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
        @endif

        @endif {{-- fechasDisponibles --}}
    </div>
</x-filament-panels::page>
