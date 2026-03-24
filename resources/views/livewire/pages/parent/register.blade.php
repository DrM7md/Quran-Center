<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        $user->assignRole('guardian');

        event(new Registered($user));

        Auth::login($user);

        $this->redirect(route('parent.dashboard', absolute: false), navigate: false);
    }
};
?>

<div class="min-h-screen bg-gray-50 flex flex-col items-center justify-center py-12 px-4">

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-emerald-100/60 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-teal-100/60 rounded-full blur-3xl"></div>
    </div>

    <div class="relative w-full max-w-md">

        <!-- Brand -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-emerald-600 text-white shadow-lg mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">إنشاء حساب ولي الأمر</h1>
            <p class="text-sm text-gray-500 mt-1">سجّل لمتابعة أبنائك في مراكز التحفيظ</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-7">

            <form wire:submit="register" class="space-y-5">

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">
                        الاسم الكامل
                    </label>
                    <input wire:model="name"
                           id="name" type="text" name="name"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="أدخل اسمك الكامل"
                           required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        البريد الإلكتروني
                    </label>
                    <input wire:model="email"
                           id="email" type="email" name="email"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="example@email.com"
                           required autocomplete="username" dir="ltr" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        كلمة المرور
                    </label>
                    <input wire:model="password"
                           id="password" type="password" name="password"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="8 أحرف على الأقل"
                           required autocomplete="new-password" dir="ltr" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                        تأكيد كلمة المرور
                    </label>
                    <input wire:model="password_confirmation"
                           id="password_confirmation" type="password" name="password_confirmation"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="••••••••"
                           required autocomplete="new-password" dir="ltr" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition-colors shadow-sm">
                    <span wire:loading.remove>إنشاء الحساب</span>
                    <span wire:loading>جارٍ التسجيل...</span>
                </button>

            </form>
        </div>

        <!-- Login link -->
        <p class="text-center text-sm text-gray-500 mt-5">
            لديك حساب بالفعل؟
            <a href="{{ route('parent.login') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
                تسجيل الدخول
            </a>
        </p>

        <p class="text-center text-xs text-gray-300 mt-4">﴿ وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا ﴾</p>
    </div>
</div>
