@php
    $enforcePermissions = true; // خليها true لأنك فعلياً شغال بالصلاحيات

    // ✅ يدعم ability كنص أو كمصفوفة (OR)، ويدعم "role:xxx" للتحقق من الدور
    $canSee = function ($ability) use ($enforcePermissions) {
        if (!$enforcePermissions || empty($ability)) {
            return true;
        }

        if (!auth()->check()) {
            return false;
        }

        // ability مصفوفة => لو عنده أي صلاحية أو دور منها
        if (is_array($ability)) {
            foreach ($ability as $ab) {
                if (str_starts_with($ab, 'role:')) {
                    if (auth()->user()->hasRole(substr($ab, 5))) return true;
                } elseif (auth()->user()->can($ab)) {
                    return true;
                }
            }
            return false;
        }

        // صيغة role:xxx
        if (str_starts_with($ability, 'role:')) {
            return auth()->user()->hasRole(substr($ability, 5));
        }

        // ability نص عادي
        return auth()->user()->can($ability);
    };

    // ✅ الشكل الصحيح للمجموعات: title + items
    $groups = [
        [
            'title' => 'عام',
            'items' => [
                [
                    'label' => 'لوحة التحكم',
                    'route' => 'dashboard',
                    'icon' => 'home',
                    'active' => request()->routeIs('dashboard'),
                    'ability' => 'dashboard.view',
                ],
            ],
        ],

        [
            'title' => 'المركز',
            'items' => [
                [
                    'label' => 'الطلاب',
                    'route' => 'students.index',
                    'icon'  => 'user',
                    'active' => request()->routeIs('students.*'),
                    'ability' => 'students.view',
                ],
                [
                    'label' => 'الحلقات',
                    'route' => 'halaqat.index',
                    'icon'  => 'settings',
                    'active' => request()->routeIs('halaqat.*'),
                    'ability' => 'halaqat.view',
                ],
                [
                    'label' => 'الحفظ',
                    'route' => 'memorization.index',
                    'icon'  => 'home',
                    'active' => request()->routeIs('memorization.*'),
                    'ability' => 'memorization.view',
                ],
                [
                    'label' => 'الحضور والغياب',
                    'route' => 'attendance.index',
                    'icon'  => 'home',
                    'active' => request()->routeIs('attendance.index') || request()->routeIs('attendance.toggle'),
                    'ability' => 'attendance.view',
                ],
                [
                    'label' => 'تقرير الحضور',
                    'route' => 'attendance.report',
                    'icon'  => 'home',
                    'active' => request()->routeIs('attendance.report'),
                    'ability' => 'attendance.report', // إذا ما عندك هالصلاحية خلّها attendance.view
                ],
            ],
        ],

        [
            'title' => 'السوبر أدمن',
            'items' => [
                [
                    'label'   => 'المراكز',
                    'route'   => 'centers.index',
                    'icon'    => 'settings',
                    'active'  => request()->routeIs('centers.*'),
                    'ability' => 'role:super-admin',
                ],
            ],
        ],

        [
            'title' => 'الإدارة',
            'items' => [
                [
                    'label' => 'إدارة النظام',
                    'icon' => 'settings',
                    'ability' => ['users.view', 'roles.view'],

                    'children' => [
                        [
                            'label' => 'المستخدمين',
                            'route' => 'users.index',
                            'icon' => 'user',
                            'active' => request()->routeIs('users.*'),
                            'ability' => 'users.view',
                        ],
                        [
                            'label' => 'الأدوار والصلاحيات',
                            'route' => 'roles.index',
                            'icon' => 'settings',
                            'active' => request()->routeIs('roles.*'),
                            'ability' => 'roles.view',
                        ],
                        [
                            'label' => 'الصلاحيات',
                            'route' => 'permissions.index',
                            'icon' => 'settings',
                            'active' => request()->routeIs('permissions.*'),
                            'ability' => 'roles.view',
                        ],
                    ],
                ],
            ],
        ],
    ];
@endphp


