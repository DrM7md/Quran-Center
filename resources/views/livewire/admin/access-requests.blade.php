<?php

use App\Models\AccessRequest;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;

new #[Layout('components.layouts.app', ['header' => 'طلبات الوصول'])] class extends Component
{
    public $requests;
    public int $pendingCount  = 0;
    public int $resolvedCount = 0;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('super-admin')) {
            AccessRequest::whereNull('center_id')->where('is_read', false)->update(['is_read' => true]);
        } else {
            AccessRequest::where('center_id', $user->center_id)->where('is_read', false)->update(['is_read' => true]);
        }

        $this->loadRequests();
    }

    private function loadRequests(): void
    {
        $user  = auth()->user();
        $query = AccessRequest::orderBy('is_resolved')->orderByDesc('created_at');

        if ($user->hasRole('super-admin')) {
            $query->whereNull('center_id');
        } else {
            $query->where('center_id', $user->center_id);
        }

        $all = $query->get();

        $this->requests     = $all;
        $this->pendingCount  = $all->where('is_resolved', false)->count();
        $this->resolvedCount = $all->where('is_resolved', true)->count();
    }

    public function resolve(int $id): void
    {
        AccessRequest::where('id', $id)->update(['is_resolved' => true]);
        $this->loadRequests();
    }

    public function unresolve(int $id): void
    {
        AccessRequest::where('id', $id)->update(['is_resolved' => false]);
        $this->loadRequests();
    }

    public function resetPassword(int $id): void
    {
        $req  = AccessRequest::find($id);
        if (!$req) return;

        $target = User::where('email', $req->email)->first();
        if ($target) {
            $target->update(['password' => Hash::make('12345678')]);
        }

        $req->update(['is_resolved' => true]);
        $this->loadRequests();
    }

    public function destroy(int $id): void
    {
        AccessRequest::where('id', $id)->delete();
        $this->loadRequests();
    }
};
?>

@php
    $isSuperAdmin = auth()->user()?->hasRole('super-admin');
    $roleLabel    = $isSuperAdmin ? 'Super Admin' : 'Admin';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-blue-700 to-indigo-800 rounded-xl p-6 text-white shadow-sm">
        <div class="flex items-start justify-between gap-4 flex-col sm:flex-row">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-wide">{{ $roleLabel }}</p>
                <h1 class="text-2xl font-bold mt-1">طلبات الوصول</h1>
                <p class="text-blue-200 mt-1 text-sm">
                    @if($isSuperAdmin)
                        طلبات نسيان كلمة المرور الواردة من المشرفين (Admins)
                    @else
                        طلبات نسيان كلمة المرور الواردة من محفظي مركزك
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <div class="text-center bg-white/10 rounded-xl px-4 py-2.5 border border-white/20">
                    <p class="text-2xl font-bold leading-none">{{ $pendingCount }}</p>
                    <p class="text-blue-200 text-xs mt-0.5">بانتظار المتابعة</p>
                </div>
                <div class="text-center bg-white/10 rounded-xl px-4 py-2.5 border border-white/20">
                    <p class="text-2xl font-bold leading-none">{{ $resolvedCount }}</p>
                    <p class="text-blue-200 text-xs mt-0.5">تم التواصل</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @if($requests->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-14 text-center">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-gray-500 font-medium">لا توجد طلبات وصول</p>
            <p class="text-gray-400 text-sm mt-1">ستظهر هنا طلبات المستخدمين عند الضغط على "نسيت كلمة المرور"</p>
        </div>

    @else
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100 text-xs text-gray-500 font-semibold">
                            <th class="px-4 py-3 text-right">#</th>
                            <th class="px-4 py-3 text-right">البريد الإلكتروني</th>
                            <th class="px-4 py-3 text-right">الاسم</th>
                            <th class="px-4 py-3 text-right">ملاحظة</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">وقت الطلب</th>
                            <th class="px-4 py-3 text-center">الحالة</th>
                            <th class="px-4 py-3 text-center">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($requests as $i => $req)
                            <tr @class([
                                'transition-colors hover:bg-gray-50/80',
                                'opacity-60' => $req->is_resolved,
                            ])>
                                <td class="px-4 py-3.5 text-gray-400 text-xs font-medium w-10">
                                    {{ $i + 1 }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <a href="mailto:{{ $req->email }}"
                                       class="font-semibold text-blue-600 hover:text-blue-700 transition-colors"
                                       dir="ltr">
                                        {{ $req->email }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 text-gray-700">
                                    {{ $req->requester_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-gray-500 max-w-xs">
                                    @if($req->message)
                                        <span class="truncate block max-w-[220px]" title="{{ $req->message }}">
                                            {{ $req->message }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center text-xs text-gray-400 whitespace-nowrap">
                                    <span title="{{ $req->created_at->format('Y-m-d H:i') }}">
                                        {{ $req->created_at->diffForHumans() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($req->is_resolved)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium ring-1 ring-inset ring-emerald-200">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            تم التواصل
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 text-xs font-medium ring-1 ring-inset ring-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                                            بانتظار المتابعة
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5 flex-wrap">

                                        {{-- Reset Password --}}
                                        <button wire:click="resetPassword({{ $req->id }})"
                                                wire:confirm="سيتم تغيير كلمة مرور هذا المستخدم إلى 12345678. هل أنت متأكد؟"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-violet-50 text-violet-700 text-xs font-medium hover:bg-violet-100 transition-colors ring-1 ring-inset ring-violet-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            تعيين كلمة مرور
                                        </button>

                                        {{-- Resolve / Unresolve --}}
                                        @if(!$req->is_resolved)
                                            <button wire:click="resolve({{ $req->id }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-emerald-100 transition-colors ring-1 ring-inset ring-emerald-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                تم التواصل
                                            </button>
                                        @else
                                            <button wire:click="unresolve({{ $req->id }})"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors ring-1 ring-inset ring-gray-200">
                                                تراجع
                                            </button>
                                        @endif

                                        {{-- Delete --}}
                                        <button wire:click="destroy({{ $req->id }})"
                                                wire:confirm="هل تريد حذف هذا الطلب نهائياً؟"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition-colors ring-1 ring-inset ring-red-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            حذف
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>
