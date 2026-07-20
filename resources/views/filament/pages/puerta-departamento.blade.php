<div
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 p-4 backdrop-blur-sm"
    style="position: fixed; inset: 0; @if ($depto === 'cocina') --brand:#0B3B60; @else --brand:#0B7A8C; @endif"
    wire:click="cerrar">

    <div
        class="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-slate-900"
        style="border: 2px solid var(--brand); box-shadow: 0 25px 50px -12px rgba(0,0,0,.45);"
        wire:click.stop>

        {{-- Botón cerrar (X) --}}
        <button
            type="button"
            wire:click.stop="cerrar"
            title="Cerrar"
            class="absolute right-4 top-4 z-10 flex h-9 w-9 items-center justify-center rounded-full text-white transition hover:bg-white/40"
            style="background: rgba(255,255,255,0.22); box-shadow: 0 0 0 1.5px rgba(255,255,255,0.55);">
            <svg class="pointer-events-none h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Cabecera con color de marca del departamento --}}
        <div class="relative px-8 pt-8 pb-6 pr-16 text-center"
             style="background: linear-gradient(135deg, var(--brand) 0%, color-mix(in srgb, var(--brand) 70%, #000) 100%);">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/25">
                <svg class="h-7 w-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white">Acceso restringido</h2>
            <p class="mt-1 text-sm text-white/80">
                Ingresa la contraseña del departamento de
                <span class="font-semibold">{{ $this->getEtiquetaDepartamento() }}</span>
            </p>
        </div>

        {{-- Formulario --}}
        <form wire:submit="desbloquear" class="px-8 py-7">
            <label for="password" class="mb-2 block text-sm font-medium text-slate-600 dark:text-slate-300">
                Contraseña
            </label>
            <input
                id="password"
                type="password"
                wire:model="password"
                autofocus
                placeholder="••••••••"
                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-base text-slate-800 shadow-sm outline-none transition focus:border-[var(--brand)] focus:ring-2 focus:ring-[var(--brand)]/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white"
            />

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:opacity-90 disabled:opacity-60"
                style="background: var(--brand);">
                <span wire:loading class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                <span wire:loading.remove>Desbloquear</span>
                <span wire:loading>Verificando…</span>
            </button>

            <p class="mt-4 text-center text-xs text-slate-400 dark:text-slate-500">
                El acceso queda habilitado hasta que cierres sesión.
            </p>
        </form>
    </div>
</div>
