<x-filament-panels::page>
    <x-hero-card title="Pacientes" subtitle="Gestiona pacientes, colaboradores y huéspedes del dispensario médico" icon="heroicon-o-user-group" color="sand">
        <button type="button" wire:click="abrirModal"
            class="btn-primary text-sm !rounded-xl !px-5 !py-3">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nuevo registro
        </button>
    </x-hero-card>

    <div class="page-enter space-y-5">

        {{-- ============================================================
        STATS KPI STRIP
        ============================================================ --}}
        @php $stats = $this->estadisticas; @endphp
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div class="stat border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900/80">
                <div class="stat-icon bg-ocean-50 text-ocean-600 dark:bg-ocean-950/30 dark:text-ocean-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="stat-label text-gray-400 dark:text-gray-500">Total registros</p>
                </div>
            </div>
            <div class="stat border-palm-100 bg-palm-50/50 dark:border-palm-900/30 dark:bg-palm-950/10">
                <div class="stat-icon bg-palm-100 text-palm-600 dark:bg-palm-950/30 dark:text-palm-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-palm-700 dark:text-palm-300">{{ $stats['activos'] }}</p>
                    <p class="stat-label text-palm-500/80 dark:text-palm-500/70">Activos</p>
                </div>
            </div>
            <div class="stat border-tide-100 bg-tide-50/50 dark:border-tide-900/30 dark:bg-tide-950/10">
                <div class="stat-icon bg-tide-100 text-tide-600 dark:bg-tide-950/30 dark:text-tide-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h2a2 2 0 012 2v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-tide-700 dark:text-tide-300">{{ $stats['colaboradores'] }}</p>
                    <p class="stat-label text-tide-500/80 dark:text-tide-500/70">Colaboradores</p>
                </div>
            </div>
            <div class="stat border-coral-100 bg-coral-50/50 dark:border-coral-900/30 dark:bg-coral-950/10">
                <div class="stat-icon bg-coral-100 text-coral-600 dark:bg-coral-950/30 dark:text-coral-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-coral-700 dark:text-coral-300">{{ $stats['conPatologia'] }}</p>
                    <p class="stat-label text-coral-500/80 dark:text-coral-500/70">Con patologías</p>
                </div>
            </div>
            <div class="stat border-sand-100 bg-sand-50/50 dark:border-sand-900/30 dark:bg-sand-950/10">
                <div class="stat-icon bg-sand-100 text-sand-600 dark:bg-sand-950/30 dark:text-sand-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-sand-700 dark:text-sand-300">{{ $stats['conExamenes'] }}</p>
                    <p class="stat-label text-sand-500/80 dark:text-sand-500/70">Con exámenes</p>
                </div>
            </div>
            <div class="stat border-tide-100 bg-tide-50/50 dark:border-tide-900/30 dark:bg-tide-950/10">
                <div class="stat-icon bg-tide-100 text-tide-600 dark:bg-tide-950/30 dark:text-tide-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-tide-700 dark:text-tide-300">{{ $stats['conTelefono'] }}</p>
                    <p class="stat-label text-tide-500/80 dark:text-tide-500/70">Con teléfono</p>
                </div>
            </div>
        </div>

        {{-- ============================================================
        LIST CARD — full width
        ============================================================ --}}
        <section class="card overflow-hidden">
            <div class="card-header">
                <div class="flex items-center gap-2.5">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Base de pacientes</h3>
                    <span class="chip bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">{{ $this->totalPacientes }}</span>
                </div>
            </div>

            {{-- Filters --}}
            <div class="border-b border-gray-50 px-5 pb-3 dark:border-gray-800">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="filter-search">
                        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                        <input wire:model.live.debounce.350ms="buscar" placeholder="Buscar nombre, cédula, área, cargo...">
                    </div>
                    <select wire:model.live="areaFiltroId" class="filter-select-sm">
                        <option value="">Todas las áreas</option>
                        @foreach ($this->areasParaFiltro as $area)
                            <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="tipoFiltro" class="filter-select-sm">
                        <option value="">Todos los tipos</option>
                        <option value="colaborador">Colaborador</option>
                        <option value="aspirante">Aspirante</option>
                        <option value="externo">Externo</option>
                        <option value="paciente">Paciente</option>
                        <option value="huesped">Huésped</option>
                    </select>
                    <select wire:model.live="estadoFiltro" class="filter-select-sm">
                        <option value="">Cualquier estado</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>
                </div>

                {{-- Active filter badges --}}
                @if($this->filtrosActivos > 0)
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @if($this->buscar !== '')
                            <span class="chip-sm bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400">
                                "{{ $this->buscar }}"
                                <button wire:click="$set('buscar', '')" class="ml-0.5 hover:text-ocean-900 dark:hover:text-ocean-200">&times;</button>
                            </span>
                        @endif
                        @if($this->areaFiltroId)
                            <span class="chip-sm bg-tide-50 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400">
                                {{ $this->areasParaFiltro->firstWhere('id', $this->areaFiltroId)?->nombre ?? 'Área #'.$this->areaFiltroId }}
                                <button wire:click="$set('areaFiltroId', null)" class="ml-0.5">&times;</button>
                            </span>
                        @endif
                        @if($this->tipoFiltro)
                            <span class="chip-sm bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400">
                                {{ $this->tipoFiltro }}
                                <button wire:click="$set('tipoFiltro', null)" class="ml-0.5">&times;</button>
                            </span>
                        @endif
                        @if($this->estadoFiltro)
                            <span class="chip-sm bg-sand-50 text-sand-700 dark:bg-sand-950/20 dark:text-sand-400">
                                {{ $this->estadoFiltro === 'activo' ? 'Activos' : 'Inactivos' }}
                                <button wire:click="$set('estadoFiltro', null)" class="ml-0.5">&times;</button>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Table --}}
            @if($this->pacientes->isNotEmpty())
                <div class="scroll-thin max-h-[650px] overflow-auto">
                    <table class="min-w-full">
                        <thead class="sticky top-0 z-10 bg-gray-50/95 backdrop-blur dark:bg-gray-950/95">
                            <tr>
                                <th class="table-header-cell w-[35%]">Paciente</th>
                                <th class="table-header-cell w-[25%]">Área / Cargo</th>
                                <th class="table-header-cell w-[22%]">Datos médicos</th>
                                <th class="table-header-cell w-[18%]"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800/50">
                            @foreach ($this->pacientes as $p)
                                <tr class="table-row group {{ !$p->activo ? 'opacity-50' : '' }}">
                                    {{-- Paciente --}}
                                    <td class="table-cell">
                                        <button wire:click="verDetalle({{ $p->id }})"
                                            class="flex w-full items-start gap-2.5 text-left">
                                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full
                                                {{ $p->tipo === 'colaborador' ? 'bg-palm-400 shadow-sm shadow-palm-400/30' : '' }}
                                                {{ $p->tipo === 'aspirante' ? 'bg-sand-400 shadow-sm shadow-sand-400/30' : '' }}
                                                {{ $p->tipo === 'huesped' ? 'bg-tide-400 shadow-sm shadow-tide-400/30' : '' }}
                                                {{ $p->tipo === 'paciente' ? 'bg-ocean-400 shadow-sm shadow-ocean-400/30' : '' }}
                                                {{ $p->tipo === 'externo' ? 'bg-gray-400 shadow-sm shadow-gray-400/30' : '' }}
                                                {{ !$p->activo ? 'bg-red-400' : '' }}"></span>
                                            <div class="min-w-0">
                                                <p class="truncate text-[13px] font-semibold text-gray-900 dark:text-white">
                                                    {{ $p->nombres }}
                                                    @if($p->patologias)
                                                        <svg class="ml-1 inline-block h-3 w-3 text-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Con patologías"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                    @endif
                                                </p>
                                                <p class="mt-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                                    {{ $p->cedula ?: 'S/C' }}
                                                    @if($p->fecha_ingreso)
                                                        · Ingreso {{ $p->fecha_ingreso->format('d/m/Y') }}
                                                    @endif
                                                </p>
                                            </div>
                                        </button>
                                    </td>
                                    {{-- Área / Cargo --}}
                                    <td class="table-cell">
                                        <p class="truncate text-[13px] font-medium text-gray-700 dark:text-gray-300">
                                            {{ $p->area?->nombre ?: '—' }}
                                        </p>
                                        <p class="mt-0.5 truncate text-[11px] text-gray-400 dark:text-gray-500">
                                            {{ $p->cargo?->nombre ?: '—' }}
                                        </p>
                                        @if($p->telefono)
                                            <p class="mt-0.5 text-[11px] text-tide-600 dark:text-tide-400">{{ $p->telefono }}</p>
                                        @endif
                                    </td>
                                    {{-- Datos médicos --}}
                                    <td class="table-cell">
                                        <div class="flex flex-wrap gap-1">
                                            @php
                                                $examenVigente = false;
                                                $hoy = now();
                                                foreach ($p->examenes as $ex) {
                                                    if ($ex->fecha && $ex->fecha->gt($hoy->copy()->subYear())) {
                                                        $examenVigente = true; break;
                                                    }
                                                }
                                                $tieneExamen = $p->examenes->isNotEmpty();
                                                $examenVencido = $tieneExamen && !$examenVigente;
                                            @endphp
                                            @if($examenVigente)
                                                <span class="chip bg-palm-50 text-palm-700 dark:bg-palm-950/30 dark:text-palm-300">Exámenes OK</span>
                                            @elseif($examenVencido)
                                                <span class="chip bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-300">Exámenes vencidos</span>
                                            @endif
                                            @if($p->vacunas)
                                                <span class="chip bg-ocean-50 text-ocean-700 dark:bg-ocean-950/30 dark:text-ocean-300">Vacunado</span>
                                            @endif
                                            @if($p->fichas_anteriores)
                                                <span class="chip bg-tide-50 text-tide-700 dark:bg-tide-950/30 dark:text-tide-300">Ficha {{ $p->fichas_anteriores }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Acciones --}}
                                    <td class="table-cell">
                                        <div class="flex items-center justify-end gap-1">
                                            <button wire:click="abrirModal({{ $p->id }})"
                                                class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-ocean-600 transition hover:bg-ocean-50 dark:text-ocean-400 dark:hover:bg-ocean-950/20"
                                                title="Editar">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>
                                            <button wire:click="alternar({{ $p->id }})"
                                                class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-gray-500 transition hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800"
                                                title="{{ $p->activo ? 'Desactivar' : 'Activar' }}">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            </button>
                                            <button wire:click="solicitarEliminar({{ $p->id }})"
                                                class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20"
                                                title="Eliminar">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- FICHA MÉDICA — Expandable detail row --}}
                                @if($this->pacienteDetalleId === $p->id)
                                    @php $det = $p; $examenes = $this->examenesEstado; @endphp
                                    <tr>
                                        <td colspan="4" class="border-b border-gray-100 bg-gradient-to-br from-gray-50/80 via-white to-white px-5 py-4 dark:border-gray-800 dark:from-gray-950/50 dark:via-gray-900 dark:to-gray-900">
                                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                                {{-- Exámenes ocupacionales --}}
                                                <div class="rounded-xl border border-gray-100 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Exámenes</p>
                                                    <div class="mt-1.5 space-y-1">
                                                        @foreach($examenes as $ex)
                                                            <span class="chip-sm
                                                                {{ $ex['estado'] === 'vigente' ? 'bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400' : '' }}
                                                                {{ $ex['estado'] === 'vencido' ? 'bg-red-50 text-red-700 dark:bg-red-950/20 dark:text-red-400' : '' }}
                                                                {{ $ex['estado'] === 'pendiente' ? 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' : '' }}">
                                                                {{ $ex['nombre'] }}
                                                                <span class="font-normal opacity-70">{{ $ex['fecha'] ? "({$ex['fecha']})" : '—' }}</span>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- Visitas anuales --}}
                                                <div class="rounded-xl border border-gray-100 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Fichas anuales</p>
                                                    <div class="mt-1.5 flex flex-wrap gap-1">
                                                        @foreach(range(2021, 2026) as $anio)
                                                            @php $vis = $det->visitas->firstWhere('anio', $anio); @endphp
                                                            <span class="chip-sm {{ $vis ? 'bg-ocean-50 text-ocean-700 dark:bg-ocean-950/20 dark:text-ocean-400' : 'bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-500' }}">
                                                                {{ $anio }}: {{ $vis?->fecha?->format('d/m/Y') ?: '—' }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- Historial médico --}}
                                                <div class="rounded-xl border border-gray-100 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Historial</p>
                                                    <div class="mt-1.5 space-y-1.5">
                                                        @if($det->patologias)
                                                            <div class="flex items-start gap-1.5 rounded-lg border border-coral-100 bg-coral-50/50 p-2 dark:border-coral-900/30 dark:bg-coral-950/10">
                                                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-coral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                                                <p class="text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">{{ $det->patologias }}</p>
                                                            </div>
                                                        @endif
                                                        @if($det->antecedentes)
                                                            <div class="flex items-start gap-1.5 rounded-lg border border-sand-100 bg-sand-50/50 p-2 dark:border-sand-900/30 dark:bg-sand-950/10">
                                                                <svg class="mt-0.5 h-3.5 w-3.5 shrink-0 text-sand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                <p class="text-[11px] leading-relaxed text-gray-700 dark:text-gray-300">{{ $det->antecedentes }}</p>
                                                            </div>
                                                        @endif
                                                        @if(!$det->patologias && !$det->antecedentes)
                                                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Sin historial registrado</p>
                                                        @endif
                                                    </div>
                                                </div>

                                                {{-- Información adicional --}}
                                                <div class="rounded-xl border border-gray-100 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                                                    <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Info</p>
                                                    <div class="mt-1.5 space-y-1 text-[11px] text-gray-600 dark:text-gray-400">
                                                        @if($det->telefono)
                                                            <p class="flex items-center gap-1"><span class="font-medium text-tide-600 dark:text-tide-400">Tel:</span> {{ $det->telefono }}</p>
                                                        @endif
                                                        @if($det->vacunas)
                                                            <p><span class="font-medium">Vacunas:</span> {{ $det->vacunas }}</p>
                                                        @endif
                                                        @if($det->fichas_anteriores)
                                                            <p><span class="font-medium">Fichas ant.:</span> {{ $det->fichas_anteriores }}</p>
                                                        @endif
                                                        @if($det->fecha_ingreso)
                                                            <p><span class="font-medium">Ingreso:</span> {{ $det->fecha_ingreso->format('d/m/Y') }}</p>
                                                        @endif
                                                        @if($det->observaciones)
                                                            <p class="text-gray-400 dark:text-gray-500">{{ $det->observaciones }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @php
                    $totalP = $this->totalPacientes;
                    $paginas = $this->totalPaginas;
                    $actual = $this->pagina;
                    $desde = ($actual - 1) * $this->porPagina + 1;
                    $hasta = min($actual * $this->porPagina, $totalP);
                @endphp
                @if($paginas > 1)
                    <div class="flex items-center justify-between gap-3 border-t border-gray-50 px-5 py-3 dark:border-gray-800">
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
                                @if($inicio > 2)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">…</span>@endif
                            @endif
                            @for($i = $inicio; $i <= $fin; $i++)
                                <button wire:click="irPagina({{ $i }})"
                                    class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition {{ $i === $actual ? 'bg-ocean-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800' }}">
                                    {{ $i }}
                                </button>
                            @endfor
                            @if($fin < $paginas)
                                @if($fin < $paginas - 1)<span class="px-0.5 text-xs text-gray-300 dark:text-gray-600">…</span>@endif
                                <button wire:click="irPagina({{ $paginas }})" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-400 transition hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-200">{{ $paginas }}</button>
                            @endif
                            <button wire:click="irPagina({{ $actual + 1 }})" @if($actual >= $paginas) disabled @endif
                                class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                @endif
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-300 dark:bg-gray-800 dark:text-gray-600">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h4 class="mt-5 text-sm font-bold text-gray-700 dark:text-gray-200">Sin resultados</h4>
                    <p class="mt-1.5 max-w-xs text-xs leading-relaxed text-gray-400 dark:text-gray-500">No se encontraron pacientes con los filtros actuales.<br>Probá ajustando los criterios de búsqueda.</p>
                </div>
            @endif
        </section>

        {{-- ============================================================
        FORM MODAL
        ============================================================ --}}
        @if($modalAbierto)
            <div class="modal-overlay" wire:click.self="cerrarModal" x-data
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-on:keydown.escape.window="$wire.cerrarModal()">
                <div class="modal-panel !max-w-3xl w-[calc(100%-0.5rem)] sm:!w-full mx-auto" @click.stop>
                    <div class="{{ $editandoId ? 'modal-accent-sand' : 'modal-accent-ocean' }}"></div>
                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-3 sm:px-6 sm:py-4 dark:border-gray-800">
                        <div class="flex min-w-0 items-center gap-2.5 sm:gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg sm:h-9 sm:w-9 sm:rounded-xl
                                {{ $editandoId
                                    ? 'bg-sand-100 text-sand-600 ring-1 ring-sand-200 dark:bg-sand-950/30 dark:text-sand-400 dark:ring-sand-800'
                                    : 'bg-ocean-100 text-ocean-600 ring-1 ring-ocean-200 dark:bg-ocean-950/30 dark:text-ocean-400 dark:ring-ocean-800' }}">
                                <svg class="h-4 w-4 sm:h-4.5 sm:w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $editandoId ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 4v16m8-8H4' }}"/>
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-sm font-bold text-gray-900 sm:text-base dark:text-white">
                                    {{ $editandoId ? 'Editar ficha médica' : 'Nuevo registro' }}
                                </h3>
                                <p class="hidden text-[11px] text-gray-400 sm:block sm:text-xs dark:text-gray-500">
                                    {{ $editandoId ? 'Modifique los campos necesarios' : 'Complete los datos del colaborador o paciente' }}
                                </p>
                            </div>
                        </div>
                        <button type="button" wire:click="cerrarModal"
                            class="shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 sm:p-2 dark:hover:bg-gray-800 dark:hover:text-gray-300">
                            <svg class="h-4.5 w-4.5 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Form body --}}
                    <form wire:submit.prevent="guardar"
                        class="scroll-thin max-h-[65vh] overflow-y-auto divide-y divide-gray-50 sm:max-h-[70vh] dark:divide-gray-800/50">

                        {{-- SECTION: Datos personales --}}
                        <div class="form-section">
                            <span class="form-section-label text-ocean-600 dark:text-ocean-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Datos personales
                            </span>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Nombres completos *</label>
                                <input wire:model="nombres" placeholder="Nombres y apellidos" class="input" required>
                                @error('nombres')<p class="mt-1 text-[11px] font-medium text-red-500">{{ $message }}</p>@enderror
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Cédula</label>
                                    <input wire:model="cedula" placeholder="Sin cédula" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Edad</label>
                                    <input type="number" wire:model="edad" placeholder="—" class="input" min="0" max="150">
                                </div>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Área</label>
                                    <select wire:model="area_id" class="input">
                                        <option value="">— Seleccionar —</option>
                                        @foreach ($this->areasParaSelect as $area)
                                            <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Cargo</label>
                                    <select wire:model="cargo_id" class="input">
                                        <option value="">— Seleccionar —</option>
                                        @foreach ($this->cargosParaSelect as $cargo)
                                            <option value="{{ $cargo->id }}">{{ $cargo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Fecha ingreso</label>
                                    <input type="date" wire:model="fecha_ingreso" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Tipo</label>
                                    <select wire:model="tipo" class="input">
                                        <option value="colaborador">Colaborador</option>
                                        <option value="aspirante">Aspirante</option>
                                        <option value="externo">Externo</option>
                                        <option value="paciente">Paciente</option>
                                        <option value="huesped">Huésped</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Teléfono</label>
                                    <input wire:model="telefono" placeholder="09..." class="input">
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-xs font-medium text-gray-600 dark:text-gray-400">
                                <input type="checkbox" wire:model="activo" class="h-4 w-4 rounded border-gray-300 text-ocean-600 focus:ring-ocean-500">
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
                                <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Patologías</label>
                                <textarea wire:model="patologias" rows="2" placeholder="Describa patologías conocidas..." class="input"></textarea>
                            </div>
                            <div class="grid gap-2.5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Vacunas</label>
                                    <input wire:model="vacunas" placeholder="Ej: COVID-19, Influenza" class="input">
                                </div>
                                <div>
                                    <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Fichas anteriores</label>
                                    <input wire:model="fichas_anteriores" placeholder="Ej: Desde 2020" class="input">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Antecedentes</label>
                                <textarea wire:model="antecedentes" rows="2" placeholder="Antecedentes médicos relevantes..." class="input"></textarea>
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
                                        <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</label>
                                        <input type="date" wire:model="examenesFechas.{{ $key }}" class="input">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECTION: Visitas médicas anuales --}}
                        <div x-data="{ abierto: {{ $editandoId ? 'true' : 'false' }} }">
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
                                            <label class="mb-1 block text-[10px] font-bold uppercase text-tide-600 dark:text-tide-400">{{ $anio }}</label>
                                            <input type="date" wire:model="visitasFechas.{{ $anio }}" class="input-xs border-tide-200 dark:border-tide-800">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Observaciones --}}
                        <div class="form-section !pb-2">
                            <label class="mb-1 block text-[11px] font-bold uppercase text-gray-500 dark:text-gray-400">Observaciones</label>
                            <textarea wire:model="observaciones" rows="2" placeholder="Notas adicionales..." class="input"></textarea>
                        </div>
                    </form>

                    {{-- ACTIONS --}}
                    <div class="flex items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 sm:gap-2.5 sm:px-6 sm:py-4 dark:border-gray-800">
                        <button type="button" wire:click="cerrarModal"
                            class="btn-outline px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            Cancelar
                        </button>
                        <button type="button" wire:click="guardar"
                            class="btn-primary px-3 py-2 text-xs sm:px-4 sm:py-2.5 sm:text-sm">
                            <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="hidden sm:inline">{{ $editandoId ? 'Actualizar ficha' : 'Guardar registro' }}</span>
                            <span class="sm:hidden">{{ $editandoId ? 'Actualizar' : 'Guardar' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================================================
        DELETE CONFIRMATION MODAL
        ============================================================ --}}
        @if($modalEliminarAbierto)
            <div class="modal-overlay" wire:click.self="cancelarEliminar" x-data
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-on:keydown.escape.window="$wire.cancelarEliminar()">
                <div class="modal-panel w-[calc(100%-1rem)] sm:max-w-sm mx-auto" @click.stop>
                    <div class="modal-accent" style="background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);"></div>
                    <div class="bg-gradient-to-br from-red-50 via-white to-white px-4 py-4 text-center sm:px-6 sm:py-5 dark:from-red-950/30 dark:via-gray-900 dark:to-gray-900">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 ring-4 ring-red-50 sm:h-14 sm:w-14 dark:bg-red-950/30 dark:ring-red-950/10">
                            <svg class="h-6 w-6 text-red-500 sm:h-7 sm:w-7 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        </div>
                        <h4 class="mt-3 text-sm font-bold text-gray-900 sm:mt-4 sm:text-base dark:text-white">¿Eliminar este paciente?</h4>
                        <p class="mt-1.5 text-xs leading-relaxed text-gray-500 sm:mt-2 sm:text-sm dark:text-gray-400">
                            Esta acción es permanente y no se puede deshacer. Se eliminará todo el historial médico asociado.
                        </p>
                    </div>
                    <div class="flex border-t border-gray-100 dark:border-gray-800">
                        <button wire:click="cancelarEliminar"
                            class="flex-1 border-r border-gray-100 px-3 py-3 text-xs font-semibold text-gray-600 transition hover:bg-gray-50 sm:px-4 sm:py-3.5 sm:text-sm dark:border-gray-800 dark:text-gray-400 dark:hover:bg-gray-950">
                            Cancelar
                        </button>
                        <button wire:click="confirmarEliminar"
                            class="flex-1 px-3 py-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 sm:px-4 sm:py-3.5 sm:text-sm dark:text-red-400 dark:hover:bg-red-950/20">
                            Sí, eliminar
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
