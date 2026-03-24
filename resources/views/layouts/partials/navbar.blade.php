<header class="sticky top-0 z-30 border-b border-gray-200 bg-white/80 backdrop-blur-md shadow-sm transition-all duration-300">
    <div class="h-16 w-full flex items-center justify-between px-4 sm:px-6 lg:px-8">

        {{-- القسم الأيمن (في العربية): زر القائمة + العناوين --}}
        <div class="flex items-center gap-4">
            
            {{-- زر طي القائمة --}}
            <button
                type="button"
                class="group inline-flex items-center justify-center h-10 w-10 rounded-xl text-gray-500 hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500/50 active:scale-95 transition-all duration-200"
                @click="toggleSidebar()"
                aria-label="Toggle Sidebar"
                title="القائمة الجانبية"
            >
                <svg class="h-6 w-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                </svg>
            </button>

            {{-- فاصل عمودي --}}
            <div class="h-8 w-px bg-gray-200 mx-1 hidden sm:block"></div>

            {{-- عنوان الصفحة --}}
            <div class="hidden sm:flex flex-col justify-center">
                <h1 class="text-base font-bold text-gray-800 leading-tight">
                    @yield('page_title', 'لوحة التحكم')
                </h1>
                <p class="text-[11px] font-medium text-gray-400 mt-0.5">
                    @yield('page_subtitle', 'نظام الإدارة المهني')
                </p>
            </div>
        </div>

        {{-- القسم الأيسر: قائمة المستخدم --}}
        <div class="flex items-center gap-3" x-data="{ open: false }" @click.away="open = false">

            {{-- زر المستخدم --}}
            <button
                type="button"
                class="group flex items-center gap-3 rounded-full bg-white py-1.5 ps-1.5 pe-3 border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition-all duration-200"
                @click="open = !open"
                :class="open ? 'ring-2 ring-blue-100 border-blue-200' : ''"
            >
                {{-- الصورة / الحرف --}}
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-blue-600 to-blue-700 text-white flex items-center justify-center font-bold text-sm shadow-sm group-hover:scale-105 transition-transform">
                    {{ mb_substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>

                {{-- النصوص (مخفية في الشاشات الصغيرة جداً) --}}
                <div class="hidden md:block text-start">
                    <div class="text-sm font-bold text-gray-700 group-hover:text-blue-700 transition-colors truncate max-w-[100px]">
                        {{ Auth::user()->name ?? 'المستخدم' }}
                    </div>
                </div>

                {{-- أيقونة السهم --}}
                <svg class="h-4 w-4 text-gray-400 group-hover:text-blue-600 transition-transform duration-200" 
                     :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- القائمة المنسدلة --}}
            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                class="absolute end-4 top-16 w-60 rounded-xl bg-white shadow-xl ring-1 ring-black/5 z-50 overflow-hidden mt-2"
            >
                {{-- رأس القائمة --}}
                <div class="bg-gray-50/50 px-4 py-3 border-b border-gray-100">
                    <p class="text-sm font-bold text-gray-900 truncate">{{ Auth::user()->name ?? 'اسم المستخدم' }}</p>
                    <p class="text-xs text-gray-500 truncate mt-0.5 font-mono">{{ Auth::user()->email ?? 'email@example.com' }}</p>
                </div>

                {{-- خيارات القائمة --}}
                <div class="p-1.5 space-y-1">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-blue-50 hover:text-blue-700 transition-colors group">
                        <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        الملف الشخصي
                    </a>

                    <div class="h-px bg-gray-100 my-1 mx-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex items-center gap-3 w-full px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors group">
                            <svg class="w-4 h-4 text-red-500 group-hover:text-red-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>