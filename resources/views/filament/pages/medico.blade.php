<x-filament-panels::page>
    <div class="flex items-center justify-between gap-4 pb-4">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">Departamento Medico</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                @if ($this->minFecha) {{ \Carbon\Carbon::parse($this->minFecha)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($this->maxFecha)->format('d/m/Y') }} @else Sin datos @endif
                &middot; {{ number_format($this->total, 0, ',', '.') }} atenciones &middot; {{ $this->archivos }} archivo{{ $this->archivos !== 1 ? 's' : '' }}
            </p>
        </div>
    </div>

    @if ($this->total === 0)
        <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-gray-200 py-16 dark:border-gray-800">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Sin datos todavia</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sube un archivo Excel desde <strong>Subir datos</strong> y presiona <strong>Importar</strong>.</p>
        </div>
    @endif

    @if ($this->total > 0 && $this->mesesDisponibles->isNotEmpty())
        {{-- SELECTOR DE MES --}}
        <div class="mb-4 rounded-2xl border border-violet-200 bg-white shadow-sm dark:border-violet-900 dark:bg-gray-900">
            <div class="flex items-center justify-between gap-2 px-5 py-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300">KARDEX Mensual</span>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Resumen generado desde los partes diarios</p>
                </div>
                <select wire:model.live="mesSeleccionado" class="rounded-lg border-gray-300 text-sm font-medium shadow-sm focus:border-violet-500 focus:ring-violet-500 dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                    @foreach ($this->mesesDisponibles as $m)
                        <option value="{{ $m->ym }}">{{ $m->label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- CARDS DEL MES --}}
            <div class="border-t border-violet-100 px-5 pb-4 dark:border-violet-900">
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atenciones</p>
                        <p class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->atenciones, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pacientes</p>
                        <p class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->pacientes, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-800 dark:bg-gray-950/40">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dias con atencion</p>
                        <p class="mt-1 text-xl font-bold text-gray-950 dark:text-white">{{ number_format($this->resumenDelMes->dias, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-orange-100 bg-orange-50/60 p-3 dark:border-orange-900 dark:bg-orange-950/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-orange-600 dark:text-orange-400">Con certificado</p>
                        <p class="mt-1 text-xl font-bold text-orange-700 dark:text-orange-300">{{ number_format($this->resumenDelMes->conCertificado, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-violet-100 bg-violet-50/60 p-3 dark:border-violet-900 dark:bg-violet-950/20">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">Medicamentos usados</p>
                        <p class="mt-1 text-xl font-bold text-violet-700 dark:text-violet-300">{{ $this->resumenDelMes->medicamentos }}</p>
                    </div>
                </div>
            </div>
        </div>

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
                                            @if ($at->certificados && $at->certificados !== 'SIN CERTIFICADO')
                                                <span class="shrink-0 rounded-md bg-orange-100 px-1.5 py-0.5 text-[10px] font-semibold text-orange-700 dark:bg-orange-950/50 dark:text-orange-300">{{ $at->certificados }}</span>
                                            @endif
                                        </div>
                                        <p class="mt-0.5 truncate text-xs text-gray-500 dark:text-gray-400">{{ $at->cargo ?: '' }}</p>
                                        @if ($at->causa)
                                            <p class="mt-1 truncate text-xs font-medium text-gray-600 dark:text-gray-300">{{ $at->causa }}</p>
                                        @endif
                                        @if ($at->diagnostico)
                                            <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ Str::limit($at->diagnostico, 60) }}</p>
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

    {{-- MEDICAMENTOS MAS USADOS --}}
    @if ($this->medicamentosMasUsados->isNotEmpty())
        <details class="mb-4 group rounded-2xl border border-violet-200 bg-white shadow-sm dark:border-violet-900 dark:bg-gray-900">
            <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-violet-700 dark:text-violet-300 select-none">
                Medicamentos mas usados (historico)
                <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="border-t border-violet-100 px-5 pb-4 dark:border-violet-900">
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($this->medicamentosMasUsados as $med)
                        <div class="flex items-center justify-between rounded-xl border border-violet-100 bg-violet-50/50 px-3 py-2 text-sm dark:border-violet-900 dark:bg-violet-950/20">
                            <span class="truncate font-medium text-gray-900 dark:text-white">{{ $med->nombre }}</span>
                            <span class="ml-2 shrink-0 font-semibold text-violet-700 dark:text-violet-300">{{ $med->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </details>
    @endif

    {{-- ALERTAS STOCK BAJO --}}
    @if ($this->kardexAlertas->isNotEmpty())
        <details class="mb-4 group rounded-2xl border border-red-200 bg-white shadow-sm dark:border-red-900 dark:bg-gray-900">
            <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-300 select-none">
                Alertas de stock bajo (KARDEX inventario)
                <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="border-t border-red-100 px-5 pb-4 dark:border-red-900">
                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($this->kardexAlertas as $alerta)
                        @php $saldoAlerta = $alerta->saldoActual(); @endphp
                        <div class="flex items-center justify-between rounded-xl border border-red-100 bg-red-50/50 px-3 py-2 text-sm dark:border-red-900 dark:bg-red-950/20">
                            <span class="truncate font-medium text-gray-900 dark:text-white">{{ $alerta->nombre }}</span>
                            <span class="ml-2 shrink-0 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700 dark:bg-red-950 dark:text-red-300">{{ $saldoAlerta }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </details>
    @endif

    {{-- MOVIMIENTOS RECIENTES --}}
    @if ($this->movimientosRecientes->isNotEmpty())
        <details class="mb-4 group rounded-2xl border border-sky-200 bg-white shadow-sm dark:border-sky-900 dark:bg-gray-900">
            <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300 select-none">
                Movimientos recientes de medicamentos
                <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="border-t border-sky-100 px-5 pb-4 dark:border-sky-900">
                <div class="mt-2 overflow-x-auto">
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
                                    <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $mov->medicamento_nombre }}</td>
                                    <td class="py-2 pr-3 text-right text-gray-600 dark:text-gray-400">{{ $mov->cantidad }}</td>
                                    <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $mov->saldo_resultante }}</td>
                                    <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->personal_responsable ?: '-' }}</td>
                                    <td class="py-2 pr-3 text-gray-600 dark:text-gray-400">{{ $mov->parteDiario?->nombres ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>
    @endif

    {{-- KARDEX MEDICINAS --}}
    @if ($this->kardexActual->isNotEmpty())
        <details class="mb-4 group rounded-2xl border border-emerald-200 bg-white shadow-sm dark:border-emerald-900 dark:bg-gray-900" open>
            <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-emerald-700 dark:text-emerald-300 select-none">
                Kardex Inventario — Medicinas
                <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
            </summary>
            <div class="border-t border-emerald-100 px-5 pb-4 dark:border-emerald-900">
                <div class="mt-2 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
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
                                <tr class="border-b border-gray-50 dark:border-gray-800/50">
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
        </details>

        @if ($this->equipos->isNotEmpty())
            <details class="mb-4 group rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <summary class="flex cursor-pointer items-center justify-between gap-2 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 select-none">
                    Kardex Inventario — Equipos
                    <svg class="h-4 w-4 transition group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                </summary>
                <div class="border-t border-gray-100 px-5 pb-4 dark:border-gray-800">
                    <div class="mt-2 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="py-2 text-left text-xs font-medium text-gray-500">Equipo</th>
                                    <th class="py-2 text-right text-xs font-medium text-gray-500">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->equipos as $eq)
                                    <tr class="border-b border-gray-50 dark:border-gray-800/50">
                                        <td class="py-2 pr-3 font-medium text-gray-900 dark:text-white">{{ $eq->nombre }}</td>
                                        <td class="py-2 pr-3 text-right font-semibold text-gray-900 dark:text-white">{{ $eq->total }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        @endif
    @endif
</x-filament-panels::page>
