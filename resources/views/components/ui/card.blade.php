<div {{ $attributes->merge(['class' => 'bg-white border border-gray-200 shadow-sm rounded-xl']) }}>
    <div class="p-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
