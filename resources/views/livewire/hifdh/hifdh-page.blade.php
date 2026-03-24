<div class="space-y-6">

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 sm:p-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <h1 class="text-2xl sm:text-3xl font-bold">تسجيل التسميع</h1>
        <p class="text-gray-600 mt-2">اختر الطالب، ثم نوع التسميع، ثم السورة والآيات والتقييم.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Form -->
        <div
            class="lg:col-span-2 bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6 space-y-4 overflow-visible">

            @if (auth()->user()?->hasRole('admin'))
                <div>
                    <label class="block text-sm font-bold mb-2">الحلقة</label>
                    <select wire:model.live="halaqa_id"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">اختر الحلقة</option>
                        @foreach ($halaqas as $h)
                            <option value="{{ $h->id }}">{{ $h->name }}</option>
                        @endforeach
                    </select>
                    @error('halaqa_id')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 overflow-visible">

                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold mb-2">الطالب</label>
                    <select wire:model.live="student_id"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">اختر الطالب</option>
                        @foreach ($students as $st)
                            <option value="{{ $st['id'] }}">
                                {{ $st['name'] }} @if (!empty($st['halaqa']))
                                    — {{ $st['halaqa'] }}
                                @endif
                            </option>
                        @endforeach

                    </select>
                    @error('student_id')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">نوع التسميع</label>
                    <select wire:model="type"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="new">حفظ جديد</option>
                        <option value="review">مراجعة</option>
                    </select>
                    @error('type')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">تاريخ التسميع</label>
                    <input type="date" wire:model="heard_at"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    @error('heard_at')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold mb-2">السورة</label>

                    <input type="text" wire:model.live.debounce.300ms="surah_search" list="surahs-list"
                        autocomplete="off" placeholder="اكتب اسم السورة أو رقمها..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">

                    <datalist id="surahs-list">
                        @foreach ($surah_results as $s)
                            <option value="{{ $s['name'] }}">رقم {{ $s['number'] }}</option>
                        @endforeach
                    </datalist>

                    @error('surah_id')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror

                    {{-- Surah info chip — يظهر بعد اختيار السورة --}}
                    @if($surah_id && !empty($selectedSurahData))
                        <div class="mt-2 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-xl px-4 py-2.5">
                            <div class="flex items-center gap-3 text-sm">
                                <div class="w-9 h-9 rounded-lg bg-blue-600 text-white text-sm font-black flex items-center justify-center shrink-0">
                                    {{ $selectedSurahData['number'] }}
                                </div>
                                <div>
                                    <div class="font-bold text-blue-900">{{ $selectedSurahData['name'] }}</div>
                                    <div class="text-blue-500 text-xs">{{ $selectedSurahData['ayahs_count'] }} آية</div>
                                </div>
                            </div>
                            <button type="button"
                                    @click="$dispatch('open-surah-viewer', {
                                        number:    {{ $selectedSurahData['number'] }},
                                        name:      '{{ addslashes($selectedSurahData['name']) }}',
                                        total:     {{ $selectedSurahData['ayahs_count'] }},
                                        studentId: {{ $student_id ?? 0 }}
                                    })"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332-.477-4.5-1.253"/>
                                </svg>
                                عرض السورة
                            </button>
                        </div>
                    @endif

                    {{-- Feature 1: بطاقة اقتراح الاستمرار من آخر جلسة --}}
                    @if($surah_id && !empty($lastSession))
                        @if($lastSession['finished'])
                            <div class="mt-2 flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 text-sm">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-base">✓</div>
                                <div>
                                    <div class="font-bold text-emerald-800">أتمّ الطالب حفظ هذه السورة</div>
                                    <div class="text-emerald-600 text-xs mt-0.5">
                                        آخر تسميع: {{ $lastSession['last_from'] }} ← {{ $lastSession['last_to'] }}
                                        ({{ $lastSession['days_ago'] === 0 ? 'اليوم' : 'قبل ' . $lastSession['days_ago'] . ' يوم' }})
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-2 flex items-center justify-between gap-3 bg-violet-50 border border-violet-200 rounded-xl px-4 py-3">
                                <div class="text-sm">
                                    <div class="font-bold text-violet-900">آخر جلسة: {{ $lastSession['last_from'] }} ← {{ $lastSession['last_to'] }}</div>
                                    <div class="text-violet-500 text-xs mt-0.5">
                                        {{ $lastSession['days_ago'] === 0 ? 'اليوم' : 'قبل ' . $lastSession['days_ago'] . ' يوم' }}
                                        &nbsp;·&nbsp; يُقترح الاستمرار من الآية {{ $lastSession['next_from'] }}
                                    </div>
                                </div>
                                <button type="button" wire:click="applySuggestion"
                                    class="shrink-0 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-lg transition-colors">
                                    استمر →
                                </button>
                            </div>
                        @endif
                    @endif
                </div>



                <div>
                    <label class="block text-sm font-bold mb-2">من آية</label>
                    <input type="number" wire:model="from_ayah"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    @error('from_ayah')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">إلى آية</label>
                    <input type="number" wire:model="to_ayah"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    @error('to_ayah')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold mb-2">التقييم</label>
                    <select wire:model="rating"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="excellent">ممتاز</option>
                        <option value="very_good">جيد جدًا</option>
                        <option value="good">جيد</option>
                        <option value="weak">ضعيف</option>
                        <option value="repeat">يحتاج إعادة</option>
                    </select>
                    @error('rating')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-bold mb-2">ملاحظات</label>
                    <textarea wire:model="notes" rows="3"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="ملاحظات اختيارية..."></textarea>
                    @error('notes')
                        <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="button" wire:click="save"
                    class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-bold">
                    💾 حفظ التسميع
                </button>
            </div>
        </div>

        <!-- History -->
        <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">

            {{-- Feature 2: تحذير إذا لم يُسمَّع الطالب منذ فترة --}}
            @if($student_id && $daysSinceLastSession !== null)
                @if($daysSinceLastSession >= 14)
                    <div class="mb-4 flex items-center gap-2 bg-rose-50 border border-rose-200 rounded-xl px-4 py-3 text-sm text-rose-700">
                        <span class="text-lg">⚠️</span>
                        <div>
                            <span class="font-bold">لم يُسمَّع منذ {{ $daysSinceLastSession }} يومًا!</span>
                            <span class="text-rose-500 text-xs mr-1">يحتاج متابعة عاجلة</span>
                        </div>
                    </div>
                @elseif($daysSinceLastSession >= 7)
                    <div class="mb-4 flex items-center gap-2 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-700">
                        <span class="text-lg">🕐</span>
                        <span class="font-bold">لم يُسمَّع منذ {{ $daysSinceLastSession }} أيام</span>
                    </div>
                @endif
            @endif

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">سجل الطالب</h2>
                    <div class="text-xs text-gray-500 mt-1">آخر 25 تسميع</div>
                </div>

                <button type="button" wire:click="openAllHistory" @disabled(!$student_id)
                    class="px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                    عرض السجل كاملًا
                </button>
            </div>

            <div class="mt-4 space-y-3">
                @if (empty($history))
                    <div class="text-sm text-gray-500">
                        اختر طالبًا لعرض السجل.
                    </div>
                @else
                    @foreach ($history as $row)
                        <div wire:key="history-{{ $row['id'] }}">

                            <div class="p-4 rounded-lg border border-gray-200 bg-gray-50/50">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="font-bold">{{ $row['surah'] }} ({{ $row['range'] }})</div>

                                    <div class="flex items-center gap-2">
                                        <div class="text-xs text-gray-500">{{ $row['date'] }}</div>

                                        <!-- 🗑️ حذف -->
                                        <button type="button" wire:click.stop="confirmDeleteMem({{ $row['id'] }})"
                                            class="p-2 rounded-xl hover:bg-rose-50 text-rose-600"
                                            title="حذف هذا التسميع">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-600 mt-2 flex flex-wrap gap-2">
                                    <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                        {{ $row['type'] === 'new' ? 'حفظ جديد' : 'مراجعة' }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                        {{ $row['muhafidh'] }}
                                    </span>
                                    <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                        @php
                                            $labels = [
                                                'excellent' => 'ممتاز',
                                                'very_good' => 'جيد جدًا',
                                                'good' => 'جيد',
                                                'weak' => 'ضعيف',
                                                'repeat' => 'يحتاج إعادة',
                                            ];
                                        @endphp
                                        {{ $labels[$row['rating']] ?? $row['rating'] }}
                                    </span>
                                </div>

                                @if (!empty($row['notes']))
                                    <div class="text-sm text-gray-700 mt-2">
                                        {{ $row['notes'] }}
                                    </div>
                                @endif

                            </div>

                        </div>
                    @endforeach

                @endif

            </div>
            @if ($showAllHistoryModal)
                <div class="fixed inset-0 z-50">
                    <div class="absolute inset-0 bg-black/40" wire:click="closeAllHistoryModal"></div>

                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200">
                            <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-gray-500">سجل الطالب</div>
                                    <div class="text-xl font-bold">السجل كاملًا</div>
                                </div>
                                <button type="button" wire:click="closeAllHistoryModal"
                                    class="p-2 rounded-lg hover:bg-gray-100">×</button>
                            </div>

                            <div class="p-5 sm:p-6 max-h-[70vh] overflow-y-auto space-y-3">
                                @if (empty($allHistory))
                                    <div class="text-sm text-gray-500">لا توجد بيانات.</div>
                                @else
                                    @foreach ($allHistory as $row)
                                        <div wire:key="allhistory-{{ $row['id'] }}"
                                            class="p-4 rounded-lg border border-gray-200 bg-gray-50/50">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="font-bold">{{ $row['surah'] }} ({{ $row['range'] }})
                                                </div>

                                                <div class="flex items-center gap-2">
                                                    <div class="text-xs text-gray-500">{{ $row['date'] }}</div>

                                                    <button type="button"
                                                        wire:click.stop="confirmDeleteMem({{ $row['id'] }})"
                                                        class="p-2 rounded-xl hover:bg-rose-50 text-rose-600"
                                                        title="حذف هذا التسميع">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                            stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7 0V5a2 2 0 012-2h4a2 2 0 012 2v2" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="text-xs text-gray-600 mt-2 flex flex-wrap gap-2">
                                                <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                                    {{ $row['type'] === 'new' ? 'حفظ جديد' : 'مراجعة' }}
                                                </span>

                                                <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                                    {{ $row['muhafidh'] }}
                                                </span>

                                                <span class="px-2 py-1 rounded-full bg-white border border-gray-200">
                                                    @php
                                                        $labels = [
                                                            'excellent' => 'ممتاز',
                                                            'very_good' => 'جيد جدًا',
                                                            'good' => 'جيد',
                                                            'weak' => 'ضعيف',
                                                            'repeat' => 'يحتاج إعادة',
                                                        ];
                                                    @endphp
                                                    {{ $labels[$row['rating']] ?? $row['rating'] }}
                                                </span>
                                            </div>

                                            @if (!empty($row['notes']))
                                                <div class="text-sm text-gray-700 mt-2">
                                                    {{ $row['notes'] }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach

                                    @if ($hasMoreAllHistory)
                                        <div class="pt-2 flex justify-center">
                                            <button type="button" wire:click="loadMoreAllHistory"
                                                class="px-4 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-bold">
                                                تحميل المزيد
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </div>


                            <div class="p-5 sm:p-6 border-t border-gray-200 flex justify-end">
                                <button type="button" wire:click="closeAllHistoryModal"
                                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold">
                                    إغلاق
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @if ($showDeleteMemModal)
                <div class="fixed inset-0 z-50">
                    <div class="absolute inset-0 bg-black/40" wire:click="closeDeleteMemModal"></div>

                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">
                            <div class="p-5 sm:p-6">
                                <div class="text-xl font-bold">تأكيد الحذف</div>
                                <p class="text-gray-600 mt-2">
                                    هل أنت متأكد من حذف هذا التسميع؟
                                    @if ($deleteMemTitle)
                                        <span class="font-bold text-gray-800">({{ $deleteMemTitle }})</span>
                                    @endif
                                </p>

                                <div class="mt-5 flex justify-end gap-2">
                                    <button type="button" wire:click="closeDeleteMemModal"
                                        class="px-4 py-2.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-bold">
                                        إلغاء
                                    </button>

                                    <button type="button" wire:click="deleteMem"
                                        class="px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold">
                                        حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

    </div>

    {{-- ── Surah Viewer Drawer ───────────────────────────────────────────── --}}
    {{--
        wire:ignore  → يمنع Livewire من إعادة morph هذا العنصر (يحل مشكلة الصفحة الفارغة)
        x-effect     → يزامن from_ayah/to_ayah بدون $wire.entangle (الأكثر أمانًا)
        x-on:.window → يستقبل حدث الفتح بدون init() / addEventListener
    --}}
    <div
        wire:ignore
        x-data="{
            open: false,
            surahName: '',
            surahTotal: 0,
            surahNumber: 0,
            studentId: 0,
            ayahs: [],
            memorized: [],
            loading: false,
            errorMsg: null,
            fAyah: 0,
            tAyah: 0,
            openDrawer(d) {
                this.open        = true;
                this.surahName   = d.name;
                this.surahTotal  = d.total;
                this.surahNumber = d.number;
                this.studentId   = d.studentId ?? 0;
                this.ayahs       = [];
                this.memorized   = [];
                this.errorMsg    = null;
                this.fetchSurah(d.number, d.studentId ?? 0);
            },
            fetchSurah(num, sid) {
                this.loading  = true;
                this.errorMsg = null;
                const url = '/quran/surah/' + num + (sid ? '?student=' + sid : '');
                fetch(url)
                    .then(r => r.json())
                    .then(d => {
                        this.ayahs     = d.ayahs     ?? [];
                        this.memorized = d.memorized ?? [];
                        this.loading   = false;
                    })
                    .catch(() => { this.errorMsg = 'تعذّر التحميل. تحقق من الإنترنت.'; this.loading = false; });
            },
            isHighlighted(n) {
                return this.fAyah > 0 && this.tAyah > 0 && n >= this.fAyah && n <= this.tAyah;
            },
            isMemorized(n) {
                return this.memorized.includes(n);
            }
        }"
        x-effect="fAyah = parseInt($wire.from_ayah) || 0; tAyah = parseInt($wire.to_ayah) || 0"
        x-on:open-surah-viewer.window="openDrawer($event.detail)"
        x-on:keydown.escape.window="open = false"
    >
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/40 z-40"
            @click="open = false"
            style="display:none"
        ></div>

        {{-- Drawer Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed inset-y-0 right-0 z-50 w-full sm:w-[480px] bg-white shadow-2xl flex flex-col"
            style="display:none"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 bg-blue-600 text-white shrink-0">
                <div class="flex items-center gap-3">
                    <div class="text-lg font-black">
                        سورة <span x-text="surahName"></span>
                    </div>
                    <div class="text-xs bg-white/20 px-2.5 py-1 rounded-lg font-semibold"
                         x-text="surahTotal + ' آية'"></div>
                    {{-- Feature 3: نسبة الحفظ --}}
                    <div x-show="memorized.length > 0 && surahTotal > 0"
                         class="text-xs bg-emerald-500 px-2.5 py-1 rounded-lg font-bold"
                         x-text="Math.round(memorized.length / surahTotal * 100) + '% محفوظ'"></div>
                </div>
                <button type="button" @click="open = false"
                        class="p-1.5 rounded-lg hover:bg-white/20 transition-colors" title="إغلاق">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Info bar: نطاق التسميع + أسطورة الألوان --}}
            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 shrink-0 space-y-1.5">
                {{-- النطاق الحالي --}}
                <div class="flex items-center gap-2 text-xs text-amber-700">
                    <span class="w-5 h-5 rounded-full bg-amber-500 shrink-0 inline-block"></span>
                    <span>
                        النطاق الحالي:
                        <strong x-text="(fAyah || '—') + ' ← ' + (tAyah || '—')"></strong>
                        <span class="text-gray-400">(يتحدث تلقائيًا)</span>
                    </span>
                </div>
                {{-- الأسطورة: محفوظ سابقًا --}}
                <div x-show="memorized.length > 0" class="flex items-center gap-2 text-xs text-emerald-700">
                    <span class="w-5 h-5 rounded-full bg-emerald-500 shrink-0 inline-block"></span>
                    <span>
                        محفوظ سابقًا
                        <span class="text-gray-400" x-text="'(' + memorized.length + ' آية)'"></span>
                    </span>
                </div>
            </div>

            {{-- Ayahs body --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-1" dir="rtl">

                {{-- Loading spinner --}}
                <div x-show="loading"
                     class="flex flex-col items-center justify-center py-20 gap-4 text-gray-400">
                    <svg class="animate-spin w-9 h-9 text-blue-500" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span class="text-sm">جارٍ تحميل السورة…</span>
                </div>

                {{-- Error state --}}
                <div x-show="errorMsg && !loading"
                     class="bg-rose-50 border border-rose-200 text-rose-700 rounded-xl p-4 text-sm flex items-start gap-2 mt-2">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <div class="font-bold mb-1">خطأ في التحميل</div>
                        <div x-text="errorMsg"></div>
                        <button type="button" @click="fetchSurah(surahNumber)"
                                class="mt-2 underline text-rose-600 text-xs">
                            إعادة المحاولة
                        </button>
                    </div>
                </div>

                {{-- Ayah list --}}
                <template x-for="ayah in ayahs" :key="ayah.n">
                    <div
                        :class="isHighlighted(ayah.n)
                            ? 'bg-amber-50 border border-amber-300 shadow-sm'
                            : (isMemorized(ayah.n)
                                ? 'bg-emerald-50/60 border border-emerald-200'
                                : 'border border-transparent hover:bg-gray-50')"
                        class="rounded-xl px-4 py-3 transition-colors duration-100 cursor-default"
                    >
                        <div class="flex items-start gap-3">
                            {{-- بادج رقم الآية: كهرماني للنطاق الحالي، أخضر للمحفوظ، رمادي للعادي --}}
                            <div
                                :class="isHighlighted(ayah.n)
                                    ? 'bg-amber-500 text-white ring-2 ring-amber-300'
                                    : (isMemorized(ayah.n)
                                        ? 'bg-emerald-500 text-white'
                                        : 'bg-gray-100 text-gray-500')"
                                class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold mt-1.5 transition-colors"
                                x-text="ayah.n"
                            ></div>
                            <div
                                :class="isHighlighted(ayah.n)
                                    ? 'text-gray-900'
                                    : (isMemorized(ayah.n) ? 'text-emerald-900' : 'text-gray-700')"
                                class="flex-1 text-xl leading-loose transition-colors"
                                style="font-family: 'Traditional Arabic', 'Amiri', 'Scheherazade New', 'Noto Naskh Arabic', serif;"
                                x-text="ayah.t"
                            ></div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </div>

</div>
