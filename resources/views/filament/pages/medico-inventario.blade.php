<x-filament-panels::page>
    <x-hero-card title="Inventario" subtitle="Gestión de productos, stock y movimientos" icon="heroicon-o-archive-box" color="ocean">
        <button type="button" wire:click="abrirModalProducto" class="btn-primary !rounded-xl !px-4 !py-2.5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo producto
        </button>
    </x-hero-card>

    {{-- STATS BAR --}}
    <div class="page-enter mb-4">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat">
                <div class="stat-icon bg-ocean-100 text-ocean-600 dark:bg-ocean-950/30 dark:text-ocean-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="stat-value">{{ number_format($this->totalProductos, 0, ',', '.') }}</p>
                    <p class="stat-label text-ocean-600 dark:text-ocean-400">Total productos</p>
                </div>
            </div>
            <div class="stat">
                <div class="stat-icon bg-palm-100 text-palm-600 dark:bg-palm-950/30 dark:text-palm-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <p class="stat-value text-palm-700 dark:text-palm-300">{{ number_format($this->totalMedicinas, 0, ',', '.') }}</p>
                    <p class="stat-label text-palm-600 dark:text-palm-400">Medicinas</p>
                </div>
            </div>
            <div class="stat">
                <div class="stat-icon bg-tide-100 text-tide-600 dark:bg-tide-950/30 dark:text-tide-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="stat-value text-tide-700 dark:text-tide-300">{{ number_format($this->totalInsumos, 0, ',', '.') }}</p>
                    <p class="stat-label text-tide-600 dark:text-tide-400">Insumos</p>
                </div>
            </div>
            <div class="stat">
                <div class="stat-icon {{ $this->stockBajo > 0 ? 'bg-red-100 text-red-600' : 'bg-sand-100 text-sand-600' }} dark:bg-red-950/30 dark:text-red-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <div>
                    <p class="stat-value {{ $this->stockBajo > 0 ? 'text-red-600 dark:text-red-400' : 'text-sand-700 dark:text-sand-300' }}">{{ number_format($this->stockBajo, 0, ',', '.') }}</p>
                    <p class="stat-label {{ $this->stockBajo > 0 ? 'text-red-500' : 'text-sand-600' }}">Stock bajo</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
    MAIN LAYOUT: Productos (izq) + Detalle/Movimiento/Historial (der)
    ============================================================ --}}
    <div class="grid gap-4 xl:grid-cols-[1fr_380px]">
        {{-- LEFT: Lista de productos --}}
        <section class="card overflow-hidden">
            <div class="card-header flex-wrap gap-2">
                <h3 class="text-xs font-bold text-gray-900 dark:text-white">Productos</h3>
                <div class="flex flex-1 items-center gap-1.5">
                    <div class="filter-search max-w-[180px]">
                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                        <input wire:model.live.debounce.250ms="buscar" placeholder="Buscar..." class="text-xs">
                    </div>
                    <select wire:model.live="tipoFiltro" class="filter-select-sm w-24 text-xs">
                        <option value="todos">Todos</option>
                        <option value="medicina">Medicinas</option>
                        <option value="insumo">Insumos</option>
                    </select>
                    <select wire:model.live="estadoFiltro" class="filter-select-sm w-24 text-xs">
                        <option value="activos">Activos</option>
                        <option value="todos">Todos</option>
                        <option value="inactivos">Inactivos</option>
                    </select>
                </div>
            </div>

            {{-- Product table compact --}}
            <div class="scroll-thin overflow-x-auto" style="max-height: calc(100vh - 300px);">
                <table class="min-w-full text-xs">
                    <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur dark:bg-gray-950/95">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="table-header-cell !text-[10px]">Producto</th>
                            <th class="table-header-cell !text-[10px] text-right w-16">Saldo</th>
                            <th class="table-header-cell !text-[10px] hidden sm:table-cell w-24">Caducidad</th>
                            <th class="table-header-cell !text-[10px] text-right w-20">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @forelse ($this->resumen as $p)
                            @php
                                $saldo = $p->saldoActual();
                                $stockBajo = $p->stock_minimo > 0 && $saldo <= $p->stock_minimo;
                                $porCaducar = $p->fecha_caducidad && $p->fecha_caducidad->lte(now()->addDays(60));
                                $isSelected = $productoSeleccionadoId === $p->id;
                            @endphp
                            <tr class="cursor-pointer transition {{ $isSelected ? 'bg-ocean-50/70 dark:bg-ocean-950/30' : 'hover:bg-gray-50/50 dark:hover:bg-gray-800/30' }}"
                                wire:click="seleccionarProducto({{ $p->id }})">
                                <td class="table-cell py-2">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-sm shrink-0">{{ $p->tipo === 'medicina' ? '💊' : '🏥' }}</span>
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $p->nombre }}</p>
                                            <p class="truncate text-[10px] text-gray-400">
                                                {{ $p->tipo === 'medicina' ? 'Medicina' : 'Insumo' }}
                                                @if($p->medicamento)
                                                    · {{ $p->medicamento->nombre }}
                                                @endif
                                                @if($p->stock_minimo > 0)
                                                    · Min {{ fmod($p->stock_minimo, 1) == 0 ? number_format($p->stock_minimo, 0, ',', '.') : number_format($p->stock_minimo, 1, ',', '.') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="table-cell py-2 text-right">
                                    <span class="font-bold tabular-nums {{ $stockBajo ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                        {{ number_format($saldo, 0, ',', '.') }}
                                    </span>
                                    @if($stockBajo)
                                        <span class="ml-0.5 inline-block h-1.5 w-1.5 rounded-full bg-red-500" title="Stock bajo"></span>
                                    @endif
                                </td>
                                <td class="table-cell py-2 hidden sm:table-cell">
                                    @if($p->fecha_caducidad)
                                        <span class="chip text-[10px] {{ $porCaducar ? 'bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                            {{ $p->fecha_caducidad->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="table-cell py-2 text-right" onclick="event.stopPropagation()">
                                    <div class="flex items-center justify-end gap-0.5">
                                        <button wire:click="editarProducto({{ $p->id }})"
                                            class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-ocean-600 dark:hover:bg-gray-800 dark:hover:text-ocean-400"
                                            title="Editar">
                                            <x-heroicon-m-pencil-square class="h-3.5 w-3.5" />
                                        </button>
                                        <button wire:click="alternarProducto({{ $p->id }})"
                                            class="rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-sand-600 dark:hover:bg-gray-800 dark:hover:text-sand-400"
                                            title="{{ $p->activo ? 'Desactivar' : 'Activar' }}">
                                            <x-heroicon-m-no-symbol class="h-3.5 w-3.5" />
                                        </button>
                                        <button wire:click="eliminarProducto({{ $p->id }})"
                                            wire:confirm="¿Eliminar este producto permanentemente?"
                                            class="rounded-md p-1 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/30 dark:hover:text-red-400"
                                            title="Eliminar">
                                            <x-heroicon-m-trash class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-gray-400">
                                    No se encontraron productos
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- RIGHT: Panel de detalle (solo si hay producto seleccionado) --}}
        <section x-data="{ tab: 'movimiento' }">
            @if($this->productoSeleccionado)
                @php $sel = $this->productoSeleccionado; @endphp
                <div class="card overflow-hidden">
                    {{-- Header compacto con info del producto --}}
                    <div class="bg-ocean-50/60 px-4 py-2.5 dark:bg-ocean-950/25">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold text-gray-900 dark:text-white">{{ $sel->nombre }}</p>
                                <p class="text-[10px] text-gray-500">
                                    {{ $sel->tipo === 'medicina' ? 'Medicina' : 'Insumo' }}
                                    @if($sel->medicamento) · {{ $sel->medicamento->nombre }} @endif
                                    @if($sel->stock_minimo > 0) · Min {{ number_format($sel->stock_minimo, 0, ',', '.') }} @endif
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-xl font-black tabular-nums {{ $sel->saldoActual() <= $sel->stock_minimo ? 'text-red-600' : 'text-ocean-700 dark:text-ocean-300' }}">
                                    {{ number_format($sel->saldoActual(), 0, ',', '.') }}
                                </span>
                                @if($sel->fecha_caducidad)
                                    <p class="text-[10px] {{ $sel->fecha_caducidad->lte(now()->addDays(60)) ? 'text-red-500' : 'text-gray-400' }}">
                                        Caduca {{ $sel->fecha_caducidad->format('d/m/Y') }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- TABS --}}
                    <div class="flex border-b border-gray-100 dark:border-gray-800">
                        <button @click="tab = 'movimiento'"
                            :class="tab === 'movimiento' ? 'border-ocean-500 text-ocean-700 dark:text-ocean-300' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex-1 border-b-2 px-3 py-2 text-[11px] font-semibold transition">
                            📥 Movimiento
                        </button>
                        <button @click="tab = 'historial'"
                            :class="tab === 'historial' ? 'border-ocean-500 text-ocean-700 dark:text-ocean-300' : 'border-transparent text-gray-400 hover:text-gray-600'"
                            class="flex-1 border-b-2 px-3 py-2 text-[11px] font-semibold transition">
                            📋 Historial
                            @if($sel->movimientos->isNotEmpty())
                                <span class="ml-1 text-[10px] opacity-60">{{ $sel->movimientos->count() }}</span>
                            @endif
                        </button>
                    </div>

                    {{-- TAB: Movimiento --}}
                    <div x-show="tab === 'movimiento'" x-cloak>
                        <form wire:submit.prevent="guardarMovimiento" class="space-y-2 p-3">
                            <div class="grid grid-cols-2 gap-1.5">
                                <select wire:model="tipo" class="input-sm text-xs">
                                    <option value="ingreso">📥 Ingreso</option>
                                    <option value="salida">📤 Salida</option>
                                    <option value="ajuste">⚙️ Ajuste</option>
                                </select>
                                <input type="number" step="0.01" wire:model="cantidad"
                                    class="input-sm text-xs" placeholder="Cantidad" required>
                            </div>
                            <div class="grid grid-cols-2 gap-1.5">
                                <input type="date" wire:model="fecha_movimiento" class="input-sm text-xs">
                                <input wire:model="responsable" placeholder="Responsable" class="input-sm text-xs">
                            </div>
                            {{-- Detalles expandibles --}}
                            <details class="group">
                                <summary class="cursor-pointer text-[10px] font-medium text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">+ Más opciones</summary>
                                <textarea wire:model="observacion" placeholder="Observación" rows="2" class="input-sm mt-1.5 w-full text-xs"></textarea>
                            </details>
                            <button type="submit" class="btn-primary w-full !rounded-lg !py-1.5 text-xs">
                                Registrar movimiento
                            </button>
                        </form>
                    </div>

                    {{-- TAB: Historial --}}
                    <div x-show="tab === 'historial'" x-cloak>
                        @if($sel->movimientos->isNotEmpty())
                            <div class="scroll-thin max-h-64 overflow-y-auto">
                                <table class="min-w-full text-[11px]">
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                        @foreach($sel->movimientos as $mov)
                                        <tr>
                                            <td class="px-3 py-1.5 text-gray-500">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                            <td class="px-3 py-1.5">
                                                <span class="chip-sm {{ $mov->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700' : ($mov->tipo === 'salida' ? 'bg-red-100 text-red-700' : 'bg-sand-100 text-sand-700') }}">
                                                    {{ $mov->tipo }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-1.5 text-right font-bold tabular-nums {{ $mov->cantidad > 0 ? 'text-gray-900 dark:text-white' : 'text-red-600' }}">
                                                {{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-1.5 text-gray-400">
                                                {{ $mov->origen === 'parte_diario' ? '🏥' : '✋' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="px-4 py-8 text-center text-xs text-gray-400">Sin movimientos registrados</div>
                        @endif
                    </div>
                </div>
            @else
                {{-- Empty state --}}
                <div class="card flex flex-col items-center justify-center py-12 text-center">
                    <div class="mb-2 flex h-12 w-12 items-center justify-center rounded-xl bg-ocean-50 text-ocean-400 dark:bg-ocean-950/20">
                        <x-heroicon-o-cursor-arrow-rays class="h-6 w-6" />
                    </div>
                    <p class="text-xs font-medium text-gray-600 dark:text-gray-400">Selecciona un producto</p>
                    <p class="mt-1 text-[10px] text-gray-400">Haz clic en la tabla para ver detalle y movimientos</p>
                </div>
            @endif
        </section>
    </div>

    {{-- ============================================================
    MOVIMIENTOS GLOBALES — Colapsable (default cerrado)
    ============================================================ --}}
    <div class="mt-4" x-data="{ abierto: false }">
        <button @click="abierto = !abierto"
            class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800/50">
            <span class="flex items-center gap-2">
                <x-heroicon-m-arrow-path-rounded-square class="h-4 w-4 text-gray-400" />
                Movimientos recientes
                <span class="chip bg-ocean-100 text-ocean-700 dark:bg-ocean-900/30 dark:text-ocean-400 text-[10px]">{{ $this->movimientos->count() }}</span>
            </span>
            <x-heroicon-m-chevron-down class="h-4 w-4 text-gray-400 transition" ::class="abierto ? 'rotate-180' : ''" />
        </button>
        <div x-show="abierto" x-cloak class="card mt-2 overflow-hidden">
            <div class="scroll-thin overflow-x-auto max-h-80">
                <table class="min-w-full text-xs">
                    <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur dark:bg-gray-950/95">
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="table-header-cell !text-[10px]">Fecha</th>
                            <th class="table-header-cell !text-[10px]">Producto</th>
                            <th class="table-header-cell !text-[10px]">Tipo</th>
                            <th class="table-header-cell !text-[10px] text-right">Cant</th>
                            <th class="table-header-cell !text-[10px]">Origen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @foreach ($this->movimientos as $m)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                            <td class="table-cell py-1.5 text-gray-500">{{ $m->fecha_movimiento?->format('d/m/Y') }}</td>
                            <td class="table-cell py-1.5 font-medium text-gray-900 dark:text-white">{{ $m->producto?->nombre ?? '—' }}</td>
                            <td class="table-cell py-1.5">
                                <span class="chip-sm {{ $m->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700' : ($m->tipo === 'salida' ? 'bg-red-100 text-red-700' : 'bg-sand-100 text-sand-700') }}">
                                    {{ $m->tipo }}
                                </span>
                            </td>
                            <td class="table-cell py-1.5 text-right font-bold tabular-nums text-gray-900 dark:text-white">{{ $m->cantidad }}</td>
                            <td class="table-cell py-1.5 text-gray-400">
                                {{ $m->origen === 'parte_diario' ? '🏥 #'.$m->parteDiario?->id : '✋ Manual' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ============================================================
    MODAL: Producto (crear/editar) — más compacto
    ============================================================ --}}
    @if ($modalProductoAbierto)
        <div class="modal-overlay" x-data @click.self="$wire.cerrarModalProducto()" x-on:keydown.escape.window="$wire.cerrarModalProducto()">
            <div class="modal-panel-lg !max-w-lg">
                <div class="modal-accent-ocean"></div>
                <section>
                    <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-gray-950 dark:text-white">
                            {{ $editandoId ? 'Editar producto' : 'Nuevo producto' }}
                        </h2>
                        <button wire:click="cerrarModalProducto" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                            <x-heroicon-m-x-mark class="h-5 w-5" />
                        </button>
                    </div>
                    <form wire:submit.prevent="guardarProducto" class="space-y-3 px-4 py-3">
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Tipo</label>
                                <select wire:model="productoTipo" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    <option value="medicina">Medicina</option>
                                    <option value="insumo">Insumo</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Stock mínimo</label>
                                <input type="number" step="0.01" wire:model="productoStockMinimo" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Nombre <span class="text-red-400">*</span></label>
                            <input wire:model="productoNombre" placeholder="Ej. Paracetamol 500mg" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white" autofocus>
                            @error('productoNombre') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($productoTipo === 'medicina')
                        <div>
                            <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">
                                Vincular con catálogo clínico
                                <span class="font-normal text-gray-400">(descuento automático al dispensar)</span>
                            </label>
                            <select wire:model="productoMedicamentoId" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="">— Sin vincular —</option>
                                @foreach($this->medicamentosCatalog as $med)
                                    <option value="{{ $med->id }}">{{ $med->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div class="grid gap-2 sm:grid-cols-2">
                            <div>
                                <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Fecha de caducidad</label>
                                <input type="date" wire:model="productoFechaCaducidad" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                                    <input type="checkbox" wire:model="productoActivo" class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500"> Activo
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="text-[11px] font-medium text-gray-600 dark:text-gray-300">Observaciones</label>
                            <textarea wire:model="productoObservaciones" rows="2" placeholder="Opcional" class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-xs shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                            <button type="button" wire:click="cerrarModalProducto" class="btn-outline !rounded-lg !px-3.5 !py-2 text-xs">Cancelar</button>
                            <button type="submit" class="btn-primary !rounded-lg !px-4 !py-2 text-xs">Guardar</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    @endif
</x-filament-panels::page>
