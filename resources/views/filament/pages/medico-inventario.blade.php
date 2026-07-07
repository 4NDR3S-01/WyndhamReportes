<x-filament-panels::page>
    <x-hero-card title="Inventario" subtitle="Gestión de productos, stock y movimientos" icon="heroicon-o-archive-box" color="ocean">
        <button type="button" wire:click="abrirModalProducto" class="btn-primary !rounded-xl !px-4 !py-2.5">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo producto
        </button>
    </x-hero-card>

    {{-- ============================================================
    STATS BAR
    ============================================================ --}}
    <div class="page-enter space-y-4">
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
                    <p class="stat-value text-tide-700 dark:text-tide-300">{{ number_format($this->totalEquipos, 0, ',', '.') }}</p>
                    <p class="stat-label text-tide-600 dark:text-tide-400">Equipos</p>
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

        {{-- ============================================================
        MAIN LAYOUT: Productos (izq) + Detalle/Movimiento (der)
        ============================================================ --}}
        <div class="grid gap-4 xl:grid-cols-[1fr_420px]">
            {{-- LEFT: Product list with filters --}}
            <section class="card overflow-hidden">
                <div class="card-header flex-wrap gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Productos</h3>
                    <div class="flex flex-1 items-center gap-2">
                        <div class="filter-search max-w-xs">
                            <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                            <input wire:model.live.debounce.300ms="buscar" placeholder="Buscar producto...">
                        </div>
                        <select wire:model.live="tipoFiltro" class="filter-select-sm w-28">
                            <option value="todos">Todos</option>
                            <option value="medicina">Medicinas</option>
                            <option value="equipo">Equipos</option>
                        </select>
                        <select wire:model.live="estadoFiltro" class="filter-select-sm w-28">
                            <option value="activos">Activos</option>
                            <option value="todos">Todos</option>
                            <option value="inactivos">Inactivos</option>
                        </select>
                    </div>
                </div>

                {{-- Product table --}}
                <div class="scroll-thin overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="table-header-cell">Producto</th>
                                <th class="table-header-cell text-right">Saldo</th>
                                <th class="table-header-cell">Caducidad</th>
                                <th class="table-header-cell text-right">Acciones</th>
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
                                <tr class="table-row cursor-pointer {{ $isSelected ? 'bg-ocean-50/50 dark:bg-ocean-950/20' : '' }}"
                                    wire:click="seleccionarProducto({{ $p->id }})">
                                    <td class="table-cell">
                                        <div class="flex items-center gap-2">
                                            <span class="text-base">
                                                {{ $p->tipo === 'medicina' ? '💊' : '🔧' }}
                                            </span>
                                            <div>
                                                <p class="font-semibold text-gray-900 dark:text-white">{{ $p->nombre }}</p>
                                                <p class="text-[10px] text-gray-400">
                                                    {{ $p->tipo === 'medicina' ? 'Medicina' : 'Equipo' }}
                                                    @if($p->medicamento)
                                                        · Vinculado a {{ $p->medicamento->nombre }}
                                                    @endif
                                                    @if($p->stock_minimo > 0)
                                                        · Min. {{ fmod($p->stock_minimo, 1) == 0 ? number_format($p->stock_minimo, 0, ',', '.') : number_format($p->stock_minimo, 1, ',', '.') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="table-cell text-right">
                                        <span class="font-bold tabular-nums {{ $stockBajo ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                                            {{ fmod($saldo, 1) == 0 ? number_format($saldo, 0, ',', '.') : number_format($saldo, 1, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="table-cell">
                                        @if($p->fecha_caducidad)
                                            <span class="chip {{ $porCaducar ? 'bg-red-100 text-red-700 dark:bg-red-950/20 dark:text-red-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">
                                                {{ $p->fecha_caducidad->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="table-cell text-right" onclick="event.stopPropagation()">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="editarProducto({{ $p->id }})"
                                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-ocean-600 dark:hover:bg-gray-800 dark:hover:text-ocean-400"
                                                title="Editar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="alternarProducto({{ $p->id }})"
                                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-sand-600 dark:hover:bg-gray-800 dark:hover:text-sand-400"
                                                title="{{ $p->activo ? 'Desactivar' : 'Activar' }}">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>
                                            <button wire:click="eliminarProducto({{ $p->id }})"
                                                wire:confirm="¿Eliminar este producto permanentemente?"
                                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/20 dark:hover:text-red-400"
                                                title="Eliminar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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

            {{-- RIGHT: Detail panel (only when product selected) --}}
            <section class="space-y-4">
                @if($this->productoSeleccionado)
                    @php $sel = $this->productoSeleccionado; @endphp
                    {{-- Product detail card --}}
                    <div class="card overflow-hidden">
                        <div class="bg-ocean-50/50 px-5 py-4 dark:bg-ocean-950/20">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-ocean-600 dark:text-ocean-400">
                                        {{ $sel->tipo === 'medicina' ? 'Medicina' : 'Equipo' }}
                                        @if($sel->medicamento)
                                            · Vinculado: {{ $sel->medicamento->nombre }}
                                        @endif
                                    </p>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $sel->nombre }}</h3>
                                </div>
                                <span class="text-2xl font-black tabular-nums {{ $sel->saldoActual() <= $sel->stock_minimo ? 'text-red-600' : 'text-ocean-700 dark:text-ocean-300' }}">
                                    {{ number_format($sel->saldoActual(), 1, ',', '.') }}
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-2 text-[10px] text-gray-500">
                                @if($sel->stock_minimo > 0)
                                    <span>Stock mínimo: {{ number_format($sel->stock_minimo, 0, ',', '.') }}</span>
                                @endif
                                @if($sel->fecha_caducidad)
                                    <span>Caduca: {{ $sel->fecha_caducidad->format('d/m/Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Movement form --}}
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Registrar movimiento</h3>
                        </div>
                        <form wire:submit.prevent="guardarMovimiento" class="space-y-3 p-5">
                            <div class="grid grid-cols-2 gap-2">
                                <select wire:model="tipo" class="select-sm w-full">
                                    <option value="ingreso">📥 Ingreso</option>
                                    <option value="salida">📤 Salida</option>
                                    <option value="ajuste">⚙️ Ajuste</option>
                                </select>
                                <input type="number" step="0.01" wire:model="cantidad"
                                    class="input-sm" placeholder="Cantidad" required>
                            </div>
                            <input type="date" wire:model="fecha_movimiento" class="input-sm w-full">
                            <input wire:model="responsable" placeholder="Responsable (opcional)" class="input-sm w-full">
                            <textarea wire:model="observacion" placeholder="Observación (opcional)" rows="2" class="input-sm w-full"></textarea>
                            <button type="submit" class="btn-primary w-full !rounded-lg !py-2 text-xs">
                                Registrar movimiento
                            </button>
                        </form>
                    </div>

                    {{-- Recent movements for selected product --}}
                    @if($sel->movimientos->isNotEmpty())
                    <div class="card overflow-hidden">
                        <div class="card-header">
                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">Últimos movimientos</h3>
                            <span class="chip bg-ocean-100 text-ocean-700 dark:bg-ocean-900/30 dark:text-ocean-400">{{ $sel->movimientos->count() }}</span>
                        </div>
                        <div class="scroll-thin max-h-64 overflow-y-auto">
                            <table class="min-w-full text-xs">
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                    @foreach($sel->movimientos as $mov)
                                    <tr>
                                        <td class="px-4 py-2 text-gray-500">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2">
                                            <span class="chip-sm {{ $mov->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700' : ($mov->tipo === 'salida' ? 'bg-red-100 text-red-700' : 'bg-sand-100 text-sand-700') }}">
                                                {{ $mov->tipo }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold tabular-nums {{ $mov->cantidad > 0 ? 'text-gray-900 dark:text-white' : 'text-red-600' }}">
                                            {{ $mov->cantidad > 0 ? '+' : '' }}{{ number_format($mov->cantidad, 1, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-gray-400">
                                            {{ $mov->origen === 'parte_diario' ? '🏥 Consulta' : '✋ Manual' }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                @else
                    {{-- Empty state --}}
                    <div class="card flex flex-col items-center justify-center py-16 text-center">
                        <div class="mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-ocean-50 text-ocean-400 dark:bg-ocean-950/20">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                        </div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Selecciona un producto</p>
                        <p class="mt-1 text-xs text-gray-400">Haz clic en un producto de la tabla para ver su detalle y registrar movimientos</p>
                    </div>
                @endif
            </section>
        </div>

        {{-- Recent all movements (bottom) --}}
        <section class="card overflow-hidden">
            <div class="card-header">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Movimientos recientes</h3>
                <span class="chip bg-ocean-100 text-ocean-700 dark:bg-ocean-900/30 dark:text-ocean-400">{{ $this->movimientos->count() }}</span>
            </div>
            <div class="scroll-thin overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="table-header-cell">Fecha</th>
                            <th class="table-header-cell">Producto</th>
                            <th class="table-header-cell">Tipo</th>
                            <th class="table-header-cell text-right">Cantidad</th>
                            <th class="table-header-cell">Origen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                        @foreach ($this->movimientos as $m)
                        <tr class="table-row">
                            <td class="table-cell text-gray-500">{{ $m->fecha_movimiento?->format('d/m/Y') }}</td>
                            <td class="table-cell font-medium">{{ $m->producto?->nombre ?? '—' }}</td>
                            <td class="table-cell">
                                <span class="chip-sm {{ $m->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400' : ($m->tipo === 'salida' ? 'bg-red-100 text-red-700 dark:bg-red-950/20 dark:text-red-400' : 'bg-sand-100 text-sand-700 dark:bg-sand-950/20 dark:text-sand-400') }}">
                                    {{ $m->tipo }}
                                </span>
                            </td>
                            <td class="table-cell text-right font-bold tabular-nums">{{ $m->cantidad }}</td>
                            <td class="table-cell text-gray-400">
                                {{ $m->origen === 'parte_diario' ? '🏥 Consulta' . ($m->parteDiario ? ' #'.$m->parteDiario->id : '') : '✋ Manual' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- ============================================================
    MODAL: Producto (crear/editar)
    ============================================================ --}}
    @if ($modalProductoAbierto)
        <div class="modal-overlay" x-data @click.self="$wire.cerrarModalProducto()" x-on:keydown.escape.window="$wire.cerrarModalProducto()">
            <div class="modal-panel-lg">
                <div class="modal-accent-ocean"></div>
                <section>
                    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                        <h2 class="text-base font-bold text-gray-950 dark:text-white">
                            {{ $editandoId ? 'Editar producto' : 'Nuevo producto' }}
                        </h2>
                        <button wire:click="cerrarModalProducto" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form wire:submit.prevent="guardarProducto" class="space-y-4 px-5 py-4">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Tipo</label>
                                <select wire:model="productoTipo" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                    <option value="medicina">Medicina</option>
                                    <option value="equipo">Equipo</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Stock mínimo</label>
                                <input type="number" step="0.01" wire:model="productoStockMinimo" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Nombre <span class="text-red-400">*</span></label>
                            <input wire:model="productoNombre" placeholder="Ej. Paracetamol 500mg" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white" autofocus>
                            @error('productoNombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($productoTipo === 'medicina')
                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                Vincular con catálogo clínico
                                <span class="font-normal text-gray-400">(descuento automático al dispensar)</span>
                            </label>
                            <select wire:model="productoMedicamentoId" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                <option value="">— Sin vincular —</option>
                                @foreach($this->medicamentosCatalog as $med)
                                    <option value="{{ $med->id }}">{{ $med->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Fecha de caducidad</label>
                            <input type="date" wire:model="productoFechaCaducidad" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Observaciones</label>
                            <textarea wire:model="productoObservaciones" rows="3" placeholder="Opcional" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" wire:model="productoActivo" class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500"> Activo
                        </label>

                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <button type="button" wire:click="cerrarModalProducto" class="btn-outline !rounded-xl !px-4 !py-2.5">Cancelar</button>
                            <button type="submit" class="btn-primary !rounded-xl !px-5 !py-2.5">Guardar</button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    @endif
</x-filament-panels::page>
