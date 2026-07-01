<x-filament-panels::page>
    <!-- Encabezado Principal (Welcome Card Premium) -->
    <div class="mb-8 relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-600 to-indigo-900 p-8 shadow-lg sm:p-10">
        <!-- Decoraciones de fondo (Micro-animaciones) -->
        <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute -bottom-10 -left-10 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
        
        <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
            <!-- Título y Subtítulo -->
            <div class="transition-all duration-300 hover:translate-x-2">
                <h2 class="flex items-center gap-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md shadow-sm">
                        <x-heroicon-o-heart class="h-7 w-7 text-white" />
                    </div>
                    Dashboard de Médico
                </h2>
                <p class="mt-4 text-sm font-medium text-primary-100 sm:text-base">
                    @if ($this->total)
                        Mostrando datos desde el {{ \Carbon\Carbon::parse($this->minFecha)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($this->maxFecha)->format('d/m/Y') }}
                    @else
                        Sin datos registrados actualmente
                    @endif
                </p>
            </div>

            <!-- Reloj -->
            <div class="flex items-center gap-4 rounded-2xl bg-white/10 px-6 py-4 backdrop-blur-md border border-white/20 shadow-inner transition-all duration-300 hover:scale-105" x-data="{ time: new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }" x-init="setInterval(() => time = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }), 1000)">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-white/20 text-white shadow-sm">
                    <x-heroicon-o-clock class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-bold tracking-wider text-primary-200 uppercase">{{ now()->translatedFormat('l, d \d\e F') }}</p>
                    <p class="text-2xl font-black tracking-tight text-white" x-text="time"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas (Colores Pasteles) -->
    <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Resumen de Atenciones</h3>
    <div class="mb-8 grid gap-6 sm:grid-cols-4">
        <!-- Atenciones -->
        <x-stat-card 
            title="Atenciones totales" 
            :value="number_format($this->totalAtenciones, 0, ',', '.')" 
            icon="heroicon-o-document-text" 
            color="primary" 
        />

        <!-- Pacientes -->
        <x-stat-card 
            title="Pacientes únicos" 
            :value="number_format($this->totalPacientes, 0, ',', '.')" 
            icon="heroicon-o-user-group" 
            color="emerald" 
        />

        <!-- Último día -->
        <x-stat-card 
            title="Último día" 
            :value="\Carbon\Carbon::parse($this->ultimaFechaStr)->format('d/m/Y')" 
            icon="heroicon-o-calendar-days" 
            color="amber" 
        />

        <!-- Kardex / Días cubiertos -->
        <x-stat-card 
            title="Días cubiertos" 
            :value="number_format($this->diasCubiertos, 0, ',', '.')" 
            icon="heroicon-o-squares-2x2" 
            color="rose" 
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
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-mensual' })" class="flex items-center justify-between rounded-2xl border border-indigo-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-indigo-900 dark:bg-gray-900 dark:hover:bg-indigo-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400">
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
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-medicamentos-usados' })" class="flex items-center justify-between rounded-2xl border border-violet-200 bg-white p-4 shadow-sm transition hover:border-violet-300 hover:bg-violet-50 focus:outline-none focus:ring-2 focus:ring-violet-500 dark:border-violet-900 dark:bg-gray-900 dark:hover:bg-violet-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-violet-100 text-violet-600 dark:bg-violet-900/50 dark:text-violet-400">
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
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-movimientos' })" class="flex items-center justify-between rounded-2xl border border-sky-200 bg-white p-4 shadow-sm transition hover:border-sky-300 hover:bg-sky-50 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:border-sky-900 dark:bg-gray-900 dark:hover:bg-sky-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400">
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
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-medicinas' })" class="flex items-center justify-between rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm transition hover:border-emerald-300 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-emerald-900 dark:bg-gray-900 dark:hover:bg-emerald-950/40">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
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

            @if ($this->equipos->isNotEmpty())
                <button type="button" x-on:click="$dispatch('open-modal', { id: 'modal-kardex-equipos' })" class="flex items-center justify-between rounded-2xl border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:hover:bg-gray-800/80">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                            <x-heroicon-o-briefcase class="h-5 w-5" />
                        </div>
                        <div class="text-left">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">Kardex de Equipos</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Inventario de instrumental</p>
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
                <select wire:model.live="fechaSeleccionada" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
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
                        <div class="flex items-center justify-between rounded-lg bg-orange-50 px-3 py-2 dark:bg-orange-950/20">
                            <span class="text-xs text-orange-600 dark:text-orange-400">Con certificado</span>
                            <span class="text-sm font-bold text-orange-700 dark:text-orange-300">{{ $this->resumenDelDia->conCertificado }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-emerald-50 px-3 py-2 dark:bg-emerald-950/20">
                            <span class="text-xs text-emerald-600 dark:text-emerald-400">Sin certificado</span>
                            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ $this->resumenDelDia->sinCertificado }}</span>
                        </div>
                    </div>
                </div>

                {{-- LISTA DE ATENCIONES POR AREA --}}
                <div class="border-t border-gray-100 px-5 pb-4 dark:border-gray-800">
                    @foreach ($this->atencionesPorArea as $area => $items)
                        <div class="mt-3 first:mt-1">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-sky-50 px-2.5 py-0.5 text-xs font-semibold text-sky-700 ring-1 ring-sky-100 dark:bg-sky-950/50 dark:text-sky-300 dark:ring-sky-900">
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
                                                <span class="shrink-0 rounded-md bg-orange-100 px-1.5 py-0.5 text-[10px] font-semibold text-orange-700 dark:bg-orange-950/50 dark:text-orange-300">{{ $at->entidadCertificado->nombre }}</span>
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

    {{-- MODALES CON LAS TABLAS --}}
    
    @if ($this->total > 0 && $this->mesesDisponibles->isNotEmpty())
        <x-filament::modal id="modal-kardex-mensual" width="5xl">
            <x-slot name="heading">Resumen KARDEX Mensual</x-slot>
            
            <div class="mt-4">
                <div class="flex items-center justify-between gap-2 mb-4">
                    <div>
                        <span class="text-sm font-semibold text-gray-950 dark:text-white">Seleccionar Mes</span>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Cambia el mes para ver el histórico de atenciones</p>
                    </div>
                    <select wire:model.live="mesSeleccionado" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
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
                    <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-4 dark:border-orange-900 dark:bg-orange-950/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">Con certificado</p>
                        <p class="mt-1 text-2xl font-bold text-orange-700 dark:text-orange-300">{{ number_format($this->resumenDelMes->conCertificado, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-violet-100 bg-violet-50/60 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">Meds. usados</p>
                        <p class="mt-1 text-2xl font-bold text-violet-700 dark:text-violet-300">{{ $this->resumenDelMes->medicamentos }}</p>
                    </div>
                </div>
            </div>
        </x-filament::modal>
    @endif

    @if ($this->medicamentosMasUsados->isNotEmpty())
        <x-filament::modal id="modal-medicamentos-usados" width="5xl">
            <x-slot name="heading">Medicamentos más usados (Histórico)</x-slot>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->medicamentosMasUsados as $med)
                    <div class="flex items-center justify-between rounded-xl border border-violet-100 bg-violet-50/50 px-3 py-2 text-sm dark:border-violet-900 dark:bg-violet-950/20">
                        <span class="truncate font-medium text-gray-900 dark:text-white">{{ $med->nombre }}</span>
                        <span class="ml-2 shrink-0 font-semibold text-violet-700 dark:text-violet-300">{{ $med->total }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::modal>
    @endif

    @if ($this->kardexAlertas->isNotEmpty())
        <x-filament::modal id="modal-alertas-stock" width="5xl">
            <x-slot name="heading">Alertas de Stock Bajo (Kardex Inventario)</x-slot>
            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->kardexAlertas as $alerta)
                    @php $saldoAlerta = $alerta->saldoActual(); @endphp
                    <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 px-3 py-2 text-sm dark:border-red-900 dark:bg-red-950/20">
                        <span class="truncate font-medium text-gray-900 dark:text-white">{{ $alerta->nombre }}</span>
                        <span class="ml-2 shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-300">{{ $saldoAlerta }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::modal>
    @endif

    @if ($this->movimientosRecientes->isNotEmpty())
        <x-filament::modal id="modal-movimientos" width="7xl">
            <x-slot name="heading">Movimientos Recientes de Medicamentos</x-slot>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="py-2 text-left text-xs font-medium text-gray-500">Fecha</th>
                            <th class="py-2 text-left text-xs font-medium text-gray-500">Medicamento</th>
                            <th class="py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                            <th class="py-2 text-right text-xs font-medium text-gray-500">Saldo</th>
                            <th class="py-2 text-left text-xs font-medium text-gray-500">Responsable</th>
                            <th class="py-2 text-left text-xs font-medium text-gray-500">Paciente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->movimientosRecientes as $mov)
                            <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $mov->kardex?->nombre ?? $mov->producto?->nombre ?? '-' }}</td>
                                <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $mov->cantidad }}</td>
                                <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $mov->origen }}</td>
                                <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->personal_responsable ?: '-' }}</td>
                                <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->observacion ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::modal>
    @endif

    @if ($this->kardexActual->isNotEmpty())
        <x-filament::modal id="modal-kardex-medicinas" width="7xl">
            <x-slot name="heading">Kardex Inventario — Medicinas</x-slot>
            <div class="mt-4 overflow-x-auto max-h-[70vh]">
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
        </x-filament::modal>

        @if ($this->equipos->isNotEmpty())
            <x-filament::modal id="modal-kardex-equipos" width="3xl">
                <x-slot name="heading">Kardex Inventario — Equipos</x-slot>
                <div class="mt-4 overflow-x-auto max-h-[60vh]">
                    <table class="min-w-full text-sm">
                        <thead class="sticky top-0 bg-white dark:bg-gray-900">
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="py-2 text-left text-xs font-medium text-gray-500">Equipo</th>
                                <th class="py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->equipos as $eq)
                                <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $eq->nombre }}</td>
                                    <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $eq->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::modal>
        @endif
    @endif
</x-filament-panels::page>
