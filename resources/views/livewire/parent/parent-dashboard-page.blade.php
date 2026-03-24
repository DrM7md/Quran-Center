<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-emerald-700 to-teal-800 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold">أهلاً، {{ auth()->user()->name }}</h1>
                <p class="text-emerald-200 text-sm mt-1">متابعة تقدم أبنائك في حفظ القرآن الكريم</p>
            </div>
            <a href="{{ route('parent.request') }}"
               class="shrink-0 px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 text-white text-sm font-semibold transition-colors">
                إضافة طالب
            </a>
        </div>
    </div>

    {{-- Students Grid --}}
    @if($students->isEmpty())
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-10 text-center">
            <div class="w-16 h-16 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="font-bold text-gray-800 text-lg">لا يوجد أبناء مسجلون</h3>
            <p class="text-gray-500 text-sm mt-2 mb-5">أرسل طلب ربط لمتابعة ابنك في مركز التحفيظ.</p>
            <a href="{{ route('parent.request') }}"
               class="inline-flex px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                إضافة طالب الآن
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach($students as $student)
                <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 flex flex-col gap-4">

                    {{-- Student Header --}}
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xl shrink-0">
                            {{ mb_substr($student->name, 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <div class="font-bold text-gray-900 truncate">{{ $student->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5 truncate">
                                {{ $student->halaqa?->center?->name ?? '—' }}
                                @if($student->halaqa)
                                    <span class="text-gray-300 mx-1">•</span>
                                    {{ $student->halaqa->name }}
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">التسميع هذا الشهر</div>
                            <div class="text-xl font-bold text-emerald-700 mt-0.5">{{ $student->mems_this_month }}</div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">الغياب هذا الشهر</div>
                            <div class="text-xl font-bold {{ $student->absences_count > 3 ? 'text-red-600' : 'text-gray-800' }} mt-0.5">
                                {{ $student->absences_count }}
                            </div>
                        </div>
                        <div class="p-2.5 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">آخر جلسة</div>
                            <div class="text-xs font-bold text-gray-700 mt-1">
                                {{ $student->last_session ? \Carbon\Carbon::parse($student->last_session)->format('m/d') : '—' }}
                            </div>
                        </div>
                    </div>

                    {{-- Action --}}
                    <a href="{{ route('parent.student.show', $student) }}"
                       class="w-full text-center py-2 rounded-lg border border-gray-200 hover:bg-gray-50 text-sm font-semibold text-gray-700 transition-colors">
                        عرض التفاصيل
                    </a>
                </div>
            @endforeach
        </div>
    @endif

</div>
