<?php

use App\Models\AccessRequest;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email          = '';
    public string $requesterName  = '';
    public string $message        = '';
    public bool   $sent           = false;

    public function sendRequest(): void
    {
        $this->validate([
            'email'        => ['required', 'string', 'email', 'max:255'],
            'requesterName'=> ['nullable', 'string', 'max:100'],
            'message'      => ['nullable', 'string', 'max:500'],
        ]);

        // المحفظ → يصل للأدمن الخاص بمركزه | الأدمن أو غير معروف → يصل للسوبر أدمن (center_id=null)
        $user     = \App\Models\User::where('email', trim($this->email))->first();
        $centerId = null;
        if ($user && $user->hasRole('muhafidh') && $user->center_id) {
            $centerId = $user->center_id;
        }

        AccessRequest::create([
            'email'          => trim($this->email),
            'requester_name' => $this->requesterName !== '' ? $this->requesterName : null,
            'message'        => $this->message        !== '' ? $this->message        : null,
            'center_id'      => $centerId,
        ]);

        $this->sent = true;
    }
};
?>

<div class="min-h-screen relative flex flex-col items-center justify-center py-10 px-4 overflow-hidden">

    {{-- Background (matching login page) --}}
    <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-900"></div>

    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-48 -right-48 w-[500px] h-[500px] bg-blue-500/25 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-48 -left-48 w-[500px] h-[500px] bg-indigo-600/25 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 -left-24 w-64 h-64 bg-sky-400/10 rounded-full blur-2xl"></div>
    </div>

    <div class="absolute inset-0 pointer-events-none opacity-[0.07]"
         style="background-image: radial-gradient(circle, #ffffff 1px, transparent 1px); background-size: 28px 28px;">
    </div>

    <div class="relative w-full max-w-md">

        {{-- Brand --}}
        <div class="text-center mb-7">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-white shadow-2xl mb-4 ring-1 ring-white/10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">طلب إعادة الوصول</h1>
            <p class="text-blue-300/80 mt-1.5 text-sm">سيتواصل معك المسؤول لإعادة تعيين كلمة المرور</p>
        </div>

        @if($sent)
            {{-- ── Success state ─────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h2 class="text-lg font-bold text-gray-900 mb-2">تم إرسال طلبك</h2>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    وصل إشعار للمسؤول بطلبك، سيتم التواصل معك قريباً على بريدك الإلكتروني.
                </p>
                <a href="{{ route('login') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-colors shadow-sm">
                    العودة لتسجيل الدخول
                </a>
            </div>

        @else
            {{-- ── Request Form ──────────────────────────────── --}}
            <div class="bg-white rounded-2xl shadow-2xl p-7">

                <p class="text-sm text-gray-500 leading-relaxed mb-5">
                    أدخل بياناتك وسيصل إشعار فوري للمسؤول ليتواصل معك ويعيد تعيين كلمة مرورك.
                </p>

                <form wire:submit="sendRequest" class="space-y-4">

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            البريد الإلكتروني
                            <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email"
                               id="email" type="email" name="email"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="example@quran.com"
                               required autofocus />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    {{-- Name --}}
                    <div>
                        <label for="requesterName" class="block text-sm font-medium text-gray-700 mb-1.5">
                            الاسم الكامل
                            <span class="text-gray-400 text-xs font-normal">(اختياري)</span>
                        </label>
                        <input wire:model="requesterName"
                               id="requesterName" type="text"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="اسمك الكامل" />
                    </div>

                    {{-- Message --}}
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1.5">
                            ملاحظة للمسؤول
                            <span class="text-gray-400 text-xs font-normal">(اختياري)</span>
                        </label>
                        <textarea wire:model="message"
                                  id="message" rows="3"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 resize-none"
                                  placeholder="أي معلومات إضافية تساعد المسؤول على التعرف عليك..."></textarea>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm transition-colors shadow-sm">
                        <span wire:loading.remove>إرسال الطلب للمسؤول</span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            جارٍ الإرسال...
                        </span>
                    </button>

                </form>
            </div>

            {{-- Back link --}}
            <div class="mt-5 text-center">
                <a href="{{ route('login') }}" wire:navigate
                   class="inline-flex items-center gap-2 text-white/50 hover:text-white/80 text-sm transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    العودة لتسجيل الدخول
                </a>
            </div>

        @endif

    </div>
</div>
