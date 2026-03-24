<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <h1 class="text-2xl font-bold">طلبات التسجيل</h1>
        <p class="text-gray-600 mt-1">مراجعة طلبات تسجيل الطلاب المرسلة من أولياء الأمور.</p>
    </div>

    {{-- Stats / Filter --}}
    <div class="grid grid-cols-3 gap-4">
        <button wire:click="$set('filterStatus','pending')"
                class="bg-white border rounded-xl p-4 text-center transition-colors
                    {{ $filterStatus === 'pending' ? 'border-amber-400 ring-1 ring-amber-400' : 'border-gray-200 hover:border-gray-300' }}">
            <div class="text-2xl font-bold text-amber-600">{{ $pendingCount }}</div>
            <div class="text-xs text-gray-500 mt-0.5">قيد الانتظار</div>
        </button>
        <button wire:click="$set('filterStatus','approved')"
                class="bg-white border rounded-xl p-4 text-center transition-colors
                    {{ $filterStatus === 'approved' ? 'border-emerald-400 ring-1 ring-emerald-400' : 'border-gray-200 hover:border-gray-300' }}">
            <div class="text-2xl font-bold text-emerald-600">{{ $approvedCount }}</div>
            <div class="text-xs text-gray-500 mt-0.5">مقبولة</div>
        </button>
        <button wire:click="$set('filterStatus','rejected')"
                class="bg-white border rounded-xl p-4 text-center transition-colors
                    {{ $filterStatus === 'rejected' ? 'border-red-400 ring-1 ring-red-400' : 'border-gray-200 hover:border-gray-300' }}">
            <div class="text-2xl font-bold text-red-600">{{ $rejectedCount }}</div>
            <div class="text-xs text-gray-500 mt-0.5">مرفوضة</div>
        </button>
    </div>

    {{-- Requests Table --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">

        @if($requests->isEmpty())
            <div class="py-12 text-center text-gray-400">
                <p class="text-sm">لا توجد طلبات
                    @if($filterStatus === 'pending') قيد الانتظار
                    @elseif($filterStatus === 'approved') مقبولة
                    @else مرفوضة
                    @endif.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-right px-5 py-3 font-semibold text-gray-600">ولي الأمر</th>
                            <th class="text-right px-5 py-3 font-semibold text-gray-600">بيانات الطالب</th>
                            <th class="text-right px-5 py-3 font-semibold text-gray-600">المركز</th>
                            <th class="text-right px-5 py-3 font-semibold text-gray-600">تاريخ الطلب</th>
                            @if($filterStatus === 'pending')
                                <th class="text-right px-5 py-3 font-semibold text-gray-600">إجراء</th>
                            @elseif($filterStatus === 'approved')
                                <th class="text-right px-5 py-3 font-semibold text-gray-600">الحلقة</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($requests as $req)
                            <tr wire:key="req-{{ $req->id }}" class="hover:bg-gray-50 transition-colors">

                                {{-- Guardian --}}
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900">{{ $req->guardian?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $req->guardian?->email ?? '' }}</div>
                                    @if($req->guardian_phone)
                                        <div class="text-xs text-gray-500 mt-0.5 font-medium" dir="ltr">{{ $req->guardian_phone }}</div>
                                    @endif
                                </td>

                                {{-- Student info (from guardian's manual entry) --}}
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900">{{ $req->student_name }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5 space-x-2 space-x-reverse">
                                        @if($req->student_age)
                                            <span>{{ $req->student_age }} سنة</span>
                                        @endif
                                        @if($req->student_notes)
                                            <span class="text-gray-300">·</span>
                                            <span class="italic">{{ Str::limit($req->student_notes, 50) }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Center --}}
                                <td class="px-5 py-3 text-gray-600">
                                    <div>{{ $req->center?->name ?? '—' }}</div>
                                    @if($req->center?->location)
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $req->center->location }}</div>
                                    @endif
                                </td>

                                {{-- Date --}}
                                <td class="px-5 py-3 text-gray-400 text-xs">
                                    {{ $req->created_at->format('Y/m/d') }}
                                </td>

                                {{-- Action / Halaqa --}}
                                @if($filterStatus === 'pending')
                                    <td class="px-5 py-3">
                                        <div class="flex gap-2">
                                            <button wire:click="openApprove({{ $req->id }})"
                                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors">
                                                قبول
                                            </button>
                                            <button wire:click="reject({{ $req->id }})"
                                                    wire:confirm="هل تريد رفض هذا الطلب؟"
                                                    class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition-colors">
                                                رفض
                                            </button>
                                        </div>
                                    </td>
                                @elseif($filterStatus === 'approved')
                                    <td class="px-5 py-3 text-gray-600 text-xs">
                                        {{ $req->student?->halaqa?->name ?? '—' }}
                                    </td>
                                @endif

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($requests->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $requests->links() }}
                </div>
            @endif
        @endif

    </div>

    {{-- Approval Modal --}}
    @if($showApproveModal)
        @php
            $approvingReq = $requests->firstWhere('id', $approvingRequestId)
                ?? \App\Models\StudentRequest::with(['guardian','center'])->find($approvingRequestId);
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
             x-data x-on:keydown.escape.window="$wire.closeApproveModal()">

            <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-5"
                 x-on:click.stop>

                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-800">قبول طلب التسجيل</h2>
                    <button wire:click="closeApproveModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Student info summary --}}
                @if($approvingReq)
                    <div class="bg-gray-50 rounded-lg p-4 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">ولي الأمر</span>
                            <span class="font-medium text-gray-800">{{ $approvingReq->guardian?->name ?? '—' }}</span>
                        </div>
                        @if($approvingReq->guardian_phone)
                            <div class="flex justify-between">
                                <span class="text-gray-500">رقم الهاتف</span>
                                <span class="font-medium text-gray-800 tracking-wide" dir="ltr">{{ $approvingReq->guardian_phone }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">اسم الطالب</span>
                            <span class="font-bold text-gray-900">{{ $approvingReq->student_name }}</span>
                        </div>
                        @if($approvingReq->student_age)
                            <div class="flex justify-between">
                                <span class="text-gray-500">العمر</span>
                                <span class="text-gray-700">{{ $approvingReq->student_age }} سنة</span>
                            </div>
                        @endif
                        @if($approvingReq->student_notes)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-500 shrink-0">ملاحظات</span>
                                <span class="text-gray-600 text-right">{{ $approvingReq->student_notes }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">المركز</span>
                            <span class="text-gray-700">{{ $approvingReq->center?->name ?? '—' }}</span>
                        </div>
                    </div>
                @endif

                {{-- Halaqa selection --}}
                <div>
                    <label for="approve-halaqa-select" class="block text-sm font-bold text-gray-700 mb-1.5">
                        اختر الحلقة المناسبة <span class="text-red-500">*</span>
                    </label>
                    <select id="approve-halaqa-select" wire:model="approveHalaqaId"
                            class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        <option value="">-- اختر الحلقة --</option>
                        @foreach($halaqas as $halaqa)
                            <option value="{{ $halaqa->id }}">{{ $halaqa->name }}</option>
                        @endforeach
                    </select>
                    @error('approveHalaqaId')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 pt-1">
                    <button wire:click="confirmApprove"
                            wire:loading.attr="disabled"
                            class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 text-white font-bold rounded-lg text-sm transition-colors">
                        <span wire:loading.remove wire:target="confirmApprove">تأكيد القبول وإنشاء الطالب</span>
                        <span wire:loading wire:target="confirmApprove">جارٍ الحفظ...</span>
                    </button>
                    <button wire:click="closeApproveModal"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-sm transition-colors">
                        إلغاء
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
