@extends('layouts.guest')

@section('title', 'استعادة كلمة المرور')

@section('content')
    <h1 class="mb-2 text-xl font-black text-slate-800">استعادة كلمة المرور</h1>
    <p class="mb-6 text-sm font-semibold text-slate-600">
        اكتب بريدك الإلكتروني، وبنرسل لك رابط إعادة تعيين كلمة المرور.
    </p>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="mb-1 block text-sm font-bold text-slate-700">البريد الإلكتروني</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800
                       outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
            >
        </div>

        <button
            class="w-full rounded-xl bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 px-4 py-3
                   text-sm font-black text-white shadow-lg shadow-rose-900/25 hover:opacity-95"
        >
            إرسال رابط الاستعادة
        </button>
    </form>

    <div class="mt-5 text-center text-sm font-semibold text-slate-600">
        تذكّرت كلمة المرور؟
        <a class="text-rose-700 hover:underline" href="{{ route('login') }}">ارجع لتسجيل الدخول</a>
    </div>
@endsection
