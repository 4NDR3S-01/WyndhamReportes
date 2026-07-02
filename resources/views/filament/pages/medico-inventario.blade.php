<x-filament-panels::page>
    <x-hero-card title="Inventario" subtitle="Registra movimientos de ingreso, salida y ajuste de stock" icon="heroicon-o-archive-box" color="ocean" />

    <div class="grid gap-4 xl:grid-cols-[360px_1fr]">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Registrar movimiento</h3>
            <form wire:submit.prevent="guardarMovimiento" class="mt-4 space-y-3">
                <select wire:model="producto_id" class="block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"><option value="">Producto</option>@foreach ($this->productos as $p)<option value="{{ $p->id }}">{{ $p->nombre }}</option>@endforeach</select>
                <select wire:model="tipo" class="block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"><option value="ingreso">Ingreso</option><option value="salida">Salida manual</option><option value="ajuste">Ajuste (+/-)</option></select>
                <div class="grid grid-cols-2 gap-2"><input type="number" step="0.01" wire:model="cantidad" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"><input type="date" wire:model="fecha_movimiento" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"></div>
                <input wire:model="responsable" placeholder="Responsable" class="block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                <textarea wire:model="observacion" placeholder="Observacion" rows="3" class="block w-full rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"></textarea>
                <button class="rounded-lg bg-palm-600 px-4 py-2 text-sm font-semibold text-white">Registrar</button>
            </form>
        </section>

        <section class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800"><h3 class="font-semibold text-gray-950 dark:text-white">Saldos actuales</h3><input wire:model.live.debounce.400ms="buscar" placeholder="Buscar" class="rounded-lg border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-950 dark:text-white"></div>
                <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-3">@foreach ($this->resumen as $p)<div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-gray-800 dark:bg-gray-950/40"><div class="flex justify-between gap-2"><span class="truncate font-medium text-gray-900 dark:text-white">{{ $p->nombre }}</span><span class="font-bold {{ $p->saldoActual() <= $p->stock_minimo ? 'text-red-600' : 'text-palm-600' }}">{{ number_format($p->saldoActual(), 2, ',', '.') }}</span></div><p class="text-xs text-gray-500">{{ $p->tipo }} · minimo {{ $p->stock_minimo }}</p></div>@endforeach</div>
            </div>
            <div class="rounded-2xl border border-teal-200 bg-white shadow-sm dark:border-teal-900 dark:bg-gray-900"><div class="border-b border-teal-100 px-5 py-4 dark:border-teal-900"><h3 class="font-semibold text-gray-950 dark:text-white">Movimientos recientes</h3></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-teal-50 dark:bg-teal-950/30"><tr><th class="px-5 py-3 text-left text-xs text-teal-700">Fecha</th><th class="px-5 py-3 text-left text-xs text-teal-700">Producto</th><th class="px-5 py-3 text-left text-xs text-teal-700">Tipo</th><th class="px-5 py-3 text-right text-xs text-teal-700">Cantidad</th><th class="px-5 py-3 text-left text-xs text-teal-700">Origen</th></tr></thead><tbody class="divide-y divide-gray-100 dark:divide-gray-800">@foreach ($this->movimientos as $m)<tr><td class="px-5 py-3">{{ $m->fecha_movimiento?->format('d/m/Y') }}</td><td class="px-5 py-3 font-medium">{{ $m->producto?->nombre ?? $m->medicamento_nombre }}</td><td class="px-5 py-3">{{ $m->tipo }}</td><td class="px-5 py-3 text-right">{{ $m->cantidad }}</td><td class="px-5 py-3">{{ $m->origen }} {{ $m->parteDiario ? '#'.$m->parteDiario->id : '' }}</td></tr>@endforeach</tbody></table></div></div>
        </section>
    </div>
</x-filament-panels::page>
