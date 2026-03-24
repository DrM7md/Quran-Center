@php
    $class = $class ?? 'w-5 h-5';
@endphp

@switch($name ?? '')
    @case('home')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  d="M3 10.5L12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z"/>
        </svg>
        @break

    @case('user')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  d="M20 21a8 8 0 0 0-16 0M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/>
        </svg>
        @break

    @case('settings')
        <svg class="{{ $class }}" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  d="M12 15.5a3.5 3.5 0 1 0-3.5-3.5 3.5 3.5 0 0 0 3.5 3.5z"/>
            <path stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                  d="M19.4 15a7.8 7.8 0 0 0 .1-1 7.8 7.8 0 0 0-.1-1l2-1.6-2-3.4-2.4 1a7.7 7.7 0 0 0-1.7-1l-.4-2.6H9.1l-.4 2.6a7.7 7.7 0 0 0-1.7 1l-2.4-1-2 3.4 2 1.6a7.8 7.8 0 0 0-.1 1 7.8 7.8 0 0 0 .1 1l-2 1.6 2 3.4 2.4-1a7.7 7.7 0 0 0 1.7 1l.4 2.6h3.8l.4-2.6a7.7 7.7 0 0 0 1.7-1l2.4 1 2-3.4-2-1.6z"/>
        </svg>
        @break

    @default
        <span class="{{ $class }}"></span>
@endswitch
