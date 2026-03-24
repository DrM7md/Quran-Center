@php
    $statusLabel = ['pending' => 'قيد الانتظار', 'approved' => 'مقبول', 'rejected' => 'مرفوض'];
    $statusColor = [
        'pending'  => 'bg-amber-50 text-amber-700 ring-amber-200',
        'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'rejected' => 'bg-red-50 text-red-700 ring-red-200',
    ];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-l from-emerald-700 to-teal-800 rounded-xl p-6 text-white shadow-sm">
        <h1 class="text-xl font-bold">تسجيل طالب</h1>
        <p class="text-emerald-200 text-sm mt-1">أدخل بيانات ابنك وسيتولى الأدمن تسجيله في الحلقة المناسبة.</p>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-sm font-semibold">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Registration Form --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <h2 class="text-base font-bold text-gray-700 mb-5">طلب تسجيل طالب جديد</h2>

        <form wire:submit="submitRequest" class="space-y-5">

            {{-- Center --}}
            <div>
                <label for="center-select" class="block text-sm font-bold text-gray-700 mb-1.5">
                    المركز
                </label>
                <select id="center-select" wire:model="selectedCenter"
                        class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">-- اختر المركز --</option>
                    @foreach($centers as $center)
                        <option value="{{ $center->id }}">
                            {{ $center->name }}{{ $center->location ? ' — ' . $center->location : '' }}
                        </option>
                    @endforeach
                </select>
                @error('selectedCenter') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Guardian Phone --}}
            <div>
                <label for="guardian-phone" class="block text-sm font-bold text-gray-700 mb-1.5">
                    رقم الهاتف
                </label>
                <input id="guardian-phone" wire:model="guardianPhone"
                       type="tel"
                       placeholder="مثال: 0512345678"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                       dir="ltr" />
                @error('guardianPhone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Student Name --}}
            <div>
                <label for="student-name" class="block text-sm font-bold text-gray-700 mb-1.5">
                    اسم الطالب
                </label>
                <input id="student-name" wire:model="studentName"
                       type="text"
                       placeholder="أدخل الاسم الكامل للطالب"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                @error('studentName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Age --}}
            <div>
                <label for="student-age" class="block text-sm font-bold text-gray-700 mb-1.5">
                    العمر <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <input id="student-age" wire:model="studentAge"
                       type="number" min="4" max="99"
                       placeholder="مثال: 10"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                       dir="ltr" />
                @error('studentAge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="student-notes" class="block text-sm font-bold text-gray-700 mb-1.5">
                    ملاحظات <span class="text-gray-400 font-normal">(اختياري)</span>
                </label>
                <textarea id="student-notes" wire:model="studentNotes"
                          rows="3"
                          placeholder="أي معلومات إضافية تريد إيصالها للأدمن..."
                          class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm resize-none"></textarea>
                @error('studentNotes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="pt-1">
                <button type="submit"
                        class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-sm transition-colors">
                    <span wire:loading.remove wire:target="submitRequest">إرسال طلب التسجيل</span>
                    <span wire:loading wire:target="submitRequest">جارٍ الإرسال...</span>
                </button>
            </div>

        </form>
    </div>

    {{-- My Requests --}}
    <div class="bg-white border border-gray-200 shadow-sm rounded-xl p-5 sm:p-6">
        <h2 class="text-base font-bold text-gray-700 mb-4">طلباتي السابقة</h2>

        @if($myRequests->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">لا توجد طلبات سابقة.</p>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($myRequests as $req)
                    <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-800 truncate">{{ $req->student_name }}</div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $req->center?->name ?? '—' }}
                                @if($req->center?->location)
                                    <span class="text-gray-300 mx-1">•</span>{{ $req->center->location }}
                                @endif
                                @if($req->student_age)
                                    <span class="text-gray-300 mx-1">·</span>{{ $req->student_age }} سنة
                                @endif
                                <span class="text-gray-300 mx-1">·</span>{{ $req->created_at->format('Y/m/d') }}
                            </div>
                        </div>
                        <span class="shrink-0 inline-flex px-2.5 py-1 rounded-md text-xs font-semibold ring-1 ring-inset {{ $statusColor[$req->status] ?? 'bg-gray-100 text-gray-600 ring-gray-200' }}">
                            {{ $statusLabel[$req->status] ?? $req->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
