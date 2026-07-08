@props([
    'title',
    'value',
    'icon',
    'color' => 'brand',
    'description' => null,
])

@php
    $colorStyles = match ($color) {
        'brand' => [
            'wrapper' => 'border-brand-100 bg-brand-50/30 dark:border-brand-500/20 dark:bg-brand-500/10',
            'title' => 'text-brand-600 dark:text-brand-500',
            'icon_wrapper' => 'bg-brand-100 text-brand-600 dark:bg-brand-500/30 dark:text-brand-400',
        ],
        'ocean' => [
            'wrapper' => 'border-ocean-100 bg-ocean-50/30 dark:border-ocean-500/20 dark:bg-ocean-500/10',
            'title' => 'text-ocean-600 dark:text-ocean-500',
            'icon_wrapper' => 'bg-ocean-100 text-ocean-600 dark:bg-ocean-500/30 dark:text-ocean-400',
        ],
        'coral' => [
            'wrapper' => 'border-coral-100 bg-coral-50/30 dark:border-coral-500/20 dark:bg-coral-500/10',
            'title' => 'text-coral-600 dark:text-coral-500',
            'icon_wrapper' => 'bg-coral-100 text-coral-600 dark:bg-coral-500/30 dark:text-coral-400',
        ],
        'sand' => [
            'wrapper' => 'border-sand-100 bg-sand-50/30 dark:border-sand-500/20 dark:bg-sand-500/10',
            'title' => 'text-sand-600 dark:text-sand-500',
            'icon_wrapper' => 'bg-sand-100 text-sand-600 dark:bg-sand-500/30 dark:text-sand-400',
        ],
        'tide' => [
            'wrapper' => 'border-tide-100 bg-tide-50/30 dark:border-tide-500/20 dark:bg-tide-500/10',
            'title' => 'text-tide-600 dark:text-tide-500',
            'icon_wrapper' => 'bg-tide-100 text-tide-600 dark:bg-tide-500/30 dark:text-tide-400',
        ],
        'palm' => [
            'wrapper' => 'border-palm-100 bg-palm-50/30 dark:border-palm-500/20 dark:bg-palm-500/10',
            'title' => 'text-palm-600 dark:text-palm-500',
            'icon_wrapper' => 'bg-palm-100 text-palm-600 dark:bg-palm-500/30 dark:text-palm-400',
        ],
        'red' => [
            'wrapper' => 'border-red-100 bg-red-50/30 dark:border-red-500/20 dark:bg-red-500/10',
            'title' => 'text-red-600 dark:text-red-500',
            'icon_wrapper' => 'bg-red-100 text-red-600 dark:bg-red-500/30 dark:text-red-400',
        ],
        default => [
            'wrapper' => 'border-gray-100 bg-gray-50/30 dark:border-gray-700 dark:bg-gray-800/30',
            'title' => 'text-gray-600 dark:text-gray-400',
            'icon_wrapper' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        ],
    };
@endphp

<div class="rounded-2xl border p-5 shadow-sm transition-all duration-300 hover:scale-[1.02] hover:shadow-md {{ $colorStyles['wrapper'] }}">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold tracking-wide uppercase {{ $colorStyles['title'] }}">
                {{ $title }}
            </p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white" {{ $attributes->except(['class', 'title', 'value', 'icon', 'color']) }}>
                @if (isset($value) && $value !== '')
                    {!! $value !!}
                @else
                    {{ $slot }}
                @endif
            </p>
            @if ($description)
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ $description }}</p>
            @endif
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-full {{ $colorStyles['icon_wrapper'] }}">
            <x-dynamic-component :component="$icon" class="h-6 w-6" />
        </div>
    </div>
</div>
