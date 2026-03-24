<div class="space-y-6">

    {{-- رسالة النجاح --}}
    @if (session('success'))
        <x-ui.card class="border border-emerald-200 bg-emerald-50">
            <div class="text-emerald-800 font-semibold">
                {{ session('success') }}
            </div>
        </x-ui.card>
    @endif

    {{-- شريط الأدوات --}}
    <x-ui.card>
        <div class="flex items-start justify-between gap-4 flex-col md:flex-row">
            <div>
                <h1 class="text-2xl font-bold">الحلقات</h1>
                <p class="text-gray-600 mt-1">إدارة الحلقات وتوزيع الطلاب والمحفّظين.</p>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <x-ui.input wire:model.live="search" placeholder="ابحث باسم الحلقة أو اسم المحفّظ..." class="md:w-72" />
                <x-ui.button variant="primary" wire:click="openCreate">
                    إضافة حلقة
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    {{-- بطاقات الحلقات --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        @forelse($halaqas as $h)
            <x-ui.card>
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-bold text-lg truncate">{{ $h->name }}</div>

                        <div class="text-sm text-gray-500 mt-1">
                            المحفّظ:
                            <span class="font-semibold text-gray-700">
                                {{ $h->primary_muhafidh_name ?? '—' }}
                            </span>
                        </div>

                        @if(($h->cover_count ?? 0) > 0)
                            <div class="text-xs text-gray-500 mt-1">
                                التغطية: {{ implode('، ', $h->cover_names ?? []) }}
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0">
                        @if($h->is_active)
                            <x-ui.badge variant="emerald">نشطة</x-ui.badge>
                        @else
                            <x-ui.badge variant="slate">متوقفة</x-ui.badge>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="text-xs text-gray-500">عدد الطلاب</div>
                        <div class="text-xl font-bold mt-1">{{ $h->students_count }}</div>
                    </div>

                    <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="text-xs text-gray-500">غياب اليوم</div>
                        <div class="text-xl font-bold mt-1">{{ $h->absences_today ?? 0 }}</div>
                    </div>
                </div>

           {{-- أزرار الإجراءات --}}
<div class="mt-4 grid grid-cols-2 gap-2">
    <x-ui.button variant="light" size="sm" class="w-full" wire:click="openEdit({{ $h->id }})">
        تعديل
    </x-ui.button>

    <x-ui.button variant="light" size="sm" class="w-full" wire:click="openCover({{ $h->id }})">
        تغطية
        @if(($h->cover_count ?? 0) > 0)
            <span class="ms-1 text-xs opacity-70">({{ $h->cover_count }})</span>
        @endif
    </x-ui.button>

    <x-ui.button variant="light" size="sm" class="w-full" wire:click="openStudents({{ $h->id }})">
        طلاب الحلقة
        <span class="ms-1 text-xs opacity-70">({{ $h->students_count }})</span>
    </x-ui.button>

    <x-ui.button variant="danger" size="sm" class="w-full" wire:click="confirmDelete({{ $h->id }})">
        حذف
    </x-ui.button>
</div>

            </x-ui.card>
        @empty
            <div class="text-sm text-gray-500">لا توجد حلقات حالياً.</div>
        @endforelse

    </div>

    {{-- نافذة الإضافة/التعديل --}}
    @if($showModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">الحلقات</div>
                            <div class="text-xl font-bold">
                                {{ $isEdit ? 'تعديل حلقة' : 'إضافة حلقة' }}
                            </div>
                        </div>

                        <button type="button" wire:click="closeModal" class="p-2 rounded-lg hover:bg-gray-100">✕</button>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4">

                        {{-- اسم الحلقة --}}
                        <div>
                            <label class="block text-sm font-bold mb-2">اسم الحلقة</label>
                            <x-ui.input wire:model="name" placeholder="مثال: حلقة الفجر" />
                            @error('name')
                                <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- المحفّظ الأساسي --}}
                        <div>
                            <label class="block text-sm font-bold mb-2">المحفّظ الأساسي</label>
                            <select wire:model="muhafidh_id"
                                    class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                <option value="">بدون</option>
                                @foreach($muhafidhs as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                            @error('muhafidh_id')
                                <div class="text-rose-600 text-xs mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- نشطة --}}
                        <label class="inline-flex items-center gap-2">
                            <input type="checkbox" wire:model="is_active"
                                   class="rounded border-gray-300 text-emerald-600 focus:ring-blue-500">
                            <span class="text-sm font-bold">حلقة نشطة</span>
                        </label>

                    </div>

                    <div class="p-5 sm:p-6 border-t border-gray-200 flex items-center justify-end gap-2">
                        <x-ui.button variant="light" wire:click="closeModal">إلغاء</x-ui.button>
                        <x-ui.button variant="primary" wire:click="save">💾 حفظ</x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- نافذة الحذف --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDeleteModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6">
                        <div class="text-xl font-bold">تأكيد الحذف</div>
                        <p class="text-gray-600 mt-2">
                            هل أنت متأكد من رغبتك في حذف الحلقة؟ لا يمكن التراجع عن هذا الإجراء.
                        </p>

                        <div class="mt-5 flex justify-end gap-2">
                            <x-ui.button variant="light" wire:click="closeDeleteModal">إلغاء</x-ui.button>
                            <x-ui.button variant="danger" wire:click="delete">حذف</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- نافذة التغطية --}}
    @if($showCoverModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeCoverModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-xl bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">تغطية الحلقة</div>
                            <div class="text-xl font-bold">{{ $coverHalaqaName }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                التغطية لا تغيّر المحفّظ الأساسي، بل تضيف محفّظين مساعدين.
                            </div>
                        </div>
                        <button wire:click="closeCoverModal" class="p-2 rounded-lg hover:bg-gray-100">✕</button>
                    </div>

                    <div class="p-5 sm:p-6 space-y-3">
                        <label class="block text-sm font-bold">اختر محفّظين للتغطية (اختيار متعدد)</label>

                        <select wire:model="cover_muhafidh_ids" multiple
                                class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 h-48">
                            @foreach($muhafidhs as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>

                        @error('cover_muhafidh_ids.*')
                            <div class="text-rose-600 text-xs">{{ $message }}</div>
                        @enderror

                        <div class="text-xs text-gray-500">
                            مثال: عند غياب محفّظ الحلقة، يمكنك تعيين محفّظين آخرين لتغطيتها مؤقتاً.
                        </div>
                    </div>

              <div class="p-5 sm:p-6 border-t border-gray-200 flex justify-end gap-2">
    <x-ui.button variant="light" wire:click="closeCoverModal">إلغاء</x-ui.button>

    <x-ui.button variant="danger" wire:click="clearCover">
        مسح التغطية
    </x-ui.button>

    <x-ui.button variant="primary" wire:click="saveCover">حفظ التغطية</x-ui.button>
</div>

                    
                </div>
            </div>
        </div>
    @endif
{{-- نافذة طلاب الحلقة --}}
@if($showStudentsModal)
    <div class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" wire:click="closeStudentsModal"></div>

        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="w-full max-w-xl bg-white rounded-xl shadow-xl border border-gray-200">
                <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="text-xs text-gray-500">طلاب الحلقة</div>
                        <div class="text-xl font-bold truncate">{{ $studentsHalaqaName }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            عدد الطلاب: <span class="font-bold text-gray-700">{{ count($studentsList) }}</span>
                        </div>
                    </div>

                    <button wire:click="closeStudentsModal" class="p-2 rounded-lg hover:bg-gray-100">✕</button>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2">بحث باسم الطالب</label>
                        <x-ui.input wire:model.live="studentsSearch" placeholder="اكتب اسم الطالب..." />
                    </div>

                    <div class="max-h-[320px] overflow-auto rounded-lg border border-gray-200">
                        @if(count($studentsList) === 0)
                            <div class="p-4 text-sm text-gray-500">لا يوجد طلاب في هذه الحلقة.</div>
                        @else
                            <ul class="divide-y divide-slate-200">
                                @foreach($studentsList as $studentName)
                                    <li class="p-3 flex items-center justify-between">
                                        <span class="font-semibold text-gray-800">{{ $studentName }}</span>
                                        <span class="text-xs text-gray-400">طالب</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                <div class="p-5 sm:p-6 border-t border-gray-200 flex justify-end">
                    <x-ui.button variant="light" wire:click="closeStudentsModal">إغلاق</x-ui.button>
                </div>
            </div>
        </div>
    </div>
@endif

</div>
