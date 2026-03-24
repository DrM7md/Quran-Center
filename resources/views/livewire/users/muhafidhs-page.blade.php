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
                <h1 class="text-2xl font-bold">المحفظون</h1>
                <p class="text-gray-600 mt-1">إضافة/تعديل/حذف المحفظ وتحديد الحلقات التي يشرف عليها.</p>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <input wire:model.live="search"
                       placeholder="ابحث بالاسم أو الإيميل..."
                       class="w-full md:w-72 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" />

                <button wire:click="openCreate"
                        class="shrink-0 px-4 py-2 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-700 transition">
                    إضافة محفظ
                </button>
            </div>
        </div>
    </div>

    <!-- Cards Wrapper -->
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <div class="flex items-center justify-between gap-3 flex-col sm:flex-row">
            <h2 class="text-lg font-bold">قائمة المحفظين</h2>
            <div class="text-xs text-gray-500">
                إجمالي: <span class="font-bold text-gray-700">{{ $muhafidhs->count() }}</span>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($muhafidhs as $u)
                <div wire:key="muhafidh-card-{{ $u->id }}"
                     class="bg-white border border-gray-200 shadow-sm rounded-xl p-5">

                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-12 h-12 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                                {{ mb_substr($u->name, 0, 1) }}
                            </div>

                            <div class="min-w-0">
                                <div class="font-bold text-lg truncate">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500 mt-1 truncate">{{ $u->email }}</div>
                            </div>
                        </div>

                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                            محفظ
                        </span>
                    </div>

                    <!-- Mini Stats -->
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">الحلقة الأساسية</div>
                            <div class="text-sm font-bold mt-1 truncate">
                                {{ $u->primary_halaqa_name ?? '—' }}
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="text-xs text-gray-500">عدد الحلقات</div>
                            <div class="text-lg font-bold mt-1">{{ $u->halaqas_count }}</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <button type="button"
                                wire:click="openEdit({{ $u->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold">
                            تعديل
                        </button>

                        <button type="button"
                                wire:click="confirmDelete({{ $u->id }})"
                                class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700 text-xs font-bold">
                            حذف
                        </button>
                    </div>

                </div>
            @empty
                <div class="sm:col-span-2 lg:col-span-3 py-10 text-center text-gray-500">
                    ما فيه محفظين حالياً.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    @if($showModal)
        <div class="fixed inset-0 z-50">
            <div class="absolute inset-0 bg-black/40" wire:click="closeModal"></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="w-full max-w-2xl bg-white rounded-xl shadow-xl border border-gray-200">
                    <div class="p-5 sm:p-6 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-xs text-gray-500">المحفظون</div>
                            <div class="text-xl font-bold">{{ $isEdit ? 'تعديل محفظ' : 'إضافة محفظ' }}</div>
                        </div>
                        <button wire:click="closeModal" class="p-2 rounded-lg hover:bg-gray-100">×</button>
                    </div>

                    <div class="p-5 sm:p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">الاسم</label>
                            <input wire:model="name"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('name') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">الإيميل</label>
                            <input wire:model="email"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('email') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">
                                كلمة المرور
                                <span class="text-xs text-gray-500">(في التعديل اتركها فاضية لو ما تبي تغير)</span>
                            </label>
                            <input wire:model="password" type="password"
                                   class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            @error('password') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-bold mb-2">الحلقة الأساسية (واحدة)</label>
                                <select wire:model="primary_halaqa_id"
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">—</option>
                                    @foreach($halaqas as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}</option>
                                    @endforeach
                                </select>
                                @error('primary_halaqa_id') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold mb-2">حلقات إضافية (تغطية عند الغياب)</label>
                                <select wire:model="extra_halaqa_ids" multiple
                                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 h-32">
                                    @foreach($halaqas as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }}</option>
                                    @endforeach
                                </select>
                                @error('extra_halaqa_ids.*') <div class="text-rose-600 text-xs mt-2">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            ✅ العادي: محفظ = حلقة وحدة (أساسية).<br>
                            ✅ عند الحاجة: تقدر تعطيه حلقات إضافية مؤقتًا/دائمًا.
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
                        <p class="text-gray-600 mt-2">متأكد تبي تحذف المحفظ؟</p>

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
