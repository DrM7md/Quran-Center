@extends('layouts.guest')

@section('title', 'تأكيد البريد الإلكتروني')

@section('content')
    <h1 class="mb-2 text-xl font-black text-slate-800">تأكيد البريد الإلكتروني</h1>
    <p class="mb-6 text-sm font-semibold text-slate-600">
        قبل ما تكمل، لازم نأكد بريدك. اضغط “إرسال رابط جديد” إذا ما وصلك الإيميل.
    </p>

    <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
        @csrf

        <button
            class="w-full rounded-xl bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 px-4 py-3
                   text-sm font-black text-white shadow-lg shadow-rose-900/25 hover:opacity-95"
        >
            إرسال رابط جديد
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700
                   hover:bg-slate-50"
        >
            خروج
        </button>
    </form>
@endsection
