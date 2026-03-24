@props(['variant' => 'primary', 'size' => 'md'])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed';
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs rounded-lg',
        'md' => 'px-4 py-2 text-sm rounded-lg',
        'lg' => 'px-5 py-2.5 text-sm rounded-lg',
    ];
    $variants = [
        'primary' => 'bg-blue-600 text-white hover:bg-blue-700 focus:ring-blue-500',
        'dark'    => 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-500',
        'emerald' => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'light'   => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-gray-400',
        'danger'  => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
        'ghost'   => 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:ring-gray-400',
    ];
@endphp

<button {{ $attributes->merge(['class' => $base.' '.($sizes[$size] ?? $sizes['md']).' '.($variants[$variant] ?? $variants['primary'])]) }}>
    {{ $slot }}
</button>
