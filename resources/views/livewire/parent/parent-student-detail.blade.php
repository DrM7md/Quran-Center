@php
    $ratingLabel = [
        'excellent' => 'ممتاز',
        'very_good' => 'جيد جدًا',
        'good'      => 'جيد',
        'weak'      => 'ضعيف',
        'repeat'    => 'يحتاج إعادة',
    ];
    $ratingColor = [
        'excellent' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'very_good' => 'bg-blue-50 text-blue-700 ring-blue-200',
        'good'      => 'bg-sky-50 text-sky-700 ring-sky-200',
        'weak'      => 'bg-amber-50 text-amber-700 ring-amber-200',
        'repeat'    => 'bg-red-50 text-red-700 ring-red-200',
    ];
    $typeLabel = ['new' => 'حفظ جديد', 'review' => 'مراجعة'];
@endphp

<div class="space-y-6">

    {{-- Back + Header --}}
    <div class="bg-gradient-to-l from-emerald-700 to-teal-800 rounded-xl p-6 text-white shadow-sm">
        <a href="{{ route('parent.dashboard') }}"
           class="inline-flex items-center gap-1.5 text-emerald-200 hover:text-white text-sm mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            العودة
        </a>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-lg bg-white/20 flex items-center justify-center text-2xl font-bold shrink-0">
                {{ mb_substr($student->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ $student->name }}</h1>
                <p class="text-emerald-200 text-sm mt-0.5">
                    {{ $student->halaqa?->center?->name ?? '—' }}
                    @if($student->halaqa)
                        <span class="opacity-50 mx-1">•</span>
                        {{ $student->halaqa->name }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
        <div class="flex border-b border-gray-200">
            <button wire:click="setTab('memorizations')"
                    class="flex-1 py-3 text-sm font-semibold transition-colors
                        {{ $tab === 'memorizations' ? 'text-emerald-700 border-b-2 border-emerald-600 bg-emerald-50/50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                سجل التسميع
                <span class="mr-1.5 text-xs text-gray-400">({{ $memorizations->total() }})</span>
            </button>
            <button wire:click="setTab('absences')"
                    class="flex-1 py-3 text-sm font-semibold transition-colors
                        {{ $tab === 'absences' ? 'text-red-700 border-b-2 border-red-500 bg-red-50/50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                سجل الغياب
                <span class="mr-1.5 text-xs text-gray-400">({{ $absences->total() }})</span>
            </button>
            <button wire:click="setTab('progress')"
                    class="flex-1 py-3 text-sm font-semibold transition-colors
                        {{ $tab === 'progress' ? 'text-teal-700 border-b-2 border-teal-600 bg-teal-50/50' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-50' }}">
                التقدم
            </button>
        </div>

        <div class="p-5">

            {{-- ══════════════════════════════════════════════════ --}}
            {{-- Memorizations Tab --}}
            {{-- ══════════════════════════════════════════════════ --}}
            @if($tab === 'memorizations')
                @if($memorizations->isEmpty())
                    <p class="text-center text-gray-400 text-sm py-8">لا توجد سجلات تسميع.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($memorizations as $mem)
                            <div class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="shrink-0 inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset
                                            {{ $mem->type === 'new' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                            {{ $typeLabel[$mem->type] ?? $mem->type }}
                                        </span>
                                        <span class="text-sm font-medium text-gray-800 truncate">
                                            {{ $mem->surah?->name ?? '—' }}
                                            <span class="text-gray-400 font-normal">({{ $mem->from_ayah }}–{{ $mem->to_ayah }})</span>
                                        </span>
                                    </div>
                                    <span class="shrink-0 inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset {{ $ratingColor[$mem->rating] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                                        {{ $ratingLabel[$mem->rating] ?? $mem->rating }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ optional($mem->heard_at)->format('Y/m/d') }}
                                    @if($mem->notes)
                                        <span class="text-gray-300 mx-1">•</span>
                                        {{ $mem->notes }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $memorizations->links() }}</div>
                @endif
            @endif

            {{-- ══════════════════════════════════════════════════ --}}
            {{-- Absences Tab --}}
            {{-- ══════════════════════════════════════════════════ --}}
            @if($tab === 'absences')
                @if($absences->isEmpty())
                    <p class="text-center text-gray-400 text-sm py-8">لا توجد سجلات غياب.</p>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($absences as $absence)
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ optional($absence->date)->format('Y/m/d') }}
                                </span>
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset bg-red-50 text-red-700 ring-red-200">
                                    غائب
                                </span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $absences->links() }}</div>
                @endif
            @endif

            {{-- ══════════════════════════════════════════════════ --}}
            {{-- Progress Tab --}}
            {{-- ══════════════════════════════════════════════════ --}}
            @if($tab === 'progress')
                <div class="space-y-8">

                    {{-- ┌─ حفظ جديد ─────────────────────────────┐ --}}
                    <div>
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <h3 class="text-sm font-bold text-gray-700">الحفظ الجديد</h3>
                        </div>

                        {{-- Progress ring + key stats --}}
                        <div class="flex items-center gap-5 mb-6">

                            {{-- SVG progress ring --}}
                            <div class="relative shrink-0 w-28 h-28">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    <circle cx="50" cy="50" r="38"
                                            fill="none" stroke="#e5e7eb" stroke-width="10"/>
                                    <circle cx="50" cy="50" r="38"
                                            fill="none" stroke="#10b981" stroke-width="10"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ $circleCircumf }}"
                                            stroke-dashoffset="{{ round($circleCircumf * (1 - $progressPct / 100), 2) }}"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-xl font-bold text-gray-800">{{ $progressPct }}%</span>
                                    <span class="text-[10px] text-gray-400 leading-tight">من القرآن</span>
                                </div>
                            </div>

                            {{-- Stats grid --}}
                            <div class="grid grid-cols-2 gap-2 flex-1 text-center">
                                <div class="bg-emerald-50 rounded-xl p-3">
                                    <div class="text-lg font-bold text-emerald-700">{{ number_format($totalAyahsNew) }}</div>
                                    <div class="text-[11px] text-emerald-600 mt-0.5">آية محفوظة</div>
                                </div>
                                <div class="bg-teal-50 rounded-xl p-3">
                                    <div class="text-lg font-bold text-teal-700">{{ $juzCompleted }}</div>
                                    <div class="text-[11px] text-teal-600 mt-0.5">جزء (تقريباً)</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                                    @if($direction)
                                        <div class="text-sm font-semibold {{ $direction === 'top_down' ? 'text-emerald-700' : 'text-blue-700' }}">
                                            {{ $direction === 'top_down' ? '↓ من الفاتحة نزولاً' : '↑ من الناس صعوداً' }}
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">اتجاه الحفظ</div>
                                    @elseif($lastNewMem)
                                        <div class="text-sm font-semibold text-gray-700">{{ $lastNewMem->heard_at->format('Y/m/d') }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">آخر جلسة حفظ</div>
                                    @else
                                        <div class="text-sm text-gray-400">لا توجد جلسات حفظ بعد</div>
                                    @endif
                                </div>
                                @if($direction && $lastNewMem)
                                    <div class="bg-gray-50 rounded-xl p-3 col-span-2">
                                        <div class="text-sm font-semibold text-gray-700">{{ $lastNewMem->heard_at->format('Y/m/d') }}</div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">آخر جلسة حفظ</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Per-surah progress bars --}}
                        @if(!empty($surahsData))
                            <div class="mb-3">
                                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">السور المحفوظة</p>
                                <div class="space-y-3">
                                    @foreach($surahsData as $row)
                                        <div>
                                            <div class="flex justify-between items-center text-xs mb-1">
                                                <span class="font-medium text-gray-700">
                                                    <span class="text-gray-400 ml-1">{{ $row['number'] }}.</span>{{ $row['name'] }}
                                                </span>
                                                <span class="{{ $row['pct'] >= 100 ? 'text-emerald-600 font-bold' : 'text-gray-400' }}">
                                                    {{ $row['memorized'] }}/{{ $row['total'] }} آية
                                                    @if($row['pct'] >= 100) ✓ @endif
                                                </span>
                                            </div>
                                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all
                                                            {{ $row['pct'] >= 100 ? 'bg-emerald-500' : 'bg-emerald-300' }}"
                                                     style="width: {{ $row['pct'] }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="text-center text-gray-400 text-sm py-4">لا توجد سجلات حفظ جديد بعد.</p>
                        @endif
                    </div>

                    <div class="border-t border-gray-100"></div>

                    {{-- ┌─ المراجعة ──────────────────────────────┐ --}}
                    <div>
                        <div class="flex items-center gap-2 mb-5">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                            <h3 class="text-sm font-bold text-gray-700">المراجعة</h3>
                        </div>

                        {{-- Review stats --}}
                        <div class="grid grid-cols-3 gap-3 mb-6">
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <div class="text-xl font-bold text-blue-700">{{ number_format($last30RevAyahs) }}</div>
                                <div class="text-[11px] text-blue-500 mt-0.5 leading-tight">آية<br>آخر 30 يوم</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="text-xl font-bold text-gray-700">{{ number_format($totalRevAyahs) }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5 leading-tight">إجمالي<br>المراجعة</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="text-sm font-bold text-gray-700">
                                    {{ $lastReview ? $lastReview->heard_at->format('d/m') : '—' }}
                                </div>
                                <div class="text-[11px] text-gray-500 mt-0.5 leading-tight">آخر<br>مراجعة</div>
                            </div>
                        </div>

                        {{-- Monthly activity chart (last 6 months) --}}
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">النشاط الشهري (6 أشهر)</p>

                        {{-- Legend --}}
                        <div class="flex items-center gap-4 mb-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-emerald-400 inline-block"></span> حفظ جديد
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-sm bg-blue-400 inline-block"></span> مراجعة
                            </span>
                        </div>

                        <div class="flex items-end gap-2" style="height: 88px;">
                            @foreach($months as $m)
                                @php
                                    $newH = $maxMonthAyahs > 0 ? max(2, (int) round($m['newAyahs'] / $maxMonthAyahs * 72)) : 2;
                                    $revH = $maxMonthAyahs > 0 ? max(2, (int) round($m['revAyahs'] / $maxMonthAyahs * 72)) : 2;
                                @endphp
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    {{-- Two side-by-side bars --}}
                                    <div class="w-full flex items-end gap-0.5 justify-center" style="height: 72px;">
                                        <div class="flex-1 rounded-t-sm {{ $m['newAyahs'] > 0 ? 'bg-emerald-400' : 'bg-gray-100' }}"
                                             style="height: {{ $m['newAyahs'] > 0 ? $newH : 3 }}px"></div>
                                        <div class="flex-1 rounded-t-sm {{ $m['revAyahs'] > 0 ? 'bg-blue-400' : 'bg-gray-100' }}"
                                             style="height: {{ $m['revAyahs'] > 0 ? $revH : 3 }}px"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 text-center leading-none mt-1">{{ $m['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals below chart --}}
                        <div class="mt-3 flex gap-2">
                            @foreach($months as $m)
                                <div class="flex-1 text-center">
                                    @if($m['newAyahs'] > 0 || $m['revAyahs'] > 0)
                                        <div class="text-[10px] text-emerald-600 font-semibold leading-none">
                                            {{ $m['newAyahs'] > 0 ? $m['newAyahs'] : '' }}
                                        </div>
                                        <div class="text-[10px] text-blue-500 font-semibold leading-none">
                                            {{ $m['revAyahs'] > 0 ? $m['revAyahs'] : '' }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            @endif
            {{-- end progress tab --}}

        </div>
    </div>

</div>
