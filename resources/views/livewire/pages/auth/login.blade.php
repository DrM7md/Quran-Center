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
        if ($user->hasRole('guardian')) {
            $this->redirectIntended(default: route('parent.dashboard', absolute: false), navigate: false);
        } else {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: false);
        }
    }
};
?>

<div class="min-h-screen relative flex flex-col items-center justify-center py-10 px-4 overflow-hidden">

    {{-- ── Background ────────────────────────────────────────────── --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-900"></div>

    {{-- Glow blobs --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-48 -right-48 w-[500px] h-[500px] bg-blue-500/25 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-48 -left-48 w-[500px] h-[500px] bg-indigo-600/25 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -left-24 w-64 h-64 bg-sky-400/10 rounded-full blur-2xl"></div>
    </div>

    {{-- Decorative concentric rings --}}
    <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
        <div class="w-[700px] h-[700px] border border-white/5 rounded-full shrink-0"></div>
        <div class="absolute w-[500px] h-[500px] border border-white/5 rounded-full shrink-0"></div>
        <div class="absolute w-[300px] h-[300px] border border-white/5 rounded-full shrink-0"></div>
    </div>

    {{-- Dot-grid pattern overlay --}}
    <div class="absolute inset-0 pointer-events-none opacity-[0.07]"
         style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;">
    </div>

    {{-- ── Content ───────────────────────────────────────────────── --}}
    <div class="relative w-full max-w-md">

        {{-- Brand --}}
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-white shadow-2xl mb-5 ring-1 ring-white/10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                          d="M12 6v14m0-14c-3 0-6 1-8 3v13c2-2 5-3 8-3m0-13c3 0 6 1 8 3v13c-2-2-5-3-8-3"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-white tracking-tight">مركز التحفيظ</h1>
            <p class="text-blue-300/80 mt-1.5 text-sm">نظام متابعة الحلقات والطلاب</p>
            <p class="text-white/30 mt-3 text-sm">﴿ وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا ﴾</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl border border-white/10 p-7">

            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form wire:submit="login" class="space-y-5">

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                        البريد الإلكتروني
                    </label>
                    <input wire:model="form.email"
                           id="email" type="email" name="email"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="example@quran.com"
                           required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('form.email')" class="mt-1.5" />
                </div>

                {{-- Password --}}
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            كلمة المرور
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" wire:navigate
                               class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                نسيت كلمة المرور؟
                            </a>
                        @endif
                    </div>
                    <input wire:model="form.password"
                           id="password" type="password" name="password"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                           placeholder="••••••••"
                           required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('form.password')" class="mt-1.5" />
                </div>

                {{-- Remember Me --}}
                <label class="flex items-center gap-2 cursor-pointer">
                    <input wire:model="form.remember" id="remember" type="checkbox" name="remember"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm text-gray-600">تذكرني</span>
                </label>

                {{-- Submit --}}
                <button type="submit"
                        class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-colors shadow-sm">
                    <span wire:loading.remove>تسجيل الدخول</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        جارٍ التحقق...
                    </span>
                </button>

            </form>
        </div>

        {{-- Divider --}}
        <div class="flex items-center gap-3 my-5">
            <div class="h-px flex-1 bg-white/15"></div>
            <span class="text-white/40 text-xs font-medium">أو</span>
            <div class="h-px flex-1 bg-white/15"></div>
        </div>

        {{-- Guardian Portal Button --}}
        <a href="{{ route('parent.login') }}"
           class="group flex items-center gap-4 w-full px-5 py-4 rounded-2xl bg-white/8 hover:bg-white/14 backdrop-blur-sm border border-emerald-400/30 hover:border-emerald-400/60 transition-all shadow-lg">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center shrink-0 group-hover:bg-emerald-500/30 transition-colors">
                <svg class="w-5 h-5 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-semibold text-sm">بوابة ولي الأمر</p>
                <p class="text-emerald-300/70 text-xs mt-0.5">تسجيل الدخول لمتابعة أبنائك في مراكز التحفيظ</p>
            </div>
            <svg class="w-4 h-4 text-white/30 group-hover:text-white/60 group-hover:-translate-x-1 transition-all shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>

        {{-- Footer --}}
        <p class="text-center text-xs text-white/25 mt-7">
            © {{ date('Y') }} مركز التحفيظ — نظام متابعة الحلقات والطلاب
        </p>

    </div>
</div>
