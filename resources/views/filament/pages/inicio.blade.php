<x-filament-panels::page>
    <div class="py-8">
        <!-- Encabezado de Bienvenida (Hero Pastel) -->
        <x-hero-card
            title="{{ '¡Hola, ' . explode(' ', auth()->user()->name ?? 'Administrador')[0] . '!' }}"
            subtitle="Bienvenido al sistema de control y reportes de Wyndham."
            icon="heroicon-o-sparkles"
            color="brand"
        />

        <!-- Tarjetas de Estadísticas (Colores Pasteles) -->
        <h3 class="mb-4 text-lg font-semibold text-gray-950 dark:text-white">Resumen General</h3>
        <div class="grid gap-6 sm:grid-cols-3">
            <x-stat-card title="Archivos importados" :value="number_format($this->totalArchivos, 0, ',', '.')" icon="heroicon-o-document-text" color="brand" />
            <x-stat-card title="Consumos cargados" :value="number_format($this->totalConsumos, 0, ',', '.')" icon="heroicon-o-shopping-cart" color="palm" />
            <x-stat-card title="Productos detectados" :value="number_format($this->totalProductos, 0, ',', '.')" icon="heroicon-o-tag" color="sand" />
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

            <a href="/admin/medico" class="group flex items-start gap-4 rounded-2xl border border-coral-100 bg-white p-6 shadow-sm transition hover:border-coral-300 hover:bg-coral-50/30 dark:border-gray-800 dark:bg-gray-900 dark:hover:border-coral-800">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-coral-50 text-coral-500 transition group-hover:bg-coral-100 group-hover:text-coral-600 dark:bg-gray-800 dark:text-coral-400 dark:group-hover:bg-coral-900/50">
                    <x-heroicon-o-heart class="h-6 w-6" />
                </div>
                <div>
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white group-hover:text-coral-600 dark:group-hover:text-coral-400">Módulo Médico</h4>
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
