<div class="space-y-6" x-data="{ tab: 'overview' }">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-blue-700 to-indigo-800 rounded-xl p-6 text-white">
        <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
            <div>
                <a href="{{ route('centers.index') }}"
                   class="inline-flex items-center gap-1 text-blue-200 text-sm hover:text-white mb-3 transition">
                    ← المراكز
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold">{{ $center->name }}</h1>
                <p class="text-blue-200 mt-1 text-sm">
                    {{ $admin ? 'الأدمن: ' . $admin->name . ' — ' . $admin->email : 'لا يوجد أدمن مُعيَّن' }}
                </p>
            </div>
            <span @class([
                'shrink-0 self-start sm:self-auto inline-flex px-4 py-2 rounded-lg text-sm font-bold border-2',
                'bg-emerald-400/20 text-emerald-200 border-emerald-300/40' => $center->is_active,
                'bg-gray-400/20 text-gray-200 border-gray-300/40'       => !$center->is_active,
            ])>
                {{ $center->is_active ? 'نشط' : 'معطل' }}
            </span>
        </div>

        {{-- Quick stats bar --}}
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white/15 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold">{{ $halaqas->count() }}</div>
                <div class="text-blue-200 text-xs mt-0.5">الحلقات</div>
            </div>
            <div class="bg-white/15 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold">{{ $students->count() }}</div>
                <div class="text-blue-200 text-xs mt-0.5">الطلاب</div>
            </div>
            <div class="bg-white/15 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold">{{ $muhafidhs->count() }}</div>
                <div class="text-blue-200 text-xs mt-0.5">المحفظون</div>
            </div>
            <div class="bg-white/15 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-emerald-300">{{ $memTodayCount }}</div>
                <div class="text-blue-200 text-xs mt-0.5">تسميع اليوم</div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">

        {{-- Tab navigation --}}
        <div class="flex items-center gap-1 p-3 border-b border-gray-200 overflow-x-auto">
            @foreach([
                ['key' => 'overview',       'label' => 'نظرة عامة'],
                ['key' => 'halaqas',        'label' => 'الحلقات'],
                ['key' => 'students',       'label' => 'الطلاب'],
                ['key' => 'muhafidhs',      'label' => 'المحفظون'],
                ['key' => 'memorizations',  'label' => 'التسميع'],
                ['key' => 'absences',       'label' => 'الغياب'],
            ] as $t)
                <button type="button"
                        @click="tab = '{{ $t['key'] }}'"
                        :class="tab === '{{ $t['key'] }}' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                        class="shrink-0 px-4 py-2 rounded-lg text-sm font-bold transition-all duration-150">
                    {{ $t['label'] }}
                </button>
            @endforeach
        </div>

        {{-- Tab: نظرة عامة --}}
        <div x-show="tab === 'overview'" x-cloak class="p-5 sm:p-6 space-y-5">

            {{-- الحلقات --}}
            <div>
                <h2 class="text-base font-bold text-gray-700 mb-3">الحلقات ({{ $halaqas->count() }})</h2>
                @if($halaqas->isEmpty())
                    <p class="text-gray-500 text-sm">لا توجد حلقات.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($halaqas as $h)
                            <div class="flex items-center justify-between p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <span class="font-bold text-sm truncate">{{ $h->name }}</span>
                                <span class="shrink-0 ml-2 text-xs font-bold text-gray-500">{{ $h->students_count }} طالب</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- المحفظون --}}
            <div>
                <h2 class="text-base font-bold text-gray-700 mb-3">المحفظون ({{ $muhafidhs->count() }})</h2>
                @if($muhafidhs->isEmpty())
                    <p class="text-gray-500 text-sm">لا يوجد محفظون.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($muhafidhs as $m)
                            <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-gray-50">
                                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                                    {{ mb_substr($m->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-sm truncate">{{ $m->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $m->halaqas_count }} حلقة</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- آخر تسميع --}}
            <div>
                <h2 class="text-base font-bold text-gray-700 mb-3">آخر سجلات التسميع</h2>
                @include('livewire.dashboard.partials.latest-mems', ['latestMems' => $latestMems->take(5)])
            </div>
        </div>

        {{-- Tab: الحلقات --}}
        <div x-show="tab === 'halaqas'" x-cloak class="p-5 sm:p-6">
            <h2 class="text-lg font-bold mb-4">الحلقات ({{ $halaqas->count() }})</h2>
            @if($halaqas->isEmpty())
                <div class="py-10 text-center text-gray-500">ما فيه حلقات في هذا المركز.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-right">
                                <th class="pb-3 font-bold">#</th>
                                <th class="pb-3 font-bold">اسم الحلقة</th>
                                <th class="pb-3 font-bold text-center">عدد الطلاب</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($halaqas as $i => $h)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 text-gray-400 w-10">{{ $i + 1 }}</td>
                                    <td class="py-3 font-bold">{{ $h->name }}</td>
                                    <td class="py-3 text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            {{ $h->students_count }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tab: الطلاب --}}
        <div x-show="tab === 'students'" x-cloak class="p-5 sm:p-6">
            <div class="flex items-center justify-between gap-3 mb-4 flex-col sm:flex-row">
                <h2 class="text-lg font-bold">الطلاب ({{ $students->count() }})</h2>
                <input wire:model.live="searchStudents"
                       placeholder="ابحث بالاسم..."
                       class="w-full sm:w-64 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm" />
            </div>

            @if($students->isEmpty())
                <div class="py-10 text-center text-gray-500">ما فيه طلاب{{ $searchStudents ? ' مطابقين للبحث' : '' }}.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-right">
                                <th class="pb-3 font-bold">#</th>
                                <th class="pb-3 font-bold">الاسم</th>
                                <th class="pb-3 font-bold">الحلقة</th>
                                <th class="pb-3 font-bold text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $i => $s)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="py-3 text-gray-400 w-10">{{ $i + 1 }}</td>
                                    <td class="py-3 font-bold">{{ $s->name }}</td>
                                    <td class="py-3 text-gray-600">{{ $s->halaqa?->name ?? '—' }}</td>
                                    <td class="py-3 text-center">
                                        <span @class([
                                            'inline-flex px-2.5 py-1 rounded-full text-xs font-bold border',
                                            'bg-emerald-50 text-emerald-700 border-emerald-100' => $s->is_active,
                                            'bg-gray-100 text-gray-500 border-gray-200'      => !$s->is_active,
                                        ])>
                                            {{ $s->is_active ? 'نشط' : 'معطل' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tab: المحفظون --}}
        <div x-show="tab === 'muhafidhs'" x-cloak class="p-5 sm:p-6">
            <h2 class="text-lg font-bold mb-4">المحفظون ({{ $muhafidhs->count() }})</h2>
            @if($muhafidhs->isEmpty())
                <div class="py-10 text-center text-gray-500">ما فيه محفظون في هذا المركز.</div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($muhafidhs as $m)
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-lg shrink-0">
                                    {{ mb_substr($m->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold truncate">{{ $m->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $m->email }}</div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    محفظ
                                </span>
                                <span class="text-xs text-gray-500">{{ $m->halaqas_count }} حلقة</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tab: التسميع --}}
        <div x-show="tab === 'memorizations'" x-cloak class="p-5 sm:p-6">
            <h2 class="text-lg font-bold mb-4">سجلات التسميع (آخر 15)</h2>
            @include('livewire.dashboard.partials.latest-mems', ['latestMems' => $latestMems])
        </div>

        {{-- Tab: الغياب --}}
        <div x-show="tab === 'absences'" x-cloak class="p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold">سجلات الغياب (آخر 20)</h2>
                @if($absTodayCount > 0)
                    <span class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                        غياب اليوم: {{ $absTodayCount }}
                    </span>
                @endif
            </div>

            @if($absences->isEmpty())
                <div class="py-10 text-center text-gray-500">ما فيه غيابات مسجّلة.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-gray-500 text-right">
                                <th class="pb-3 font-bold">الطالب</th>
                                <th class="pb-3 font-bold text-center">التاريخ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($absences as $a)
                                <tr @class([
                                    'transition',
                                    'bg-rose-50/60' => $a->date == now()->toDateString(),
                                    'hover:bg-gray-50' => $a->date != now()->toDateString(),
                                ])>
                                    <td class="py-3 font-bold">{{ $a->student?->name ?? '—' }}</td>
                                    <td class="py-3 text-center">
                                        <span @class([
                                            'px-2.5 py-1 rounded-full text-xs font-bold border',
                                            'bg-rose-100 text-rose-700 border-rose-200' => $a->date == now()->toDateString(),
                                            'bg-gray-100 text-gray-600 border-gray-200' => $a->date != now()->toDateString(),
                                        ])>
                                            {{ $a->date }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

</div>