<aside
    class="min-h-screen bg-gradient-to-br from-white via-slate-50 to-gray-50 border-s border-slate-200/50 transition-all duration-300 overflow-hidden relative"
    :class="sidebarCollapsed ? 'w-[72px]' : 'w-[280px]'" style="font-family: 'Cairo', sans-serif;">
    {{-- نسيج خلفية راقي جداً --}}
    <div class="absolute inset-0 opacity-[0.015]"
        style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23475569\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
    </div>

    {{-- تدرج لوني علوي فاخر --}}
    <div
        class="absolute top-0 left-0 right-0 h-48 bg-gradient-to-b from-rose-50/40 via-transparent to-transparent pointer-events-none">
    </div>

    {{-- Header / Logo فاخر --}}
    <div
        class="relative h-[72px] flex items-center gap-4 px-5 border-b border-slate-200/60 bg-white/40 backdrop-blur-sm">
        <div class="relative group cursor-pointer">
            {{-- توهج خلفي --}}
            <div
                class="absolute -inset-1 bg-gradient-to-br from-rose-500 via-rose-600 to-rose-700 rounded-[14px] opacity-0 group-hover:opacity-20 blur-md transition-all duration-300">
            </div>

            {{-- الأيقونة الرئيسية --}}
            <div
                class="relative h-[42px] w-[42px] rounded-[14px] bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 flex items-center justify-center font-black text-white shadow-lg shadow-rose-900/30 ring-2 ring-white/20 transform group-hover:scale-105 group-hover:rotate-3 transition-all duration-300">
                <span class="text-lg">{{ mb_substr(config('app.name'), 0, 1) }}</span>
            </div>
        </div>

        <div class="leading-tight" x-show="!sidebarCollapsed" x-cloak>
            <div class="font-black text-slate-800 text-lg tracking-tight">{{ config('app.name') }}</div>
            <div class="text-xs text-slate-500 font-bold tracking-wide mt-0.5">لوحة الإدارة المتقدمة</div>
        </div>
    </div>

    {{-- القائمة الرئيسية --}}
    <nav class="relative p-4 space-y-8 mt-3">
        @foreach ($groups as $g)
            <div>
                {{-- عنوان القسم --}}
                <div class="px-4 mb-4 flex items-center gap-3" x-show="!sidebarCollapsed" x-cloak>
                    <div class="h-[3px] w-8 rounded-full bg-gradient-to-l from-rose-300 to-rose-400"></div>
                    <span class="text-[13px] font-black text-slate-400 uppercase tracking-[0.1em]">
                        {{ $g['title'] }}
                    </span>
                    <div class="h-px flex-1 bg-slate-200"></div>
                </div>

                <div class="space-y-1.5">
                    @foreach ($g['items'] as $item)
                        @php
                            if (!$canSee($item['ability'] ?? null)) {
                                continue;
                            }

                            $hasChildren = !empty($item['children']) && is_array($item['children']);

                            $isActive = $item['active'] ?? false;

                            if ($hasChildren) {
                                foreach ($item['children'] as $ch) {
                                    if (!empty($ch['active'])) {
                                        $isActive = true;
                                        break;
                                    }
                                }
                            }

                            $href = '#';
                            if (!empty($item['route'])) {
                                $href = route($item['route']);
                            } elseif (!empty($item['url'])) {
                                $href = $item['url'];
                            }
                        @endphp

                        {{-- =============== عنصر بدون Submenu =============== --}}
                        @if (!$hasChildren)
                            <a href="{{ $href }}"
                                class="group relative flex items-center gap-3.5 rounded-[14px] px-3.5 py-3 text-[15px] font-bold transition-all duration-300
                                      hover:translate-x-[-3px] overflow-hidden"
                                @class([
                                    'bg-gradient-to-l from-rose-50/80 via-rose-50/40 to-transparent text-slate-900 shadow-md shadow-rose-100/50' => $isActive,
                                    'text-slate-600 hover:text-slate-900 hover:bg-white/70 hover:shadow-sm' => !$isActive,
                                ])>
                                {{-- خلفية متحركة للعنصر النشط --}}
                                @if ($isActive)
                                    <div
                                        class="absolute inset-0 bg-gradient-to-r from-rose-100/30 to-transparent opacity-60">
                                    </div>
                                @endif

                                {{-- حاوية الأيقونة الفاخرة --}}
                                <div class="relative shrink-0 h-[38px] w-[38px] rounded-[11px] flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:rotate-6 overflow-hidden"
                                    @class([
                                        'bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 shadow-lg shadow-rose-900/40 ring-2 ring-rose-100' => $isActive,
                                        'bg-gradient-to-br from-slate-100 to-slate-200 group-hover:from-slate-200 group-hover:to-slate-300 shadow-sm' => !$isActive,
                                    ])>
                                    @if ($isActive)
                                        <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent">
                                        </div>
                                    @endif

                                    <span class="relative z-10" @class([
                                        'text-white drop-shadow-sm' => $isActive,
                                        'text-slate-600 group-hover:text-slate-700' => !$isActive,
                                    ])>
                                        @include('layouts.partials.icon', [
                                            'name' => $item['icon'],
                                            'class' => 'w-5 h-5',
                                        ])
                                    </span>
                                </div>

                                <span class="relative flex-1 tracking-wide" x-show="!sidebarCollapsed" x-cloak>
                                    {{ $item['label'] }}
                                </span>

                                @if ($isActive)
                                    <span
                                        class="absolute inset-y-0 start-0 w-[3px] rounded-l-full bg-gradient-to-b from-rose-600 via-rose-700 to-rose-800 shadow-lg shadow-rose-600/50"></span>
                                    <div
                                        class="absolute -right-2 top-1/2 -translate-y-1/2 h-12 w-12 bg-rose-400/15 rounded-full blur-xl">
                                    </div>

                                    <span class="text-rose-400 mr-1" x-show="!sidebarCollapsed" x-cloak>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </span>
                                @endif

                                {{-- Tooltip فاخر عند الطي --}}
                                <span
                                    class="pointer-events-none absolute right-full top-1/2 -translate-y-1/2 mr-4
                                           whitespace-nowrap rounded-xl bg-gradient-to-br from-slate-900 to-slate-800 px-4 py-2.5 text-[13px] text-white font-bold
                                           opacity-0 shadow-2xl shadow-slate-900/60 transition-all duration-300 group-hover:opacity-100 group-hover:mr-5
                                           border border-white/10 backdrop-blur-md
                                           before:content-[''] before:absolute before:left-full before:top-1/2 before:-translate-y-1/2
                                           before:border-[7px] before:border-transparent before:border-r-slate-900"
                                    x-show="sidebarCollapsed" x-cloak>
                                    {{ $item['label'] }}
                                </span>
                            </a>
                        @else
                            {{-- =============== عنصر مع Submenu =============== --}}
                            <div class="relative" x-data="{ open: false }" @mouseenter="if(sidebarCollapsed) open=true"
                                @mouseleave="if(sidebarCollapsed) open=false">
                                <button type="button"
                                    class="w-full group relative flex items-center gap-3.5 rounded-[14px] px-3.5 py-3 text-[15px] font-bold transition-all duration-300
                                           hover:translate-x-[-3px] overflow-hidden"
                                    @class([
                                        'bg-gradient-to-l from-rose-50/80 via-rose-50/40 to-transparent text-slate-900 shadow-md shadow-rose-100/50' => $isActive,
                                        'text-slate-600 hover:text-slate-900 hover:bg-white/70 hover:shadow-sm' => !$isActive,
                                    ]) @click="if(!sidebarCollapsed) open = !open">
                                    @if ($isActive)
                                        <div
                                            class="absolute inset-0 bg-gradient-to-r from-rose-100/30 to-transparent opacity-60">
                                        </div>
                                    @endif

                                    <div class="relative shrink-0 h-[38px] w-[38px] rounded-[11px] flex items-center justify-center transition-all duration-300 group-hover:scale-110 group-hover:rotate-6 overflow-hidden"
                                        @class([
                                            'bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 shadow-lg shadow-rose-900/40 ring-2 ring-rose-100' => $isActive,
                                            'bg-gradient-to-br from-slate-100 to-slate-200 group-hover:from-slate-200 group-hover:to-slate-300 shadow-sm' => !$isActive,
                                        ])>
                                        @if ($isActive)
                                            <div
                                                class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent">
                                            </div>
                                        @endif

                                        <span class="relative z-10" @class([
                                            'text-white drop-shadow-sm' => $isActive,
                                            'text-slate-600 group-hover:text-slate-700' => !$isActive,
                                        ])>
                                            @include('layouts.partials.icon', [
                                                'name' => $item['icon'],
                                                'class' => 'w-5 h-5',
                                            ])
                                        </span>
                                    </div>

                                    <span class="relative flex-1 tracking-wide text-start" x-show="!sidebarCollapsed"
                                        x-cloak>
                                        {{ $item['label'] }}
                                    </span>

                                    {{-- سهم submenu (فقط إذا مفتوح) --}}
                                    <span class="relative text-rose-400 mr-1" x-show="!sidebarCollapsed" x-cloak>
                                        <svg class="w-4 h-4 transition-all duration-300"
                                            :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>

                                    @if ($isActive)
                                        <span
                                            class="absolute inset-y-0 start-0 w-[3px] rounded-l-full bg-gradient-to-b from-rose-600 via-rose-700 to-rose-800 shadow-lg shadow-rose-600/50"></span>
                                        <div
                                            class="absolute -right-2 top-1/2 -translate-y-1/2 h-12 w-12 bg-rose-400/15 rounded-full blur-xl">
                                        </div>
                                    @endif

                                    {{-- Tooltip للأب عند الطي --}}
                                    <span
                                        class="pointer-events-none absolute right-full top-1/2 -translate-y-1/2 mr-4
                                               whitespace-nowrap rounded-xl bg-gradient-to-br from-slate-900 to-slate-800 px-4 py-2.5 text-[13px] text-white font-bold
                                               opacity-0 shadow-2xl shadow-slate-900/60 transition-all duration-300 group-hover:opacity-100 group-hover:mr-5
                                               border border-white/10 backdrop-blur-md
                                               before:content-[''] before:absolute before:left-full before:top-1/2 before:-translate-y-1/2
                                               before:border-[7px] before:border-transparent before:border-r-slate-900"
                                        x-show="sidebarCollapsed" x-cloak>
                                        {{ $item['label'] }}
                                    </span>
                                </button>

                                {{-- Submenu: Accordion لما السايدبار مفتوح --}}
                                <div x-show="!sidebarCollapsed && open" x-cloak class="mt-2 space-y-1 ps-2">
                                    @foreach ($item['children'] as $ch)
                                        @php
                                            if (!$canSee($ch['ability'] ?? null)) {
                                                continue;
                                            }

                                            $childHref = '#';
                                            if (!empty($ch['route'])) {
                                                $childHref = route($ch['route']);
                                            } elseif (!empty($ch['url'])) {
                                                $childHref = $ch['url'];
                                            }

                                            $childActive = !empty($ch['active']);
                                        @endphp

                                        <a href="{{ $childHref }}"
                                            class="group relative flex items-center gap-3 rounded-[12px] px-3 py-2 text-[13px] font-bold transition-all duration-300 overflow-hidden
                                                  hover:translate-x-[-2px]"
                                            @class([
                                                'bg-white/80 text-slate-900 shadow-sm' => $childActive,
                                                'text-slate-600 hover:text-slate-900 hover:bg-white/70' => !$childActive,
                                            ])>
                                            <span
                                                class="shrink-0 h-8 w-8 rounded-[10px] flex items-center justify-center"
                                                @class([
                                                    'bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 text-white shadow-md' => $childActive,
                                                    'bg-slate-100 text-slate-600 group-hover:bg-slate-200' => !$childActive,
                                                ])>
                                                @include('layouts.partials.icon', [
                                                    'name' => $ch['icon'],
                                                    'class' => 'w-4 h-4',
                                                ])
                                            </span>

                                            <span class="flex-1">{{ $ch['label'] }}</span>

                                            @if ($childActive)
                                                <span class="text-rose-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2.5" d="M15 19l-7-7 7-7" />
                                                    </svg>
                                                </span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>

                                {{-- Submenu: Popover لما السايدبار مطوي --}}
                                <div x-show="sidebarCollapsed && open" x-cloak
                                    class="absolute right-full top-2 mr-4 w-64 rounded-2xl bg-white/90 backdrop-blur-md border border-slate-200/60 shadow-2xl shadow-slate-900/10 overflow-hidden">
                                    <div
                                        class="px-4 py-3 bg-gradient-to-l from-rose-50/80 to-transparent border-b border-slate-200/60">
                                        <div class="font-black text-slate-800 text-sm">{{ $item['label'] }}</div>
                                        <div class="text-xs text-slate-500 font-bold mt-0.5">قائمة فرعية</div>
                                    </div>

                                    <div class="p-2 space-y-1">
                                        @foreach ($item['children'] as $ch)
                                            @php
                                                if (!$canSee($ch['ability'] ?? null)) {
                                                    continue;
                                                }

                                                $childHref = '#';
                                                if (!empty($ch['route'])) {
                                                    $childHref = route($ch['route']);
                                                } elseif (!empty($ch['url'])) {
                                                    $childHref = $ch['url'];
                                                }

                                                $childActive = !empty($ch['active']);
                                            @endphp

                                            <a href="{{ $childHref }}"
                                                class="flex items-center gap-3 rounded-[14px] px-3 py-2 text-[13px] font-bold transition-all duration-300
                                                      hover:bg-white/70"
                                                @class([
                                                    'bg-gradient-to-l from-rose-50/90 via-rose-50/40 to-transparent text-slate-900' => $childActive,
                                                    'text-slate-700' => !$childActive,
                                                ])>
                                                <span class="h-8 w-8 rounded-[11px] flex items-center justify-center"
                                                    @class([
                                                        'bg-gradient-to-br from-rose-900 via-rose-800 to-rose-900 text-white shadow-md' => $childActive,
                                                        'bg-slate-100 text-slate-600' => !$childActive,
                                                    ])>
                                                    @include('layouts.partials.icon', [
                                                        'name' => $ch['icon'],
                                                        'class' => 'w-4 h-4',
                                                    ])
                                                </span>

                                                <span class="flex-1">{{ $ch['label'] }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>
</aside>
