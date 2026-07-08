<x-filament-panels::page>
    <x-hero-card title="Medicinas y Insumos" subtitle="Gestiona el catálogo de productos, stock mínimo y fechas de caducidad" icon="heroicon-o-beaker" color="brand" />

    <div class="space-y-4">
        <section class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
            <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-palm-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-palm-700 ring-1 ring-palm-100 dark:bg-palm-950/40 dark:text-palm-300 dark:ring-palm-900">
                            Inventario medico
                        </span>
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            Medicinas e insumos
                        </span>
                    </div>
                </div>

                <div class="grid w-full gap-3 2xl:max-w-[560px]" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                    <div class="min-w-0 rounded-2xl border border-gray-100 bg-gray-50/80 p-3 text-center dark:border-gray-800 dark:bg-gray-950/50">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-gray-950 dark:text-white">{{ number_format($this->totalProductos, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-palm-100 bg-palm-50/70 p-3 text-center dark:border-palm-900 dark:bg-palm-950/20">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-palm-700 dark:text-palm-300">Medicinas</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-palm-700 dark:text-palm-300">{{ number_format($this->totalMedicinas, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-ocean-100 bg-ocean-50/70 p-3 text-center dark:border-ocean-900 dark:bg-ocean-950/20">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-ocean-700 dark:text-ocean-300">Insumos</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-ocean-700 dark:text-ocean-300">{{ number_format($this->totalInsumos, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-red-100 bg-red-50/70 p-3 text-center dark:border-red-900 dark:bg-red-950/20">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">Stock bajo</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-red-700 dark:text-red-300">{{ number_format($this->stockBajo, 0, ',', '.') }}</p>
                    </div>

                </div>
            </div>
        </section>

        <section class="min-w-0 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:px-5">
                <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-end 2xl:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">Productos registrados</h3>
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                {{ number_format($this->totalFiltrado, 0, ',', '.') }} resultados
                            </span>
                            @if ($this->porCaducar > 0)
                                <span class="inline-flex items-center rounded-full bg-sand-50 px-2.5 py-1 text-xs font-semibold text-sand-700 ring-1 ring-sand-100 dark:bg-sand-950/40 dark:text-sand-300 dark:ring-sand-900">
                                    {{ $this->porCaducar }} por caducar
                                </span>
                            @endif
                        </div>
                        <p class="mt-1 max-w-3xl text-sm leading-5 text-gray-500 dark:text-gray-400">Busca, edita o agrega productos del dispensario medico.</p>
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center 2xl:w-auto 2xl:justify-end" role="search" aria-label="Filtros de productos medicos">
                        <label class="sr-only" for="buscar-producto-medico">Buscar producto</label>
                        <div class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 shadow-sm transition focus-within:border-palm-500 focus-within:ring-1 focus-within:ring-palm-500/20 dark:border-gray-700 dark:bg-gray-950 sm:w-72 xl:w-80">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                            <input id="buscar-producto-medico" wire:model.live.debounce.300ms="buscar" placeholder="Buscar producto" class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500">
                        </div>

                        <select wire:model.live="tipoFiltro" class="h-10 rounded-xl border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm focus:border-palm-500 focus:ring-1 focus:ring-palm-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-white sm:w-32" style="color-scheme: light dark;">
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="todos">Todos</option>
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="medicina">Medicinas</option>
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="insumo">Insumos</option>
                        </select>

                        <select wire:model.live="estado" class="h-10 rounded-xl border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm focus:border-palm-500 focus:ring-1 focus:ring-palm-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-white sm:w-32" style="color-scheme: light dark;">
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="activos">Activos</option>
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="todos">Todos</option>
                            <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="inactivos">Inactivos</option>
                        </select>


                        <button wire:click="abrirModalProducto" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-palm-600 px-4 text-sm font-semibold text-white shadow-sm shadow-palm-600/20 transition hover:bg-palm-700 sm:w-auto">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                            Nuevo
                        </button>
                    </div>
                </div>
            </div>

            @if ($this->productos->isEmpty())
                <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-palm-50 text-palm-600 dark:bg-palm-950/40 dark:text-palm-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7 10 17l-5-5" /></svg>
                    </div>
                    <h4 class="mt-4 font-semibold text-gray-950 dark:text-white">Sin productos para los filtros actuales</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cambia los filtros o registra una nueva medicina/insumo.</p>
                </div>
            @else
                <div class="max-h-[650px] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 z-10 bg-gray-50/95 shadow-sm backdrop-blur dark:bg-gray-950/95">
                            <tr>
                                <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Producto</th>

                                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Stock</th>
                                <th class="hidden px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 lg:table-cell">Caducidad</th>
                                <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($this->productos as $p)
                                @php
                                    $saldo = $p->saldoActual();
                                    $stockBajo = $p->stock_minimo > 0 && $saldo <= $p->stock_minimo;
                                    $caducaPronto = $p->fecha_caducidad && $p->fecha_caducidad->lte(now()->addDays(60));
                                @endphp
                                <tr class="transition hover:bg-palm-50/40 dark:hover:bg-palm-950/10">
                                    <td class="px-4 py-3 sm:px-5">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $p->tipo === 'insumo' ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/40 dark:text-ocean-300' : 'bg-palm-50 text-palm-700 dark:bg-palm-950/40 dark:text-palm-300' }}">
                                                @if ($p->tipo === 'insumo')
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-9 4h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-3.5a2 2 0 0 0-1.6.8L12 7 11.1 5.8A2 2 0 0 0 9.5 5H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z" /></svg>
                                                @else
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25 14.25 19.5a5.25 5.25 0 0 1-7.42-7.42l5.25-5.25a5.25 5.25 0 0 1 7.42 7.42ZM8.25 15.75l7.5-7.5" /></svg>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-950 dark:text-white">{{ $p->nombre }}</p>
                                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $p->tipo === 'insumo' ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/40 dark:text-ocean-300' : 'bg-palm-50 text-palm-700 dark:bg-palm-950/40 dark:text-palm-300' }}">{{ $p->tipo === 'insumo' ? 'Insumo' : 'Medicina' }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">Min. {{ fmod($p->stock_minimo, 1) == 0 ? number_format($p->stock_minimo, 0, ',', '.') : number_format($p->stock_minimo, 1, ',', '.') }}</span>
                                                    @if (! $p->activo)
                                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">Inactivo</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <p class="font-bold {{ $stockBajo ? 'text-red-600 dark:text-red-400' : 'text-gray-950 dark:text-white' }}">{{ fmod($saldo, 1) == 0 ? number_format($saldo, 0, ',', '.') : number_format($saldo, 1, ',', '.') }}</p>
                                        @if ($stockBajo)
                                            <p class="text-[11px] font-semibold text-red-600 dark:text-red-400">Stock bajo</p>
                                        @endif
                                    </td>
                                    <td class="hidden px-4 py-3 lg:table-cell">
                                        @if ($p->fecha_caducidad)
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $caducaPronto ? 'bg-sand-50 text-sand-700 ring-1 ring-sand-100 dark:bg-sand-950/40 dark:text-sand-300 dark:ring-sand-900' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">
                                                {{ $p->fecha_caducidad->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">Sin caducidad</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right sm:px-5">
                                        <div class="inline-flex items-center gap-1 rounded-full bg-gray-50 p-1 dark:bg-gray-950">
                                            <button wire:click="editar({{ $p->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-palm-700 transition hover:bg-palm-50 dark:text-palm-300 dark:hover:bg-palm-950/50">Editar</button>
                                            <button wire:click="alternar({{ $p->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-gray-500 transition hover:bg-white hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-200">{{ $p->activo ? 'Desactivar' : 'Activar' }}</button>
                                            <button wire:click="eliminar({{ $p->id }})" wire:confirm="Eliminar este producto permanentemente?" class="rounded-full px-3 py-1 text-xs font-semibold text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/50">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if ($modalProductoAbierto)
            <div class="modal-overlay" wire:click.self="cerrarModalProducto" x-data
                 x-on:keydown.escape.window="$wire.cerrarModalProducto()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel !max-w-lg w-full mx-auto" wire:click.stop>
                    <div class="modal-accent" style="background: linear-gradient(90deg, #059669, #10b981, #34d399);"></div>

                    {{-- Header con icono --}}
                    <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-palm-400 to-palm-600 text-white shadow-md shadow-palm-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">{{ $editandoId ? 'Editar producto' : 'Nuevo producto' }}</h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Registra medicinas o insumos usados por el dispensario</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModalProducto"
                            class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            aria-label="Cerrar modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Formulario --}}
                    <form wire:submit.prevent="guardar" class="scroll-thin max-h-[65vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 space-y-4 dark:bg-gray-950/20">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipo</label>
                                <select wire:model="tipo" class="input">
                                    <option value="medicina">Medicina</option>
                                    <option value="insumo">Insumo</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock mínimo</label>
                                <input type="number" step="0.01" wire:model="stock_minimo" class="input">
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nombre <span class="text-red-400">*</span></label>
                            <input wire:model="nombre" placeholder="Ej. Paracetamol 500mg" class="input" autofocus>
                            @error('nombre') <p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p> @enderror
                        </div>

                        @if($tipo === 'medicina')
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                Vincular con catálogo clínico
                                <span class="font-normal text-gray-400">(para descuento automático de inventario)</span>
                            </label>
                            <select wire:model="medicamento_id" class="input">
                                <option value="">— Sin vincular —</option>
                                @foreach($this->medicamentosCatalog as $med)
                                    <option value="{{ $med->id }}">{{ $med->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fecha de caducidad</label>
                            <input type="date" wire:model="fecha_caducidad" class="input">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Observaciones</label>
                            <textarea wire:model="observaciones" rows="3" placeholder="Opcional" class="input"></textarea>
                        </div>

                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                            <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-palm-600 focus:ring-palm-500"> Disponible para usar
                        </label>
                    </form>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-3.5 sm:px-6 dark:border-gray-800">
                        <button type="button" wire:click="cerrarModalProducto"
                            class="btn-outline px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            Cancelar
                        </button>
                        <button type="button" wire:click="guardar"
                            class="btn-primary px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar producto</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
