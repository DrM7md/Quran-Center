<div class="space-y-6">

    {{-- ══ Header ══════════════════════════════════════════════════ --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">تقدم الحفظ</h1>
                <p class="text-gray-500 text-sm mt-0.5">نظرة شاملة على مسيرة كل طالب في حفظ القرآن الكريم.</p>
            </div>
            {{-- Export Excel --}}
            <a href="{{ route('progress.export', $filterHalaqaId ? ['halaqa' => $filterHalaqaId] : []) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                تصدير Excel
            </a>
        </div>
    </div>

    {{-- ══ Summary Cards ════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <div class="text-3xl font-black text-gray-800">{{ $totalStudents }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">إجمالي الطلاب</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <div class="text-3xl font-black text-emerald-600">{{ $activeStudents }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">نشطون (30 يوم)</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <div class="text-3xl font-black text-blue-600">{{ $avgPct }}%</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">متوسط التقدم</div>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm text-center">
            <div class="text-3xl font-black text-violet-600">{{ number_format($monthTotal) }}</div>
            <div class="text-xs text-gray-500 mt-1 font-medium">آيات هذا الشهر</div>
        </div>
    </div>

    {{-- ══ Filters ══════════════════════════════════════════════════ --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-4 flex flex-wrap gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-48">
            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="ابحث باسم الطالب..."
                   class="w-full pr-9 pl-3 py-2 text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"/>
        </div>

        {{-- Halaqa filter (admin or muhafidh with multiple halaqas) --}}
        @if($halaqas->count() > 1)
            <select wire:model.live="filterHalaqaId"
                    class="py-2 text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                <option value="">كل الحلقات</option>
                @foreach($halaqas as $h)
                    <option value="{{ $h->id }}">{{ $h->name }}</option>
                @endforeach
            </select>
        @endif

        {{-- Sort --}}
        <select wire:model.live="sortBy"
                class="py-2 text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            <option value="progress">الأكثر حفظاً</option>
            <option value="name">الاسم</option>
            <option value="last_session">آخر نشاط</option>
            <option value="inactive">الأقل نشاطاً</option>
        </select>
    </div>

    {{-- ══ Students Grid ════════════════════════════════════════════ --}}
    @if($rows->isEmpty())
        <div class="bg-white border border-gray-200 rounded-xl p-12 text-center text-gray-400 shadow-sm">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-sm">لا يوجد طلاب مطابقون.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($rows as $row)
                @php
                    $circumf   = round(2 * M_PI * 18, 2); // mini ring r=18
                    $dashOffset = round($circumf * (1 - $row['pct'] / 100), 2);
                    $ringColor  = $row['pct'] >= 75 ? '#10b981'
                        : ($row['pct'] >= 40 ? '#3b82f6'
                        : ($row['pct'] > 0  ? '#f59e0b' : '#e5e7eb'));
                @endphp

                <button wire:click="openDetail({{ $row['id'] }})"
                        wire:key="student-{{ $row['id'] }}"
                        class="bg-white border rounded-xl p-4 shadow-sm text-right w-full transition-all
                               hover:shadow-md hover:border-blue-300 hover:-translate-y-0.5
                               {{ $detailStudentId === $row['id'] ? 'border-blue-400 ring-1 ring-blue-400' : 'border-gray-200' }}">

                    {{-- Top row: avatar + ring + badge --}}
                    <div class="flex items-start justify-between mb-3">
                        {{-- Avatar --}}
                        <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 font-black text-lg flex items-center justify-center shrink-0">
                            {{ mb_substr($row['name'], 0, 1) }}
                        </div>

                        {{-- Mini progress ring --}}
                        <div class="relative w-12 h-12">
                            <svg viewBox="0 0 44 44" class="w-full h-full -rotate-90">
                                <circle cx="22" cy="22" r="18" fill="none" stroke="#f3f4f6" stroke-width="5"/>
                                <circle cx="22" cy="22" r="18" fill="none"
                                        stroke="{{ $ringColor }}" stroke-width="5"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ $circumf }}"
                                        stroke-dashoffset="{{ $dashOffset }}"/>
                            </svg>
                            <div class="absolute inset-0 flex items-center justify-center">
                                <span class="text-[10px] font-bold text-gray-700">{{ $row['pct'] }}%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Name + halaqa --}}
                    <div class="mb-3">
                        <div class="font-bold text-gray-900 text-sm truncate">{{ $row['name'] }}</div>
                        <div class="text-xs text-gray-400 mt-0.5 truncate">{{ $row['halaqa'] }}</div>
                    </div>

                    {{-- Stats row --}}
                    <div class="grid grid-cols-2 gap-2 text-center mb-3">
                        <div class="bg-gray-50 rounded-lg py-1.5">
                            <div class="text-sm font-bold text-gray-800">{{ number_format($row['totalAyahs']) }}</div>
                            <div class="text-[10px] text-gray-500">آية</div>
                        </div>
                        <div class="bg-gray-50 rounded-lg py-1.5">
                            <div class="text-sm font-bold text-gray-800">{{ $row['juz'] }}</div>
                            <div class="text-[10px] text-gray-500">جزء</div>
                        </div>
                    </div>

                    {{-- Bottom: last session + status badge --}}
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] text-gray-400">
                            {{ $row['lastSession'] ? \Carbon\Carbon::parse($row['lastSession'])->format('Y/m/d') : 'لم يبدأ' }}
                        </span>
                        @if(! $row['started'])
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500">
                                لم يبدأ
                            </span>
                        @elseif($row['isActive'])
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                نشط
                            </span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700">
                                خامل
                            </span>
                        @endif
                    </div>

                    {{-- Monthly ayahs / goal --}}
                    @if($row['monthTarget'])
                        @php
                            $goalPct = min(100, $row['monthTarget'] > 0
                                ? round($row['monthAyahs'] / $row['monthTarget'] * 100)
                                : 0);
                            $goalColor = $goalPct >= 100 ? 'bg-emerald-500' : ($goalPct >= 60 ? 'bg-blue-400' : 'bg-amber-400');
                        @endphp
                        <div class="mt-2 pt-2 border-t border-gray-100">
                            <div class="flex justify-between text-[10px] text-gray-500 mb-1">
                                <span>الهدف الشهري</span>
                                <span class="{{ $goalPct >= 100 ? 'text-emerald-600 font-bold' : 'text-gray-500' }}">
                                    {{ number_format($row['monthAyahs']) }} / {{ number_format($row['monthTarget']) }}
                                </span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $goalColor }}" style="width: {{ $goalPct }}%"></div>
                            </div>
                        </div>
                    @elseif($row['monthAyahs'] > 0)
                        <div class="mt-2 pt-2 border-t border-gray-100 text-[10px] text-emerald-600 font-semibold text-center">
                            +{{ number_format($row['monthAyahs']) }} آية هذا الشهر
                        </div>
                    @endif
                </button>
            @endforeach
        </div>
    @endif

    {{-- ══ Detail Modal ════════════════════════════════════════════ --}}
    @if($showDetailModal && !empty($detailData))
        @php $d = $detailData; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6"
             x-data x-on:keydown.escape.window="$wire.closeDetail()">

            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                 x-on:click.stop>

                {{-- Modal header --}}
                <div class="sticky top-0 bg-white border-b border-gray-100 flex items-center justify-between px-6 py-4 z-10 rounded-t-2xl">
                    <div>
                        <div class="font-black text-lg text-gray-900">{{ $d['student']->name }}</div>
                        <div class="text-xs text-gray-400">{{ $d['student']->halaqa?->name ?? '—' }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                        {{-- Print report --}}
                        <a href="{{ route('students.report', $d['student']->id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold rounded-lg transition-colors border border-blue-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            طباعة
                        </a>
                        <button wire:click="closeDetail"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-7">

                    {{-- ─ حفظ جديد ─────────────────────────────── --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                            <h3 class="font-bold text-gray-700 text-sm">الحفظ الجديد</h3>
                        </div>

                        {{-- Ring + stats --}}
                        <div class="flex items-center gap-5 mb-5">
                            {{-- SVG ring --}}
                            <div class="relative w-24 h-24 shrink-0">
                                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="#f3f4f6" stroke-width="10"/>
                                    <circle cx="50" cy="50" r="38" fill="none" stroke="#10b981" stroke-width="10"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ $d['circleCircumf'] }}"
                                            stroke-dashoffset="{{ round($d['circleCircumf'] * (1 - $d['pct'] / 100), 2) }}"/>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-lg font-black text-gray-800">{{ $d['pct'] }}%</span>
                                    <span class="text-[10px] text-gray-400">من القرآن</span>
                                </div>
                            </div>

                            {{-- Key stats --}}
                            <div class="grid grid-cols-2 gap-2 flex-1 text-center">
                                <div class="bg-emerald-50 rounded-xl p-2.5">
                                    <div class="text-base font-black text-emerald-700">{{ number_format($d['totalAyahs']) }}</div>
                                    <div class="text-[11px] text-emerald-600">آية محفوظة</div>
                                </div>
                                <div class="bg-teal-50 rounded-xl p-2.5">
                                    <div class="text-base font-black text-teal-700">{{ $d['juz'] }}</div>
                                    <div class="text-[11px] text-teal-600">جزء</div>
                                </div>
                                @if($d['direction'])
                                    <div class="bg-gray-50 rounded-xl p-2.5 col-span-2">
                                        <div class="text-sm font-bold {{ $d['direction'] === 'top_down' ? 'text-emerald-700' : 'text-blue-700' }}">
                                            {{ $d['direction'] === 'top_down' ? '↓ من الفاتحة نزولاً' : '↑ من الناس صعوداً' }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">اتجاه الحفظ</div>
                                    </div>
                                @endif
                                @if($d['lastSession'])
                                    <div class="bg-gray-50 rounded-xl p-2.5 col-span-2">
                                        <div class="text-sm font-bold text-gray-700">
                                            {{ \Carbon\Carbon::parse($d['lastSession'])->format('Y/m/d') }}
                                        </div>
                                        <div class="text-[11px] text-gray-400">آخر جلسة حفظ</div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Surah bars --}}
                        @if(!empty($d['surahsData']))
                            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">السور المحفوظة</p>
                            <div class="space-y-2.5 max-h-48 overflow-y-auto pl-1">
                                @foreach($d['surahsData'] as $row)
                                    <div>
                                        <div class="flex justify-between text-xs mb-0.5">
                                            <span class="font-medium text-gray-700">
                                                <span class="text-gray-400 ml-1">{{ $row['number'] }}.</span>{{ $row['name'] }}
                                            </span>
                                            <span class="{{ $row['pct'] >= 100 ? 'text-emerald-600 font-bold' : 'text-gray-400' }}">
                                                {{ $row['memorized'] }}/{{ $row['total'] }} @if($row['pct'] >= 100) ✓ @endif
                                            </span>
                                        </div>
                                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $row['pct'] >= 100 ? 'bg-emerald-500' : 'bg-emerald-300' }}"
                                                 style="width: {{ $row['pct'] }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-400 text-center py-3">لا توجد سجلات حفظ جديد بعد.</p>
                        @endif
                    </div>

                    <div class="border-t border-gray-100"></div>

                    {{-- ─ المراجعة ──────────────────────────────── --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                            <h3 class="font-bold text-gray-700 text-sm">المراجعة</h3>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-5">
                            <div class="bg-blue-50 rounded-xl p-3 text-center">
                                <div class="text-xl font-black text-blue-700">{{ number_format($d['rev30']) }}</div>
                                <div class="text-[11px] text-blue-500 mt-0.5">آية · آخر 30 يوم</div>
                            </div>
                            <div class="bg-gray-50 rounded-xl p-3 text-center">
                                <div class="text-xl font-black text-gray-700">{{ number_format($d['reviewTotal']) }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">إجمالي المراجعة</div>
                            </div>
                        </div>

                        {{-- Monthly bar chart --}}
                        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-3">النشاط الشهري (6 أشهر)</p>
                        <div class="flex items-center gap-4 mb-3 text-[11px] text-gray-500">
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-400 inline-block"></span> حفظ جديد</span>
                            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-blue-400 inline-block"></span> مراجعة</span>
                        </div>

                        <div class="flex items-end gap-2" style="height: 80px;">
                            @foreach($d['months'] as $m)
                                @php
                                    $newH = $d['maxMonth'] > 0 ? max(2, (int) round($m['new'] / $d['maxMonth'] * 64)) : 2;
                                    $revH = $d['maxMonth'] > 0 ? max(2, (int) round($m['rev'] / $d['maxMonth'] * 64)) : 2;
                                @endphp
                                <div class="flex-1 flex flex-col items-center gap-0.5">
                                    <div class="w-full flex items-end gap-0.5 justify-center" style="height: 64px;">
                                        <div class="flex-1 rounded-t-sm {{ $m['new'] > 0 ? 'bg-emerald-400' : 'bg-gray-100' }}"
                                             style="height: {{ $m['new'] > 0 ? $newH : 3 }}px"></div>
                                        <div class="flex-1 rounded-t-sm {{ $m['rev'] > 0 ? 'bg-blue-400' : 'bg-gray-100' }}"
                                             style="height: {{ $m['rev'] > 0 ? $revH : 3 }}px"></div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 mt-0.5">{{ $m['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    {{-- ─ الهدف الشهري ────────────────────────────── --}}
                    <div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-500 shrink-0"></span>
                            <h3 class="font-bold text-gray-700 text-sm">الهدف الشهري</h3>
                        </div>

                        @if($d['monthlyTarget'])
                            @php
                                $gPct   = min(100, round($d['monthAyahsForStudent'] / $d['monthlyTarget'] * 100));
                                $gColor = $gPct >= 100 ? 'bg-emerald-500' : ($gPct >= 60 ? 'bg-blue-400' : 'bg-amber-400');
                                $gText  = $gPct >= 100 ? 'text-emerald-700' : 'text-gray-700';
                            @endphp
                            <div class="mb-4 p-4 bg-violet-50 rounded-xl border border-violet-100">
                                <div class="flex items-end justify-between mb-2">
                                    <div>
                                        <span class="text-2xl font-black {{ $gText }}">{{ number_format($d['monthAyahsForStudent']) }}</span>
                                        <span class="text-sm text-gray-400 mr-1">/ {{ number_format($d['monthlyTarget']) }} آية</span>
                                    </div>
                                    <span class="text-sm font-bold {{ $gPct >= 100 ? 'text-emerald-600' : 'text-violet-600' }}">{{ $gPct }}%</span>
                                </div>
                                <div class="h-2.5 bg-white rounded-full overflow-hidden border border-violet-200">
                                    <div class="h-full rounded-full {{ $gColor }} transition-all duration-500"
                                         style="width: {{ $gPct }}%"></div>
                                </div>
                                @if($gPct >= 100)
                                    <p class="text-xs text-emerald-600 font-semibold mt-2 text-center">أحسنت! تم تحقيق الهدف الشهري</p>
                                @else
                                    <p class="text-xs text-gray-400 mt-2 text-center">
                                        يحتاج {{ number_format($d['monthlyTarget'] - $d['monthAyahsForStudent']) }} آية لإتمام الهدف
                                    </p>
                                @endif
                            </div>
                        @endif

                        {{-- Goal input --}}
                        <div class="flex gap-2">
                            <input type="number"
                                   wire:model="goalInput"
                                   min="1" max="6236"
                                   placeholder="{{ $d['monthlyTarget'] ? 'تعديل الهدف...' : 'حدد هدف شهري (عدد الآيات)...' }}"
                                   class="flex-1 text-sm rounded-lg border-gray-300 focus:border-violet-500 focus:ring-violet-500 py-2"/>
                            <button wire:click="saveGoal"
                                    class="px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold rounded-lg transition-colors shrink-0">
                                حفظ
                            </button>
                            @if($d['monthlyTarget'])
                                <button onclick="$wire.goalInput = ''; $wire.saveGoal()"
                                        class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-semibold rounded-lg transition-colors shrink-0">
                                    إلغاء الهدف
                                </button>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
