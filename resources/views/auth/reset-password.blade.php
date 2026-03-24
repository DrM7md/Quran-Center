@extends('layouts.guest')

@section('title', 'إعادة تعيين كلمة المرور')

@section('content')
    <h1 class="mb-2 text-xl font-black text-slate-800">إعادة تعيين كلمة المرور</h1>
    <p class="mb-6 text-sm font-semibold text-slate-600">
        اكتب كلمة مرور جديدة لحسابك.
    </p>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        {{-- Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email --}}
        <div>
            <label for="email" class="mb-1 block text-sm font-bold text-slate-700">البريد الإلكتروني</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800
                       outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
            >
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="mb-1 block text-sm font-bold text-slate-700">كلمة المرور الجديدة</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800
                       outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
            >
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-bold text-slate-700">تأكيد كلمة المرور</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800
                       outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
            >
        </div>

        <button
            class="w-full rounded-xl bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 px-4 py-3
                   text-sm font-black text-white shadow-lg shadow-rose-900/25 hover:opacity-95"
        >
            حفظ كلمة المرور
        </button>
    </form>

    <div class="mt-5 text-center text-sm font-semibold text-slate-600">
        تبي تلغي؟
        <a class="text-rose-700 hover:underline" href="{{ route('login') }}">ارجع لتسجيل الدخول</a>
    </div>
@endsection
