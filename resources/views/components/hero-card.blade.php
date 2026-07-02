@props([
    'title',
    'subtitle' => null,
    'icon',
    'color' => 'brand',
])

@php
    $colorStyles = match ($color) {
        'brand' => [
            'gradient' => 'from-brand-100 via-brand-50 to-brand-200',
            'decoration1' => 'bg-brand-300/30',
            'decoration2' => 'bg-brand-200/25',
            'border' => 'border-brand-300/50',
            'icon_bg' => 'bg-white text-brand-600 ring-brand-300/60',
        ],
        'ocean' => [
            'gradient' => 'from-ocean-100 via-ocean-50 to-ocean-200',
            'decoration1' => 'bg-ocean-300/30',
            'decoration2' => 'bg-ocean-200/25',
            'border' => 'border-ocean-300/50',
            'icon_bg' => 'bg-white text-ocean-600 ring-ocean-300/60',
        ],
        'coral' => [
            'gradient' => 'from-coral-100 via-coral-50 to-coral-200',
            'decoration1' => 'bg-coral-300/30',
            'decoration2' => 'bg-coral-200/25',
            'border' => 'border-coral-300/50',
            'icon_bg' => 'bg-white text-coral-600 ring-coral-300/60',
        ],
        'sand' => [
            'gradient' => 'from-sand-100 via-sand-50 to-sand-200',
            'decoration1' => 'bg-sand-300/30',
            'decoration2' => 'bg-sand-200/25',
            'border' => 'border-sand-300/50',
            'icon_bg' => 'bg-white text-sand-600 ring-sand-300/60',
        ],
        'tide' => [
            'gradient' => 'from-tide-100 via-tide-50 to-tide-200',
            'decoration1' => 'bg-tide-300/30',
            'decoration2' => 'bg-tide-200/25',
            'border' => 'border-tide-300/50',
            'icon_bg' => 'bg-white text-tide-600 ring-tide-300/60',
        ],
        'palm' => [
            'gradient' => 'from-palm-100 via-palm-50 to-palm-200',
            'decoration1' => 'bg-palm-300/30',
            'decoration2' => 'bg-palm-200/25',
            'border' => 'border-palm-300/50',
            'icon_bg' => 'bg-white text-palm-600 ring-palm-300/60',
        ],
        default => [
            'gradient' => 'from-gray-50 via-white to-gray-100',
            'decoration1' => 'bg-gray-200/25',
            'decoration2' => 'bg-gray-300/10',
            'border' => 'border-gray-200/40',
            'icon_bg' => 'bg-white text-gray-600 ring-gray-200/60',
        ],
    };
    $s = $colorStyles;
@endphp

<div class="mb-8 relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $s['gradient'] }} border {{ $s['border'] }} p-8 shadow-sm sm:p-10 dark:border-0 dark:bg-gray-900 dark:bg-none">
    <!-- Decoraciones de fondo (solo en light mode) -->
    <div class="absolute -right-10 -top-10 h-64 w-64 rounded-full {{ $s['decoration1'] }} blur-3xl dark:hidden"></div>
    <div class="absolute -bottom-8 -left-8 h-40 w-40 rounded-full {{ $s['decoration2'] }} blur-3xl dark:hidden"></div>

    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        {{-- Título e ícono --}}
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $s['icon_bg'] }} shadow-sm ring-1">
                <x-dynamic-component :component="$icon" class="h-6 w-6" />
            </div>
            <div>
                <h2 class="text-2xl font-extrabold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                    {{ $title }}
                </h2>
                @if ($subtitle)
                    <p class="mt-1.5 text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Contenido derecho (reloj, filtros, acciones) --}}
        <div class="shrink-0">
            {{ $slot }}
        </div>
    </div>
</div>
