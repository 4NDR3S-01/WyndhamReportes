<x-filament-panels::page>
    <x-hero-card title="Kardex Mensual" subtitle="Genera y exporta cierres mensuales de inventario médico" icon="heroicon-o-calendar-days" color="tide" />

    <div class="page-enter space-y-4">
        {{-- ============================================================
        CONTROLS: Generar / Cerrar / Exportar
        ============================================================ --}}
        <section class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Generar Kardex mensual</h3>
                <span class="text-xs text-gray-400">Cierre por mes calendario o rango personalizado</span>
            </div>
            <div class="flex flex-wrap items-end gap-2.5 p-5">
                <div class="flex-1 min-w-[160px]">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Mes</label>
                    <input type="month" wire:model="mes" wire:change="aplicarMes"
                        class="input-sm w-full">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Desde</label>
                    <input type="date" wire:model="desde" class="input-sm w-full">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="mb-1 block text-[10px] font-bold uppercase tracking-widest text-gray-400">Hasta</label>
                    <input type="date" wire:model="hasta" class="input-sm w-full">
                </div>
                <button wire:click="generar" class="btn-primary !rounded-lg !px-4 !py-2 text-xs">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Generar
                </button>
            </div>
        </section>

        {{-- ============================================================
        RESULT TABLE
        ============================================================ --}}
        @if ($items)
        <section class="card overflow-hidden">
            <div class="card-header">
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                        Kardex {{ \Carbon\Carbon::parse($kardexDesde)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($kardexHasta)->format('d/m/Y') }}
                    </h3>
                    <p class="text-xs text-gray-400">
                        {{ $cerrado ? '🔒 Cerrado' : '📝 Abierto' }} ·
                        {{ $this->itemsCount }} items ·
                        Ingresos: <span class="font-semibold text-palm-600">${{ number_format($this->totalIngresos, 2, ',', '.') }}</span> ·
                        Egresos: <span class="font-semibold text-red-500">${{ number_format($this->totalEgresos, 2, ',', '.') }}</span>
                    </p>
                </div>
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
            </div>

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
                                <p class="text-[10px] text-gray-400">{{ $item['tipo'] === 'medicina' ? 'Medicina' : 'Equipo' }}</p>
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
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals footer --}}
            <div class="border-t border-gray-100 bg-gray-50/50 px-5 py-3 dark:border-gray-800 dark:bg-gray-950/30">
                <div class="flex gap-6 text-xs text-gray-500">
                    <span>Total items: <strong class="text-gray-900 dark:text-white">{{ $this->itemsCount }}</strong></span>
                    <span>Ingresos totales: <strong class="text-palm-600">${{ number_format($this->totalIngresos, 2, ',', '.') }}</strong></span>
                    <span>Egresos totales: <strong class="text-red-500">${{ number_format($this->totalEgresos, 2, ',', '.') }}</strong></span>
                </div>
            </div>
        </section>
        @else
            {{-- Empty state --}}
            <div class="card flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-tide-50 text-tide-400 dark:bg-tide-950/20">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No hay kardex generado</p>
                <p class="mt-1 text-xs text-gray-400">Selecciona un mes y haz clic en "Generar" para calcular el cierre</p>
            </div>
        @endif

        {{-- ============================================================
        HISTORIAL: Meses disponibles
        ============================================================ --}}
        @if ($this->mesesDisponibles->isNotEmpty())
        <section class="card">
            <div class="card-header">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Historial de meses</h3>
                <span class="chip bg-tide-100 text-tide-700 dark:bg-tide-900/30 dark:text-tide-400">{{ $this->mesesDisponibles->count() }}</span>
            </div>
            <div class="flex flex-wrap gap-2 p-4">
                @foreach($this->mesesDisponibles as $ym)
                    @php
                        $fecha = \Carbon\Carbon::parse($ym . '-01');
                        $isActive = $historialSeleccionado === $ym;
                    @endphp
                    <button wire:click="cargarHistorial('{{ $ym }}')"
                        class="rounded-lg border px-3.5 py-2 text-xs font-semibold transition-all duration-150
                            {{ $isActive
                                ? 'border-tide-400 bg-tide-50 text-tide-700 dark:border-tide-600 dark:bg-tide-950/20 dark:text-tide-400'
                                : 'border-gray-200 bg-white text-gray-600 hover:border-tide-300 hover:bg-tide-50/50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400 dark:hover:border-tide-700 dark:hover:bg-tide-950/10' }}">
                        {{ ucfirst($fecha->isoFormat('MMMM YYYY')) }}
                    </button>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</x-filament-panels::page>
