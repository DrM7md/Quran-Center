@props(['variant' => 'gray'])

@php
  $map = [
    'gray'    => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200',
    'slate'   => 'bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200',
    'emerald' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
    'amber'   => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',
    'rose'    => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
    'blue'    => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
  ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium '.($map[$variant] ?? $map['gray'])]) }}>
  {{ $slot }}
</span>
