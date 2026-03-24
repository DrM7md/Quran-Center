<div class="space-y-6">

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl p-4 sm:p-5">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 rounded-xl p-4 sm:p-5">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header Card -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4 flex-col md:flex-row">
            <div>
                <h1 class="text-2xl font-bold">المراكز</h1>
                <p class="text-gray-600 mt-1">إدارة مراكز التحفيظ وتعيين أدمن لكل مركز.</p>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <input wire:model.live="search"
                       placeholder="ابحث باسم المركز..."
                       class="w-full md:w-72 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />

                <button wire:click="openCreate"
                        class="shrink-0 px-4 py-2 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 transition">
                    إضافة مركز
                </button>
            </div>
        </div>
    </div>

    <!-- Cards Grid -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 flex-col sm:flex-row">
            <h2 class="text-lg font-bold">قائمة المراكز</h2>
            <div class="text-xs text-gray-500">
                الإجمالي: <span class="font-bold text-gray-700">{{ $centers->count() }}</span>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($centers as $center)
                <div wire:key="center-card-{{ $center->id }}"
                     class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">

                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xl shrink-0">
                                {{ mb_substr($center->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-lg truncate">{{ $center->name }}</div>
                                @if($center->location)
                                    <div class="text-xs text-gray-400 mt-0.5 truncate">{{ $center->location }}</div>
                                @endif
                                @if($center->admin)
                                    <div class="text-xs text-gray-500 mt-0.5 truncate">{{ $center->admin->email }}</div>
                                @endif
                            </div>
                        </div>

                        <span @class([
                            'inline-flex px-3 py-1 rounded-full text-xs font-bold border',
                            'bg-emerald-50 text-emerald-700 border-emerald-100' => $center->is_active,
                            'bg-gray-100 text-gray-500 border-gray-200' => !$center->is_active,
                        ])>
                            {{ $center->is_active ? 'نشط' : 'معطل' }}
                        </span>
                    </div>

                    <!-- Stats -->
                    <div class="mt-4 grid grid-cols-3 gap-2">
                        <div class="p-2 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">الحلقات</div>
                            <div class="text-lg font-bold mt-0.5">{{ $center->halaqas_count }}</div>
                        </div>
                        <div class="p-2 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">المستخدمون</div>
                            <div class="text-lg font-bold mt-0.5">{{ $center->users_count }}</div>
                        </div>
                        <div class="p-2 rounded-lg bg-gray-50 border border-gray-200 text-center">
                            <div class="text-xs text-gray-500">الأدمن</div>
                            <div class="text-sm font-bold mt-0.5 truncate">{{ $center->admin?->name ?? '—' }}</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="openEdit({{ $center->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold">
                            تعديل
                        </button>
                        <button type="button"
                                wire:click="confirmDelete({{ $center->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 text-xs font-bold">
                            حذف
                        </button>
                    </div>
                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 py-10 text-center text-gray-500">
                    ما فيه مراكز حالياً.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-xl bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">المراكز</div>
                            <div class="text-xl font-bold">{{ $isEdit ? 'تعديل مركز' : 'إضافة مركز' }}</div>
                        </div>
                        <button wire:click="closeModal" class="p-2 rounded-lg hover:bg-gray-100">×</button>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4">

                        {{-- بيانات المركز --}}
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="text-sm font-bold text-gray-700">بيانات المركز</div>

                            <div>
                                <label class="block text-sm font-bold mb-2">اسم المركز</label>
                                <input wire:model="name" placeholder="مثال: مركز النور"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('name') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">المنطقة / الموقع <span class="text-gray-400 font-normal">(اختياري)</span></label>
                                <input wire:model="location" placeholder="مثال: حي النزهة، الرياض"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('location') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div class="flex items-center gap-3">
                                <input wire:model="isActive" type="checkbox" id="isActive"
                                       class="rounded border-gray-300 text-emerald-600">
                                <label for="isActive" class="text-sm font-bold">المركز نشط</label>
                            </div>
                        </div>

                        {{-- بيانات الأدمن --}}
                        <div class="bg-blue-50 rounded-lg p-4 space-y-3">
                            <div class="text-sm font-bold text-blue-700">أدمن المركز</div>

                            <div>
                                <label class="block text-sm font-bold mb-2">اسم الأدمن</label>
                                <input wire:model="adminName"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('adminName') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">البريد الإلكتروني</label>
                                <input wire:model="adminEmail" type="email"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('adminEmail') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">
                                    كلمة المرور
                                    @if($isEdit)
                                        <span class="text-xs text-gray-500">(اتركها فاضية إذا ما تبي تغير)</span>
                                    @endif
                                </label>
                                <input wire:model="adminPassword" type="password"
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                @error('adminPassword') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6 border-t border-gray-200 flex justify-end gap-2">
                        <button wire:click="closeModal"
                                class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold">
                            إلغاء
                        </button>
                        <button wire:click="save"
                                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 font-bold">
                            💾 حفظ
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Delete --}}
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDeleteModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6">
                        <div class="text-xl font-bold">تأكيد الحذف</div>
                        <p class="text-gray-600 mt-2">متأكد تبي تحذف المركز؟ لا يمكن حذفه إذا يحتوي حلقات.</p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button wire:click="closeDeleteModal"
                                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold">
                                إلغاء
                            </button>
                            <button wire:click="delete"
                                    class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 font-bold">
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
