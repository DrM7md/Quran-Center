<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-lg bg-white/20 flex items-center justify-center text-3xl font-bold shrink-0">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ auth()->user()->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ auth()->user()->email }}</p>
                <span class="inline-flex mt-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white">
                    @php
                        $roles = ['super-admin' => 'Super Admin', 'admin' => 'Admin', 'muhafidh' => 'محفظ'];
                        $role  = collect($roles)->keys()->first(fn($r) => auth()->user()->hasRole($r));
                    @endphp
                    {{ $roles[$role] ?? 'مستخدم' }}
                </span>
            </div>
        </div>
    </div>

    {{-- معلومات الحساب --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6">
        <h2 class="text-base font-bold text-gray-700 mb-5">معلومات الحساب</h2>

        @if($profileSaved)
            <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm font-semibold">
                ✅ تم حفظ المعلومات بنجاح.
            </div>
        @endif

        <form wire:submit="saveProfile" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1.5">الاسم الكامل</label>
                <input wire:model="name"
                       type="text"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="أدخل اسمك الكامل" />
                @error('name')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1.5">البريد الإلكتروني</label>
                <input wire:model="email"
                       type="email"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="example@email.com"
                       dir="ltr" />
                @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-sm transition">
                    <span wire:loading.remove wire:target="saveProfile">حفظ المعلومات</span>
                    <span wire:loading wire:target="saveProfile">جارٍ الحفظ...</span>
                </button>
            </div>
        </form>
    </div>

    {{-- تغيير كلمة المرور --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-6">
        <h2 class="text-base font-bold text-gray-700 mb-5">تغيير كلمة المرور</h2>

        @if($passwordSaved)
            <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm font-semibold">
                ✅ تم تغيير كلمة المرور بنجاح.
            </div>
        @endif

        <form wire:submit="savePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1.5">كلمة المرور الحالية</label>
                <input wire:model="current_password"
                       type="password"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="••••••••"
                       dir="ltr" />
                @error('current_password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1.5">كلمة المرور الجديدة</label>
                <input wire:model="new_password"
                       type="password"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="8 أحرف على الأقل"
                       dir="ltr" />
                @error('new_password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-600 mb-1.5">تأكيد كلمة المرور الجديدة</label>
                <input wire:model="confirm_password"
                       type="password"
                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm"
                       placeholder="••••••••"
                       dir="ltr" />
                @error('confirm_password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-bold rounded-lg text-sm transition">
                    <span wire:loading.remove wire:target="savePassword">تغيير كلمة المرور</span>
                    <span wire:loading wire:target="savePassword">جارٍ التغيير...</span>
                </button>
            </div>
        </form>
    </div>

</div>
