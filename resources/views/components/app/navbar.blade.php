@props(['header' => null])

@php
    $user         = auth()->user();
    $isAdmin      = $user?->hasRole(['admin', 'super-admin']);
    $isSuperAdmin = $user?->hasRole('super-admin');
    $currentRoute = request()->route()?->getName();
    $navClass     = fn(string $route) =>
        ($currentRoute === $route)
            ? 'px-3.5 py-2 rounded-lg text-sm font-medium bg-blue-50 text-blue-700'
            : 'px-3.5 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors';
    $isCenterAdmin = $isAdmin && !$isSuperAdmin;
    $pendingRequestsCount = $isCenterAdmin && $user?->center_id
        ? \App\Models\StudentRequest::where('center_id', $user->center_id)->where('status', 'pending')->count()
        : 0;
    $accessRequestsCount = $isSuperAdmin
        ? \App\Models\AccessRequest::whereNull('center_id')->where('is_read', false)->count()
        : ($isCenterAdmin && $user?->center_id
            ? \App\Models\AccessRequest::where('center_id', $user->center_id)->where('is_read', false)->count()
            : 0);

    $mobileLink = fn(string $route) =>
        'flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors ' .
        ($currentRoute === $route
            ? 'bg-blue-50 text-blue-700'
            : 'text-gray-700 hover:bg-gray-100');
@endphp

<div x-data="{ mobileMenu: false }">

{{-- ══════════════════ Top Bar ══════════════════ --}}
<header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">

            {{-- Brand --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 shrink-0 group">
                <div class="w-9 h-9 rounded-lg bg-blue-600 text-white flex items-center justify-center font-black text-lg group-hover:bg-blue-700 transition-colors">
                    ق
                </div>
                <div class="hidden sm:block">
                    <div class="font-bold text-gray-900 leading-none text-sm">مركز التحفيظ</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $header ?? 'لوحة التحكم' }}</div>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-1" aria-label="القائمة الرئيسية">
                <a href="{{ route('dashboard') }}" class="{{ $navClass('dashboard') }}">الرئيسية</a>

                @if(!$isSuperAdmin)
                    <a href="{{ route('hifdh.index') }}" class="{{ $navClass('hifdh.index') }}">تسجيل التسميع</a>
                    <a href="{{ route('hifdh.progress') }}" class="{{ $navClass('hifdh.progress') }}">التقدم</a>
                    <a href="{{ route('absences.index') }}" class="{{ $navClass('absences.index') }}">الغياب</a>
                    <a href="{{ route('students.index') }}" class="{{ $navClass('students.index') }}">الطلاب</a>
                    @if($isAdmin)
                        <a href="{{ route('halaqas.index') }}" class="{{ $navClass('halaqas.index') }}">الحلقات</a>
                        <a href="{{ route('muhafidhs.index') }}" class="{{ $navClass('muhafidhs.index') }}">المحفظون</a>
                        <a href="{{ route('student-requests.index') }}"
                           class="{{ $navClass('student-requests.index') }} relative inline-flex items-center gap-1">
                            الطلبات
                            @if($pendingRequestsCount > 0)
                                <span class="inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-red-500 text-white rounded-full">
                                    {{ $pendingRequestsCount > 9 ? '9+' : $pendingRequestsCount }}
                                </span>
                            @endif
                        </a>
                    @endif
                @endif

                @if($isSuperAdmin)
                    <a href="{{ route('centers.index') }}" class="{{ $navClass('centers.index') }}">المراكز</a>
                @endif
            </nav>

            {{-- Right Side --}}
            <div class="flex items-center gap-2">

                {{-- Date --}}
                <span class="hidden xl:block text-xs text-gray-400 font-medium">
                    {{ now()->format('Y/m/d') }}
                </span>

                {{-- Bell: super-admin + center admin --}}
                @if($isSuperAdmin || $isCenterAdmin)
                    <a href="{{ route('admin.access-requests') }}"
                       class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors"
                       title="طلبات الوصول">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($accessRequestsCount > 0)
                            <span class="absolute top-1 right-1 flex items-center justify-center w-4 h-4 text-[9px] font-bold bg-red-500 text-white rounded-full leading-none">
                                {{ $accessRequestsCount > 9 ? '9+' : $accessRequestsCount }}
                            </span>
                        @endif
                    </a>
                @endif

                {{-- User Pill (desktop) --}}
                <a href="{{ route('profile.edit') }}"
                   class="hidden md:flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-colors">
                    <div class="w-7 h-7 rounded-md bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ mb_substr($user->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="leading-none">
                        <div class="text-xs font-semibold text-gray-800 truncate max-w-[130px]">
                            {{ $user->name ?? '' }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">
                            {{ $isSuperAdmin ? 'Super Admin' : ($isAdmin ? 'Admin' : 'محفظ') }}
                        </div>
                    </div>
                </a>

                {{-- Logout (desktop) --}}
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        خروج
                    </button>
                </form>

                {{-- Hamburger (mobile) --}}
                <button type="button"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                        @click="mobileMenu = true"
                        aria-label="فتح القائمة">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════ Mobile Drawer ══════════════════ --}}
{{-- Backdrop --}}
<div x-show="mobileMenu"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileMenu = false"
     class="lg:hidden fixed inset-0 z-50 bg-black/40 backdrop-blur-[2px]"
     style="display:none">
