@extends('layouts.guest')

@section('title', 'إنشاء حساب')

@section('content')
    <h1 class="mb-6 text-xl font-bold text-gray-800">إنشاء حساب</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        {{-- الاسم --}}
        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">الاسم</label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        {{-- البريد --}}
        <div>
            <label for="email" class="mb-1 block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        {{-- كلمة المرور --}}
        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-gray-700">كلمة المرور</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        {{-- تأكيد كلمة المرور --}}
        <div>
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">تأكيد كلمة المرور</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring"
            >
        </div>

        <button class="w-full rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
            تسجيل
        </button>
    </form>

    <div class="mt-4 text-center text-sm text-gray-600">
        عندك حساب؟
        <a class="text-blue-600 hover:underline" href="{{ route('login') }}">سجل دخول</a>
    </div>
@endsection
