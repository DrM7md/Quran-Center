
<x-layouts.app header="لوحة التحكم">

@php $isSuperAdmin = auth()->user()?->isSuperAdmin(); @endphp

<div class="space-y-6">

@if($isSuperAdmin)
    {{-- ============================================================ --}}
    {{-- داشبورد السوبر أدمن                                          --}}
    {{-- ============================================================ --}}

    {{-- Page Header --}}
    <div class="bg-gradient-to-l from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">Super Admin</p>
                <h1 class="text-2xl font-bold mt-1">نظرة عامة على جميع المراكز</h1>
                <p class="text-blue-200 mt-1 text-sm">إحصاءات شاملة لجميع المراكز في النظام.</p>
            </div>
            <a href="{{ route('centers.index') }}"
               class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-white font-medium text-sm border border-white/20 transition-colors">
                إدارة المراكز
            </a>
        </div>
    </div>

    {{-- Global Stats — 6 cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        @foreach([
            ['label' => 'المراكز',        'value' => $centersCount   ?? 0, 'color' => 'text-blue-600',   'bg' => 'bg-blue-50'],
            ['label' => 'الطلاب',         'value' => $studentsCount  ?? 0, 'color' => 'text-gray-900',   'bg' => 'bg-gray-50'],
            ['label' => 'الحلقات',        'value' => $halaqatCount   ?? 0, 'color' => 'text-gray-900',   'bg' => 'bg-gray-50'],
            ['label' => 'المحفظون',       'value' => $teachersCount  ?? 0, 'color' => 'text-gray-900',   'bg' => 'bg-gray-50'],
            ['label' => 'حافظو القرآن',   'value' => $quranCompleted ?? 0, 'color' => 'text-amber-600',  'bg' => 'bg-amber-50'],
            ['label' => 'تسميع اليوم',    'value' => $memTodayCount  ?? 0, 'color' => 'text-emerald-600','bg' => 'bg-emerald-50'],
        ] as $stat)
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">
                <div class="w-8 h-8 rounded-lg {{ $stat['bg'] }} flex items-center justify-center mb-3">
                    <span class="text-xs font-bold {{ $stat['color'] }}">#</span>
                </div>
                <p class="text-xs font-medium text-gray-500">{{ $stat['label'] }}</p>
                <p class="text-2xl font-bold mt-1 {{ $stat['color'] }}">{{ number_format($stat['value']) }}</p>
            </div>
        @endforeach
    </div>

    {{-- Monthly Trend + نجمة الشهر --}}
    @php
        $trend    = $monthlyTrend ?? [];
        $maxAyahs = max(array_column($trend, 'ayahs') ?: [1]);
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Bar Chart: last 6 months --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">الآيات المحفوظة — آخر 6 أشهر</h2>
                <p class="text-xs text-gray-400 mt-0.5">مجموع آيات الحفظ الجديد لجميع المراكز</p>
            </div>
            <div class="p-6">
                @if(empty($trend) || $maxAyahs === 0)
                    <p class="text-center text-gray-400 py-8 text-sm">لا توجد بيانات كافية.</p>
                @else
                    <div class="flex items-end gap-3 h-40 rtl:flex-row-reverse">
                        @foreach($trend as $m)
                            @php
                                $barPct = $maxAyahs > 0 ? round($m['ayahs'] / $maxAyahs * 100) : 0;
                                $isLast = $loop->last;
                            @endphp
                            <div class="flex flex-col items-center gap-1 flex-1 min-w-0">
                                <span class="text-[10px] text-gray-500 font-medium truncate w-full text-center">
                                    {{ $m['ayahs'] > 0 ? number_format($m['ayahs']) : '' }}
                                </span>
                                <div class="w-full rounded-t-md transition-all"
                                     style="height: {{ max(4, $barPct * 1.2) }}px; background: {{ $isLast ? '#2563eb' : '#93c5fd' }}">
                                </div>
                                <span class="text-[10px] text-gray-500 truncate w-full text-center">{{ $m['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex items-center gap-4 text-xs text-gray-400">
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-blue-600"></span> الشهر الحالي</span>
                        <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-sm bg-blue-300"></span> الأشهر السابقة</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- نجمة الشهر --}}
        <div class="bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-200 shadow-sm rounded-xl flex flex-col">
            <div class="px-6 py-4 border-b border-amber-100">
                <h2 class="text-base font-semibold text-amber-800">نجم الشهر</h2>
                <p class="text-xs text-amber-500 mt-0.5">الأكثر حفظاً هذا الشهر</p>
            </div>
            <div class="p-6 flex-1 flex flex-col items-center justify-center text-center">
                @if(!empty($monthStar))
                    <div class="w-16 h-16 rounded-full bg-amber-400 text-white flex items-center justify-center text-2xl font-bold shadow-md mb-4">
                        {{ mb_substr($monthStar['name'], 0, 1) }}
                    </div>
                    <p class="font-bold text-gray-900 text-lg leading-snug">{{ $monthStar['name'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $monthStar['center_name'] }}</p>
                    <div class="mt-4 bg-white border border-amber-200 rounded-xl px-5 py-3 shadow-sm">
                        <p class="text-3xl font-bold text-amber-600 leading-none">{{ number_format($monthStar['month_ayahs']) }}</p>
                        <p class="text-xs text-gray-400 mt-1">آية هذا الشهر</p>
                    </div>
                @else
                    <p class="text-sm text-gray-400 py-8">لا توجد بيانات هذا الشهر.</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Centers Ranking + Top Students --}}
    @php
        $cd        = ($centersData ?? collect())->where('is_active', true)->sortByDesc('avg_progress')->values();
        $topRankBy = $cd->sortByDesc('completers_count')->first();
    @endphp
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Centers Comparison Table --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-800">تصنيف المراكز</h2>
                    <p class="text-xs text-gray-400 mt-0.5">مرتبة حسب متوسط التقدم</p>
                </div>
                <a href="{{ route('centers.index') }}"
                   class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                    إدارة المراكز ←
                </a>
            </div>
            <div class="overflow-x-auto">
                @if($cd->isEmpty())
                    <p class="text-center text-gray-400 py-8 text-sm">لا توجد مراكز نشطة.</p>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 font-semibold">
                                <th class="px-4 py-3 text-right">#</th>
                                <th class="px-4 py-3 text-right">المركز</th>
                                <th class="px-4 py-3 text-center">الطلاب</th>
                                <th class="px-4 py-3 text-center min-w-[140px]">متوسط التقدم</th>
                                <th class="px-4 py-3 text-center">حافظو القرآن</th>
                                <th class="px-4 py-3 text-center">آيات الشهر</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($cd as $i => $center)
                                @php
                                    $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '' };
                                    $pct   = $center->avg_progress;
                                    $barColor = $pct >= 70 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-blue-500' : 'bg-gray-300');
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-3 text-gray-400 font-medium w-10">
                                        @if($medal)
                                            <span>{{ $medal }}</span>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('centers.show', $center) }}"
                                           class="font-semibold text-gray-900 hover:text-blue-600 transition-colors truncate block max-w-[160px]">
                                            {{ $center->name }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center text-gray-700 font-medium">
                                        {{ $center->students_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                                                <div class="{{ $barColor }} h-1.5 rounded-full"
                                                     style="width: {{ min(100, $pct) }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-600 w-10 text-left shrink-0">{{ $pct }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($center->completers_count > 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold ring-1 ring-inset ring-amber-200">
                                                {{ $center->completers_count }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs font-medium text-blue-600">
                                        {{ $center->month_ayahs > 0 ? number_format($center->month_ayahs) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        {{-- Top 5 Students Leaderboard --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl flex flex-col">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">أفضل الطلاب حفظاً</h2>
                <p class="text-xs text-gray-400 mt-0.5">الأعلى في إجمالي الآيات المحفوظة</p>
            </div>
            <div class="p-4 flex-1 space-y-2">
                @forelse(($topStudents ?? collect()) as $idx => $ts)
                    @php
                        $rankColors = ['bg-amber-400 text-white', 'bg-gray-300 text-gray-700', 'bg-orange-400 text-white'];
                        $rankBg = $rankColors[$idx] ?? 'bg-gray-100 text-gray-500';
                        $pctOfQuran = round($ts->total_ayahs / 6236 * 100, 1);
                    @endphp
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 hover:bg-gray-50 transition-colors">
                        <span class="w-7 h-7 rounded-full {{ $rankBg }} flex items-center justify-center text-xs font-bold shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $ts->name }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ $ts->center_name }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-bold text-blue-600">{{ number_format($ts->total_ayahs) }}</p>
                            <p class="text-[10px] text-gray-400">{{ $pctOfQuran }}% من القرآن</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-6">لا توجد بيانات.</p>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Centers Grid --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">المراكز</h2>
            <a href="{{ route('centers.index') }}"
               class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                إدارة المراكز ←
            </a>
        </div>

        <div class="p-6">
            @if(($centersData ?? collect())->isEmpty())
                <p class="text-center text-gray-400 py-8 text-sm">لم يتم إضافة أي مركز بعد.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach(($centersData ?? collect()) as $center)
                        <a href="{{ route('centers.show', $center) }}"
                           class="block border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm shrink-0 group-hover:bg-blue-700 transition-colors">
                                        {{ mb_substr($center->name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-900 truncate text-sm">{{ $center->name }}</span>
                                </div>
                                <span @class([
                                    'shrink-0 inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset',
                                    'bg-emerald-50 text-emerald-700 ring-emerald-200' => $center->is_active,
                                    'bg-gray-100 text-gray-500 ring-gray-200'         => !$center->is_active,
                                ])>
                                    {{ $center->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </div>

                            {{-- avg progress bar --}}
                            @if($center->avg_progress > 0)
                                <div class="mb-3">
                                    <div class="flex items-center justify-between text-[10px] text-gray-400 mb-1">
                                        <span>متوسط التقدم</span>
                                        <span class="font-semibold text-gray-600">{{ $center->avg_progress }}%</span>
                                    </div>
                                    <div class="h-1.5 bg-gray-100 rounded-full">
                                        <div class="h-1.5 rounded-full bg-blue-500"
                                             style="width: {{ min(100, $center->avg_progress) }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="grid grid-cols-3 gap-2 text-center mb-2">
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-[10px] text-gray-400">الحلقات</p>
                                    <p class="font-bold text-sm text-gray-800 mt-0.5">{{ $center->halaqas_count }}</p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-2">
                                    <p class="text-[10px] text-gray-400">الطلاب</p>
                                    <p class="font-bold text-sm text-gray-800 mt-0.5">{{ $center->students_count }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-lg p-2">
                                    <p class="text-[10px] text-amber-600 font-medium">حافظو القرآن</p>
                                    <p class="font-bold text-sm text-amber-700 mt-0.5">{{ $center->completers_count }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-center">
                                <div class="bg-emerald-50 rounded-lg p-2 ring-1 ring-inset ring-emerald-100">
                                    <p class="text-[10px] text-emerald-600 font-medium">التسميع</p>
                                    <p class="font-bold text-sm text-emerald-700 mt-0.5">{{ number_format($center->mems_count) }}</p>
                                </div>
                                <div class="bg-red-50 rounded-lg p-2 ring-1 ring-inset ring-red-100">
                                    <p class="text-[10px] text-red-600 font-medium">الغياب</p>
                                    <p class="font-bold text-sm text-red-700 mt-0.5">{{ number_format($center->absences_count) }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Performance Rankings --}}
    @php
        $cdActive = ($centersData ?? collect())->where('is_active', true);
        $topMem   = $cdActive->sortByDesc('mems_count')->first();
        $topProg  = $cdActive->sortByDesc('avg_progress')->first();
        $topComp  = $cdActive->sortByDesc('completers_count')->first();
    @endphp
    @if($cdActive->isNotEmpty())
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-800">مؤشرات الأداء</h2>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">

            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-3">الأكثر تسميعاً</p>
                @if($topMem)
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $topMem->name }}</p>
                    <p class="text-3xl font-bold text-emerald-700 mt-1 leading-none">{{ number_format($topMem->mems_count) }}</p>
                    <p class="text-xs text-gray-400 mt-1">سجل تسميع</p>
                @else
                    <p class="text-sm text-gray-400">لا توجد بيانات</p>
                @endif
            </div>

            <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">
                <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-3">الأعلى تقدماً</p>
                @if($topProg)
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $topProg->name }}</p>
                    <p class="text-3xl font-bold text-blue-700 mt-1 leading-none">{{ $topProg->avg_progress }}%</p>
                    <p class="text-xs text-gray-400 mt-1">متوسط تقدم الطلاب</p>
                @else
                    <p class="text-sm text-gray-400">لا توجد بيانات</p>
                @endif
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide mb-3">أكثر حفاظ للقرآن</p>
                @if($topComp && $topComp->completers_count > 0)
                    <p class="font-semibold text-gray-800 text-sm truncate">{{ $topComp->name }}</p>
                    <p class="text-3xl font-bold text-amber-700 mt-1 leading-none">{{ $topComp->completers_count }}</p>
                    <p class="text-xs text-gray-400 mt-1">طالب أتم حفظ القرآن</p>
                @else
                    <p class="text-sm text-gray-400">لا يوجد حافظ مكتمل بعد</p>
                @endif
            </div>

        </div>
    </div>
    @endif

    {{-- حافظو القرآن الكريم --}}
    @if(($completedStudents ?? collect())->isNotEmpty())
    <div class="bg-white border border-amber-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-amber-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-400 flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-semibold text-amber-800">حافظو القرآن الكريم</h2>
                    <p class="text-xs text-amber-500 mt-0.5">الطلاب الذين أتموا حفظ القرآن كاملاً</p>
                </div>
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-bold ring-1 ring-inset ring-amber-300">
                    {{ ($completedStudents ?? collect())->count() }}
                </span>
            </div>
            <a href="{{ route('hifdh.progress') }}"
               class="text-xs font-medium text-amber-600 hover:text-amber-700 transition-colors">
                صفحة التقدم ←
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-amber-50 border-b border-amber-100 text-xs text-amber-700 font-semibold">
                        <th class="px-4 py-3 text-right">#</th>
                        <th class="px-4 py-3 text-right">اسم الطالب</th>
                        <th class="px-4 py-3 text-center">العمر</th>
                        <th class="px-4 py-3 text-right">المركز</th>
                        <th class="px-4 py-3 text-right">الحلقة</th>
                        <th class="px-4 py-3 text-center">رقم هاتف ولي الأمر</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-amber-50">
                    @foreach(($completedStudents ?? collect()) as $i => $cs)
                        <tr class="hover:bg-amber-50/50 transition-colors">
                            <td class="px-4 py-3 text-amber-400 font-bold text-center w-10">
                                {{ $i + 1 }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-amber-400 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ mb_substr($cs->name, 0, 1) }}
                                    </div>
                                    <span class="font-semibold text-gray-900">{{ $cs->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                @if($cs->age)
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-xs font-medium">
                                        {{ $cs->age }} سنة
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-medium">
                                {{ $cs->halaqa?->center?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $cs->halaqa?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($cs->phone)
                                    <a href="tel:{{ $cs->phone }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium ring-1 ring-inset ring-emerald-200 hover:bg-emerald-100 transition-colors"
                                       dir="ltr">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $cs->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-300 text-xs">غير مسجّل</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@else
    {{-- ============================================================ --}}
    {{-- داشبورد الأدمن / المحفظ                                      --}}
    {{-- ============================================================ --}}

    {{-- Page Header --}}
    <div class="bg-gradient-to-l from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">
                    {{ auth()->user()->hasRole('admin') ? 'Admin' : 'محفظ' }}
                </p>
                <h1 class="text-2xl font-bold mt-1">لوحة التحكم</h1>
                <p class="text-blue-200 mt-1 text-sm">متابعة الحلقات والطلاب وسجلات التسميع والغياب.</p>
            </div>
            <a href="{{ route('students.index') }}"
               class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-white/15 hover:bg-white/25 text-white font-medium text-sm border border-white/20 transition-colors">
                إضافة طالب
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-blue-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">الطلاب</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($studentsCount ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-indigo-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">الحلقات</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($halaqatCount ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">غياب اليوم</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($absTodayCount ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">تسميع اليوم</p>
                <p class="text-2xl font-bold text-emerald-600">{{ number_format($memTodayCount ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex items-center gap-4">
            <div class="w-11 h-11 rounded-lg bg-violet-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">آيات هذا الشهر</p>
                <p class="text-2xl font-bold text-violet-600">{{ number_format($monthAyahs ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Two Columns --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Latest Memorizations --}}
        <div class="lg:col-span-2 bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800">آخر سجلات التسميع</h2>
            </div>
            <div class="p-6">
                @include('livewire.dashboard.partials.latest-mems', ['latestMems' => $latestMems ?? collect()])
            </div>
        </div>

        {{-- Absences Today --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-base font-semibold text-gray-800">غياب اليوم</h2>
                <span class="text-xs font-semibold text-gray-400">{{ number_format($absTodayCount ?? 0) }}</span>
            </div>
            <div class="p-6">
                <div class="space-y-2">
                    @forelse(($todayAbsences ?? collect()) as $a)
                        <div class="flex items-center justify-between px-3 py-2.5 rounded-lg bg-gray-50 border border-gray-100">
                            <span class="text-sm font-medium text-gray-800 truncate">{{ $a->student?->name ?? '—' }}</span>
                            <span class="shrink-0 inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 ring-1 ring-inset ring-red-200">غائب</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400 text-center py-4">لا يوجد غياب مسجّل اليوم.</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- Students Needing Attention --}}
    @if(($inactiveStudents ?? collect())->isNotEmpty())
    <div class="bg-white border border-amber-200 shadow-sm rounded-xl">
        <div class="px-6 py-4 border-b border-amber-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h2 class="text-base font-semibold text-amber-800">طلاب يحتاجون متابعة</h2>
                <span class="text-xs text-amber-600 bg-amber-100 px-2 py-0.5 rounded-full font-medium">
                    لم يُسمّعوا منذ 14 يوماً أو أكثر
                </span>
            </div>
            <a href="{{ route('hifdh.progress') }}"
               class="text-xs font-medium text-amber-600 hover:text-amber-700 transition-colors">
                عرض الكل ←
            </a>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($inactiveStudents as $s)
                    @php
                        $days = $s->last_session
                            ? (int) now()->diffInDays($s->last_session)
                            : null;
                        $badgeClass = $days === null
                            ? 'bg-gray-100 text-gray-600'
                            : ($days >= 30 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                    @endphp
                    <div class="flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-amber-100 bg-amber-50">
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-900 truncate">{{ $s->name }}</div>
                            <div class="text-xs text-gray-400 truncate mt-0.5">{{ $s->halaqa?->name ?? '—' }}</div>
                        </div>
                        <span class="shrink-0 inline-flex px-2.5 py-1 rounded-lg text-xs font-bold {{ $badgeClass }}">
                            @if($days === null)
                                لم يبدأ
                            @else
                                {{ $days }} يوم
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

@endif

</div>

</x-layouts.app>
