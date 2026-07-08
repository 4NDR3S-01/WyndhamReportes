<x-filament-panels::page>
    <x-hero-card title="Partes Diarios" subtitle="Registra y consulta las atenciones médicas diarias" icon="heroicon-o-clipboard-document-list" color="ocean">
        <button type="button" wire:click="abrirModalAtencion"
            wire:loading.attr="disabled" wire:target="abrirModalAtencion"
            class="btn-primary text-sm !rounded-xl !px-5 !py-3">
            <svg wire:loading.remove wire:target="abrirModalAtencion" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <svg wire:loading wire:target="abrirModalAtencion" class="h-5 w-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span wire:loading.remove wire:target="abrirModalAtencion">Nueva Atenci&oacute;n</span>
            <span wire:loading wire:target="abrirModalAtencion" style="display:none">Abriendo…</span>
        </button>
    </x-hero-card>

    <div class="page-enter space-y-5">

        {{-- ============================================================
        STATS STRIP — Reusable stat-card components
        ============================================================ --}}
        @php $hoy = $this->estadisticasHoy; @endphp
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <x-stat-card title="{{ $mostrarSoloHoy ? 'Atenciones Hoy' : 'Atenciones' }}" :value="$hoy['total']" icon="heroicon-o-document-text" color="ocean" />
            <x-stat-card title="Huéspedes" :value="$hoy['huespedes']" icon="heroicon-o-building-office-2" color="sand" />
            <x-stat-card title="Colaboradores" :value="$hoy['colabs']" icon="heroicon-o-users" color="palm" />
            <x-stat-card title="Con Certificado" :value="$hoy['conCert']" icon="heroicon-o-shield-check" color="coral" />
            <x-stat-card title="Áreas Activas" :value="$hoy['areas']" icon="heroicon-o-map-pin" color="tide" />
        </div>

        {{-- ============================================================
        ATTENTION LIST (full width)
        ============================================================ --}}
        <section class="card overflow-hidden">
            {{-- Header con título + conteo + toggle de vista --}}
            <div class="card-header">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Atenciones registradas</h3>
                    <span class="chip bg-ocean-100 text-ocean-700 dark:bg-ocean-900/30 dark:text-ocean-400">
                        {{ $this->totalPartes }}
                    </span>
                </div>
                <div class="pill-group" role="group" aria-label="Modo de vista">
                    <button type="button" wire:click="cambiarModo('hoy')"
                        class="pill {{ $mostrarSoloHoy ? 'pill-active' : 'pill-inactive' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Hoy</span>
                    </button>
                    <button type="button" wire:click="cambiarModo('rango')"
                        class="pill {{ ! $mostrarSoloHoy ? 'pill-active' : 'pill-inactive' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zM4 8h16v8H4V8z"/>
                        </svg>
                        <span>Rango</span>
                    </button>
                </div>
            </div>

            {{-- Filters --}}
            <div class="border-b border-gray-50 px-5 pb-3 dark:border-gray-800">
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$mostrarSoloHoy)
                        <input type="date" wire:model.live="desde" class="filter-input">
                        <span class="text-xs text-gray-400 dark:text-gray-500">a</span>
                        <input type="date" wire:model.live="hasta" class="filter-input">
                    @endif
                    <div class="filter-search">
                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                        <input wire:model.live.debounce.350ms="buscar" placeholder="Buscar paciente, diagnóstico...">
                    </div>
                    <select wire:model.live="areaFiltroId" class="filter-select-sm">
                        <option value="">Todas las áreas</option>
                        @foreach ($this->areaCatalog as $a)<option value="{{ $a->id }}">{{ $a->nombre }}</option>@endforeach
                    </select>
                    <select wire:model.live="causaFiltroId" class="filter-select-sm">
                        <option value="">Todas las causas</option>
                        @foreach ($this->causaCatalog as $c)<option value="{{ $c->id }}">{{ $c->nombre }}</option>@endforeach
                    </select>
                    <select wire:model.live="tipoPacienteFiltro" class="filter-select-sm">
                        <option value="">Todos los tipos</option>
                        <option value="colaborador">Colaborador</option>
                        <option value="huesped">Huésped</option>
                    </select>
                    <select wire:model.live="estadoFiltro" class="filter-select-sm">
                        <option value="">Cualquier certificado</option>
                        <option value="con_certificado">Con certificado</option>
                        <option value="sin_certificado">Sin certificado</option>
                    </select>
                </div>

                {{-- Active filter badges --}}
                @if($this->filtrosActivos > 0)
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @if($this->buscar !== '')
                            <span class="chip-sm bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400">
                                "{{ $this->buscar }}"
                                <button wire:click="$set('buscar', '')" class="ml-0.5 hover:text-ocean-900 dark:hover:text-ocean-200" aria-label="Quitar filtro búsqueda">&times;</button>
                            </span>
                        @endif
                        @if($this->areaFiltroId)
                            <span class="chip-sm bg-tide-50 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400">
                                {{ $this->areaCatalog->firstWhere('id', $this->areaFiltroId)?->nombre ?? 'Área' }}
                                <button wire:click="$set('areaFiltroId', null)" class="ml-0.5 hover:text-tide-900" aria-label="Quitar filtro área">&times;</button>
                            </span>
                        @endif
                        @if($this->causaFiltroId)
                            <span class="chip-sm bg-sand-50 text-sand-700 dark:bg-sand-950/20 dark:text-sand-400">
                                {{ $this->causaCatalog->firstWhere('id', $this->causaFiltroId)?->nombre ?? 'Causa' }}
                                <button wire:click="$set('causaFiltroId', null)" class="ml-0.5 hover:text-sand-900" aria-label="Quitar filtro causa">&times;</button>
                            </span>
                        @endif
                        @if($this->tipoPacienteFiltro)
                            <span class="chip-sm bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400">
                                {{ $this->tipoPacienteFiltro }}
                                <button wire:click="$set('tipoPacienteFiltro', null)" class="ml-0.5 hover:text-palm-900" aria-label="Quitar filtro tipo">&times;</button>
                            </span>
                        @endif
                        @if($this->estadoFiltro)
                            <span class="chip-sm bg-coral-50 text-coral-700 dark:bg-coral-950/20 dark:text-coral-400">
                                {{ $this->estadoFiltro === 'con_certificado' ? 'Con certificado' : 'Sin certificado' }}
                                <button wire:click="$set('estadoFiltro', null)" class="ml-0.5 hover:text-coral-900" aria-label="Quitar filtro certificado">&times;</button>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

                {{-- Table with loading state --}}
                <div class="relative" wire:loading.class="opacity-50 pointer-events-none" wire:target="buscar,desde,hasta,areaFiltroId,causaFiltroId,tipoPacienteFiltro,estadoFiltro,mostrarSoloHoy,pagina">
                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="buscar,desde,hasta,areaFiltroId,causaFiltroId,tipoPacienteFiltro,estadoFiltro,mostrarSoloHoy,pagina"
                        class="absolute inset-0 z-20 flex items-start justify-center pt-16 bg-white/40 dark:bg-gray-900/40 backdrop-blur-[2px]">
                        <div class="flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-gray-500 shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-800 dark:text-gray-400 dark:ring-white/10">
                            <svg class="h-4 w-4 animate-spin text-ocean-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Cargando...
                        </div>
                    </div>

                    @if($this->partes->isNotEmpty())
                        <div class="scroll-thin max-h-[550px] overflow-auto">
                            <table class="min-w-full">
                                <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur dark:bg-gray-950/95">
                                    <tr>
                                        <th class="table-header-cell w-[32%]">Paciente</th>
                                        <th class="table-header-cell w-[30%]">Atención</th>
                                        <th class="table-header-cell w-[26%]">Medicación</th>
                                        <th class="table-header-cell w-[12%]"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                                    @foreach ($this->partes as $p)
                                        <tr class="table-row transition-all duration-200
                                            {{ $editandoId === $p->id ? 'bg-ocean-50/60 ring-1 ring-inset ring-ocean-200 dark:bg-ocean-950/20 dark:ring-ocean-800/50' : '' }}">
                                            {{-- Paciente --}}
                                            <td class="table-cell">
                                                <div class="flex items-start gap-2.5">
                                                    <span class="mt-1 h-2 w-2 shrink-0 rounded-full
                                                        {{ $p->tipo_paciente === 'huesped' ? 'bg-sand-400 shadow-sm shadow-sand-400/30' : 'bg-palm-400 shadow-sm shadow-palm-400/30' }}"></span>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-[13px] font-semibold text-gray-900 dark:text-white">
                                                            {{ $p->nombres }}
                                                            @if($editandoId === $p->id)
                                                                <span class="ml-1.5 inline-flex items-center rounded-full bg-ocean-100 px-1.5 py-0.5 text-[9px] font-bold text-ocean-700 dark:bg-ocean-900/40 dark:text-ocean-300">editando</span>
                                                            @endif
                                                        </p>
                                                        <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                                            <span class="font-medium text-gray-500 dark:text-gray-400">{{ $p->fecha instanceof \Carbon\Carbon ? $p->fecha->format('d/m/Y') : $p->fecha }}</span>
                                                            @if($p->area)<span>·</span><span>{{ $p->area->nombre }}</span>@endif
                                                            @if($p->cargo)<span>·</span><span class="truncate">{{ $p->cargo->nombre }}</span>@endif
                                                            @if($p->turno)<span>·</span><span class="capitalize">{{ $p->turno }}</span>@endif
                                                        </div>
                                                        @if($p->tipo_paciente === 'huesped' && $p->habitacion)
                                                            <p class="mt-0.5 text-[11px] font-medium text-sand-600 dark:text-sand-400">
                                                                Hab. {{ $p->habitacion }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            {{-- Atención --}}
                                            <td class="table-cell">
                                                <p class="truncate text-[13px] font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $p->causa?->nombre ?: '—' }}
                                                </p>
                                                @if($p->diagnostico)
                                                    <p class="mt-0.5 line-clamp-1 text-[11px] leading-relaxed text-gray-400 dark:text-gray-500">
                                                        {{ $p->diagnostico->nombre }}
                                                    </p>
                                                @endif
                                                @if($p->entidadCertificado)
                                                    <span class="chip mt-1.5 bg-coral-50 text-coral-700 dark:bg-coral-950/30 dark:text-coral-300">
                                                        {{ $p->entidadCertificado->nombre }}
                                                    </span>
                                                @endif
                                            </td>
                                            {{-- Medicación --}}
                                            <td class="table-cell">
                                                <div class="flex flex-wrap gap-1">
                                                    @forelse ($p->medicamentos as $m)
                                                        <span class="chip bg-tide-50 text-tide-700 dark:bg-tide-950/30 dark:text-tide-300">
                                                            {{ $m->medicamento?->nombre ?? $m->nombre_original }}
                                                            <span class="font-bold">&times;{{ $m->cantidad }}</span>
                                                        </span>
                                                    @empty
                                                        <span class="text-[11px] text-gray-300 dark:text-gray-600">—</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            {{-- Acciones — always visible --}}
                                            <td class="table-cell">
                                                <div class="flex items-center justify-end gap-0.5">
                                                    <button wire:click="editar({{ $p->id }})"
                                                        class="rounded-lg p-1.5 text-ocean-600 transition hover:bg-ocean-50 dark:text-ocean-400 dark:hover:bg-ocean-950/20"
                                                        aria-label="Editar atención de {{ $p->nombres }}" title="Editar">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    </button>
                                                    <button wire:click="solicitarEliminar({{ $p->id }})"
                                                        class="rounded-lg p-1.5 text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20"
                                                        aria-label="Eliminar atención de {{ $p->nombres }}" title="Eliminar">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @php
                            $totalP = $this->totalPartes;
                            $paginas = $this->totalPaginas;
                            $actual = $this->pagina;
                            $desde = ($actual - 1) * $this->porPagina + 1;
                            $hasta = min($actual * $this->porPagina, $totalP);
                        @endphp
                        @if($paginas > 1)
                            <div class="flex items-center justify-between gap-3 border-t border-gray-50 px-5 py-3 dark:border-gray-800">
                                <p class="text-[11px] font-medium text-gray-400 dark:text-gray-500">
                                    {{ $desde }}–{{ $hasta }} de {{ $totalP }}
                                </p>
                                <div class="flex items-center gap-1">
                                    <button wire:click="irPagina({{ $actual - 1 }})"
                                        @if($actual <= 1) disabled @endif
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200"
                                        aria-label="Página anterior">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7 7-7-7"/></svg>
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
                                        @if($inicio > 2)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">…</span>@endif
                                    @endif
                                    @for($i = $inicio; $i <= $fin; $i++)
                                        <button wire:click="irPagina({{ $i }})"
                                            class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition
                                            {{ $i === $actual ? 'bg-ocean-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800' }}">
                                            {{ $i }}
                                        </button>
                                    @endfor
                                    @if($fin < $paginas)
                                        @if($fin < $paginas - 1)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">…</span>@endif
                                        <button wire:click="irPagina({{ $paginas }})" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200">{{ $paginas }}</button>
                                    @endif
                                    <button wire:click="irPagina({{ $actual + 1 }})"
                                        @if($actual >= $paginas) disabled @endif
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200"
                                        aria-label="Página siguiente">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="flex flex-col items-center justify-center py-24 text-center">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-300 dark:bg-gray-800 dark:text-gray-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <h4 class="mt-5 text-sm font-bold text-gray-700 dark:text-gray-200">Sin atenciones registradas</h4>
                            <p class="mt-1.5 max-w-xs text-xs leading-relaxed text-gray-400 dark:text-gray-500">
                                @if($mostrarSoloHoy)
                                    No hay atenciones para hoy.<br>Desactivá «Solo hoy» para ver más o registrá una nueva.
                                @else
                                    No se encontraron resultados con los filtros actuales.<br>Probá ajustando los criterios de búsqueda.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </section>

        {{-- ============================================================
        NEW ATTENTION MODAL — REDESIGNED
        ============================================================ --}}
        @if($modalAtencionAbierto)
            <div class="modal-overlay" wire:click.self="cerrarModalAtencion" x-data
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel !max-w-5xl w-full mx-auto sm:px-0" @click.stop
                     x-on:keydown.escape.window="$wire.cerrarModalAtencion()">
                    {{-- Accent gradient bar --}}
                    <div class="{{ $editandoId ? 'modal-accent-sand' : 'modal-accent-ocean' }}"></div>

                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                                {{ $editandoId
                                    ? 'bg-sand-100 text-sand-600 ring-1 ring-sand-200 dark:bg-sand-950/30 dark:text-sand-400 dark:ring-sand-800'
                                    : 'bg-gradient-to-br from-ocean-400 to-ocean-600 text-white shadow-md shadow-ocean-500/20' }}">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $editandoId ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 4v16m8-8H4' }}"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-base font-bold text-gray-900 dark:text-white">
                                    {{ $editandoId ? 'Editando atención #' . $editandoId : 'Nueva atención médica' }}
                                </h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500">
                                    {{ $editandoId ? 'Modifique los campos necesarios y guarde los cambios' : 'Complete el formulario para registrar la atención' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModalAtencion"
                            class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            aria-label="Cerrar modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form body — scrollable --}}
                    <form wire:submit.prevent="guardar"
                        class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-5 sm:p-7 space-y-6 dark:bg-gray-950/20">

                        {{-- 1. INFORMACIÓN DEL PACIENTE --}}
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-5 py-3.5 rounded-t-2xl dark:border-gray-800 dark:bg-gray-900/50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-ocean-100 text-ocean-600 dark:bg-ocean-900/30 dark:text-ocean-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">1. Información del Paciente</h4>
                                    <p class="text-xs text-gray-500">Datos personales, ubicación y ficha médica</p>
                                </div>
                            </div>
                            
                            <div class="p-5 sm:p-6 space-y-5">
                                {{-- Fecha + Buscar paciente (Nombre Completo) --}}
                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div class="sm:col-span-1">
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Fecha <span class="text-red-400">*</span>
                                        </label>
                                        <input type="date" wire:model="fecha" class="input">
                                    </div>
                                    <div class="sm:col-span-2 relative" x-data="{
                                        abierto: false,
                                        indiceResaltado: -1,
                                        nombres: @entangle('nombres'),
                                        buscar: @entangle('buscarPaciente').live,
                                        seleccionadoId: @entangle('pacienteId'),
                                        init() {
                                            this.$watch('nombres', v => {
                                                if (v && v.length >= 2 && !this.seleccionadoId) {
                                                    this.buscar = v;
                                                    this.abierto = true;
                                                    this.indiceResaltado = -1;
                                                } else if (!v) {
                                                    this.buscar = '';
                                                    this.abierto = false;
                                                }
                                            });
                                            this.$watch('seleccionadoId', v => { 
                                                if (v) { 
                                                    this.abierto = false; 
                                                    this.indiceResaltado = -1;
                                                } 
                                            });
                                        },
                                        cerrar() { this.abierto = false; this.indiceResaltado = -1; },
                                        limpiar() {
                                            this.nombres = '';
                                            this.buscar = '';
                                            this.seleccionadoId = null;
                                            this.abierto = false;
                                        },
                                        navegar(e) {
                                            const items = this.$refs.dropdown?.querySelectorAll('[data-paciente]') || [];
                                            if (e.key === 'ArrowDown') { e.preventDefault(); this.indiceResaltado = Math.min(this.indiceResaltado + 1, items.length - 1); items[this.indiceResaltado]?.scrollIntoView({ block: 'nearest' }); }
                                            if (e.key === 'ArrowUp') { e.preventDefault(); this.indiceResaltado = Math.max(this.indiceResaltado - 1, 0); items[this.indiceResaltado]?.scrollIntoView({ block: 'nearest' }); }
                                            if (e.key === 'Enter' && this.indiceResaltado >= 0 && items[this.indiceResaltado]) { e.preventDefault(); items[this.indiceResaltado].click(); }
                                            if (e.key === 'Escape') { e.preventDefault(); this.cerrar(); }
                                        }
                                    }" x-on:click.outside="cerrar()" x-on:close-comboboxes.window="cerrar()" x-on:keydown="navegar($event)">
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Nombre completo <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                            <input type="text" x-model="nombres" placeholder="Escriba los nombres y apellidos..."
                                                class="input pl-9 pr-8"
                                                x-on:focus="if(nombres && nombres.length >= 2 && !seleccionadoId) abierto = true"
                                                x-on:input="nombres = nombres.toUpperCase(); seleccionadoId = null"
                                                autocomplete="off">
                                            
                                            <!-- Botón Limpiar -->
                                            <button type="button" x-show="nombres && nombres.length > 0"
                                                x-on:click="limpiar()"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                                aria-label="Limpiar">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            
                                            <!-- Indicador de Carga -->
                                            <div wire:loading wire:target="buscarPaciente" class="absolute right-8 top-1/2 -translate-y-1/2">
                                                <svg class="h-4 w-4 animate-spin text-ocean-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        @error('nombres')<p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>@enderror

                                        {{-- Dropdown --}}
                                        <div x-show="abierto"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 translate-y-0"
                                            x-transition:leave-end="opacity-0 -translate-y-1"
                                            x-ref="dropdown"
                                            class="absolute z-50 mt-1 w-full rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
                                            style="max-height: 260px; overflow-y: auto;" x-cloak>
                                            @if($this->pacientes->isEmpty())
                                                @if(strlen($buscarPaciente) >= 2)
                                                    <div class="px-4 py-3 text-center">
                                                        <p class="text-xs text-gray-400 dark:text-gray-500">Sin resultados para "{{ $buscarPaciente }}"</p>
                                                        <button type="button" wire:click="abrirQuickCreate" x-on:click="cerrar()"
                                                            class="mt-1.5 inline-flex items-center gap-1 text-[11px] font-semibold text-ocean-600 hover:text-ocean-700 dark:text-ocean-400 dark:hover:text-ocean-300">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                            Registrar "{{ $buscarPaciente }}"
                                                        </button>
                                                    </div>
                                                @endif
                                            @else
                                                @foreach ($this->pacientes as $index => $p)
                                                    <button type="button" wire:click="$set('pacienteId', {{ $p->id }})"
                                                        data-paciente
                                                        class="flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-xs transition
                                                            hover:bg-ocean-50 dark:hover:bg-ocean-950/20"
                                                        :class="{ 'bg-ocean-50 dark:bg-ocean-950/30': indiceResaltado === {{ $index }} }">
                                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                                                            {{ $p->tipo === 'huesped' ? 'bg-sand-100 text-sand-600 dark:bg-sand-900/30 dark:text-sand-400' : 'bg-palm-100 text-palm-600 dark:bg-palm-900/30 dark:text-palm-400' }}">
                                                            <span class="text-[11px] font-bold">{{ Str::substr($p->nombres, 0, 2) }}</span>
                                                        </div>
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-1.5">
                                                                <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $p->nombres }}</p>
                                                                @if($p->patologias)
                                                                    <svg class="h-3 w-3 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Con patologías"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                                @endif
                                                            </div>
                                                            <p class="mt-0.5 truncate text-[11px] text-gray-400 dark:text-gray-500">
                                                                {{ $p->area?->nombre ?: 'Sin área' }} · {{ $p->cargo?->nombre ?: 'Sin cargo' }}
                                                            </p>
                                                        </div>
                                                        <span class="chip {{ $p->tipo === 'huesped' ? 'bg-sand-50 text-sand-700 dark:bg-sand-950/30 dark:text-sand-300' : 'bg-palm-50 text-palm-700 dark:bg-palm-950/30 dark:text-palm-300' }}">
                                                            {{ $p->tipo === 'huesped' ? 'Huésped' : 'Colab' }}
                                                        </span>
                                                    </button>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- FICHA MÉDICA (collapsible) --}}
                                @php
                                    $sel = $this->pacienteSeleccionado;
                                    $examenes = $this->examenesPendientes;
                                    $visitasAnuales = $this->visitasAnuales;
                                    $ultimas = $this->ultimasVisitas;
                                    $tieneInfo = $sel && (
                                        $sel->patologias || $sel->vacunas || $sel->fichas_anteriores ||
                                        $sel->antecedentes || $sel->telefono || $sel->fecha_ingreso ||
                                        !empty($examenes) || array_filter($visitasAnuales) || $ultimas->isNotEmpty()
                                    );
                                @endphp
                                @if($tieneInfo)
                                    <div class="rounded-xl border border-ocean-100 bg-ocean-50/30 p-3.5 dark:border-ocean-900/30 dark:bg-ocean-950/10 space-y-2.5"
                                         x-data="{ abierto: false }">
                                        <button type="button" class="flex w-full items-center justify-between gap-2" x-on:click="abierto = !abierto">
                                            <div class="flex items-center gap-2">
                                                <span class="flex h-6 w-6 items-center justify-center rounded-md bg-ocean-100 text-ocean-600 dark:bg-ocean-900/40 dark:text-ocean-400">
                                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </span>
                                                <p class="text-[11px] font-bold uppercase tracking-wide text-ocean-700 dark:text-ocean-400">
                                                    Ficha Médica — {{ $sel->nombres }}
                                                </p>
                                            </div>
                                            <svg class="h-4 w-4 text-ocean-400 transition-transform duration-200" :class="abierto && 'rotate-180'"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                                            </svg>
                                        </button>

                                        <div x-show="abierto" x-collapse class="space-y-2.5">
                                            @if($sel->patologias)
                                                <div class="flex items-start gap-2 rounded-lg border border-red-100 bg-red-50/50 p-2.5 dark:border-red-900/30 dark:bg-red-950/10">
                                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-bold uppercase text-red-600 dark:text-red-400">Patologías</p>
                                                        <p class="mt-0.5 text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">{{ $sel->patologias }}</p>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($sel->vacunas || $sel->fichas_anteriores)
                                                <div class="flex flex-wrap gap-1.5">
                                                    @if($sel->vacunas)
                                                        <span class="chip-sm bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            Vacunas: {{ $sel->vacunas }}
                                                        </span>
                                                    @endif
                                                    @if($sel->fichas_anteriores)
                                                        <span class="chip-sm bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            Fichas desde {{ $sel->fichas_anteriores }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif

                                            @php $visitasConDato = array_filter($visitasAnuales); @endphp
                                            @if(!empty($visitasConDato))
                                                <div>
                                                    <p class="mb-1 text-[10px] font-bold text-gray-400 dark:text-gray-500">Fichas médicas anuales</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($visitasAnuales as $anio => $fecha)
                                                            <span class="chip-sm {{ $fecha ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' }}">
                                                                {{ $anio }}: {{ $fecha ?: '—' }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if(!empty($examenes))
                                                <div>
                                                    <p class="mb-1 text-[10px] font-bold text-gray-400 dark:text-gray-500">Exámenes ocupacionales</p>
                                                    <div class="flex flex-wrap gap-1">
                                                        @foreach($examenes as $ex)
                                                            <span class="chip-sm
                                                                {{ $ex['estado'] === 'vigente' ? 'bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400' : '' }}
                                                                {{ $ex['estado'] === 'vencido' ? 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400' : '' }}
                                                                {{ $ex['estado'] === 'pendiente' ? 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' : '' }}">
                                                                {{ $ex['nombre'] }}
                                                                <span class="font-normal opacity-70">
                                                                    {{ $ex['fecha'] ? "({$ex['fecha']})" : '— sin registro' }}
                                                                </span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                                @if($sel->telefono)
                                                    <span class="inline-flex items-center gap-1 font-medium text-gray-600 dark:text-gray-300">
                                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                        {{ $sel->telefono }}
                                                    </span>
                                                @endif
                                                @if($sel->fecha_ingreso)
                                                    <span>Ingreso: {{ $sel->fecha_ingreso instanceof \Carbon\Carbon ? $sel->fecha_ingreso->format('d/m/Y') : $sel->fecha_ingreso }}</span>
                                                @endif
                                                @if($sel->edad)
                                                    <span>{{ $sel->edad }} años</span>
                                                @endif
                                            </div>

                                            @if($ultimas->isNotEmpty())
                                                <div>
                                                    <p class="mb-1 text-[10px] font-bold text-gray-400 dark:text-gray-500">Últimas atenciones</p>
                                                    <div class="space-y-1">
                                                        @foreach($ultimas->take(3) as $v)
                                                            <p class="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
                                                                <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $v->fecha instanceof \Carbon\Carbon ? $v->fecha->format('d/m/Y') : $v->fecha }}</span>
                                                                — {{ $v->causa_nombre ?: 'Sin causa' }}
                                                            </p>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($sel->antecedentes)
                                                <div class="flex items-start gap-2 rounded-lg border border-sand-100 bg-sand-50/50 p-2.5 dark:border-sand-900/30 dark:bg-sand-950/10">
                                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-sand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <div class="min-w-0">
                                                        <p class="text-[10px] font-bold uppercase text-sand-600 dark:text-sand-400">Antecedentes</p>
                                                        <p class="mt-0.5 text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">{{ $sel->antecedentes }}</p>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                {{-- Fila: Edad / Área / Cargo --}}
                                <div class="grid grid-cols-4 gap-3 sm:gap-4">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Edad</label>
                                        <input type="number" wire:model="edad" placeholder="—" class="input text-center">
                                    </div>
                                    <div class="col-span-2">
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Área
                                            @if($tipoPaciente === 'huesped')
                                                <span class="font-normal normal-case text-gray-400">(no aplica)</span>
                                            @elseif($pacienteId)
                                                <span class="font-normal normal-case text-gray-400">(datos del paciente)</span>
                                            @endif
                                        </label>
                                        <select wire:model="area_id" class="input"
                                            @if($tipoPaciente === 'huesped' || $pacienteId) disabled @endif>
                                            <option value="">— Seleccionar —</option>
                                            @foreach ($this->areaCatalog as $a)
                                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Cargo
                                            @if($tipoPaciente === 'huesped')
                                                <span class="font-normal normal-case text-gray-400">(no aplica)</span>
                                            @elseif($pacienteId)
                                                <span class="font-normal normal-case text-gray-400">(datos del paciente)</span>
                                            @endif
                                        </label>
                                        <select wire:model="cargo_id" class="input"
                                            @if($tipoPaciente === 'huesped' || $pacienteId) disabled @endif>
                                            <option value="">— Seleccionar —</option>
                                            @foreach ($this->cargoCatalog as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Fila: Tipo paciente + Turno + Habitación --}}
                                <div class="grid gap-3 sm:gap-4 sm:grid-cols-2">
                                    {{-- Tipo --}}
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Tipo de paciente <span class="text-red-400">*</span>
                                        </label>
                                        <div class="flex gap-2">
                                            <button type="button" wire:click="$set('tipoPaciente', 'colaborador')"
                                                class="flex-1 rounded-xl border px-3 py-2.5 text-xs font-semibold transition-all
                                                {{ $tipoPaciente === 'colaborador' ? 'bg-ocean-50 border-ocean-200 text-ocean-700 shadow-sm dark:bg-ocean-900/30 dark:border-ocean-700 dark:text-ocean-300' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                                                Colaborador
                                            </button>
                                            <button type="button" wire:click="$set('tipoPaciente', 'huesped')"
                                                class="flex-1 rounded-xl border px-3 py-2.5 text-xs font-semibold transition-all
                                                {{ $tipoPaciente === 'huesped' ? 'bg-sand-50 border-sand-200 text-sand-700 shadow-sm dark:bg-sand-900/30 dark:border-sand-700 dark:text-sand-300' : 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800' }}">
                                                Huésped
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Turno --}}
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Turno
                                            @if($tipoPaciente === 'huesped')
                                                <span class="font-normal normal-case text-gray-400">(no aplica)</span>
                                            @endif
                                        </label>
                                        <div class="flex gap-2">
                                            @foreach(['mañana','tarde','noche'] as $t)
                                                <button type="button"
                                                    @if($tipoPaciente === 'huesped') disabled @else wire:click="toggleTurno('{{ $t }}')" @endif
                                                    class="flex-1 rounded-xl border px-2 py-2.5 text-xs font-semibold capitalize transition-all
                                                    {{ $tipoPaciente === 'huesped' ? 'bg-gray-100 border-gray-200 text-gray-300 cursor-not-allowed dark:bg-gray-800/30 dark:border-gray-700 dark:text-gray-600' : '' }}
                                                    {{ $tipoPaciente !== 'huesped' && $turno === $t ? 'bg-gray-900 border-gray-900 text-white shadow-sm dark:bg-white dark:border-white dark:text-gray-900' : '' }}
                                                    {{ $tipoPaciente !== 'huesped' && $turno !== $t ? 'bg-white border-gray-200 text-gray-500 hover:bg-gray-50 dark:bg-gray-800/50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800' : '' }}">
                                                    {{ $t }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Habitación (solo huéspedes) --}}
                                @if($tipoPaciente === 'huesped')
                                    <div x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 -translate-y-1"
                                         class="sm:max-w-xs">
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-sand-600 dark:text-sand-400">
                                            Nº Habitación <span class="text-red-400">*</span>
                                        </label>
                                        <input wire:model="habitacion" placeholder="Ej: 301"
                                            class="input border-sand-200 bg-sand-50/30 dark:border-sand-800 dark:bg-sand-950/10 focus:border-sand-400">
                                        @error('habitacion')<p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>@enderror
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 2. EVALUACIÓN CLÍNICA --}}
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-5 py-3.5 rounded-t-2xl dark:border-gray-800 dark:bg-gray-900/50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-coral-100 text-coral-600 dark:bg-coral-900/30 dark:text-coral-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">2. Evaluación Clínica</h4>
                                    <p class="text-xs text-gray-500">Causa, diagnóstico y observaciones de la atención</p>
                                </div>
                            </div>
                            <div class="p-5 space-y-5">
                                {{-- Causa + Diágnóstico --}}
                                <div class="grid gap-4 sm:grid-cols-2">

                                    {{-- CAUSA — combobox búsqueda local --}}
                                    <div
                                        x-data="{
                                            open: false,
                                            q: '',
                                            selected: null,
                                            indice: -1,
                                            items: @js($this->causaCatalog->values()),
                                            get filtered() {
                                                if (!this.q) return this.items;
                                                const s = this.q.toLowerCase();
                                                return this.items.filter(i => i.nombre.toLowerCase().includes(s));
                                            },
                                            pick(item) {
                                                this.selected = item;
                                                this.q = item.nombre;
                                                this.open = false;
                                                this.indice = -1;
                                                $wire.set('causa_id', item.id);
                                            },
                                            clear() { this.selected = null; this.q = ''; this.indice = -1; $wire.set('causa_id', null); },
                                            cerrar() { this.open = false; this.indice = -1; },
                                            navegar(e) {
                                                if (!this.open || this.filtered.length === 0) return;
                                                if (e.key === 'ArrowDown') { e.preventDefault(); this.indice = Math.min(this.indice + 1, this.filtered.length - 1); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                else if (e.key === 'ArrowUp') { e.preventDefault(); this.indice = Math.max(this.indice - 1, 0); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                else if (e.key === 'Enter' && this.indice >= 0 && this.filtered[this.indice]) { e.preventDefault(); this.pick(this.filtered[this.indice]); }
                                                else if (e.key === 'Escape') { e.preventDefault(); this.cerrar(); }
                                            }
                                        }"
                                        x-init="
                                            @if($causa_id)
                                                const pre = items.find(i => i.id === {{ (int)($causa_id ?? 0) }});
                                                if (pre) { selected = pre; q = pre.nombre; }
                                            @endif
                                        "
                                        x-on:click.outside="open = false"
                                        x-on:mousedown.outside="open = false"
                                        x-on:close-comboboxes.window="open = false">

                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                            Causa <span class="text-red-400">*</span>
                                        </label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                x-model="q"
                                                x-on:focus="open = true; indice = -1; $dispatch('close-comboboxes')"
                                                x-on:input="q = q.toUpperCase(); open = true; indice = -1"
                                                x-on:keydown="navegar($event)"
                                                placeholder="Buscar causa..."
                                                class="input pr-8"
                                                autocomplete="off">
                                            <button type="button" x-show="q" @click="clear()"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            <div x-show="open && filtered.length > 0" x-transition x-ref="list"
                                                class="absolute z-30 mt-1 w-full max-h-48 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <template x-for="(item, i) in filtered" :key="item.id">
                                                    <button type="button"
                                                        @click="pick(item)"
                                                        @mouseenter="indice = i"
                                                        class="flex w-full items-center px-3 py-2 text-left text-[13px] hover:bg-gray-50 dark:hover:bg-gray-800"
                                                        :class="indice === i ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400' : (selected && selected.id === item.id ? 'bg-ocean-50/60 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400' : 'text-gray-700 dark:text-gray-300')">
                                                        <span x-text="item.nombre"></span>
                                                    </button>
                                                </template>
                                            </div>
                                            <div x-show="open && q && filtered.length === 0" x-transition
                                                class="absolute z-30 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-[12px] text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                Sin resultados
                                            </div>
                                        </div>
                                        @error('causa_id')<p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>@enderror
                                    </div>

                                    {{-- DIAGNÓSTICO — combobox búsqueda server-side (lista muy larga) --}}
                                    <div
                                        x-data="{
                                            open: false,
                                            q: '',
                                            selected: null,
                                            items: [],
                                            searching: false,
                                            debounceTimer: null,
                                            buscar() {
                                                clearTimeout(this.debounceTimer);
                                                if (!this.q || this.q.trim().length < 2) {
                                                    this.items = [];
                                                    return;
                                                }
                                                this.searching = true;
                                                this.debounceTimer = setTimeout(() => {
                                                    $wire.call('buscarDiagnosticos', this.q.trim())
                                                        .then(r => { this.items = Array.isArray(r) ? r : []; this.searching = false; })
                                                        .catch(() => { this.items = []; this.searching = false; });
                                                }, 300);
                                            },
                                            pick(item) {
                                                this.selected = item;
                                                this.q = item.nombre;
                                                this.open = false;
                                                this.items = [];
                                                $wire.set('diagnostico_id', item.id);
                                            },
                                            clear() { this.selected = null; this.q = ''; this.items = []; $wire.set('diagnostico_id', null); },
                                            cerrar() { this.open = false; }
                                        }"
                                        x-init="
                                            @if($diagnostico_id)
                                                $wire.call('buscarDiagnosticoPorId', {{ (int)$diagnostico_id }})
                                                    .then(item => { if (item) { selected = item; q = item.nombre; } });
                                            @endif
                                        "
                                        x-on:keydown.escape="open = false"
                                        x-on:click.outside="open = false"
                                        x-on:mousedown.outside="open = false"
                                        x-on:close-comboboxes.window="open = false">

                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Diagnóstico</label>
                                        <div class="relative">
                                            <input
                                                type="text"
                                                x-model="q"
                                                x-on:focus="open = true; $dispatch('close-comboboxes')"
                                                x-on:input="q = q.toUpperCase(); open = true; buscar()"
                                                placeholder="Escriba al menos 2 caracteres para buscar..."
                                                class="input pr-8"
                                                autocomplete="off">
                                            <button type="button" x-show="q && !searching" @click="clear()"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                            {{-- Spinner de búsqueda --}}
                                            <div x-show="searching" class="absolute right-2 top-1/2 -translate-y-1/2">
                                                <svg class="h-4 w-4 animate-spin text-ocean-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </div>
                                            {{-- Dropdown con resultados --}}
                                            <div x-show="open && items.length > 0" x-transition
                                                class="absolute z-30 mt-1 w-full max-h-48 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                <template x-for="item in items" :key="item.id">
                                                    <button type="button"
                                                        @click="pick(item)"
                                                        class="flex w-full items-center px-3 py-2 text-left text-[13px] hover:bg-gray-50 dark:hover:bg-gray-800"
                                                        :class="selected && selected.id === item.id ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400' : 'text-gray-700 dark:text-gray-300'">
                                                        <span x-text="item.nombre"></span>
                                                    </button>
                                                </template>
                                            </div>
                                            {{-- Mensaje "escribe para buscar" --}}
                                            <div x-show="open && q.trim().length < 2 && items.length === 0 && !searching" x-transition
                                                class="absolute z-30 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-[12px] text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                Escriba al menos 2 caracteres para buscar diagnósticos.
                                            </div>
                                            {{-- Sin resultados --}}
                                            <div x-show="open && q.trim().length >= 2 && items.length === 0 && !searching" x-transition
                                                class="absolute z-30 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-[12px] text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                Sin resultados para "<span x-text="q"></span>"
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Tipo de Salida + Incidente --}}
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipo de salida <span class="font-normal normal-case text-gray-400">(opcional)</span></label>
                                        <select wire:model="tipo_salida" class="input">
                                            <option value="">— Sin especificar —</option>
                                            @foreach($this->tiposSalidaCatalog as $t)
                                                <option value="{{ $t }}">{{ $t }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Incidente <span class="font-normal normal-case text-gray-400">(opcional)</span></label>
                                        <select wire:model="incidente" class="input">
                                            <option value="">— Sin especificar —</option>
                                            @foreach($this->incidentesCatalog as $i)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Observaciones y Notas Clínicas</label>
                                    <textarea wire:model="observacion" rows="3" placeholder="Redacte cualquier nota adicional sobre el diagnóstico, la consulta o el estado del paciente..."
                                        class="input resize-none bg-gray-50/50 dark:bg-gray-950/20 focus:bg-white dark:focus:bg-gray-900 transition-colors"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- 3. TRATAMIENTO Y REPOSO --}}
                        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-3 border-b border-gray-100 bg-gray-50/50 px-5 py-3.5 rounded-t-2xl dark:border-gray-800 dark:bg-gray-900/50">
                                <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-tide-100 text-tide-600 dark:bg-tide-900/30 dark:text-tide-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                </span>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">3. Tratamiento y Reposo</h4>
                                    <p class="text-xs text-gray-500">Medicación prescrita y certificados médicos emitidos</p>
                                </div>
                            </div>
                            <div class="p-5 sm:p-6 space-y-5">

                                {{-- Medicamentos --}}
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <label class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Medicamentos prescritos</label>
                                        <button type="button" wire:click="agregarMedicamento"
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-tide-50 px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-tide-600 transition hover:bg-tide-100 dark:bg-tide-950/20 dark:text-tide-400 dark:hover:bg-tide-950/40">
                                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                            Añadir
                                        </button>
                                    </div>
                                    @if(count($medicamentos) === 0)
                                        <div class="rounded-xl border border-dashed border-gray-300 py-6 text-center dark:border-gray-700">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">No se han recetado medicamentos.</p>
                                        </div>
                                    @else
                                        <div class="space-y-2">
                                            @foreach ($medicamentos as $i => $m)
                                                <div
                                                    class="flex items-center gap-2 rounded-xl border border-gray-200 bg-gray-50 p-2.5 dark:border-gray-800 dark:bg-gray-900/50"
                                                    x-data="{
                                                        open: false,
                                                        q: '',
                                                        selected: null,
                                                        indice: -1,
                                                        items: @js($this->medicamentosCatalog->values()),
                                                        get filtered() {
                                                            if (!this.q) return this.items;
                                                            const s = this.q.toLowerCase();
                                                            return this.items.filter(i => i.nombre.toLowerCase().includes(s));
                                                        },
                                                        pick(item) {
                                                            this.selected = item;
                                                            this.q = item.nombre;
                                                            this.open = false;
                                                            this.indice = -1;
                                                            $wire.set('medicamentos.{{ $i }}.medicamento_id', item.id);
                                                        },
                                                        clear() { this.selected = null; this.q = ''; this.indice = -1; $wire.set('medicamentos.{{ $i }}.medicamento_id', null); },
                                                        cerrar() { this.open = false; this.indice = -1; },
                                                        navegar(e) {
                                                            if (!this.open || this.filtered.length === 0) return;
                                                            if (e.key === 'ArrowDown') { e.preventDefault(); this.indice = Math.min(this.indice + 1, this.filtered.length - 1); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                            else if (e.key === 'ArrowUp') { e.preventDefault(); this.indice = Math.max(this.indice - 1, 0); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                            else if (e.key === 'Enter' && this.indice >= 0 && this.filtered[this.indice]) { e.preventDefault(); this.pick(this.filtered[this.indice]); }
                                                            else if (e.key === 'Escape') { e.preventDefault(); this.cerrar(); }
                                                        }
                                                    }"
                                                    x-init="
                                                        @if(!empty($m['medicamento_id']))
                                                            const pre = items.find(i => i.id === {{ (int)($m['medicamento_id'] ?? 0) }});
                                                            if (pre) { selected = pre; q = pre.nombre; }
                                                        @endif
                                                    "
                                                    x-on:click.outside="open = false"
                                                    x-on:mousedown.outside="open = false"
                                                    x-on:close-comboboxes.window="open = false">

                                                    {{-- Numero --}}
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-tide-100 text-[10px] font-bold text-tide-700 dark:bg-tide-900/50 dark:text-tide-300">
                                                        {{ $i + 1 }}
                                                    </span>

                                                    {{-- Buscador medicamento --}}
                                                    <div class="relative min-w-0 flex-1">
                                                        <input
                                                            type="text"
                                                            x-model="q"
                                                            x-on:focus="open = true; indice = -1; $dispatch('close-comboboxes')"
                                                            x-on:input="open = true; indice = -1"
                                                            x-on:keydown="navegar($event)"
                                                            placeholder="Buscar medicamento..."
                                                            class="input-sm w-full pr-7 bg-white dark:bg-gray-900"
                                                            autocomplete="off">
                                                        <button type="button" x-show="q" @click="clear()"
                                                            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                        <div x-show="open && filtered.length > 0" x-transition x-ref="list"
                                                            class="absolute z-30 mt-1 w-full max-h-44 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                            <template x-for="(item, i) in filtered" :key="item.id">
                                                                <button type="button"
                                                                    @click="pick(item)"
                                                                    @mouseenter="indice = i"
                                                                    class="flex w-full items-center px-3 py-2 text-left text-[12px] hover:bg-gray-50 dark:hover:bg-gray-800"
                                                                    :class="indice === i ? 'bg-tide-50 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400' : (selected && selected.id === item.id ? 'bg-tide-50/60 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400' : 'text-gray-700 dark:text-gray-300')">
                                                                    <span x-text="item.nombre"></span>
                                                                </button>
                                                            </template>
                                                        </div>
                                                        <div x-show="open && q && filtered.length === 0" x-transition
                                                            class="absolute z-30 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-[11px] text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                            Sin resultados
                                                        </div>
                                                    </div>

                                                    {{-- Cantidad compacta --}}
                                                    <div class="flex shrink-0 items-center gap-1">
                                                        <input type="number" step="0.01"
                                                            wire:model="medicamentos.{{ $i }}.cantidad"
                                                            placeholder="Cant."
                                                            min="0.01"
                                                            class="input-sm w-16 text-center bg-white dark:bg-gray-900"
                                                            aria-label="Cantidad">
                                                        <span class="text-[10px] font-semibold text-gray-400">ud.</span>
                                                    </div>

                                                    {{-- Eliminar --}}
                                                    <button type="button" wire:click="quitarMedicamento({{ $i }})"
                                                        class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                                        aria-label="Eliminar medicamento">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @error('medicamentos.*.medicamento_id')
                                        <p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                    @error('medicamentos.*.cantidad')
                                        <p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <hr class="border-gray-100 dark:border-gray-800">

                                {{-- Certificado Médico --}}
                                <div x-data="{ abierto: {{ $this->tieneCertificado ? 'true' : 'false' }} }">
                                    <button type="button" x-on:click="abierto = !abierto"
                                        class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-left transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/80">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white shadow-sm dark:bg-gray-900">
                                                <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-bold text-gray-900 dark:text-white">Emitir Certificado Médico</h5>
                                                <p class="text-[11px] text-gray-500">Haz clic para agregar reposo o subsidio al paciente</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            @if($entidad_certificado_id)
                                                <span class="chip bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Activado</span>
                                            @endif
                                            <svg class="h-5 w-5 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                                        </div>
                                    </button>
                                    
                                    <div x-show="abierto" x-collapse>
                                        <div class="mt-3 grid gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:grid-cols-2 dark:border-gray-800 dark:bg-gray-950/50">
                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Entidad / Tipo</label>
                                                <select wire:model="entidad_certificado_id" class="input">
                                                    <option value="">— Seleccionar —</option>
                                                    @foreach ($this->entidadCatalog as $ec)
                                                        <option value="{{ $ec->id }}">{{ $ec->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipo de certificado</label>
                                                <select wire:model="tipo_certificado" class="input">
                                                    <option value="">— Seleccionar —</option>
                                                    @foreach ($this->tipoCertCatalog as $tc)
                                                        <option value="{{ $tc }}">{{ $tc }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tiempo de Reposo</label>
                                                <div class="flex gap-1 mb-2">
                                                    <button type="button"
                                                        wire:click="$set('unidadDescanso', 'horas')"
                                                        class="flex-1 rounded-lg border px-3 py-2 text-xs font-semibold transition-all
                                                        {{ $unidadDescanso === 'horas' ? 'bg-ocean-600 border-ocean-600 text-white dark:bg-ocean-500 dark:border-ocean-500' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400' }}">
                                                        Horas
                                                    </button>
                                                    <button type="button"
                                                        wire:click="$set('unidadDescanso', 'dias')"
                                                        class="flex-1 rounded-lg border px-3 py-2 text-xs font-semibold transition-all
                                                        {{ $unidadDescanso === 'dias' ? 'bg-ocean-600 border-ocean-600 text-white dark:bg-ocean-500 dark:border-ocean-500' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400' }}">
                                                        Días
                                                    </button>
                                                </div>
                                                @if($unidadDescanso === 'horas')
                                                    <input type="number" step="0.5" wire:model="horas_certificado" placeholder="Cantidad de horas" class="input">
                                                @else
                                                    <input type="number" wire:model="dias_certificado" placeholder="Cantidad de días" class="input">
                                                @endif
                                            </div>
                                            <div>
                                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Fechas (Inicio - Fin)</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="date" wire:model="fecha_inicio_certificado" class="input flex-1 px-2">
                                                    <span class="text-gray-400">-</span>
                                                    <input type="date" wire:model="fecha_fin_certificado" class="input flex-1 px-2">
                                                </div>
                                            </div>
                                            <div class="sm:col-span-2"
                                                x-data="{
                                                    open: false,
                                                    q: @js($medico_certifica ?? ''),
                                                    selected: null,
                                                    indice: -1,
                                                    items: @js($this->personalMedico->map(fn($p) => ['id' => $p->id, 'nombre' => $p->nombres, 'cargo' => $p->cargo?->nombre])->values()),
                                                    get filtered() {
                                                        if (!this.q) return this.items;
                                                        const s = this.q.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
                                                        return this.items.filter(i =>
                                                            i.nombre.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().includes(s) ||
                                                            (i.cargo && i.cargo.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().includes(s))
                                                        );
                                                    },
                                                    pick(item) {
                                                        this.selected = item;
                                                        this.q = item.nombre;
                                                        this.open = false;
                                                        this.indice = -1;
                                                        $wire.set('medico_certifica', item.nombre);
                                                    },
                                                    clear() {
                                                        this.selected = null;
                                                        this.q = '';
                                                        this.indice = -1;
                                                        $wire.set('medico_certifica', null);
                                                    },
                                                    cerrar() { this.open = false; this.indice = -1; },
                                                    navegar(e) {
                                                        if (!this.open || this.filtered.length === 0) return;
                                                        if (e.key === 'ArrowDown') { e.preventDefault(); this.indice = Math.min(this.indice + 1, this.filtered.length - 1); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                        else if (e.key === 'ArrowUp') { e.preventDefault(); this.indice = Math.max(this.indice - 1, 0); this.$refs.list?.children[this.indice]?.scrollIntoView({ block: 'nearest' }); }
                                                        else if (e.key === 'Enter' && this.indice >= 0 && this.filtered[this.indice]) { e.preventDefault(); this.pick(this.filtered[this.indice]); }
                                                        else if (e.key === 'Escape') { e.preventDefault(); this.cerrar(); }
                                                    },
                                                    guardarTextoLibre() {
                                                        setTimeout(() => {
                                                            const val = this.q ? this.q.trim().toUpperCase() : '';
                                                            if (val) { $wire.set('medico_certifica', val); }
                                                            else { $wire.set('medico_certifica', null); }
                                                            this.open = false;
                                                        }, 150);
                                                    }
                                                }"
                                                x-init="
                                                    @if($medico_certifica)
                                                        const found = items.find(i => i.nombre === @js($medico_certifica));
                                                        if (found) { selected = found; q = found.nombre; }
                                                    @endif
                                                "
                                                x-on:click.outside="guardarTextoLibre()"
                                                x-on:close-comboboxes.window="open = false">
                                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Médico que certifica <span class="font-normal normal-case text-gray-400">(opcional)</span></label>
                                                <div class="relative">
                                                    <input type="text" x-model="q"
                                                        @focus="open = true; indice = -1; $dispatch('close-comboboxes')"
                                                        @input="q = q.toUpperCase(); open = true; indice = -1"
                                                        @keydown="navegar($event)"
                                                        placeholder="Buscar por nombre o cargo..."
                                                        class="input pr-8" autocomplete="off">
                                                    <button type="button" x-show="q" @click="clear()"
                                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                    <div x-show="open && filtered.length > 0" x-transition x-ref="list"
                                                        class="absolute z-50 mt-1 w-full max-h-44 overflow-auto rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                        <template x-for="(item, i) in filtered" :key="item.id">
                                                            <button type="button"
                                                                @click="pick(item)"
                                                                @mouseenter="indice = i"
                                                                class="flex w-full items-center px-3 py-2 text-left text-[12px] hover:bg-gray-50 dark:hover:bg-gray-800"
                                                                :class="indice === i ? 'bg-tide-50 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400' : (selected && selected.id === item.id ? 'bg-tide-50/60 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400' : 'text-gray-700 dark:text-gray-300')">
                                                                <div>
                                                                    <span x-text="item.nombre" class="font-medium"></span>
                                                                    <span x-text="item.cargo ? ' · ' + item.cargo : ''" class="text-gray-400"></span>
                                                                </div>
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <div x-show="open && q && filtered.length === 0" x-transition
                                                        class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-[11px] text-gray-400 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                                                        Sin coincidencias — se guardará como texto libre
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </form>
                    {{-- FOOTER ACTIONS --}}
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-5 py-3.5 sm:px-6 dark:border-gray-800">
                        <button type="button" wire:click="abrirQuickCreate"
                            class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            Nuevo paciente
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cerrarModalAtencion"
                                class="btn-outline px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                                Cancelar
                            </button>
                            <button type="button" wire:click="guardar"
                                class="btn-primary px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                                <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $editandoId ? 'Actualizar atención' : 'Guardar atención' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================
        QUICK-CREATE PATIENT MODAL — Formulario completo (igual que Personas)
        ============================================================ --}}
        @if($quickCreateAbierto)
            <div class="modal-overlay" wire:click.self="cerrarQuickCreate" x-data
                 x-on:keydown.escape.window="$wire.cerrarQuickCreate()"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel !max-w-2xl w-[calc(100%-0.5rem)] sm:!w-full mx-auto" @click.stop>
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-800">
                        <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg sm:h-9 sm:w-9 sm:rounded-xl bg-ocean-100 text-ocean-600 ring-1 ring-ocean-200 dark:bg-ocean-950/30 dark:text-ocean-400 dark:ring-ocean-800">
                                <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-gray-900 sm:text-base dark:text-white">Nuevo registro de paciente</h3>
                                <p class="hidden text-[11px] text-gray-400 sm:block sm:text-xs dark:text-gray-500">Complete los datos y el paciente quedará seleccionado automáticamente</p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarQuickCreate"
                            class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 sm:p-2 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form body --}}
                    <form wire:submit.prevent="quickGuardarPaciente"
                        class="scroll-thin max-h-[55vh] overflow-y-auto divide-y divide-gray-50 sm:max-h-[60vh] dark:divide-gray-800/50">

                        {{-- SECTION: Datos personales --}}
                        <div class="form-section">
                            <span class="form-section-label text-ocean-600 dark:text-ocean-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Datos personales
                            </span>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Nombres completos <span class="text-red-400">*</span></label>
                                <input wire:model="qNombres" placeholder="Nombres y apellidos" class="input" required>
                                @error('qNombres')<p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Cédula</label>
                                    <input wire:model="qCedula" placeholder="Sin cédula" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Edad</label>
                                    <input type="number" wire:model="qEdad" placeholder="—" class="input" min="0" max="150">
                                </div>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Área</label>
                                    <select wire:model="qAreaId" class="input">
                                        <option value="">— Seleccionar —</option>
                                        @foreach ($this->areaCatalog as $area)
                                            <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Cargo</label>
                                    <select wire:model="qCargoId" class="input">
                                        <option value="">— Seleccionar —</option>
                                        @foreach ($this->cargoCatalog as $cargo)
                                            <option value="{{ $cargo->id }}">{{ $cargo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Fecha ingreso</label>
                                    <input type="date" wire:model="qFechaIngreso" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Tipo <span class="text-red-400">*</span></label>
                                    <select wire:model="qTipo" class="input">
                                        <option value="colaborador">Colaborador</option>
                                        <option value="aspirante">Aspirante</option>
                                        <option value="externo">Externo</option>
                                        <option value="paciente">Paciente</option>
                                        <option value="huesped">Huésped</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Teléfono</label>
                                    <input wire:model="qTelefono" placeholder="09..." class="input">
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                <input type="checkbox" wire:model="qActivo" class="h-4 w-4 rounded border-gray-300 text-ocean-600 focus:ring-ocean-500">
                                Activo
                            </label>
                        </div>

                        {{-- SECTION: Historial médico --}}
                        <div class="form-section">
                            <span class="form-section-label text-coral-600 dark:text-coral-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                Historial médico
                            </span>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Patologías</label>
                                <textarea wire:model="qPatologias" rows="2" placeholder="Describa patologías conocidas..." class="input"></textarea>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Vacunas</label>
                                    <input wire:model="qVacunas" placeholder="Ej: COVID-19, Influenza" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Fichas anteriores</label>
                                    <input wire:model="qFichasAnteriores" placeholder="Ej: Desde 2020" class="input">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Antecedentes</label>
                                <textarea wire:model="qAntecedentes" rows="2" placeholder="Antecedentes médicos relevantes..." class="input"></textarea>
                            </div>
                        </div>

                        {{-- SECTION: Exámenes ocupacionales --}}
                        <div class="form-section">
                            <span class="form-section-label text-sand-600 dark:text-sand-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Exámenes ocupacionales
                            </span>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                @foreach(['espirometria' => 'Espirometría', 'ecografia' => 'Ecografía', 'audiometria' => 'Audiometría', 'optometria' => 'Optometría'] as $key => $label)
                                    <div>
                                        <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</label>
                                        <input type="date" wire:model="qExamenesFechas.{{ $key }}" class="input">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECTION: Visitas médicas anuales (colapsable) --}}
                        <div x-data="{ abierto: false }">
                            <button type="button" x-on:click="abierto = !abierto"
                                class="flex w-full items-center justify-between gap-2 px-4 py-3 text-left transition hover:bg-gray-50/50 sm:px-5 sm:py-3.5 dark:hover:bg-gray-950/30">
                                <span class="form-section-label text-tide-600 dark:text-tide-400 !mb-0 after:hidden">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Visitas médicas anuales
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                            </button>
                            <div x-show="abierto" x-collapse class="px-4 pb-3.5 sm:px-5 sm:pb-4">
                                <div class="grid grid-cols-2 gap-2 rounded-xl border border-tide-100 bg-tide-50/30 p-3 sm:grid-cols-3 dark:border-tide-900/30 dark:bg-tide-950/10">
                                    @foreach(range(2021, 2026) as $anio)
                                        <div>
                                            <label class="mb-1 block text-xs font-bold uppercase text-tide-600 dark:text-tide-400">{{ $anio }}</label>
                                            <input type="date" wire:model="qVisitasFechas.{{ $anio }}" class="input-xs border-tide-200 dark:border-tide-800">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Observaciones --}}
                        <div class="form-section !pb-2">
                            <label class="mb-1 block text-xs font-bold uppercase text-gray-500 dark:text-gray-400">Observaciones</label>
                            <textarea wire:model="qObservaciones" rows="2" placeholder="Notas adicionales..." class="input"></textarea>
                        </div>
                    </form>

                    {{-- ACTIONS --}}
                    <div class="flex items-center justify-between gap-2 border-t border-gray-100 px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-800">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">
                            <span class="inline-flex items-center gap-1">
                                <svg class="h-3 w-3 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                El paciente se seleccionará automáticamente
                            </span>
                        </p>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="cerrarQuickCreate"
                                class="btn-outline px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                                Cancelar
                            </button>
                            <button type="button" wire:click="quickGuardarPaciente"
                                class="btn-primary px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                                <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Crear y seleccionar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        {{-- ============================================================
        DELETE MODAL
        ============================================================ --}}
        @if($modalEliminarAbierto)
            <div class="modal-overlay" wire:click.self="cancelarEliminar" x-data
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0">
                <div class="modal-panel" @click.stop>
                    <div class="bg-gradient-to-br from-red-50 via-white to-white px-6 py-5 text-center dark:from-red-950/30 dark:via-gray-900 dark:to-gray-900">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 ring-4 ring-red-50 dark:bg-red-950/30 dark:ring-red-950/10">
                            <svg class="h-7 w-7 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <h4 class="mt-4 text-base font-bold text-gray-900 dark:text-white">¿Eliminar esta atención?</h4>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
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
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
