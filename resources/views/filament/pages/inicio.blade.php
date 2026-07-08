<x-filament-panels::page>
    <div class="page-enter space-y-6">

        {{-- ============================================================
        HERO CARD — Bienvenida
        ============================================================ --}}
        <x-hero-card
            title="{{ '¡Hola, ' . explode(' ', auth()->user()->name ?? 'Administrador')[0] . '!' }}"
            subtitle="Panel de control del hotel Wyndham Manta — gestiona cocina y m&eacute;dico desde un solo lugar."
            icon="heroicon-o-sparkles"
            color="brand"
        />

        {{-- ============================================================
        STATS — KPIs principales
        ============================================================ --}}
        <div>
            <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Resumen General</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-stat-card title="Archivos importados" :value="number_format($this->totalArchivos, 0, ',', '.')" icon="heroicon-o-document-text" color="brand" />
                <x-stat-card title="Consumos cargados" :value="number_format($this->totalConsumos, 0, ',', '.')" icon="heroicon-o-shopping-cart" color="palm" />
                <x-stat-card title="Productos detectados" :value="number_format($this->totalProductos, 0, ',', '.')" icon="heroicon-o-tag" color="sand" />
            </div>
        </div>

        {{-- ============================================================
        ACCESOS RÁPIDOS — Módulos del sistema
        ============================================================ --}}
        <div>
            <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Accesos R&aacute;pidos</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                {{-- Cocina --}}
                <a href="/admin/cocina" class="group flex items-start gap-4 rounded-2xl border border-primary-100 bg-white p-5 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-primary-700">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 transition group-hover:bg-primary-100 group-hover:text-primary-700 dark:bg-gray-800 dark:text-primary-400 dark:group-hover:bg-primary-900/40">
                        <x-heroicon-o-cake class="h-6 w-6" />
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-900 transition group-hover:text-primary-600 dark:text-white dark:group-hover:text-primary-400">M&oacute;dulo de Cocina</h4>
                        <p class="mt-0.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400">Analiza el consumo del desayuno buffet, tendencias y estima la producci&oacute;n necesaria.</p>
                    </div>
                </a>

                {{-- Médico --}}
                <a href="/admin/medico" class="group flex items-start gap-4 rounded-2xl border border-coral-100 bg-white p-5 shadow-sm transition-all duration-150 hover:-translate-y-0.5 hover:border-coral-300 hover:shadow-md dark:border-gray-800 dark:bg-gray-900 dark:hover:border-coral-700">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-coral-50 text-coral-500 transition group-hover:bg-coral-100 group-hover:text-coral-600 dark:bg-gray-800 dark:text-coral-400 dark:group-hover:bg-coral-900/40">
                        <x-heroicon-o-heart class="h-6 w-6" />
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-900 transition group-hover:text-coral-600 dark:text-white dark:group-hover:text-coral-400">M&oacute;dulo M&eacute;dico</h4>
                        <p class="mt-0.5 text-sm leading-relaxed text-gray-500 dark:text-gray-400">Gestiona atenciones, partes diarios, medicinas e inventario del dispensario m&eacute;dico.</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- ============================================================
        GRÁFICO DE ACTIVIDAD
        ============================================================ --}}
        <div>
            <h3 class="mb-3 text-sm font-bold text-gray-900 dark:text-white">Actividad Reciente</h3>
            <div class="card overflow-hidden">
                @livewire(\App\Filament\Widgets\InicioChartWidget::class)
            </div>
        </div>

    </div>
</x-filament-panels::page>
