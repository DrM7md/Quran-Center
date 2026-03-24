@php
    $typeLabel = [
        'new'    => 'حفظ جديد',
        'review' => 'مراجعة',
    ];
    $ratingLabel = [
        'excellent' => 'ممتاز',
        'very_good' => 'جيد جدًا',
        'good'      => 'جيد',
        'weak'      => 'ضعيف',
        'repeat'    => 'يحتاج إعادة',
    ];
@endphp

<div class="divide-y divide-gray-100">
    @forelse($latestMems as $m)
        <div class="py-3 first:pt-0 last:pb-0">
            <div class="flex items-center justify-between gap-3">
                <span class="font-medium text-sm text-gray-900 truncate">{{ $m->student?->name ?? '—' }}</span>
                <span @class([
                    'shrink-0 inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset',
                    'bg-emerald-50 text-emerald-700 ring-emerald-200' => $m->type === 'new',
                    'bg-gray-100 text-gray-600 ring-gray-200'         => $m->type !== 'new',
                ])>
                    {{ $typeLabel[$m->type] ?? $m->type }}
                </span>
            </div>
            <div class="text-xs text-gray-500 mt-1 flex items-center justify-between">
                <span>{{ $m->surah?->name ?? '—' }} • {{ $m->from_ayah }}–{{ $m->to_ayah }} • {{ $ratingLabel[$m->rating] ?? $m->rating }}</span>
                <span class="text-gray-400">{{ optional($m->heard_at)->format('Y/m/d') }}</span>
            </div>
        </div>
    @empty
        <p class="text-sm text-gray-400 text-center py-4">لا توجد سجلات تسميع للعرض.</p>
    @endforelse
</div>
