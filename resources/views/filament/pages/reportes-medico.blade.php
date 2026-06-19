<x-filament-panels::page>
    <div class="flex items-center gap-3 pb-6">
        <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">Reportes Medico</h2>
    </div>

    @if ($this->fechasDisponibles->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
            <svg class="h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Importa un archivo Excel para generar reportes.</p>
        </div>
    @endif

    {{-- ACCIONES DE DESCARGA --}}
    @if ($this->fechasDisponibles->isNotEmpty())
        <div class="mb-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <a href="#" wire:click.prevent="descargarKardexMensual" class="flex flex-col items-center gap-2 rounded-2xl border border-violet-200 bg-violet-50/60 p-5 text-center shadow-sm transition hover:bg-violet-100 dark:border-violet-900 dark:bg-violet-950/30 dark:hover:bg-violet-950/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"/></svg>
                </div>
                <span class="text-sm font-semibold text-violet-700 dark:text-violet-300">KARDEX Mensual</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Excel multi-hoja por mes</span>
            </a>

            <a href="#" wire:click.prevent="descargarParteDiarioRango" class="flex flex-col items-center gap-2 rounded-2xl border border-sky-200 bg-sky-50/60 p-5 text-center shadow-sm transition hover:bg-sky-100 dark:border-sky-900 dark:bg-sky-950/30 dark:hover:bg-sky-950/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-sky-700 dark:text-sky-300">Parte Diario</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Excel por rango de fechas</span>
            </a>

            <a href="#" wire:click.prevent="descargarKardex" class="flex flex-col items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5 text-center shadow-sm transition hover:bg-emerald-100 dark:border-emerald-900 dark:bg-emerald-950/30 dark:hover:bg-emerald-950/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">KARDEX Inventario</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Medicinas y equipos</span>
            </a>

            <a href="#" wire:click.prevent="descargarKardexConMovimientos" class="flex flex-col items-center gap-2 rounded-2xl border border-teal-200 bg-teal-50/60 p-5 text-center shadow-sm transition hover:bg-teal-100 dark:border-teal-900 dark:bg-teal-950/30 dark:hover:bg-teal-950/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 0 0 3-3V8a3 3 0 0 0-3-3H6a3 3 0 0 0-3 3v8a3 3 0 0 0 3 3z"/></svg>
                </div>
                <span class="text-sm font-semibold text-teal-700 dark:text-teal-300">KARDEX Movimientos</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Saldo real y salidas</span>
            </a>

            <a href="#" wire:click.prevent="descargarTodo" class="flex flex-col items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 text-center shadow-sm transition hover:bg-gray-100 dark:border-gray-800 dark:bg-gray-950/30 dark:hover:bg-gray-950/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-600 text-white shadow-sm">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                </div>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Todo</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">Parte diario completo</span>
            </a>
        </div>

        {{-- RANGO DE FECHAS --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-end gap-3 px-5 py-4">
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Desde</label>
                    <input type="date" wire:model="desde" class="mt-1 block rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('desde') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Hasta</label>
                    <input type="date" wire:model="hasta" class="mt-1 block rounded-lg border-gray-300 text-sm shadow-sm focus:border-sky-500 focus:ring-sky-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @error('hasta') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
                <a href="#" wire:click.prevent="descargarParteDiarioRango" class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700">
                    <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                    Aplicar
                </a>
            </div>
        </div>
    @endif

    {{-- KARDEX POR MES - PRINCIPAL --}}
    @if ($this->kardexMensualDetallado->isNotEmpty())
        <div class="rounded-2xl border border-violet-200 bg-white shadow-sm dark:border-violet-900 dark:bg-gray-900">
            <div class="border-b border-violet-100 px-5 py-4 dark:border-violet-900">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">KARDEX Mensual</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $this->kardexMensualDetallado->count() }} meses &middot; Generado desde los partes diarios</p>
                    </div>
                    <a href="#" wire:click.prevent="descargarKardexMensual" class="inline-flex items-center rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-violet-700">
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3v-1m-4-4-4 4m0 0-4-4m4 4V4"/></svg>
                        Descargar Excel
                    </a>
                </div>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($this->kardexMensualDetallado as $mes)
                    <details class="group">
                        <summary class="flex cursor-pointer items-center gap-3 px-5 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300 select-none hover:bg-gray-50 dark:hover:bg-gray-950/50">
                            <svg class="h-4 w-4 shrink-0 text-violet-500 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            <span class="flex-1 text-base text-gray-900 dark:text-white">{{ $mes->mes }}</span>
                            <div class="flex items-center gap-3 text-xs font-normal text-gray-500 dark:text-gray-400">
                                <span>{{ $mes->atenciones }} atenciones</span>
                                <span class="hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ $mes->pacientes }} pacientes</span>
                                <span class="hidden sm:inline">&middot;</span>
                                <span class="hidden sm:inline">{{ $mes->dias }} dias</span>
                            </div>
                        </summary>
                        <div class="border-t border-gray-100 px-5 pb-5 dark:border-gray-800">
                            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-950/30">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Causas</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->causas as $c)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $c->causa }}</span>
                                                <span class="shrink-0 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $c->total }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-violet-100 bg-violet-50/40 p-4 dark:border-violet-900 dark:bg-violet-950/20">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">Medicamentos</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->medicamentos as $nombre => $count)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $nombre }}</span>
                                                <span class="shrink-0 rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700 dark:bg-violet-900 dark:text-violet-300">{{ $count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-950/30">
                                    <h4 class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Areas</h4>
                                    <div class="space-y-2">
                                        @foreach ($mes->areas as $a)
                                            <div class="flex items-center justify-between text-sm">
                                                <span class="truncate pr-2 text-gray-700 dark:text-gray-300">{{ $a->area }}</span>
                                                <span class="shrink-0 rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">{{ $a->total }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endif

    {{-- COMPARATIVO SEMANAL --}}
    @if ($this->semanas->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Comparativo semanal</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-950/40">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Semana</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pacientes</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atenciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->semanas as $semana)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($semana->inicio)->format('d/m') }} – {{ \Carbon\Carbon::parse($semana->fin)->format('d/m/Y') }}
                                </td>
                                <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ $semana->pacientes }}</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-950 dark:text-white">{{ number_format($semana->total, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- KARDEX INVENTARIO --}}
    @if ($this->kardex->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">KARDEX Inventario</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Importado desde la hoja KARDEX del Excel</p>
                    </div>
                    <a href="#" wire:click.prevent="descargarKardex" class="inline-flex items-center rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <svg class="mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3m2 8H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                        Descargar
                    </a>
                </div>
            </div>
            <div class="space-y-3 p-5">
                @foreach ($this->kardex as $periodo => $items)
                    <details class="group rounded-xl border border-gray-100 dark:border-gray-800">
                        <summary class="flex cursor-pointer items-center gap-2 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 select-none hover:bg-gray-50 dark:hover:bg-gray-950/50">
                            <svg class="h-3.5 w-3.5 transition group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7"/></svg>
                            {{ $periodo }}
                            <span class="text-xs text-gray-400">({{ $items->count() }} items)</span>
                        </summary>
                        <div class="overflow-x-auto px-4 pb-3">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Nombre</th>
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Tipo</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Saldo Ant.</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Ingresos</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Egresos</th>
                                        <th class="py-2 text-right text-xs font-medium text-gray-500">Total</th>
                                        <th class="py-2 text-left text-xs font-medium text-gray-500">Caducidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                            <td class="py-1.5 pr-3 font-medium text-gray-900 dark:text-white">{{ $item->nombre }}</td>
                                            <td class="py-1.5 pr-3 text-gray-500 dark:text-gray-400">{{ $item->tipo === 'equipo' ? 'Equipo' : 'Medicina' }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->saldo_anterior }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->ingresos }}</td>
                                            <td class="py-1.5 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $item->egresos }}</td>
                                            <td class="py-1.5 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $item->total }}</td>
                                            <td class="py-1.5 pr-3 text-gray-600 dark:text-gray-400">{{ $item->fecha_caducidad ?: '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                @endforeach
            </div>
        </div>
    @endif

    {{-- MOVIMIENTOS KARDEX --}}
    @if ($this->kardexMovimientos->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-teal-200 bg-white shadow-sm dark:border-teal-900 dark:bg-gray-900">
            <div class="border-b border-teal-100 px-5 py-4 dark:border-teal-900">
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <h3 class="text-base font-semibold text-gray-950 dark:text-white">Movimientos de KARDEX</h3>
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Ultimas salidas generadas desde los partes diarios</p>
                    </div>
                    <a href="#" wire:click.prevent="descargarKardexConMovimientos" class="inline-flex items-center rounded-lg bg-teal-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-teal-700">
                        Descargar Excel
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-teal-100 bg-teal-50 dark:border-teal-900 dark:bg-teal-950/30">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Fecha</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Medicamento</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Cantidad</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Saldo</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Responsable</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-teal-700 dark:text-teal-300">Paciente</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->kardexMovimientos as $mov)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $mov->fecha_movimiento?->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-950 dark:text-white">{{ $mov->medicamento_nombre }}</td>
                                <td class="px-5 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ $mov->cantidad }}</td>
                                <td class="px-5 py-3 text-right text-sm font-semibold text-gray-950 dark:text-white">{{ $mov->saldo_resultante }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $mov->personal_responsable ?: '-' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $mov->parteDiario?->nombres ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ERRORES --}}
    @if ($this->erroresImportacion->isNotEmpty())
        <div class="mt-4 rounded-2xl border border-rose-100 bg-white shadow-sm dark:border-rose-900/40 dark:bg-gray-900">
            <div class="border-b border-rose-100 px-5 py-4 dark:border-rose-900/40">
                <h3 class="text-base font-semibold text-gray-950 dark:text-white">Errores de importacion</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-rose-100 bg-rose-50 dark:border-rose-900/40 dark:bg-rose-950/30">
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Archivo</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Fila</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-rose-700 dark:text-rose-300">Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->erroresImportacion as $error)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->archivoImportado?->nombre_original }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->fila }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $error->mensaje }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
