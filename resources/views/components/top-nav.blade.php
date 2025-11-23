@php
    $user = auth()->user();
@endphp

<div x-data="{ mobileMenuOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm sticky top-0 z-40">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <!-- Mobile sidebar toggle button -->
            <button 
                onclick="document.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                class="lg:hidden p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Desktop: Page Title or Logo -->
            <div class="flex-1 lg:flex lg:items-center lg:justify-center min-w-0">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                    @yield('page-title', config('app.name', 'Noteds'))
                </h1>
            </div>

            <!-- Right side actions -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Locale Switcher -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        <span class="hidden sm:inline">{{ strtoupper(app()->getLocale()) }}</span>
                    </button>
                    <div x-show="open" x-cloak @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-32 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50">
                        <a href="{{ route('locale.switch', 'en') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'en' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
                            🇺🇸 English
                        </a>
                        <a href="{{ route('locale.switch', 'id') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'id' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
                            🇮🇩 Indonesia
                        </a>
                        <a href="{{ route('locale.switch', 'ar') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 {{ app()->getLocale() === 'ar' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400' : '' }}">
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
                                class="relative p-2 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                @if ($unreadCount > 0)
                                    <span
                                        class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full" data-notif-badge>{{ $unreadCount }}</span>
                                @endif
                            </button>

                            <!-- Notifications Dropdown -->
                            <div x-show="open" x-cloak @click.away="open = false" x-transition
                                class="absolute right-0 mt-2 w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-2 z-50 max-h-96 overflow-y-auto">
                                <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.notifications') }}
                                    </h3>
                                    @if ($unreadCount > 0)
                                        <a href="{{ route('notifications.index') }}"
                                            class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">{{ __('messages.view_all') }}</a>
                                    @endif
                                </div>
                                @php
                                    $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                                @endphp
                                <div id="notif-list">
                                    @forelse($notifications as $notification)
                                        @include('partials.notification-item', ['notification' => $notification])
                                    @empty
                                        <div class="px-4 py-8 text-center" data-notif-empty>
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
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
                                            a.className = 'block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 bg-blue-50 dark:bg-blue-900/20';
                                            a.innerHTML = `
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${n.title || 'Notification'}</p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">${n.message || ''}</p>
                                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">baru saja</p>
                                                    </div>
                                                    <div class="flex-shrink-0"><span class="w-2 h-2 bg-blue-600 rounded-full block"></span></div>
                                                </div>`;
                                            list.prepend(a);
                                        } catch(e) {}
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
                                class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-3 py-1.5">
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                    @if (auth()->user()->avatar)
                                        @if (str_starts_with(auth()->user()->avatar, 'http'))
                                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                                class="w-8 h-8 rounded-full object-cover"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        @else
                                            @php
                                                $avatarPath = auth()->user()->avatar;
                                                $avatarPath = ltrim($avatarPath, '/');
                                                $avatarPath = preg_replace('#^marketplace/#', '', $avatarPath);
                                            @endphp
                                            <img src="{{ asset('storage/' . $avatarPath) }}" alt="{{ auth()->user()->name }}"
                                                class="w-8 h-8 rounded-full object-cover"
                                                loading="lazy"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        @endif
                                    @endif
                                    <span
                                        class="text-xs font-semibold text-white" style="display: {{ auth()->user()->avatar ? 'none' : 'flex' }};">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
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
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <button type="button" onclick="toggleDarkMode()"
                                    class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 dark:text-gray-300 dark:hover:bg-gray-700">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <svg id="dark-mode-icon" class="w-4 h-4 mr-2 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                            </svg>
                                            <svg id="light-mode-icon" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
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
                        class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors duration-200">
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

</div>

