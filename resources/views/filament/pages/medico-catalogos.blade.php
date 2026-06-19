<x-filament-panels::page>
    <div class="space-y-4">
        <section class="rounded-3xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 sm:p-5">
            <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex rounded-full bg-sky-50 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-sky-700 ring-1 ring-sky-100 dark:bg-sky-950/40 dark:text-sky-300 dark:ring-sky-900">
                            Base operativa
                        </span>
                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $tipos[$tipo] ?? $tipo }}
                        </span>
                    </div>
                    <h2 class="mt-3 text-xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-2xl">Listas maestras del dispensario</h2>
                    <p class="mt-1 max-w-4xl text-sm leading-6 text-gray-600 dark:text-gray-300">
                        Administra los datos base que alimentan partes diarios, certificados, diagnosticos e inventario. Todo se guarda en la base de datos del panel.
                    </p>
                </div>

                <div class="grid w-full gap-3 2xl:max-w-[460px]" style="grid-template-columns: repeat(3, minmax(0, 1fr));">
                    <div class="min-w-0 rounded-2xl border border-gray-100 bg-gray-50/80 p-3 text-center dark:border-gray-800 dark:bg-gray-950/50">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-gray-950 dark:text-white">{{ number_format($this->totalSeccion, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-3 text-center dark:border-emerald-900 dark:bg-emerald-950/20">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Activos</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-emerald-700 dark:text-emerald-300">{{ number_format($this->activosSeccion, 0, ',', '.') }}</p>
                    </div>
                    <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-3 text-center dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="truncate text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ocultos</p>
                        <p class="mt-1 truncate text-2xl font-black tracking-tight text-gray-700 dark:text-gray-300">{{ number_format($this->ocultosSeccion, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-7">
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
                        class="group rounded-2xl border p-3 text-left transition {{ $selected ? 'border-sky-300 bg-sky-50 shadow-sm ring-2 ring-sky-100 dark:border-sky-700 dark:bg-sky-950/40 dark:ring-sky-900' : 'border-gray-100 bg-gray-50/70 hover:border-sky-200 hover:bg-white dark:border-gray-800 dark:bg-gray-950/40 dark:hover:border-sky-900 dark:hover:bg-gray-900' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span class="line-clamp-1 text-sm font-semibold {{ $selected ? 'text-sky-800 dark:text-sky-200' : 'text-gray-800 dark:text-gray-200' }}">{{ $label }}</span>
                            <span class="shrink-0 rounded-full bg-white px-2 py-0.5 text-[11px] font-bold {{ $selected ? 'text-sky-700 ring-1 ring-sky-100 dark:bg-sky-900 dark:text-sky-200 dark:ring-sky-800' : 'text-gray-500 ring-1 ring-gray-100 dark:bg-gray-900 dark:text-gray-400 dark:ring-gray-800' }}">{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2">
                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                                <div class="h-full rounded-full {{ $selected ? 'bg-sky-500' : 'bg-gray-400 dark:bg-gray-600' }}" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                        <p class="mt-1 text-[11px] {{ $selected ? 'text-sky-600 dark:text-sky-300' : 'text-gray-500 dark:text-gray-400' }}">{{ number_format($activos, 0, ',', '.') }} disponibles</p>
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
                                @if ($this->totalFiltrado > 120)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-100 dark:bg-amber-950/40 dark:text-amber-300 dark:ring-amber-900">Mostrando 120</span>
                                @endif
                            </div>
                            <p class="mt-1 max-w-3xl text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $descripciones[$tipo] ?? 'Lista editable de la base medica.' }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center xl:w-auto xl:justify-end" role="search" aria-label="Filtros de registros">
                            <label class="sr-only" for="buscar-registro-base-medica">Buscar registro</label>
                            <div class="flex h-10 w-full items-center gap-2 rounded-xl border border-gray-300 bg-white px-3 shadow-sm transition focus-within:border-sky-500 focus-within:ring-1 focus-within:ring-sky-500/20 dark:border-gray-700 dark:bg-gray-950 sm:w-72 xl:w-80">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z" /></svg>
                                <input id="buscar-registro-base-medica" wire:model.live.debounce.300ms="buscar" placeholder="Buscar registro" class="h-full min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white dark:placeholder:text-gray-500">
                            </div>

                            <label class="sr-only" for="estado-base-medica">Estado</label>
                            <select id="estado-base-medica" wire:model.live="estado" class="h-10 rounded-xl border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-sm focus:border-sky-500 focus:ring-1 focus:ring-sky-500/20 dark:border-gray-700 dark:bg-gray-950 dark:text-white sm:w-32" style="color-scheme: light dark;">
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="activos">Activos</option>
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="todos">Todos</option>
                                <option class="bg-white text-gray-900 dark:bg-gray-950 dark:text-white" value="ocultos">Ocultos</option>
                            </select>

                            <button wire:click="abrirModalNuevo" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 sm:w-auto">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                                <span>Nuevo</span>
                            </button>
                        </div>
                    </div>
                </div>

                @if ($this->items->isEmpty())
                    <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6v12m6-6H6" /></svg>
                        </div>
                        <h4 class="mt-4 font-semibold text-gray-950 dark:text-white">Sin registros para los filtros actuales</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cambia la busqueda, el estado o agrega un nuevo valor.</p>
                    </div>
                @else
                    <div class="max-h-[640px] overflow-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 z-10 bg-gray-50/95 shadow-sm backdrop-blur dark:bg-gray-950/95">
                                <tr>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Registro</th>
                                    <th class="hidden px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 md:table-cell">Descripcion</th>
                                    <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Estado</th>
                                    <th class="px-4 py-3 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 sm:px-5">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach ($this->items as $item)
                                    <tr class="transition hover:bg-sky-50/50 dark:hover:bg-sky-950/10">
                                        <td class="px-4 py-3 sm:px-5">
                                            <p class="font-medium text-gray-950 dark:text-white">{{ $item->nombre }}</p>
                                            <p class="mt-0.5 text-xs text-gray-400">ID {{ $item->id }}</p>
                                            @if ($item->descripcion)
                                                <p class="mt-1 line-clamp-2 text-xs text-gray-500 md:hidden">{{ $item->descripcion }}</p>
                                            @endif
                                        </td>
                                        <td class="hidden max-w-md px-4 py-3 text-gray-600 dark:text-gray-300 md:table-cell">
                                            @if ($item->descripcion)
                                                <span class="line-clamp-2">{{ $item->descripcion }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $item->activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900' : 'bg-gray-100 text-gray-500 ring-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:ring-gray-700' }}">
                                                {{ $item->activo ? 'Disponible' : 'Oculto' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right sm:px-5">
                                            <div class="inline-flex items-center gap-1 rounded-full bg-gray-50 p-1 dark:bg-gray-950">
                                                <button wire:click="editar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-sky-700 transition hover:bg-sky-50 dark:text-sky-300 dark:hover:bg-sky-950/50">Editar</button>
                                                <button wire:click="alternar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-gray-500 transition hover:bg-white hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-900 dark:hover:text-gray-200">{{ $item->activo ? 'Ocultar' : 'Mostrar' }}</button>
                                                <button wire:click="solicitarEliminar({{ $item->id }})" class="rounded-full px-3 py-1 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">Eliminar</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
        </section>

        @if ($modalAbierto)
            <div
                class="flex items-center justify-center p-4 sm:p-6"
                style="position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.58); backdrop-filter: blur(6px);"
                wire:click.self="cerrarModal"
            >
                <section class="w-full overflow-hidden rounded-2xl border border-white/70 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.35)] ring-1 ring-gray-950/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10" style="max-width: 460px;">
                    <div class="bg-gradient-to-br from-sky-50 via-white to-white px-5 py-4 dark:from-sky-950/30 dark:via-gray-900 dark:to-gray-900">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-sky-600 text-white shadow-sm shadow-sky-600/25">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" /></svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-base font-semibold text-gray-950 dark:text-white">{{ $editandoId ? 'Editar registro' : 'Nuevo registro' }}</h3>
                                    <p class="mt-1 text-sm leading-5 text-gray-500 dark:text-gray-400">{{ $editandoId ? 'Actualiza el valor seleccionado.' : 'Agrega un valor a ' . ($tipos[$tipo] ?? $tipo) . '.' }}</p>
                                </div>
                            </div>
                            <button type="button" wire:click="cerrarModal" class="rounded-full p-2 text-gray-400 transition hover:bg-white hover:text-gray-700 hover:shadow-sm dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 18 6M6 6l12 12" /></svg>
                            </button>
                        </div>
                    </div>

                    <form wire:submit.prevent="guardar" class="space-y-4 px-5 py-4">
                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Seccion</label>
                            <select wire:model.live="tipo" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                @foreach ($tipos as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Nombre</label>
                            <input wire:model="nombre" placeholder="Ej. NUEVA AREA" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white" autofocus>
                            @error('nombre') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-600 dark:text-gray-300">Descripcion interna</label>
                            <textarea wire:model="descripcion" rows="3" placeholder="Opcional" class="mt-1 block w-full rounded-xl border-gray-300 bg-white text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <input type="checkbox" wire:model="activo" class="rounded border-gray-300 text-sky-600 focus:ring-sky-500"> Disponible para usar
                        </label>

                        <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="cerrarModal" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</button>
                            <button class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-sky-600/20 transition hover:bg-sky-700">Guardar registro</button>
                        </div>
                    </form>
                </section>
            </div>
        @endif

        @if ($modalEliminarAbierto)
            <div
                class="flex items-center justify-center p-4 sm:p-6"
                style="position: fixed; inset: 0; z-index: 99999; background: rgba(15, 23, 42, 0.58); backdrop-filter: blur(6px);"
                wire:click.self="cancelarEliminar"
            >
                <section class="w-full overflow-hidden rounded-2xl border border-white/70 bg-white shadow-[0_24px_80px_rgba(15,23,42,0.35)] ring-1 ring-gray-950/5 dark:border-gray-800 dark:bg-gray-900 dark:ring-white/10" style="max-width: 400px;">
                    <div class="px-5 py-5">
                        <div class="flex gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-50 text-red-600 ring-1 ring-red-100 dark:bg-red-950/30 dark:text-red-400 dark:ring-red-900/50">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Eliminar registro</h3>
                                <p class="mt-1 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                    @if ($this->registroAEliminar)
                                        Vas a eliminar <strong class="font-semibold text-gray-900 dark:text-white">{{ $this->registroAEliminar->nombre }}</strong> de {{ $tipos[$this->registroAEliminar->tipo] ?? 'esta seccion' }}.
                                    @else
                                        Vas a eliminar este registro.
                                    @endif
                                    Esta accion no se puede deshacer.
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                            <button type="button" wire:click="cancelarEliminar" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">Cancelar</button>
                            <button
                                type="button"
                                wire:click="confirmarEliminar"
                                class="rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition"
                                style="background-color: #dc2626; color: #ffffff; border: 1px solid #dc2626; min-width: 120px;"
                                onmouseover="this.style.backgroundColor='#b91c1c'; this.style.borderColor='#b91c1c'"
                                onmouseout="this.style.backgroundColor='#dc2626'; this.style.borderColor='#dc2626'"
                            >Eliminar</button>
                        </div>
                    </div>
                </section>
            </div>
        @endif
    </div>
</x-filament-panels::page>
