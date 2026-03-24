<div class="space-y-6">

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 sm:p-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- Toolbar -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4 flex-col md:flex-row">
            <div>
                <h1 class="text-2xl font-bold">الغياب</h1>
                <p class="text-gray-600 mt-1">
                    الأصل حضور. علّم الغائب فقط — وإذا حضر شيل العلامة.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <input type="date"
                       wire:model.live="date"
                       class="w-full sm:w-auto rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                <input type="text"
                       wire:model.live="search"
                       placeholder="ابحث باسم الطالب أو الجوال..."
                       class="w-full sm:w-72 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
            </div>
        </div>

        <!-- Stats -->
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-200">
                <div class="text-xs text-gray-500">عدد الطلاب</div>
                <div class="text-2xl font-bold mt-1">{{ $total }}</div>
            </div>
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200">
                <div class="text-xs text-rose-700">الغائبين</div>
                <div class="text-2xl font-bold mt-1 text-rose-700">{{ $absents }}</div>
            </div>
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 hidden sm:block">
                <div class="text-xs text-emerald-700">الحاضرين (تقريبًا)</div>
                <div class="text-2xl font-bold mt-1 text-emerald-700">{{ max($total - $absents, 0) }}</div>
            </div>
        </div>
    </div>

<!-- Students list -->
<div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">

    <div class="flex items-center justify-between gap-3 flex-col sm:flex-row">
        <h2 class="text-lg font-bold">قائمة الطلاب</h2>

        <!-- Tabs -->
        <div class="inline-flex p-1 rounded-lg bg-gray-100 border border-gray-200">
            <button type="button"
                    wire:click="$set('tab','all')"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition
                           {{ $tab === 'all' ? 'bg-white shadow-sm border border-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                الكل
                <span class="ms-1 text-xs text-gray-500">({{ $total }})</span>
            </button>

            <button type="button"
                    wire:click="$set('tab','absents')"
                    class="px-4 py-2 rounded-lg text-sm font-bold transition
                           {{ $tab === 'absents' ? 'bg-white shadow-sm border border-gray-200' : 'text-gray-600 hover:text-gray-900' }}">
                الغائبين
                <span class="ms-1 text-xs text-rose-600">({{ $absents }})</span>
            </button>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @php
            $list = $students;
            if ($tab === 'absents') {
                $list = $students->filter(fn($s) => in_array($s->id, $absentIds, true));
            }
        @endphp

        @forelse($list as $st)
            @php $isAbsent = in_array($st->id, $absentIds, true); @endphp

            <div class="p-4 rounded-xl border shadow-sm
                        {{ $isAbsent ? 'border-rose-200 bg-rose-50' : 'border-gray-200 bg-white' }}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold truncate">{{ $st->name }}</div>
                        <div class="text-xs text-gray-500 mt-1 truncate">
                            {{ $st->halaqa?->name ?? '—' }} • {{ $st->phone ?? '—' }}
                        </div>
                    </div>

                    <label class="inline-flex items-center gap-2 select-none">
                        <input type="checkbox"
                               class="rounded border-gray-300 text-rose-600 focus:ring-rose-500"
                               @checked($isAbsent)
                               wire:change="setAbsent({{ $st->id }}, $event.target.checked)">
                        <span class="text-sm font-bold {{ $isAbsent ? 'text-rose-700' : 'text-gray-700' }}">
                            غائب
                        </span>
                    </label>
                </div>

                <div class="mt-3">
                    @if($isAbsent)
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                            تم تسجيله غائب
                        </span>
                    @else
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                            حاضر (افتراضي)
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-sm text-gray-500">
                @if($tab === 'absents')
                    ما فيه غياب مسجل لهذا اليوم.
                @else
                    ما فيه طلاب مطابقين للبحث.
                @endif
            </div>
        @endforelse
    </div>

</div>


</div>
