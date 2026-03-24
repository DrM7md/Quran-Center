{{-- <x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout> --}}


<x-app-layout>
    <div class="space-y-6">

        {{-- رسالة نجاح --}}
        @if (session('status') === 'profile-updated' || session('status') === 'password-updated')
            <x-ui.card class="border border-emerald-200 bg-emerald-50">
                <div class="text-emerald-800 font-semibold">
                    تم حفظ التغييرات بنجاح ✅
                </div>
            </x-ui.card>
        @endif

        {{-- Header --}}
        <x-ui.card>
            <div class="flex items-start justify-between gap-4 flex-col md:flex-row">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold">الملف الشخصي</h1>
                    <p class="text-slate-600 mt-1">عدّل بياناتك وغيّر كلمة المرور بكل سهولة.</p>
                </div>

                <div class="text-xs text-slate-500">
                    {{ auth()->user()->name }} • {{ auth()->user()->email }}
                </div>
            </div>
        </x-ui.card>

        {{-- Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ✅ بيانات الحساب --}}
            <div class="lg:col-span-2 space-y-6">
                <x-ui.card>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-xs text-slate-500">البيانات</div>
                            <div class="text-lg font-extrabold">معلومات الحساب</div>
                        </div>
                    </div>

                    @include('profile.partials.update-profile-information-form')
                </x-ui.card>

                <x-ui.card>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <div class="text-xs text-slate-500">الأمان</div>
                            <div class="text-lg font-extrabold">تغيير كلمة المرور</div>
                        </div>
                    </div>

                    @include('profile.partials.update-password-form')
                </x-ui.card>
            </div>

            {{-- ✅ منطقة خطرة --}}
            <div class="space-y-6">
                <x-ui.card class="border border-rose-200 bg-rose-50/40">
                    <div class="mb-3">
                        <div class="text-xs text-rose-700">تنبيه</div>
                        <div class="text-lg font-extrabold text-rose-800">إعدادات حساسة</div>
                    </div>

                    <div class="text-sm text-rose-700 mb-4">
                        حذف الحساب إجراء نهائي. الأفضل نخليه فقط للأدمن.
                    </div>

                    {{-- لو تبي تخليه للأدمن فقط --}}
                    @if(auth()->user()->hasRole('admin'))
                        @include('profile.partials.delete-user-form')
                    @else
                        <div class="text-xs text-slate-500">
                            الحذف متاح للأدمن فقط.
                        </div>
                    @endif
                </x-ui.card>
            </div>

        </div>
    </div>
</x-app-layout>
