<x-filament-panels::page>
    <div class="mx-auto max-w-5xl py-8">
        <!-- Encabezado de Bienvenida -->
        <div class="mb-10 flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="flex items-center gap-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
                    ¡Hola, {{ explode(' ', auth()->user()->name ?? 'Administrador')[0] }}! 
                    <x-heroicon-o-sparkles class="h-8 w-8 text-primary-500" />
                </h2>
                <p class="mt-2 text-lg leading-relaxed text-gray-600 dark:text-gray-300">
                    Bienvenido al sistema de control y reportes de Wyndham.
                </p>
            </div>
            
            <!-- Fecha y Reloj -->
            <div class="mt-4 sm:mt-0 flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-3 shadow-sm dark:border-gray-800 dark:bg-gray-900" x-data="{ time: new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }" x-init="setInterval(() => time = new Date().toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }), 1000)">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                    <x-heroicon-o-clock class="h-7 w-7" />
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ now()->translatedFormat('l, d \d\e F') }}</p>
                    <p class="text-xl font-bold tracking-tight text-gray-900 dark:text-white" x-text="time"></p>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas (Colores Pasteles Corregidos) -->
        <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Resumen General</h3>
        <div class="grid gap-6 sm:grid-cols-3">
            <!-- Archivos -->
            <div class="rounded-2xl border border-primary-100 bg-primary-50/30 p-6 shadow-sm transition hover:shadow-md dark:border-primary-900/30 dark:bg-primary-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-primary-600 dark:text-primary-400">Archivos importados</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalArchivos, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400">
                        <x-heroicon-o-document-text class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Consumos -->
            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/30 p-6 shadow-sm transition hover:shadow-md dark:border-emerald-900/30 dark:bg-emerald-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Consumos cargados</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalConsumos, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400">
                        <x-heroicon-o-shopping-cart class="h-6 w-6" />
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="rounded-2xl border border-amber-100 bg-amber-50/30 p-6 shadow-sm transition hover:shadow-md dark:border-amber-900/30 dark:bg-amber-900/10">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-amber-600 dark:text-amber-400">Productos detectados</p>
                        <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ number_format($this->totalProductos, 0, ',', '.') }}</p>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400">
                        <x-heroicon-o-tag class="h-6 w-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Módulos - Quick Actions -->
        <h3 class="mb-4 mt-10 text-lg font-semibold text-gray-950 dark:text-white">Accesos Rápidos</h3>
        <div class="grid gap-6 sm:grid-cols-2">
            <a href="/admin/cocina" class="group flex items-start gap-4 rounded-2xl border border-primary-100 bg-white p-6 shadow-sm transition hover:border-primary-300 hover:bg-primary-50/30 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-800">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-500 transition group-hover:bg-primary-100 group-hover:text-primary-600 dark:bg-gray-800 dark:text-primary-400 dark:group-hover:bg-primary-900/50">
                    <x-heroicon-o-cake class="h-6 w-6" />
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400">Módulo de Cocina</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analiza el consumo del desayuno buffet, tendencias y estima la producción necesaria.</p>
                </div>
            </a>

            <a href="/admin/medico" class="group flex items-start gap-4 rounded-2xl border border-rose-100 bg-white p-6 shadow-sm transition hover:border-rose-300 hover:bg-rose-50/30 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-rose-800">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-rose-50 text-rose-500 transition group-hover:bg-rose-100 group-hover:text-rose-600 dark:bg-gray-800 dark:text-rose-400 dark:group-hover:bg-rose-900/50">
                    <x-heroicon-o-heart class="h-6 w-6" />
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400">Módulo Médico</h4>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona atenciones, partes diarios, medicinas e inventario del dispensario médico.</p>
                </div>
            </a>
        </div>
        
        <!-- Gráfico de Tendencias -->
        <div class="mt-10">
            @livewire(\App\Filament\Widgets\InicioChartWidget::class)
        </div>
    </div>
</x-filament-panels::page>
