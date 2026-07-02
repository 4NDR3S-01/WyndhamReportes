<x-filament-panels::page>
    <x-hero-card title="Partes Diarios" subtitle="Registra y consulta las atenciones médicas diarias" icon="heroicon-o-clipboard-document-list" color="ocean" />

    <div class="page-enter space-y-5">

        {{-- ============================================================
        STATS STRIP — Compact KPI cards with icons
        ============================================================ --}}
        @php $hoy = $this->estadisticasHoy; @endphp
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            {{-- Total --}}
            <div class="stat border-gray-100 bg-white dark:border-gray-800 dark:bg-gray-900/80">
                <div class="stat-icon bg-ocean-50 text-ocean-600 dark:bg-ocean-950/30 dark:text-ocean-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-gray-900 dark:text-white">{{ $hoy['total'] }}</p>
                    <p class="stat-label text-gray-400 dark:text-gray-500">Atenciones Hoy</p>
                </div>
            </div>
            {{-- Huéspedes --}}
            <div class="stat border-sand-100 bg-sand-50/50 dark:border-sand-900/30 dark:bg-sand-950/10">
                <div class="stat-icon bg-sand-100 text-sand-600 dark:bg-sand-950/30 dark:text-sand-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a2 2 0 012-2h2a2 2 0 012 2v5m-4 0h4"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-sand-700 dark:text-sand-300">{{ $hoy['huespedes'] }}</p>
                    <p class="stat-label text-sand-500/80 dark:text-sand-500/70">Huéspedes</p>
                </div>
            </div>
            {{-- Colaboradores --}}
            <div class="stat border-palm-100 bg-palm-50/50 dark:border-palm-900/30 dark:bg-palm-950/10">
                <div class="stat-icon bg-palm-100 text-palm-600 dark:bg-palm-950/30 dark:text-palm-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-palm-700 dark:text-palm-300">{{ $hoy['colabs'] }}</p>
                    <p class="stat-label text-palm-500/80 dark:text-palm-500/70">Colaboradores</p>
                </div>
            </div>
            {{-- Certificados --}}
            <div class="stat border-coral-100 bg-coral-50/50 dark:border-coral-900/30 dark:bg-coral-950/10">
                <div class="stat-icon bg-coral-100 text-coral-600 dark:bg-coral-950/30 dark:text-coral-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-coral-700 dark:text-coral-300">{{ $hoy['conCert'] }}</p>
                    <p class="stat-label text-coral-500/80 dark:text-coral-500/70">Con Certificado</p>
                </div>
            </div>
            {{-- Áreas --}}
            <div class="stat border-tide-100 bg-tide-50/50 dark:border-tide-900/30 dark:bg-tide-950/10">
                <div class="stat-icon bg-tide-100 text-tide-600 dark:bg-tide-950/30 dark:text-tide-400">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="min-w-0">
                    <p class="stat-value text-tide-700 dark:text-tide-300">{{ $hoy['areas'] }}</p>
                    <p class="stat-label text-tide-500/80 dark:text-tide-500/70">Áreas Activas</p>
                </div>
            </div>
        </div>

        {{-- ============================================================
        MAIN LAYOUT — 2 columns
        ============================================================ --}}
        <div class="grid gap-5 xl:grid-cols-[420px_1fr] xl:items-start">

            {{-- ============================================================
            LEFT — REGISTRATION FORM
            ============================================================ --}}
            <section class="card overflow-hidden">
                {{-- Header --}}
                <div class="card-header">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl
                            {{ $editandoId
                                ? 'bg-sand-100 text-sand-600 ring-1 ring-sand-200 dark:bg-sand-950/30 dark:text-sand-400 dark:ring-sand-800'
                                : 'bg-ocean-100 text-ocean-600 ring-1 ring-ocean-200 dark:bg-ocean-950/30 dark:text-ocean-400 dark:ring-ocean-800' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="{{ $editandoId ? 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' : 'M12 4v16m8-8H4' }}"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $editandoId ? 'Editando atención #' . $editandoId : 'Nueva atención' }}
                            </h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">
                                {{ $editandoId ? 'Modifique los campos necesarios' : 'Complete el formulario para registrar' }}
                            </p>
                        </div>
                    </div>
                    @if($editandoId)
                        <button type="button" wire:click="limpiarFormulario"
                            class="btn-ghost rounded-lg px-3 py-1.5 text-xs">
                            Cancelar
                        </button>
                    @endif
                </div>

                {{-- Form body — scrollable --}}
                <form wire:submit.prevent="guardar"
                    class="scroll-thin max-h-[calc(100vh-18rem)] overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800/50">

                    {{-- SECTION: Datos del Paciente --}}
                    <div class="form-section">
                        <span class="form-section-label text-ocean-600 dark:text-ocean-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Datos del Paciente
                        </span>

                        {{-- Fecha + Buscar paciente --}}
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Fecha</label>
                                <input type="date" wire:model="fecha" class="input">
                            </div>
                            <div class="relative" x-data="{
                                abierto: false,
                                buscar: @entangle('buscarPaciente'),
                                seleccionadoId: @entangle('pacienteId'),
                                init() {
                                    this.$watch('buscar', v => { if (v.length >= 1) this.abierto = true });
                                    this.$watch('seleccionadoId', v => { if (v) this.abierto = false });
                                },
                                cerrar() { this.abierto = false; }
                            }" x-on:click.outside="cerrar()">
                                <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Buscar paciente</label>
                                <div class="relative">
                                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>
                                    <input type="text" x-model="buscar" placeholder="Escriba para buscar..."
                                        class="input pl-9 pr-8"
                                        x-on:focus="if(buscar.length >= 1) abierto = true"
                                        x-on:keydown.escape="cerrar()">
                                    <button type="button" x-show="buscar.length > 0"
                                        x-on:click="buscar = ''; abierto = false; seleccionadoId = null"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-0.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                {{-- Dropdown --}}
                                <div x-show="abierto"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="absolute z-20 mt-1 w-full rounded-xl border border-gray-100 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-900"
                                    style="max-height: 260px; overflow-y: auto;">
                                    @if($this->pacientes->isEmpty())
                                        <p class="px-4 py-3 text-xs text-gray-400 dark:text-gray-500">Sin resultados</p>
                                    @else
                                        @foreach ($this->pacientes as $p)
                                            <button type="button" wire:click="$set('pacienteId', {{ $p->id }})"
                                                class="flex w-full items-center gap-3 px-3.5 py-2.5 text-left text-xs transition hover:bg-ocean-50 dark:hover:bg-ocean-950/20">
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

                        {{-- Nombres --}}
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Nombre completo</label>
                            <input wire:model="nombres" placeholder="Nombres y apellidos" class="input">
                        </div>

                        {{-- FICHA MÉDICA — NOMINA data displayed when patient is selected --}}
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
                            <div class="rounded-xl border border-gray-100 bg-gradient-to-br from-gray-50/80 to-white p-3.5 dark:border-gray-800 dark:from-gray-950/50 dark:to-gray-900 space-y-2.5">
                                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Ficha Médica — NOMINA</p>

                                @if($sel->patologias)
                                    <div class="flex items-start gap-2 rounded-lg border border-red-100 bg-red-50/50 p-2.5 dark:border-red-900/30 dark:bg-red-950/10">
                                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                        <div class="min-w-0">
                                            <p class="text-[10px] font-bold uppercase text-red-600 dark:text-red-400">Patologías</p>
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
                                        <span>Ingreso: {{ $sel->fecha_ingreso->format('d/m/Y') }}</span>
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
                                                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $v->fecha?->format('d/m/Y') }}</span>
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
                        @endif

                        {{-- Edad / Área / Cargo --}}
                        <div class="grid gap-2.5 sm:grid-cols-3">
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Edad</label>
                                <input type="number" wire:model="edad" placeholder="—" class="input">
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Área</label>
                                <select wire:model="area_id" class="input">
                                    <option value="">—</option>
                                    @foreach ($this->areaCatalog as $a)
                                        <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Cargo</label>
                                <select wire:model="cargo_id" class="input">
                                    <option value="">—</option>
                                    @foreach ($this->cargoCatalog as $c)
                                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tipo paciente + Turno --}}
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Tipo de paciente</label>
                                <div class="pill-group w-full">
                                    <button type="button" wire:click="$set('tipoPaciente', 'colaborador')"
                                        class="pill {{ $tipoPaciente === 'colaborador' ? 'pill-active' : 'pill-inactive' }}">
                                        Colaborador
                                    </button>
                                    <button type="button" wire:click="$set('tipoPaciente', 'huesped')"
                                        class="pill {{ $tipoPaciente === 'huesped' ? 'pill-active ring-1 ring-sand-200 dark:ring-sand-800' : 'pill-inactive' }}">
                                        Huésped
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Turno</label>
                                <div class="pill-group w-full">
                                    @foreach(['mañana','tarde','noche'] as $t)
                                        <button type="button" wire:click="$set('turno', '{{ $turno === $t ? '' : $t }}')"
                                            class="pill capitalize {{ $turno === $t ? 'pill-active' : 'pill-inactive' }}">
                                            {{ $t }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Habitación (solo huéspedes) --}}
                        @if($tipoPaciente === 'huesped')
                            <div>
                                <label class="mb-1 block text-[10px] font-bold uppercase text-sand-600 dark:text-sand-400">Nº Habitación</label>
                                <input wire:model="habitacion" placeholder="Ej: 301"
                                    class="input border-sand-200 bg-sand-50/30 dark:border-sand-800 dark:bg-sand-950/10 focus:border-sand-400">
                            </div>
                        @endif
                    </div>

                    {{-- SECTION: Certificado Médico --}}
                    <div x-data="{ abierto: {{ ($this->tieneCertificado) ? 'true' : 'false' }} }">
                        <button type="button" x-on:click="abierto = !abierto"
                            class="flex w-full items-center justify-between gap-2 px-5 py-3.5 text-left transition hover:bg-gray-50/50 dark:hover:bg-gray-950/30">
                            <div class="flex items-center gap-2.5">
                                <span class="form-section-label text-coral-600 dark:text-coral-400 !mb-0 after:hidden">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Certificado Médico
                                </span>
                                @if($entidad_certificado_id)
                                    @php $entCert = $this->entidadCatalog->firstWhere('id', $entidad_certificado_id); @endphp
                                    <span class="chip bg-coral-100 text-coral-700 dark:bg-coral-950/30 dark:text-coral-300">
                                        {{ $entCert?->nombre ?? 'Certificado' }}
                                    </span>
                                @endif
                            </div>
                            <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="abierto && 'rotate-180'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="abierto" x-collapse class="px-5 pb-4">
                            <div class="space-y-2.5 rounded-xl border border-coral-100 bg-coral-50/30 p-3.5 dark:border-coral-900/30 dark:bg-coral-950/10">
                                <div class="grid gap-2.5 sm:grid-cols-2">
                                    <select wire:model="entidad_certificado_id" class="input-sm focus:border-coral-400">
                                        <option value="">Tipo de certificado</option>
                                        @foreach ($this->entidadCatalog as $ec)
                                            <option value="{{ $ec->id }}">{{ $ec->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <select wire:model="tipo_certificado_id" class="input-sm focus:border-coral-400">
                                        <option value="">Subsidio</option>
                                        @foreach ($this->tipoCertCatalog as $tc)
                                            <option value="{{ $tc->id }}">{{ $tc->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid gap-2.5 sm:grid-cols-2">
                                    <input type="number" step="0.5" wire:model="horas_certificado" placeholder="Horas" class="input-sm focus:border-coral-400">
                                    <input type="number" wire:model="dias_certificado" placeholder="Días" class="input-sm focus:border-coral-400">
                                </div>
                                <div class="grid gap-2.5 sm:grid-cols-2">
                                    <input type="date" wire:model="fecha_inicio_certificado" class="input-sm focus:border-coral-400">
                                    <input type="date" wire:model="fecha_fin_certificado" class="input-sm focus:border-coral-400">
                                </div>
                                <input wire:model="medico_certifica" placeholder="Médico que certifica" class="input-sm focus:border-coral-400">
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: Atención --}}
                    <div class="form-section">
                        <span class="form-section-label text-coral-600 dark:text-coral-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Atención
                        </span>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Causa</label>
                            <select wire:model="causa_id" class="input">
                                <option value="">Seleccionar causa...</option>
                                @foreach ($this->causaCatalog as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Diagnóstico</label>
                            <select wire:model="diagnostico_id" class="input">
                                <option value="">— Seleccionar —</option>
                                @foreach ($this->diagnosticoCatalog as $d)
                                    <option value="{{ $d->id }}">{{ Str::limit($d->nombre, 80) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- SECTION: Medicación Entregada --}}
                    <div class="form-section">
                        <div class="flex items-center justify-between">
                            <span class="form-section-label text-tide-600 dark:text-tide-400 after:!hidden">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                Medicación Entregada
                            </span>
                            <button type="button" wire:click="agregarMedicamento"
                                class="inline-flex items-center gap-1 rounded-lg bg-tide-50 px-2.5 py-1.5 text-[11px] font-semibold text-tide-600 transition hover:bg-tide-100 dark:bg-tide-950/20 dark:text-tide-400 dark:hover:bg-tide-950/40">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Agregar
                            </button>
                        </div>

                        @foreach ($medicamentos as $i => $m)
                            <div class="flex items-start gap-2 rounded-xl border border-gray-100 bg-gray-50/60 p-3 dark:border-gray-800 dark:bg-gray-950/40">
                                <span class="mt-2 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-tide-100 text-[10px] font-bold text-tide-600 dark:bg-tide-950/30 dark:text-tide-400">
                                    {{ $i + 1 }}
                                </span>
                                <div class="flex-1 space-y-2">
                                    <select wire:model="medicamentos.{{ $i }}.medicamento_id" class="input-sm" required>
                                        <option value="">Seleccionar medicamento...</option>
                                        @foreach ($this->medicamentosCatalog as $med)
                                            <option value="{{ $med->id }}">{{ $med->nombre }}</option>
                                        @endforeach
                                    </select>
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.01" wire:model="medicamentos.{{ $i }}.cantidad"
                                            placeholder="Cant." min="0.01" class="w-24 input-xs">
                                    </div>
                                </div>
                                <button type="button" wire:click="quitarMedicamento({{ $i }})"
                                    class="mt-0.5 shrink-0 rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 dark:hover:bg-red-950/20 dark:hover:text-red-400">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach

                        @error('medicamentos.*.medicamento_id')
                            <p class="text-[11px] font-medium text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        @error('medicamentos.*.cantidad')
                            <p class="text-[11px] font-medium text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SECTION: Observaciones --}}
                    <div class="form-section !pb-2">
                        <label class="mb-1 block text-[10px] font-bold uppercase text-gray-400 dark:text-gray-500">Observaciones</label>
                        <textarea wire:model="observacion" rows="2" placeholder="Notas adicionales..." class="input"></textarea>
                    </div>

                    {{-- ACTIONS — sticky footer --}}
                    <div class="sticky bottom-0 flex items-center gap-2.5 border-t border-gray-50 bg-white/95 px-5 py-3.5 backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
                        <button type="submit" class="btn-primary flex-1">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $editandoId ? 'Actualizar atención' : 'Guardar atención' }}
                        </button>
                        <button type="button" wire:click="limpiarFormulario" class="btn-outline">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Limpiar
                        </button>
                    </div>
                </form>
            </section>

            {{-- ============================================================
            RIGHT — ATTENTION LIST
            ============================================================ --}}
            <section class="card overflow-hidden">
                {{-- Header --}}
                <div class="card-header">
                    <div class="flex items-center gap-2.5">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Atenciones registradas</h3>
                        <span class="chip bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            {{ $this->totalPartes }}
                        </span>
                    </div>
                    <button type="button" wire:click="toggleSoloHoy"
                        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150
                        {{ $mostrarSoloHoy
                            ? 'bg-ocean-100 text-ocean-700 shadow-sm ring-1 ring-ocean-200 dark:bg-ocean-950/30 dark:text-ocean-400 dark:ring-ocean-800'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700' }}">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Solo hoy
                    </button>
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
                                    <button wire:click="$set('buscar', '')" class="ml-0.5 hover:text-ocean-900 dark:hover:text-ocean-200">&times;</button>
                                </span>
                            @endif
                            @if($this->areaFiltroId)
                                <span class="chip-sm bg-tide-50 text-tide-700 dark:bg-tide-950/20 dark:text-tide-400">
                                    {{ $this->areaCatalog->firstWhere('id', $this->areaFiltroId)?->nombre ?? 'Área' }}
                                    <button wire:click="$set('areaFiltroId', null)" class="ml-0.5 hover:text-tide-900">&times;</button>
                                </span>
                            @endif
                            @if($this->causaFiltroId)
                                <span class="chip-sm bg-sand-50 text-sand-700 dark:bg-sand-950/20 dark:text-sand-400">
                                    {{ $this->causaCatalog->firstWhere('id', $this->causaFiltroId)?->nombre ?? 'Causa' }}
                                    <button wire:click="$set('causaFiltroId', null)" class="ml-0.5 hover:text-sand-900">&times;</button>
                                </span>
                            @endif
                            @if($this->tipoPacienteFiltro)
                                <span class="chip-sm bg-palm-50 text-palm-700 dark:bg-palm-950/20 dark:text-palm-400">
                                    {{ $this->tipoPacienteFiltro }}
                                    <button wire:click="$set('tipoPacienteFiltro', null)" class="ml-0.5 hover:text-palm-900">&times;</button>
                                </span>
                            @endif
                            @if($this->estadoFiltro)
                                <span class="chip-sm bg-coral-50 text-coral-700 dark:bg-coral-950/20 dark:text-coral-400">
                                    {{ $this->estadoFiltro === 'con_certificado' ? 'Con certificado' : 'Sin certificado' }}
                                    <button wire:click="$set('estadoFiltro', null)" class="ml-0.5 hover:text-coral-900">&times;</button>
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Table --}}
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
                                    <tr class="table-row group">
                                        {{-- Paciente --}}
                                        <td class="table-cell">
                                            <div class="flex items-start gap-2.5">
                                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full
                                                    {{ $p->tipo_paciente === 'huesped' ? 'bg-sand-400 shadow-sm shadow-sand-400/30' : 'bg-palm-400 shadow-sm shadow-palm-400/30' }}"></span>
                                                <div class="min-w-0">
                                                    <p class="truncate text-[13px] font-semibold text-gray-900 dark:text-white">
                                                        {{ $p->nombres }}
                                                    </p>
                                                    <div class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-[11px] text-gray-400 dark:text-gray-500">
                                                        <span class="font-medium text-gray-500 dark:text-gray-400">{{ $p->fecha?->format('d/m/Y') }}</span>
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
                                        {{-- Acciones --}}
                                        <td class="table-cell">
                                            <div class="flex items-center justify-end gap-1 opacity-0 transition-opacity duration-150 group-hover:opacity-100">
                                                <button wire:click="editar({{ $p->id }})"
                                                    class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-ocean-600 transition hover:bg-ocean-50 dark:text-ocean-400 dark:hover:bg-ocean-950/20">
                                                    Editar
                                                </button>
                                                <button wire:click="solicitarEliminar({{ $p->id }})"
                                                    class="rounded-lg px-2.5 py-1.5 text-[11px] font-semibold text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/20">
                                                    Eliminar
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
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200">
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
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:text-gray-700 disabled:opacity-30 dark:text-gray-500 dark:hover:text-gray-200">
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
            </section>
        </div>

        {{-- ============================================================
        DELETE MODAL
        ============================================================ --}}
        @if($modalEliminarAbierto)
            <div class="modal-overlay" wire:click.self="cancelarEliminar">
                <div class="modal-panel">
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
