@props(['header' => null])

@php
    $user         = auth()->user();
    $currentRoute = request()->route()?->getName();
    $navClass     = fn(string $route) =>
        ($currentRoute === $route)
            ? 'px-3.5 py-2 rounded-lg text-sm font-medium bg-emerald-50 text-emerald-700'
            : 'px-3.5 py-2 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors';
    $unread = $user ? $user->unreadNotificationsCount() : 0;
@endphp

<header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div x-data="{ mobileMenu: false, notifOpen: false }" class="flex items-center justify-between h-16 gap-4">

            {{-- Brand --}}
            <a href="{{ route('parent.dashboard') }}" class="flex items-center gap-3 shrink-0 group">
                <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-black text-lg group-hover:bg-emerald-700 transition-colors">
                    ق
                </div>
                <div class="hidden sm:block">
                    <div class="font-bold text-gray-900 leading-none text-sm">بوابة ولي الأمر</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $header ?? 'متابعة الأبناء' }}</div>
                </div>
            </a>

            {{-- Desktop Nav --}}
            <nav class="hidden lg:flex items-center gap-1">
                <a href="{{ route('parent.dashboard') }}" class="{{ $navClass('parent.dashboard') }}">الرئيسية</a>
                <a href="{{ route('parent.request') }}" class="{{ $navClass('parent.request') }}">إضافة طالب</a>
            </nav>

            {{-- Right Side --}}
            <div class="flex items-center gap-2">

                {{-- Notification Bell --}}
                @if($user)
                    <div class="relative">
                        <button type="button"
                                @click="notifOpen = !notifOpen"
                                class="relative p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unread > 0)
                                <span class="absolute top-1 right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-red-500 text-white rounded-full">
                                    {{ $unread > 9 ? '9+' : $unread }}
                                </span>
                            @endif
                        </button>

                        {{-- Notifications Dropdown --}}
                        <div x-show="notifOpen"
                             @click.outside="notifOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 mt-2 w-80 bg-white rounded-xl border border-gray-200 shadow-lg z-50 origin-top-left"
                             style="display: none;">
                            <div class="p-3 border-b border-gray-100">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-sm text-gray-800">الإشعارات</span>
                                    @if($unread > 0)
                                        <a href="{{ route('parent.dashboard') }}"
                                           class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                            تعيين كمقروءة
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                @forelse($user->guardianNotifications()->latest()->take(10)->get() as $notif)
                                    <div class="px-4 py-3 {{ $notif->is_read ? 'bg-white' : 'bg-emerald-50' }}">
                                        <p class="text-sm text-gray-800">{{ $notif->message }}</p>
                                        <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-400 text-center py-6">لا توجد إشعارات.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @endif

                {{-- User Pill --}}
                <div class="hidden md:flex items-center gap-2.5 px-3 py-2 rounded-lg border border-gray-200">
                    <div class="w-7 h-7 rounded-md bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ mb_substr($user?->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="leading-none">
                        <div class="text-xs font-semibold text-gray-800 truncate max-w-[130px]">
                            {{ $user?->name ?? '' }}
                        </div>
                        <div class="text-[10px] text-gray-400 mt-0.5">ولي الأمر</div>
                    </div>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                        خروج
                    </button>
                </form>

                {{-- Mobile Toggle --}}
                <button type="button"
                        class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors"
                        @click="mobileMenu = !mobileMenu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             class="lg:hidden border-t border-gray-100 py-3 space-y-0.5">

            <a href="{{ route('parent.dashboard') }}"
               class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ $currentRoute === 'parent.dashboard' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-100' }}">
                الرئيسية
            </a>
            <a href="{{ route('parent.request') }}"
               class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium {{ $currentRoute === 'parent.request' ? 'bg-emerald-50 text-emerald-700' : 'text-gray-700 hover:bg-gray-100' }}">
                إضافة طالب
            </a>

            <div class="border-t border-gray-100 pt-3 mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full text-right flex items-center px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
