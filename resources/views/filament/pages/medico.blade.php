<x-filament-panels::page>
    <!-- Encabezado Principal (Hero Pastel) -->
    <x-hero-card
        title="Dashboard de Médico"
        icon="heroicon-o-heart"
        color="coral"
        :subtitle="$this->total
            ? 'Mostrando datos desde el ' . \Carbon\Carbon::parse($this->minFecha)->format('d/m/Y') . ' al ' . \Carbon\Carbon::parse($this->maxFecha)->format('d/m/Y')
            : 'Sin datos registrados actualmente'"
    />

    <!-- Tarjetas de Estadísticas (Colores Pasteles) -->
    <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Resumen de Atenciones</h3>
    <div class="mb-8 grid gap-6 sm:grid-cols-4">
        <!-- Atenciones -->
        <x-stat-card
            title="Atenciones totales"
            :value="number_format($this->totalAtenciones, 0, ',', '.')"
            icon="heroicon-o-document-text"
            color="brand"
        />

        <!-- Pacientes -->
        <x-stat-card
            title="Pacientes únicos"
            :value="number_format($this->totalPacientes, 0, ',', '.')"
            icon="heroicon-o-user-group"
            color="ocean"
        />

        <!-- Último día -->
        <x-stat-card
            title="Último día"
            :value="\Carbon\Carbon::parse($this->ultimaFechaStr)->format('d/m/Y')"
            icon="heroicon-o-calendar-days"
            color="coral"
        />

        <!-- Kardex / Días cubiertos -->
        <x-stat-card
            title="Días cubiertos"
            :value="number_format($this->diasCubiertos, 0, ',', '.')"
            icon="heroicon-o-squares-2x2"
            color="palm"
        />
    </div>

    @if ($this->total === 0)
        <!-- Empty State -->
        <div class="my-10 flex flex-col items-center justify-center rounded-3xl border border-dashed border-gray-300 bg-gray-50/50 py-16 dark:border-gray-800 dark:bg-gray-900/50">
            <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/30 dark:text-primary-400">
                <x-heroicon-o-clipboard-document-list class="h-10 w-10" />
            </div>
            <h3 class="text-lg font-bold text-gray-950 dark:text-white">Aún no hay datos médicos</h3>
            <p class="mt-2 text-center text-sm text-gray-500 max-w-md dark:text-gray-400">
                El dashboard está vacío. Comenzá registrando atenciones manualmente desde Partes Diarios.
            </p>
            <a href="/admin/medico/partes-diarios" class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                <x-heroicon-m-plus class="h-5 w-5" />
                Registrar atención
            </a>
        </div>
    @endif

    {{-- BOTONES DE ACCESO A REPORTES DETALLADOS --}}
    @if ($this->total > 0)
        <h3 class="mb-4 mt-4 text-lg font-semibold text-gray-950 dark:text-white">Reportes Detallados</h3>
        <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            
            @if ($this->mesesDisponibles->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-mensual' })" class="flex items-center justify-between rounded-2xl border border-tide-200 bg-white p-4 shadow-sm transition hover:border-tide-300 hover:bg-tide-50 focus:outline-none focus:ring-2 focus:ring-tide-500 dark:border-tide-900 dark:bg-gray-900 dark:hover:bg-tide-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-tide-100 text-tide-600 dark:bg-tide-900/50 dark:text-tide-400">
                            <x-heroicon-o-calendar-days class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Resumen Mensual</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Atenciones y pacientes por mes</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif

            @if ($this->medicamentosMasUsados->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-medicamentos-usados' })" class="flex items-center justify-between rounded-2xl border border-tide-200 bg-white p-4 shadow-sm transition hover:border-tide-300 hover:bg-tide-50 focus:outline-none focus:ring-2 focus:ring-tide-500 dark:border-tide-900 dark:bg-gray-900 dark:hover:bg-tide-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-tide-100 text-tide-600 dark:bg-tide-900/50 dark:text-tide-400">
                            <x-heroicon-o-star class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Medicamentos más usados</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Histórico de consumos</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif

            @if ($this->kardexAlertas->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-alertas-stock' })" class="flex items-center justify-between rounded-2xl border border-red-200 bg-white p-4 shadow-sm transition hover:border-red-300 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 dark:border-red-900 dark:bg-gray-900 dark:hover:bg-red-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-400">
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Alertas de Stock Bajo</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $this->kardexAlertas->count() }} alertas en inventario</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif

            @if ($this->movimientosRecientes->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-movimientos' })" class="flex items-center justify-between rounded-2xl border border-ocean-200 bg-white p-4 shadow-sm transition hover:border-ocean-300 hover:bg-ocean-50 focus:outline-none focus:ring-2 focus:ring-ocean-500 dark:border-ocean-900 dark:bg-gray-900 dark:hover:bg-ocean-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-ocean-100 text-ocean-600 dark:bg-ocean-900/50 dark:text-ocean-400">
                            <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Movimientos Recientes</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Últimos ingresos y egresos</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif

            @if ($this->kardexActual->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-medicinas' })" class="flex items-center justify-between rounded-2xl border border-palm-200 bg-white p-4 shadow-sm transition hover:border-palm-300 hover:bg-palm-50 focus:outline-none focus:ring-2 focus:ring-palm-500 dark:border-palm-900 dark:bg-gray-900 dark:hover:bg-palm-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-palm-100 text-palm-600 dark:bg-palm-900/50 dark:text-palm-400">
                            <x-heroicon-o-beaker class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Kardex de Medicinas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Inventario y saldos actuales</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif

            @if ($this->insumos->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-insumos' })" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800/80">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            <x-heroicon-o-building-office-2 class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Kardex de Insumos</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Insumos y suministros médicos</p>
                        </div>
                    </div>
                    <x-heroicon-m-chevron-right class="h-5 w-5 text-gray-400" />
                </button>
            @endif
        </div>
    @endif

    @if ($this->total > 0 && $this->fechasDisponibles->isNotEmpty())
        {{-- SELECTOR DE DIA --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-2 px-5 py-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atenciones del dia</span>
                    @if ($this->fechaSeleccionada)
                        <p class="mt-0.5 text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($this->fechaSeleccionada)->translatedFormat('l, d \d\e F \d\e Y') }}</p>
                    @endif
                </div>
                <select wire:model.live="fechaSeleccionada" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-ocean-500 focus:ring-ocean-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @foreach ($this->fechasDisponibles as $f)
                        @php $fd = \Carbon\Carbon::parse($f)->format('Y-m-d'); @endphp
                        <option value="{{ $fd }}">{{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}</option>
                    @endforeach
                </select>
            </div>

            {{-- CARDS DEL DIA --}}
            @if ($this->fechaSeleccionada && $this->atencionesDelDia->isNotEmpty())
                <div class="border-t border-gray-100 px-5 pb-1 dark:border-gray-800">
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-950/40">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Total atenciones</span>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">{{ $this->resumenDelDia->atenciones }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-950/40">
                            <span class="text-xs text-gray-500 dark:text-gray-400">Areas</span>
                            <span class="text-sm font-bold text-gray-950 dark:text-white">{{ $this->resumenDelDia->areas }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-coral-50 px-3 py-2 dark:bg-coral-950/20">
                            <span class="text-xs text-coral-600 dark:text-coral-400">Con certificado</span>
                            <span class="text-sm font-bold text-coral-700 dark:text-coral-300">{{ $this->resumenDelDia->conCertificado }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-palm-50 px-3 py-2 dark:bg-palm-950/20">
                            <span class="text-xs text-palm-600 dark:text-palm-400">Sin certificado</span>
                            <span class="text-sm font-bold text-palm-700 dark:text-palm-300">{{ $this->resumenDelDia->sinCertificado }}</span>
                        </div>
                    </div>
                </div>

                {{-- LISTA DE ATENCIONES POR AREA --}}
                <div class="border-t border-gray-100 px-5 pb-4 dark:border-gray-800">
                    @foreach ($this->atencionesPorArea as $area => $items)
                        <div class="mt-3 first:mt-1">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-ocean-50 px-2.5 py-0.5 text-xs font-semibold text-ocean-700 ring-1 ring-ocean-100 dark:bg-ocean-950/50 dark:text-ocean-300 dark:ring-ocean-900">
                                    {{ $area }}
                                </span>
                                <span class="text-xs text-gray-400">{{ count($items) }}</span>
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($items as $at)
                                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 px-3 py-2.5 text-sm dark:border-gray-800 dark:bg-gray-950/40">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="truncate font-medium text-gray-950 dark:text-white">{{ $at->nombres }}</p>
                                            @if ($at->entidadCertificado)
                                                <span class="shrink-0 rounded-md bg-coral-100 px-1.5 py-0.5 text-[10px] font-semibold text-coral-700 dark:bg-coral-950/50 dark:text-coral-300">{{ $at->entidadCertificado->nombre }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $at->cargo?->nombre ?: '' }}</p>
                                        @if ($at->causa)
                                            <p class="mt-1 truncate text-xs font-medium text-gray-600 dark:text-gray-300">{{ $at->causa->nombre }}</p>
                                        @endif
                                        @if ($at->diagnostico)
                                            <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ Str::limit($at->diagnostico->nombre, 60) }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ============================================================
    MODALES CON LAS TABLAS — ESTANDARIZADOS
    ============================================================ --}}

    @if ($this->total > 0 && $this->mesesDisponibles->isNotEmpty())
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail.id === 'modal-kardex-mensual') open = true"
             x-on:keydown.escape.window="open = false"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-overlay" @click.self="open = false">
            <div class="modal-panel !max-w-4xl w-full mx-auto" @click.stop>
                <div class="modal-accent-ocean"></div>
                <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-tide-400 to-tide-600 text-white shadow-md shadow-tide-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Resumen KARDEX Mensual</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Atenciones y pacientes por mes</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">Seleccionar Mes</span>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Cambia el mes para ver el histórico de atenciones</p>
                        </div>
                        <select wire:model.live="mesSeleccionado" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-tide-500 focus:ring-tide-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                            @foreach ($this->mesesDisponibles as $m)
                                <option value="{{ $m->ym }}">{{ $m->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atenciones</p>
                            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->atenciones, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pacientes</p>
                            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->pacientes, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-950/40">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Días con atención</p>
                            <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->dias, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-coral-100 bg-coral-50/60 p-4 dark:border-coral-900 dark:bg-coral-950/20">
                            <p class="text-xs font-semibold uppercase tracking-wide text-coral-600 dark:text-coral-400">Con certificado</p>
                            <p class="mt-1 text-2xl font-bold text-coral-700 dark:text-coral-300">{{ number_format($this->resumenDelMes->conCertificado, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl border border-tide-100 bg-tide-50/60 p-4 dark:border-tide-900 dark:bg-tide-950/20">
                            <p class="text-xs font-semibold uppercase tracking-wide text-tide-600 dark:text-tide-400">Meds. usados</p>
                            <p class="mt-1 text-2xl font-bold text-tide-700 dark:text-tide-300">{{ $this->resumenDelMes->medicamentos }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($this->medicamentosMasUsados->isNotEmpty())
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail.id === 'modal-medicamentos-usados') open = true"
             x-on:keydown.escape.window="open = false"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-overlay" @click.self="open = false">
            <div class="modal-panel !max-w-3xl w-full mx-auto" @click.stop>
                <div class="modal-accent-ocean"></div>
                <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ocean-400 to-ocean-600 text-white shadow-md shadow-ocean-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Medicamentos más usados</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Histórico de consumos en el dispensario</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($this->medicamentosMasUsados as $med)
                            <div class="flex items-center justify-between rounded-xl border border-tide-100 bg-tide-50/50 px-3 py-2 text-sm dark:border-tide-900 dark:bg-tide-950/20">
                                <span class="truncate font-medium text-gray-900 dark:text-white">{{ $med->nombre }}</span>
                                <span class="ml-2 shrink-0 font-semibold text-tide-700 dark:text-tide-300">{{ $med->total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($this->kardexAlertas->isNotEmpty())
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail.id === 'modal-alertas-stock') open = true"
             x-on:keydown.escape.window="open = false"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-overlay" @click.self="open = false">
            <div class="modal-panel !max-w-3xl w-full mx-auto" @click.stop>
                <div class="modal-accent" style="background: linear-gradient(90deg, #ef4444, #dc2626, #b91c1c);"></div>
                <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-red-400 to-red-600 text-white shadow-md shadow-red-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Alertas de Stock Bajo</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $this->kardexAlertas->count() }} productos por debajo del stock mínimo</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @foreach ($this->kardexAlertas as $alerta)
                            @php $saldoAlerta = $alerta->saldoActual(); @endphp
                            <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 px-3 py-2 text-sm dark:border-red-900 dark:bg-red-950/20">
                                <span class="truncate font-medium text-gray-900 dark:text-white">{{ $alerta->nombre }}</span>
                                <span class="ml-2 shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-300">{{ $saldoAlerta }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($this->movimientosRecientes->isNotEmpty())
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail.id === 'modal-movimientos') open = true"
             x-on:keydown.escape.window="open = false"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-overlay" @click.self="open = false">
            <div class="modal-panel !max-w-6xl w-full mx-auto" @click.stop>
                <div class="modal-accent-ocean"></div>
                <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-ocean-400 to-ocean-600 text-white shadow-md shadow-ocean-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Movimientos Recientes</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Últimos ingresos, egresos y ajustes de inventario</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Medicamento</th>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Origen</th>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->movimientosRecientes as $mov)
                                    <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                        <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                        <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $mov->kardex?->nombre ?? $mov->producto?->nombre ?? '-' }}</td>
                                        <td class="py-2 pr-3">
                                            <span class="chip-sm {{ $mov->tipo === 'ingreso' ? 'bg-palm-100 text-palm-700' : ($mov->tipo === 'salida' ? 'bg-red-100 text-red-700' : 'bg-sand-100 text-sand-700') }}">
                                                {{ $mov->tipo }}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $mov->cantidad }}</td>
                                        <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">
                                            {{ $mov->origen === 'parte_diario' ? '🏥 Consulta' : '✋ Manual' }}
                                        </td>
                                        <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->parteDiario?->nombres ?? $mov->observacion ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($this->kardexActual->isNotEmpty())
        <div x-data="{ open: false }"
             x-on:open-modal.window="if ($event.detail.id === 'modal-kardex-medicinas') open = true"
             x-on:keydown.escape.window="open = false"
             x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="modal-overlay" @click.self="open = false">
            <div class="modal-panel !max-w-6xl w-full mx-auto" @click.stop>
                <div class="modal-accent-ocean"></div>
                <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-palm-400 to-palm-600 text-white shadow-md shadow-palm-500/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 14.25l-2.625 2.625M15 13.5l-2.625 2.625M19.5 14.25l-4.5 4.5M19.5 14.25V11.25m0 0h-3.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Kardex Inventario — Medicinas</h3>
                            <p class="text-[11px] text-gray-400 dark:text-gray-500">Inventario detallado con saldos y consumos</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                        class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                        aria-label="Cerrar modal">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="scroll-thin max-h-[70vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-white dark:bg-gray-900">
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Medicina</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Saldo Ant.</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Ingresos</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Egresos</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Total Ref.</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Consumos</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Saldo Real</th>
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Caducidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->kardexActual as $k)
                                    @php
                                        $consumos = $k->totalSalidas();
                                        $saldoReal = $k->saldoActual();
                                    @endphp
                                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                        <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $k->nombre }}</td>
                                        <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $k->saldo_anterior }}</td>
                                        <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $k->ingresos }}</td>
                                        <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $k->egresos }}</td>
                                        <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $k->total }}</td>
                                        <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $consumos }}</td>
                                        <td class="py-2 pr-3 text-right">
                                            <span class="font-semibold {{ $saldoReal <= 2 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $saldoReal }}</span>
                                        </td>
                                        <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $k->fecha_caducidad ?: '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if ($this->insumos->isNotEmpty())
            <div x-data="{ open: false }"
                 x-on:open-modal.window="if ($event.detail.id === 'modal-kardex-insumos') open = true"
                 x-on:keydown.escape.window="open = false"
                 x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="modal-overlay" @click.self="open = false">
                <div class="modal-panel !max-w-lg w-full mx-auto" @click.stop>
                    <div class="modal-accent-ocean"></div>
                    <div class="flex items-center justify-between gap-2 px-5 py-4 sm:px-6">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-gray-400 to-gray-600 text-white shadow-md shadow-gray-500/20">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h3 class="truncate text-[15px] font-bold text-gray-900 dark:text-white">Kardex de Insumos</h3>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">Insumos y suministros médicos</p>
                            </div>
                        </div>
                        <button type="button" @click="open = false"
                            class="shrink-0 rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            aria-label="Cerrar modal">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="scroll-thin max-h-[60vh] overflow-y-auto bg-gray-50/50 p-4 sm:p-6 dark:bg-gray-950/20">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="sticky top-0 bg-white dark:bg-gray-900">
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Insumo</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->insumos as $eq)
                                        <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                            <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $eq->nombre }}</td>
                                            <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $eq->total }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</x-filament-panels::page>
