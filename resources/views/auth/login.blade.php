@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <h1 class="mb-6 text-xl font-bold text-gray-800">تسجيل الدخول</h1>

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-gray-700">كلمة المرور</label>
            <input
                type="password"
                name="password"
                required
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="remember" class="rounded border">
                تذكرني
            </label>

            <a class="text-sm text-blue-600 hover:underline" href="{{ route('password.request') }}">
                نسيت كلمة المرور؟
            </a>
        </div>

        <button class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            دخول
        </button>
    </form>

    @if (Route::has('register'))
        <div class="mt-4 text-center text-sm text-gray-600">
            ما عندك حساب؟
            <a class="text-blue-600 hover:underline" href="{{ route('register') }}">سجل الآن</a>
        </div>
    @endif
@endsection
