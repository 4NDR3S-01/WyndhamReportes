<x-filament-panels::page>
    <div class="mx-auto max-w-2xl py-16 text-center">
        <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-sky-100 text-sky-600 shadow-sm ring-1 ring-sky-200 dark:bg-sky-950 dark:text-sky-400 dark:ring-sky-900">
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2Z" />
            </svg>
        </div>

        <h2 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">Wyndham Reportes</h2>
        <p class="mt-4 leading-relaxed text-gray-600 dark:text-gray-300">
            Sistema de control de consumo alimentario para el departamento de cocina del hotel.
            Centraliza los reportes Excel del desayuno buffet, analiza tendencias de consumo
            y genera estimaciones de produccion para reducir desperdicios.
        </p>

        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-3xl font-bold text-sky-600 dark:text-sky-400">{{ number_format($this->totalArchivos, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Archivos importados</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-3xl font-bold text-sky-600 dark:text-sky-400">{{ number_format($this->totalConsumos, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Consumos cargados</p>
            </div>
            <div class="rounded-2xl border border-gray-100 bg-white p-6 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-3xl font-bold text-sky-600 dark:text-sky-400">{{ number_format($this->totalProductos, 0, ',', '.') }}</p>
                <p class="mt-2 text-sm font-medium text-gray-500 dark:text-gray-400">Productos detectados</p>
            </div>
        </div>

        <div class="mt-10 rounded-2xl border border-gray-100 bg-gray-50/70 p-8 dark:border-gray-800 dark:bg-gray-950/40">
            <h3 class="text-base font-semibold text-gray-950 dark:text-white">Modulo disponible</h3>
            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                Accede a <strong>Cocina</strong> desde la barra lateral para ver el dashboard con graficos,
                el consumo diario por fecha y generar recomendaciones de produccion basadas en datos reales.
            </p>
        </div>
    </div>
</x-filament-panels::page>