</div>

{{-- Drawer panel --}}
<div x-show="mobileMenu"
     x-transition:enter="transition ease-out duration-250 transform"
     x-transition:enter-start="translate-x-full opacity-0"
     x-transition:enter-end="translate-x-0 opacity-100"
     x-transition:leave="transition ease-in duration-200 transform"
     x-transition:leave-start="translate-x-0 opacity-100"
     x-transition:leave-end="translate-x-full opacity-0"
     class="lg:hidden fixed top-0 right-0 bottom-0 z-50 w-72 bg-white shadow-2xl flex flex-col"
     style="display:none">

    {{-- Drawer header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 bg-gradient-to-l from-blue-700 to-indigo-700">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-white/20 text-white flex items-center justify-center font-black text-lg">
                ق
            </div>
            <div>
                <div class="font-bold text-white text-sm leading-none">مركز التحفيظ</div>
                <div class="text-blue-200 text-xs mt-0.5">{{ $header ?? 'لوحة التحكم' }}</div>
            </div>
        </div>
        <button type="button"
                class="p-2 rounded-lg text-white/70 hover:text-white hover:bg-white/10 transition-colors"
                @click="mobileMenu = false"
                aria-label="إغلاق">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- User card --}}
    <a href="{{ route('profile.edit') }}" @click="mobileMenu = false"
       class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 hover:bg-gray-50 transition-colors">
        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-base shrink-0">
            {{ mb_substr($user->name ?? 'U', 0, 1) }}
        </div>
        <div>
            <div class="text-sm font-semibold text-gray-900 truncate max-w-[180px]">{{ $user->name ?? '' }}</div>
            <div class="text-xs text-gray-400 mt-0.5">{{ $user->email ?? '' }}</div>
        </div>
        <span class="mr-auto text-[10px] font-semibold px-2 py-0.5 rounded-full
            {{ $isSuperAdmin ? 'bg-violet-100 text-violet-700' : ($isAdmin ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600') }}">
            {{ $isSuperAdmin ? 'Super Admin' : ($isAdmin ? 'Admin' : 'محفظ') }}
        </span>
    </a>

    {{-- Nav links --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5" aria-label="قائمة الجوال">

        <a href="{{ route('dashboard') }}" @click="mobileMenu = false" class="{{ $mobileLink('dashboard') }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            الرئيسية
        </a>

        @if(!$isSuperAdmin)
            <a href="{{ route('hifdh.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('hifdh.index') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                تسجيل التسميع
            </a>
            <a href="{{ route('hifdh.progress') }}" @click="mobileMenu = false" class="{{ $mobileLink('hifdh.progress') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                التقدم
            </a>
            <a href="{{ route('absences.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('absences.index') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                الغياب
            </a>
            <a href="{{ route('students.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('students.index') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                الطلاب
            </a>

            @if($isAdmin)
                {{-- Divider --}}
                <div class="my-2 border-t border-gray-100"></div>
                <p class="px-4 pb-1 text-[10px] font-semibold text-gray-400 uppercase tracking-wide">إدارة المركز</p>

                <a href="{{ route('halaqas.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('halaqas.index') }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    الحلقات
                </a>
                <a href="{{ route('muhafidhs.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('muhafidhs.index') }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    المحفظون
                </a>
                <a href="{{ route('student-requests.index') }}" @click="mobileMenu = false"
                   class="{{ $mobileLink('student-requests.index') }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="flex-1">الطلبات</span>
                    @if($pendingRequestsCount > 0)
                        <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold bg-red-500 text-white rounded-full">
                            {{ $pendingRequestsCount > 9 ? '9+' : $pendingRequestsCount }}
                        </span>
                    @endif
                </a>
            @endif
        @endif

        @if($isSuperAdmin)
            <a href="{{ route('centers.index') }}" @click="mobileMenu = false" class="{{ $mobileLink('centers.index') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                المراكز
            </a>
        @endif

        @if($isSuperAdmin || $isCenterAdmin)
            <a href="{{ route('admin.access-requests') }}" @click="mobileMenu = false"
               class="{{ $mobileLink('admin.access-requests') }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="flex-1">طلبات الوصول</span>
                @if($accessRequestsCount > 0)
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 text-[10px] font-bold bg-red-500 text-white rounded-full">
                        {{ $accessRequestsCount > 9 ? '9+' : $accessRequestsCount }}
                    </span>
                @endif
            </a>
        @endif

    </nav>

    {{-- Bottom: logout --}}
    <div class="px-3 py-3 border-t border-gray-100">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                تسجيل الخروج
            </button>
        </form>
    </div>

</div>{{-- /drawer panel --}}

</div>{{-- /x-data --}}
