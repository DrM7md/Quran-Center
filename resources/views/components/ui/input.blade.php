@props(['type' => 'text'])

<input type="{{ $type }}"
  {{ $attributes->merge(['class' => 'w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500']) }} />
