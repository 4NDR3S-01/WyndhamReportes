@props([
    'title',
    'value',
    'icon',
    'color' => 'primary',
])

@php
    $colorStyles = match ($color) {
        'primary' => [
            'wrapper' => 'border-primary-100 bg-primary-50/30 dark:border-primary-900/30 dark:bg-primary-900/10',
            'title' => 'text-primary-600 dark:text-primary-400',
            'icon_wrapper' => 'bg-primary-100 text-primary-600 dark:bg-primary-900/50 dark:text-primary-400',
        ],
        'emerald' => [
            'wrapper' => 'border-emerald-100 bg-emerald-50/30 dark:border-emerald-900/30 dark:bg-emerald-900/10',
            'title' => 'text-emerald-600 dark:text-emerald-400',
            'icon_wrapper' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400',
        ],
        'amber' => [
            'wrapper' => 'border-amber-100 bg-amber-50/30 dark:border-amber-900/30 dark:bg-amber-900/10',
            'title' => 'text-amber-600 dark:text-amber-400',
            'icon_wrapper' => 'bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-400',
        ],
        'rose' => [
            'wrapper' => 'border-rose-100 bg-rose-50/30 dark:border-rose-900/30 dark:bg-rose-900/10',
            'title' => 'text-rose-600 dark:text-rose-400',
            'icon_wrapper' => 'bg-rose-100 text-rose-600 dark:bg-rose-900/50 dark:text-rose-400',
        ],
        'indigo' => [
            'wrapper' => 'border-indigo-100 bg-indigo-50/30 dark:border-indigo-900/30 dark:bg-indigo-900/10',
            'title' => 'text-indigo-600 dark:text-indigo-400',
            'icon_wrapper' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-400',
        ],
        'sky' => [
            'wrapper' => 'border-sky-100 bg-sky-50/30 dark:border-sky-900/30 dark:bg-sky-900/10',
            'title' => 'text-sky-600 dark:text-sky-400',
            'icon_wrapper' => 'bg-sky-100 text-sky-600 dark:bg-sky-900/50 dark:text-sky-400',
        ],
        default => [
            'wrapper' => 'border-gray-100 bg-gray-50/30 dark:border-gray-900/30 dark:bg-gray-900/10',
            'title' => 'text-gray-600 dark:text-gray-400',
            'icon_wrapper' => 'bg-gray-100 text-gray-600 dark:bg-gray-900/50 dark:text-gray-400',
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
        </div>
        <div class="flex h-12 w-12 items-center justify-center rounded-full {{ $colorStyles['icon_wrapper'] }}">
            <x-dynamic-component :component="$icon" class="h-6 w-6" />
        </div>
    </div>
</div>
