<div class="relative">
    @php
        use Illuminate\Support\Facades\Cache;

        $user = auth()->user();
        $isAdmin = $user?->hasRole('admin');
        $isSeller = $user?->role === 'seller';
        $isBuyer = $user?->role === 'buyer';
        $isSellerOrAdmin = $user && ($isSeller || $isAdmin);
        $isBuyerOrAdmin = $user && ($isBuyer || $isAdmin);
        $activeSubscription = $user?->activeBuyerSubscription();

        $desktopLinkClasses =
            'px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-gray-800 rounded-md transition-all duration-200';
        $desktopActiveClasses = 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-gray-800';
        $dropdownLinkClasses =
            'block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150';
        $dropdownActiveClasses = 'bg-blue-50 dark:bg-gray-800 text-blue-600 dark:text-blue-400';

        $primaryLinks = [
            [
                'label' => __('messages.home'),
                'href' => url('/'),
                'active' => request()->routeIs('welcome'),
            ],
        ];

        $moreLinks = [
            [
                'label' => __('messages.ecosystem'),
                'href' => route('ecosystem.index'),
                'active' => request()->routeIs('ecosystem.*'),
            ],
            [
                'label' => __('messages.tuts'),
                'href' => route('tuts.index'),
                'active' => request()->routeIs('tuts.*'),
            ],
            [
                'label' => __('messages.studio'),
                'href' => route('studio.index'),
                'active' => request()->routeIs('studio.*'),
            ],
        ];
        $adminMoreLinks = [];

        if ($user) {
            if ($user->role === 'user_workspaces') {
                $primaryLinks[] = [
                    'label' => __('messages.workspaces'),
                    'href' => route('workspaces.index'),
                    'active' => request()->routeIs('workspaces.*'),
                ];
            } else {
                $primaryLinks[] = [
                    'label' => __('messages.dashboard'),
                    'href' => route('dashboard'),
                    'active' => request()->routeIs('dashboard'),
                ];

                if ($isSellerOrAdmin) {
                    $primaryLinks[] = [
                        'label' => __('messages.notes'),
                        'href' => route('notes.index'),
                        'active' => request()->routeIs('notes.*'),
                    ];
                }

                // Workspaces link - available for all authenticated users with KYC
                $primaryLinks[] = [
                    'label' => __('messages.workspaces'),
                    'href' => route('workspaces.index'),
                    'active' => request()->routeIs('workspaces.*'),
                ];

                $primaryLinks[] = [
                    'label' => __('messages.wallet'),
                    'href' => route('wallet.index'),
                    'active' => request()->routeIs('wallet.*'),
                ];

                $primaryLinks[] = [
                    'label' => __('messages.marketplace'),
                    'href' => route('marketplace.index'),
                    'active' => request()->routeIs('marketplace.*'),
                ];

                // Subscription link for buyers
                if ($isBuyerOrAdmin) {
                    $primaryLinks[] = [
                        'label' => 'Subscriptions',
                        'href' => route('subscriptions.index'),
                        'active' => request()->routeIs('subscriptions.*'),
                    ];
                }

                $primaryLinks[] = [
                    'label' => 'Leaderboards',
                    'href' => route('leaderboard.index'),
                    'active' => request()->routeIs('leaderboard.*'),
                ];

                // Forum submenu
                $forumIsActive = request()->routeIs('forum.*');
                $forumSubmenu = [
                    [
                        'label' => __('messages.forum'),
                        'href' => route('forum.index'),
                        'active' =>
                            request()->routeIs('forum.index') ||
                            (request()->routeIs('forum.*') &&
                                !request()->routeIs('forum.analytics') &&
                                !request()->routeIs('forum.preferences.*')),
                    ],
                    [
                        'label' => __('messages.analytics'),
                        'href' => route('forum.analytics'),
                        'active' => request()->routeIs('forum.analytics'),
                    ],
                    [
                        'label' => __('messages.preferences'),
                        'href' => route('forum.preferences.edit'),
                        'active' => request()->routeIs('forum.preferences.*'),
                    ],
                ];

                $primaryLinks[] = [
                    'label' => __('messages.forum'),
                    'href' => route('forum.index'),
                    'active' => $forumIsActive,
                    'submenu' => $forumSubmenu,
                ];

                // Seller Tools submenu
                if ($isSellerOrAdmin) {
                    $sellerToolsSubmenu = [
                        [
                            'label' => __('messages.featured_notes'),
                            'href' => route('featured-notes.index'),
                            'active' => request()->routeIs('featured-notes.*'),
                        ],
                    ];
                    $sellerToolsIsActive = request()->routeIs('featured-notes.*');

                    $moreLinks[] = [
                        'label' => __('messages.seller_tools'),
                        'href' => route('featured-notes.index'),
                        'active' => $sellerToolsIsActive,
                        'submenu' => $sellerToolsSubmenu,
                    ];
                }

                // My Library submenu - available for Buyer, Seller, and Admin
                // Seller can also buy notes from other sellers, so they need library access
                if ($isBuyerOrAdmin || $isSellerOrAdmin) {
                    $buyerLibrarySubmenu = [
                        [
                            'label' => __('messages.collections'),
                            'href' => route('collections.index'),
                            'active' => request()->routeIs('collections.*'),
                        ],
                        [
                            'label' => __('messages.analytics'),
                            'href' => route('buyer-analytics.index'),
                            'active' => request()->routeIs('buyer-analytics.*'),
                        ],
                        [
                            'label' => __('messages.reading_history'),
                            'href' => route('reading-history.index'),
                            'active' => request()->routeIs('reading-history.*'),
                        ],
                        [
                            'label' => __('messages.batch_download'),
                            'href' => route('batch-download.index'),
                            'active' => request()->routeIs('batch-download.*'),
                        ],
                    ];
                    $buyerLibraryIsActive =
                        request()->routeIs('collections.*') ||
                        request()->routeIs('buyer-analytics.*') ||
                        request()->routeIs('reading-history.*') ||
                        request()->routeIs('batch-download.*');

                    $moreLinks[] = [
                        'label' => __('messages.my_library'),
                        'href' => route('collections.index'),
                        'active' => $buyerLibraryIsActive,
                        'submenu' => $buyerLibrarySubmenu,
                    ];
                }

                // Settings submenu
                $settingsSubmenu = [];
                // Subscription removed - all users have free access to all features
                // if (!$isAdmin) {
                //     $settingsSubmenu[] = [
                //         'label' => __('messages.subscription'),
                //         'href' => route('subscription.index'),
                //         'active' => request()->routeIs('subscription.*'),
                //     ];
                // }
                $settingsSubmenu[] = [
                    'label' => __('messages.referral'),
                    'href' => route('referral.index'),
                    'active' => request()->routeIs('referral.*'),
                ];
                $settingsSubmenu[] = [
                    'label' => 'Share Analytics',
                    'href' => route('share.analytics'),
                    'active' => request()->routeIs('share.analytics'),
                ];
                $settingsSubmenu[] = [
                    'label' => 'Share Leaderboard',
                    'href' => route('share.leaderboard'),
                    'active' => request()->routeIs('share.leaderboard'),
                ];
                $settingsSubmenu[] = [
                    'label' => 'Points & Rewards',
                    'href' => route('points.index'),
                    'active' => request()->routeIs('points.*'),
                ];
                $settingsIsActive =
                    request()->routeIs('referral.*') || request()->routeIs('share.*') || request()->routeIs('points.*');

                if (!empty($settingsSubmenu)) {
                    $moreLinks[] = [
                        'label' => 'Settings',
                        'href' => route('referral.index'),
                        'active' => $settingsIsActive,
                        'submenu' => $settingsSubmenu,
                    ];
                }

                // Product Chats
                $moreLinks[] = [
                    'label' => __('messages.produk_chats'),
                    'href' => route('note-conversations.index'),
                    'active' => request()->routeIs('note-conversations.*'),
                ];

                // Tools submenu
                $moreLinks[] = [
                    'label' => __('messages.simulators'),
                    'href' => route('simulators.index'),
                    'active' => request()->routeIs('simulators.*'),
                ];

                if ($user->hasRole('vendor') || $isAdmin) {
                    $moreLinks[] = [
                        'label' => __('messages.vendor'),
                        'href' => route('vendor.index'),
                        'active' => request()->routeIs('vendor.*'),
                    ];
                }

                if ($isAdmin) {
                    $adminMoreLinks[] = [
                        'label' => __('messages.admin'),
                        'href' => route('admin.dashboard'),
                        'active' => request()->routeIs('admin.*'),
                    ];

                    $adminMoreLinks[] = [
                        'label' => 'Forum Moderation',
                        'href' => route('admin.forum.moderation.index'),
                        'active' => request()->routeIs('admin.forum.moderation.*'),
                    ];

                    $adminMoreLinks[] = [
                        'label' => 'Note Moderation',
                        'href' => route('admin.notes.moderation.index'),
                        'active' => request()->routeIs('admin.notes.moderation.*'),
                    ];

                    $adminMoreLinks[] = [
                        'label' => 'Account Moderation',
                        'href' => route('admin.accounts.moderation.index'),
                        'active' => request()->routeIs('admin.accounts.moderation.*'),
                    ];

                    $adminMoreLinks[] = [
                        'label' => __('messages.system_health'),
                        'href' => route('admin.system-health.index'),
                        'active' => request()->routeIs('admin.system-health.*'),
                    ];
                }
            }
        }

        $moreIsActive = false;
        foreach (array_merge($moreLinks, $adminMoreLinks) as $link) {
            if ($link['active']) {
                $moreIsActive = true;
                break;
            }
        }
    @endphp
    <nav class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-40 transition-colors duration-200"
        x-cloak>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="/"
                        class="flex items-center gap-2 text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}" class="h-8 w-8">
                        <span class="hidden sm:inline">{{ config('app.name', 'Noteds') }}</span>
                    </a>

                    <!-- Desktop Navigation - Always Visible -->
                    <nav class="flex items-center gap-1 overflow-x-auto scrollbar-hide">
                        @foreach ($primaryLinks as $link)
                            @if (isset($link['submenu']) && !empty($link['submenu']))
                                <!-- Submenu Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <a href="{{ $link['href'] }}" @mouseenter="open = true" @mouseleave="open = false"
                                        class="{{ $desktopLinkClasses }} flex items-center gap-1 {{ $link['active'] ? $desktopActiveClasses : '' }}">
                                        {{ $link['label'] }}
                                        <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </a>
                                    <div x-show="open" x-cloak @mouseenter="open = true" @mouseleave="open = false"
                                        x-transition
                                        class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                                        @foreach ($link['submenu'] as $subLink)
                                            <a href="{{ $subLink['href'] }}"
                                                class="{{ $dropdownLinkClasses }} {{ $subLink['active'] ? $dropdownActiveClasses : '' }}">
                                                {{ $subLink['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <a href="{{ $link['href'] }}"
                                    class="{{ $desktopLinkClasses }} {{ $link['active'] ? $desktopActiveClasses : '' }}">
                                    {{ $link['label'] }}
                                </a>
                            @endif
                        @endforeach

                        @if ($user && $user->role !== 'user_workspaces' && (!empty($moreLinks) || !empty($adminMoreLinks)))
                            <!-- More Menu Dropdown -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="{{ $desktopLinkClasses }} flex items-center gap-1 {{ $moreIsActive ? $desktopActiveClasses : '' }}">
                                    More
                                    <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak @click.away="open = false" x-transition
                                    class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                                    @foreach ($moreLinks as $link)
                                        @if (isset($link['submenu']) && !empty($link['submenu']))
                                            <!-- Submenu in More dropdown -->
                                            <div
                                                class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100 mb-1">
                                                {{ $link['label'] }}
                                            </div>
                                            @foreach ($link['submenu'] as $subLink)
                                                <a href="{{ $subLink['href'] }}"
                                                    class="{{ $dropdownLinkClasses }} pl-6 {{ $subLink['active'] ? $dropdownActiveClasses : '' }}">
                                                    {{ $subLink['label'] }}
                                                </a>
                                            @endforeach
                                            @if (!$loop->last)
                                                <div class="border-t border-gray-100 my-1"></div>
                                            @endif
                                        @else
                                            <a href="{{ $link['href'] }}"
                                                class="{{ $dropdownLinkClasses }} {{ $link['active'] ? $dropdownActiveClasses : '' }}">
                                                {{ $link['label'] }}
                                            </a>
                                        @endif
                                    @endforeach

                                    @if (!empty($adminMoreLinks))
                                        @if (!empty($moreLinks))
                                            <div class="border-t border-gray-200 my-1"></div>
                                        @endif
                                        <div
                                            class="px-4 py-1 text-xs font-semibold text-gray-500 uppercase tracking-wide">
                                            Admin
                                        </div>
                                        @foreach ($adminMoreLinks as $link)
                                            <a href="{{ $link['href'] }}"
                                                class="{{ $dropdownLinkClasses }} {{ $link['active'] ? $dropdownActiveClasses : '' }}">
                                                {{ $link['label'] }}
                                            </a>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endif
                    </nav>
                </div>
                <div class="flex items-center gap-3 sm:gap-4">
                    <!-- Locale Switcher -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                            </svg>
                            <span class="hidden sm:inline">{{ strtoupper(app()->getLocale()) }}</span>
                        </button>
                        <div x-show="open" x-cloak @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                            <a href="{{ route('locale.switch', 'en') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === 'en' ? 'bg-blue-50 text-blue-600' : '' }}">
                                🇺🇸 English
                            </a>
                            <a href="{{ route('locale.switch', 'id') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === 'id' ? 'bg-blue-50 text-blue-600' : '' }}">
                                🇮🇩 Indonesia
                            </a>
                            <a href="{{ route('locale.switch', 'ar') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ app()->getLocale() === 'ar' ? 'bg-blue-50 text-blue-600' : '' }}">
                                🇸🇦 العربية
                            </a>
                        </div>
                    </div>

                    @auth
                        <div class="flex items-center gap-3">
                            <!-- Notifications Bell -->
                            <div class="relative" x-data="{
                                open: false,
                                init() {
                                    try {
                                        if (window.Echo && {{ auth()->check() ? 'true' : 'false' }}) {
                                            window.Echo.private('user.{{ auth()->id() }}')
                                                .listen('.NotificationCreated', (e) => {
                                                    // Optimistically bump badge and show subtle pulse
                                                    const badge = this.$root.querySelector('[data-notif-badge]');
                                                    if (badge) {
                                                        const val = parseInt(badge.innerText || '0', 10) || 0;
                                                        badge.innerText = val + 1;
                                                        badge.classList.add('animate-pulse');
                                                        setTimeout(() => badge.classList.remove('animate-pulse'), 1000);
                                                    }
                                                });
                                        }
                                    } catch (err) {}
                                }
                            }">
                                @php
                                    $unreadCount = auth()->user()->notifications()->unread()->count();
                                @endphp
                                <button @click="open = !open"
                                    class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                    </svg>
                                    @if ($unreadCount > 0)
                                        <span
                                            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full"
                                            data-notif-badge>{{ $unreadCount }}</span>
                                    @endif
                                </button>

                                <!-- Notifications Dropdown -->
                                <div x-show="open" x-cloak @click.away="open = false" x-transition
                                    class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50 max-h-96 overflow-y-auto">
                                    <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                        <h3 class="text-sm font-semibold text-gray-900">{{ __('messages.notifications') }}
                                        </h3>
                                        @if ($unreadCount > 0)
                                            <a href="{{ route('notifications.index') }}"
                                                class="text-xs text-blue-600 hover:text-blue-700">{{ __('messages.view_all') }}</a>
                                        @endif
                                    </div>
                                    @php
                                        // Cache notifications for 1 minute to reduce DB load
                                        $notifications = Cache::remember(
                                            'user_notifications_' . auth()->id(),
                                            now()->addMinute(),
                                            fn() => auth()->user()->notifications()->latest()->take(5)->get(),
                                        );
                                    @endphp
                                    <div id="notif-list">
                                        @forelse($notifications as $notification)
                                            @include('partials.notification-item', [
                                                'notification' => $notification,
                                            ])
                                        @empty
                                            <div class="px-4 py-8 text-center" data-notif-empty>
                                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                                </svg>
                                                <p class="mt-2 text-sm text-gray-600">
                                                    {{ __('messages.no_notifications_yet') }}</p>
                                            </div>
                                        @endforelse
                                    </div>
                                    <script>
                                        function prependNotificationItem(n) {
                                            try {
                                                const list = document.getElementById('notif-list');
                                                if (!list) return;
                                                const empty = list.querySelector('[data-notif-empty]');
                                                if (empty) empty.remove();
                                                const a = document.createElement('a');
                                                a.href = n.link || '#';
                                                a.className = 'block px-4 py-3 hover:bg-gray-50 transition-colors duration-150 bg-blue-50';
                                                a.innerHTML = `
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0">
                                                            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3 flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">${n.title || 'Notification'}</p>
                                                            <p class="text-xs text-gray-500 truncate">${n.message || ''}</p>
                                                            <p class="text-xs text-gray-400 mt-1">baru saja</p>
                                                        </div>
                                                        <div class="flex-shrink-0"><span class="w-2 h-2 bg-blue-600 rounded-full block"></span></div>
                                                    </div>`;
                                                list.prepend(a);
                                            } catch (e) {}
                                        }
                                        if (window.Echo && {{ auth()->check() ? 'true' : 'false' }}) {
                                            window.Echo.private('user.{{ auth()->id() }}')
                                                .listen('.NotificationCreated', (e) => prependNotificationItem(e));
                                        }
                                    </script>
                                </div>
                            </div>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-3 py-1.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                        @if (auth()->user()->avatar)
                                            @if (str_starts_with(auth()->user()->avatar, 'http'))
                                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                                    class="w-8 h-8 rounded-full object-cover" loading="lazy"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            @else
                                                @php
                                                    $avatarPath = auth()->user()->avatar;
                                                    $avatarPath = ltrim($avatarPath, '/');
                                                    $avatarPath = preg_replace('#^marketplace/#', '', $avatarPath);
                                                @endphp
                                                <img src="{{ asset('storage/' . $avatarPath) }}"
                                                    alt="{{ auth()->user()->name }}"
                                                    class="w-8 h-8 rounded-full object-cover" loading="lazy"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            @endif
                                        @endif
                                        <span class="text-xs font-semibold text-white"
                                            style="display: {{ auth()->user()->avatar ? 'none' : 'flex' }};">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" x-cloak @click.away="open = false" x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50 transition-colors duration-200">
                                    <a href="{{ route('public.profile.show', auth()->user()->username) }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 7a4 4 0 110 8 4 4 0 010-8zm0 10c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5z" />
                                            </svg>
                                            {{ __('messages.view_public_profile') }}
                                        </div>
                                    </a>
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ __('messages.my_profile') }}
                                        </div>
                                    </a>
                                    <a href="{{ route('notifications.preferences.index') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            Notification Preferences
                                        </div>
                                    </a>
                                    @if ($isBuyerOrAdmin)
                                        <a href="{{ route('subscriptions.my-subscription') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                    </svg>
                                                    Subscription
                                                </div>
                                                @if ($activeSubscription)
                                                    <span
                                                        class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Active
                                                    </span>
                                                @endif
                                            </div>
                                        </a>
                                    @endif
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="{{ route('support-tickets.create') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ __('messages.support_ticket') }}
                                        </div>
                                    </a>
                                    @if (auth()->user()->hasRole('admin'))
                                        <a href="/telescope" target="_blank"
                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                {{ __('messages.telescope') }}
                                                <svg class="w-3 h-3 ml-auto" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                </svg>
                                            </div>
                                        </a>
                                    @endif
                                    <!-- Dark Mode Toggle -->
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <button type="button" onclick="toggleDarkMode()"
                                        class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 dark:text-gray-300 dark:hover:bg-gray-700">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <svg id="dark-mode-icon" class="w-4 h-4 mr-2 hidden" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                                </svg>
                                                <svg id="light-mode-icon" class="w-4 h-4 mr-2" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                                <span id="dark-mode-text">Dark Mode</span>
                                            </div>
                                        </div>
                                    </button>
                                    <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left block px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors duration-150">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                                </svg>
                                                {{ __('messages.logout') }}
                                            </div>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors duration-200">
                            {{ __('messages.login') }}
                        </a>
                        <a href="{{ route('register') }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                            {{ __('messages.register') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</div>
