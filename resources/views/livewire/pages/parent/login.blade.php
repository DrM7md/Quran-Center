<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = auth()->user();

        if (! $user->hasRole('guardian')) {
            auth()->logout();
            Session::invalidate();
            $this->addError('form.email', 'هذا الحساب ليس لولي أمر. يرجى استخدام صفحة الدخول الرئيسية.');
            return;
        }

        $this->redirectIntended(default: route('parent.dashboard', absolute: false), navigate: false);
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
            <h1 class="text-2xl font-bold text-gray-900">بوابة ولي الأمر</h1>
            <p class="text-sm text-gray-500 mt-1">سجّل دخولك لمتابعة أبنائك في مراكز التحفيظ</p>
        </div>

        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-7">

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-5">

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        البريد الإلكتروني
                    </label>
                    <input wire:model="form.email"
                           id="email" type="email" name="email"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="example@email.com"
                           required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                        كلمة المرور
                    </label>
                    <input wire:model="form.password"
                           id="password" type="password" name="password"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                           placeholder="••••••••"
                           required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
                </div>

                <!-- Submit -->
                <button type="submit"
                        class="w-full py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm transition-colors shadow-sm">
                    <span wire:loading.remove>تسجيل الدخول</span>
                    <span wire:loading>جارٍ التحقق...</span>
                </button>

            </form>
        </div>

        <!-- Register link -->
        <p class="text-center text-sm text-gray-500 mt-5">
            ليس لديك حساب؟
            <a href="{{ route('parent.register') }}" class="font-semibold text-emerald-600 hover:text-emerald-700">
                إنشاء حساب جديد
            </a>
        </p>

        <!-- Back to admin login -->
        <div class="flex items-center gap-3 my-4">
            <div class="h-px flex-1 bg-gray-200"></div>
            <span class="text-gray-300 text-xs">أو</span>
            <div class="h-px flex-1 bg-gray-200"></div>
        </div>
        <a href="{{ route('login') }}"
           class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-600 hover:text-gray-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            تسجيل دخول المشرفين والمحفظين
        </a>

        <p class="text-center text-xs text-gray-300 mt-4">﴿ وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا ﴾</p>
    </div>
</div>
