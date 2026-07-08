<x-filament-panels::page>
    <x-hero-card title="Base Médica" subtitle="Administra los catálogos del dispensario: áreas, causas, diagnósticos y más" icon="heroicon-o-building-library" color="palm">
        <button type="button" wire:click="abrirModalNuevo"
            class="btn-primary text-sm !rounded-xl !px-5 !py-3">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo registro
        </button>
    </x-hero-card>

    <div class="space-y-4">
        <section class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
            <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-ocean-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/40 dark:text-ocean-300 dark:ring-ocean-900">
                            Base operativa
                        </span>
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $tipos[$tipo] ?? $tipo }}
                        </span>
                    </div>
                </div>

                <div class="grid w-full gap-3 2xl:max-w-[460px]" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    <div class="min-w-0 rounded-2xl border border-gray-100 bg-gray-50/80 p-3 text-center dark:border-gray-800 dark:bg-gray-950/50">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-gray-950 dark:text-white">{{ number_format($this->totalSeccion, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-palm-100 bg-palm-50/70 p-3 text-center dark:border-palm-900 dark:bg-palm-950/20">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-palm-700 dark:text-palm-300">Activos</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-palm-700 dark:text-palm-300">{{ number_format($this->activosSeccion, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-3 text-center dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ocultos</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-gray-700 dark:text-gray-300">{{ number_format($this->ocultosSeccion, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($tipos as $key => $label)
                    @php
                        $selected = $tipo === $key;
                        $total = $this->conteosPorTipo[$key] ?? 0;
                        $activos = $this->activosPorTipo[$key] ?? 0;
                        $percent = $total > 0 ? min(100, round(($activos / $total) * 100)) : 0;
                    @endphp
                    <button
                        type="button"
                        wire:click="seleccionarTipo('{{ $key }}')"
                        class="group rounded-2xl border p-3 text-left transition {{ $selected ? 'border-ocean-300 bg-ocean-50 shadow-sm ring-2 ring-ocean-100 dark:border-ocean-700 dark:bg-ocean-950/40 dark:ring-ocean-900' : 'border-gray-100 bg-gray-50/70 hover:border-ocean-200 hover:bg-white dark:border-gray-800 dark:bg-gray-950/40 dark:hover:border-ocean-900 dark:hover:bg-gray-900' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="line-clamp-1 text-sm font-semibold {{ $selected ? 'text-ocean-800 dark:text-ocean-200' : 'text-gray-800 dark:text-gray-200' }}">{{ $label }}</span>
                            <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[11px] font-bold {{ $selected ? 'text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-900 dark:text-ocean-200 dark:ring-ocean-800' : 'text-gray-500 ring-1 ring-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:ring-gray-800' }}">{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                                <div class="h-full rounded-full {{ $selected ? 'bg-ocean-500' : 'bg-gray-400 dark:bg-gray-600' }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        <p class="mt-1 text-[11px] {{ $selected ? 'text-ocean-600 dark:text-ocean-300' : 'text-gray-500 dark:text-gray-400' }}">{{ number_format($activos, 0, ',', '.') }} disponibles</p>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="min-w-0 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:px-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="text-lg font-semibold tracking-tight text-gray-950 dark:text-white">{{ $tipos[$tipo] ?? $tipo }}</h3>
                                <span class="inline-flex items-center rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 ring-1 ring-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                    {{ number_format($this->totalFiltrado, 0, ',', '.') }} resultados
                                </span>
                            </div>
                            <p class="mt-1 max-w-3xl text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $descripciones[$tipo] ?? 'Lista editable de la base médica.' }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center xl:w-auto xl:justify-end" role="search" aria-label="Filtros de registros">
                            <label class="sr-only" for="buscar-registro-base-medica">Buscar registro</label>
                            <div class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 shadow-sm transition focus-within:border-ocean-500 focus-within:ring-1 focus-within:ring-ocean-500/20 dark:border-gray-700 dark:bg-gray-950 sm:w-72 xl:w-80">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                                <input id="buscar-registro-base-medica" wire:model.live.debounce.300ms="buscar" placeholder="Buscar registro" class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500">
                            </div>

                            <label class="sr-only" for="estado-base-medica">Estado</label>
                            <select id="estado-base-medica" wire:model.live="estado" class="h-10 rounded-xl border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm focus:border-ocean-500 focus:ring-1 focus:ring-ocean-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-white sm:w-32" style="color-scheme: light dark;">
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="activos">Activos</option>
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="todos">Todos</option>
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="ocultos">Ocultos</option>
                            </select>

                        </div>
                    </div>
                </div>

                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-ocean-50 text-ocean-600 dark:bg-ocean-950/40 dark:text-ocean-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m6-6H6" /></svg>
                        </div>
                        <h4 class="mt-4 font-semibold text-gray-950 dark:text-white">Sin registros para los filtros actuales</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cambiá la búsqueda, el estado o agregá un nuevo valor.</p>
                    </div>
                @else
                    <div class="overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50/95 shadow-sm backdrop-blur dark:bg-gray-950/95">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Registro</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->items as $item)
                                    <tr class="transition hover:bg-ocean-50/50 dark:hover:bg-ocean-950/10">
                                        <td class="px-4 py-3 sm:px-5">
                                            <p class="font-medium text-gray-950 dark:text-white">{{ $item->nombre }}</p>
                                            <p class="mt-0.5 text-xs text-gray-400">ID {{ $item->id }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $item->activo ? 'bg-palm-50 text-palm-700 ring-palm-100 dark:bg-palm-950/40 dark:text-palm-300 dark:ring-palm-900' : 'bg-gray-100 text-gray-500 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' }}">
                                                {{ $item->activo ? 'Disponible' : 'Oculto' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right sm:px-5">
                                            <div class="inline-flex items-center gap-1 rounded-full bg-gray-50 p-1 dark:bg-gray-950">
                                                <button wire:click="editar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-ocean-700 transition hover:bg-ocean-50 dark:text-ocean-300 dark:hover:bg-ocean-950/50">Editar</button>
                                                <button wire:click="alternar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-gray-500 transition hover:bg-white hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-200">{{ $item->activo ? 'Ocultar' : 'Mostrar' }}</button>
                                                <button wire:click="solicitarEliminar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">Eliminar</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- PAGINATION --}}
                    @php
                        $totalP = $this->totalFiltrado;
                        $paginas = $this->totalPaginas;
                        $actual = $this->pagina;
                        $desde = ($actual - 1) * $this->porPagina + 1;
                        $hasta = min($actual * $this->porPagina, $totalP);
                    @endphp
                    @if($paginas > 1)
                        <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-5 py-3 dark:border-gray-800">
                            <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500">{{ $desde }}–{{ $hasta }} de {{ $totalP }}</p>
                            <div class="flex items-center gap-1">
                                <button wire:click="irPagina({{ $actual - 1 }})" @if($actual <= 1) disabled @endif
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7"/></svg>
                                </button>
                                @php
                                    $inicio = max(1, $actual - 2);
                                    $fin = min($paginas, $actual + 2);
                                    if ($fin - $inicio < 4) {
                                        if ($inicio === 1) $fin = min($paginas, 5);
                                        else $inicio = max(1, $paginas - 4);
                                    }
                                @endphp
                                @if($inicio > 1)
                                    <button wire:click="irPagina(1)" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200">1</button>
                                    @if($inicio > 2)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">&hellip;</span>@endif
                                @endif
                                @for($i = $inicio; $i <= $fin; $i++)
                                    <button wire:click="irPagina({{ $i }})"
                                        class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition {{ $i === $actual ? 'bg-ocean-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800' }}">
                                        {{ $i }}
                                    </button>
                                @endfor
                                @if($fin < $paginas)
                                    @if($fin < $paginas - 1)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">&hellip;</span>@endif
                                    <button wire:click="irPagina({{ $paginas }})" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200">{{ $paginas }}</button>
                                @endif
                                <button wire:click="irPagina({{ $actual + 1 }})" @if($actual >= $paginas) disabled @endif
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
        </section>

        {{-- MODAL: CREAR / EDITAR — ESTANDARIZADO --}}
        @if ($modalAbierto)
            <div class="modal-overlay" wire:click.self="cerrarModal" x-data
                 x-on:keydown.escape.window="$wire.cerrarModal()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel !max-w-md w-full mx-auto" wire:click.stop>
                    <div class="modal-accent-ocean"></div>

                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ocean-400 to-ocean-600 text-white shadow-md shadow-ocean-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">{{ $editandoId ? 'Editar registro' : 'Nuevo registro' }}</h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $editandoId ? 'Actualizá el valor seleccionado.' : 'Agregá un valor a ' . ($tipos[$tipo] ?? $tipo) . '.' }}</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModal"
                            class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            aria-label="Cerrar modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Formulario --}}
                    <form wire:submit.prevent="guardar" class="scroll-thin max-h-[65vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 space-y-4 dark:bg-gray-950/20">
                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sección</label>
                            <select wire:model.live="tipo" class="input">
                                @foreach ($tipos as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nombre <span class="text-red-400">*</span></label>
                            <input wire:model="nombre" placeholder="Ej. NUEVA ÁREA" class="input" autofocus>
                            @error('nombre') <p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                            <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-ocean-600 focus:ring-ocean-500"> Disponible para usar
                        </label>
                    </form>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-5 py-3.5 sm:px-6 dark:border-gray-800">
                        <button type="button" wire:click="cerrarModal"
                            class="btn-outline px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            Cancelar
                        </button>
                        <button type="button" wire:click="guardar"
                            class="btn-primary px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Guardar registro</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- MODAL: ELIMINAR — ESTANDARIZADO --}}
        @if ($modalEliminarAbierto)
            <div class="modal-overlay" wire:click.self="cancelarEliminar" x-data
                 x-on:keydown.escape.window="$wire.cancelarEliminar()"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel w-[calc(100%-1rem)] sm:max-w-sm mx-auto" wire:click.stop>
                    <div class="modal-accent" style="background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);"></div>
                    <div class="bg-gradient-to-br from-red-50 via-white to-white px-5 py-5 text-center dark:from-red-950/30 dark:via-gray-900 dark:to-gray-900">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 ring-4 ring-red-50 dark:bg-red-950/30 dark:ring-red-950/10">
                            <svg class="h-7 w-7 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" /></svg>
                        </div>
                        <h4 class="mt-4 text-base font-bold text-gray-900 dark:text-white">Eliminar registro</h4>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
                            @if ($this->registroAEliminar)
                                Vas a eliminar <strong class="font-semibold text-gray-900 dark:text-white">{{ $this->registroAEliminar->nombre }}</strong> de {{ $tipos[$tipo] ?? 'esta sección' }}.
                            @else
                                Vas a eliminar este registro.
                            @endif
                            Esta acción no se puede deshacer.
                        </p>
                    </div>
                    <div class="flex border-t border-gray-100 dark:border-gray-800">
                        <button wire:click="cancelarEliminar"
                            class="flex-1 border-r border-gray-100 px-4 py-3.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-950">
                            Cancelar
                        </button>
                        <button wire:click="confirmarEliminar"
                            class="flex-1 px-4 py-3.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20">
                            Eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
