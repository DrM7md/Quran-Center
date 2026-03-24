<div class="space-y-6">

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 sm:p-5">
            {{ session('success') }}
        </div>
    @endif

    <!-- Header Card -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4 flex-col md:flex-row">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold">إدارة الطلاب</h1>
                <p class="text-gray-600 mt-2">بحث + إضافة/تعديل/حذف بشكل سريع ومناسب للجوال.</p>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-80">
                    <input
                        type="text"
                        wire:model.live="search"
                        placeholder="ابحث بالاسم أو رقم الجوال..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                    />
                </div>

                <button
                    type="button"
                    wire:click="openCreate"
                    class="shrink-0 px-5 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 transition"
                >
                    إضافة
                </button>
            </div>
        </div>
    </div>

    <!-- Cards Card -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 flex-col sm:flex-row">
            <h2 class="text-lg font-bold">قائمة الطلاب</h2>

            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500">إظهار</span>
                <select wire:model.live="perPage"
                        class="rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>

        {{-- ✅ بطاقات الطلاب --}}
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($students as $st)
                <div wire:key="student-card-{{ $st->id }}"
                     class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                                {{ mb_substr($st->name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="font-bold text-lg truncate">{{ $st->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">ID: {{ $st->id }}</div>
                            </div>
                        </div>

                        <div class="shrink-0">
                            @if($st->is_active)
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                    نشط
                                </span>
                            @else
                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-100">
                                    غير نشط
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Mini stats --}}
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">العمر</div>
                            <div class="text-lg font-bold mt-1">{{ $st->age ?? '—' }}</div>
                        </div>

                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">الجوال</div>
                            <div class="text-sm font-bold mt-1 truncate">{{ $st->phone ?? '—' }}</div>
                        </div>
                    </div>

                    {{-- الحلقة --}}
                    <div class="mt-3">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $st->halaqa?->name ?? '—' }}
                        </span>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <button type="button"
                                wire:click="openView({{ $st->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-100 hover:bg-emerald-100 text-xs font-bold">
                            عرض
                        </button>

                        <button type="button"
                                wire:click="openEdit({{ $st->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold">
                            تعديل
                        </button>

                        <button type="button"
                                wire:click="confirmDelete({{ $st->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold">
                            حذف
                        </button>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 py-10 text-center text-gray-500">
                    ما فيه نتائج… جرّب بحث مختلف.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $students->links() }}
        </div>
    </div>

    <!-- ✅ Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">الطلاب</div>
                            <div class="text-xl font-bold">
                                {{ $isEdit ? 'تعديل طالب' : 'إضافة طالب' }}
                            </div>
                        </div>

                        <button type="button"
                                wire:click="closeModal"
                                class="p-2 rounded-lg hover:bg-gray-100">
                            ×
                        </button>
                    </div>

                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold mb-2">اسم الطالب</label>
                                <input type="text" wire:model="name"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('name') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">العمر</label>
                                <input type="number" wire:model="age"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('age') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">رقم الجوال</label>
                                <input type="text" wire:model="phone"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('phone') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-sm font-bold mb-2">الحلقة</label>
                                <select wire:model="halaqa_id"
                                        @if($halaqaLocked) disabled @endif
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @if($halaqaLocked) bg-gray-100 @endif">
                                    <option value="">اختر الحلقة</option>
                                    @foreach($halaqas as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}</option>
                                    @endforeach
                                </select>
                                @error('halaqa_id') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror

                                @if($halaqaLocked)
                                    <div class="text-xs text-gray-500 mt-2">
                                        * الحلقة مقفولة لأن حسابك محفظ.
                                    </div>
                                @endif
                            </div>

                            <div class="sm:col-span-2">
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" wire:model="is_active"
                                           class="rounded border-gray-300 text-emerald-600 focus:ring-blue-500">
                                    <span class="text-sm font-bold">نشط</span>
                                </label>
                                @error('is_active') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 border-t border-gray-200 flex items-center justify-end gap-2">
                        <button type="button"
                                wire:click="closeModal"
                                class="px-4 py-2.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-bold">
                            إلغاء
                        </button>

                        <button type="button"
                                wire:click="save"
                                class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-bold">
                            💾 حفظ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ✅ Delete Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDeleteModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6">
                        <div class="text-xl font-bold">تأكيد الحذف</div>
                        <p class="text-gray-600 mt-2">متأكد تبي تحذف الطالب؟ ما تقدر ترجعها بعد.</p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button type="button"
                                    wire:click="closeDeleteModal"
                                    class="px-4 py-2.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-bold">
                                إلغاء
                            </button>

                            <button type="button"
                                    wire:click="delete"
                                    class="px-5 py-2.5 rounded-lg bg-rose-600 hover:bg-rose-500 text-white text-sm font-bold">
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ✅ View Student Modal -->
    @if($showViewModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeViewModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-3xl bg-white rounded-xl shadow-xl border border-gray-200">

                    <!-- Header -->
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div class="min-w-0">
                            <div class="text-xs text-gray-500">عرض الطالب</div>
                            <div class="text-xl font-bold truncate">{{ $viewStudent['name'] ?? '' }}</div>

                            <div class="text-xs text-gray-500 mt-2 flex flex-wrap gap-2">
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                                    العمر: <b>{{ $viewStudent['age'] ?? '—' }}</b>
                                </span>
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                                    الجوال: <b>{{ $viewStudent['phone'] ?? '—' }}</b>
                                </span>
                                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">
                                    الحلقة: <b>{{ $viewStudent['halaqa'] ?? '—' }}</b>
                                </span>
                            </div>
                        </div>

                        <button type="button" wire:click="closeViewModal" class="p-2 rounded-lg hover:bg-gray-100">×</button>
                    </div>

                    <!-- Tabs -->
                    <div class="p-5 sm:p-6">
                        <div class="flex gap-2 mb-4">
                            <button type="button"
                                    wire:click="$set('viewTab','absences')"
                                    class="px-4 py-2 rounded-lg text-sm font-bold border transition
                                    {{ $viewTab === 'absences' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                الغياب
                            </button>

                            <button type="button"
                                    wire:click="$set('viewTab','hifdh')"
                                    class="px-4 py-2 rounded-lg text-sm font-bold border transition
                                    {{ $viewTab === 'hifdh' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                                سجل الحفظ
                            </button>
                        </div>

                        <!-- Tab: Absences -->
                        @if($viewTab === 'absences')
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <div class="p-3 bg-gray-50 border-b border-gray-200 text-sm font-bold">
                                    أيام الغياب
                                </div>

                                @if(count($viewAbsences) === 0)
                                    <div class="p-4 text-sm text-gray-500">لا يوجد غياب مسجّل لهذا الطالب.</div>
                                @else
                                    <ul class="divide-y divide-slate-200">
                                        @foreach($viewAbsences as $a)
                                            <li class="p-3 flex items-start justify-between gap-3">
                                                <div class="font-semibold text-gray-800">
                                                    {{ substr($a['date'], 0, 10) }}
                                                </div>

                                                <div class="text-xs text-gray-500">
                                                    {{ $a['notes'] ?: '—' }}
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif

                        <!-- Tab: Hifdh -->
                        @if($viewTab === 'hifdh')
                            <div class="rounded-lg border border-gray-200 overflow-hidden">
                                <div class="p-3 bg-gray-50 border-b border-gray-200 text-sm font-bold">
                                    سجل التسميع
                                </div>

                                @if(count($viewHifdh) === 0)
                                    <div class="p-4 text-sm text-gray-500">لا توجد سجلات حفظ لهذا الطالب.</div>
                                @else
                                    <div class="overflow-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                            <tr class="text-gray-500 border-b border-gray-200">
                                                <th class="text-right py-3 px-3">التاريخ</th>
                                                <th class="text-right py-3 px-3">النوع</th>
                                                <th class="text-right py-3 px-3">السورة</th>
                                                <th class="text-right py-3 px-3">من - إلى</th>
                                                <th class="text-right py-3 px-3">التقييم</th>
                                            </tr>
                                            </thead>

                                            <tbody class="divide-y divide-slate-200">
                                            @foreach($viewHifdh as $r)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="py-3 px-3">{{ $r['date'] ?? '—' }}</td>
                                                    <td class="py-3 px-3">{{ $r['type'] ?? '—' }}</td>
                                                    <td class="py-3 px-3 font-semibold">{{ $r['surah'] ?? '—' }}</td>
                                                    <td class="py-3 px-3">{{ $r['from'] }} - {{ $r['to'] }}</td>
                                                    <td class="py-3 px-3">{{ $r['rating'] ?? '—' }}</td>
                                                </tr>

                                                @if(!empty($r['notes']))
                                                    <tr class="bg-gray-50">
                                                        <td class="py-2 px-3 text-xs text-gray-600" colspan="5">
                                                            ملاحظة: {{ $r['notes'] }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="p-5 sm:p-6 border-t border-gray-200 flex justify-end">
                        <button type="button"
                                wire:click="closeViewModal"
                                class="px-4 py-2.5 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-sm font-bold">
                            إغلاق
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
