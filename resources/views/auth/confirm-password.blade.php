@extends('layouts.guest')

@section('title', 'تأكيد كلمة المرور')

@section('content')
    <h1 class="mb-2 text-xl font-black text-slate-800">تأكيد كلمة المرور</h1>
    <p class="mb-6 text-sm font-semibold text-slate-600">
        لأسباب أمنية، اكتب كلمة المرور مرة ثانية.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="mb-1 block text-sm font-bold text-slate-700">كلمة المرور</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-800
                       outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100"
            >
        </div>

        <button
            class="w-full rounded-xl bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 px-4 py-3
                   text-sm font-black text-white shadow-lg shadow-rose-900/25 hover:opacity-95"
        >
            متابعة
        </button>
    </form>
@endsection
