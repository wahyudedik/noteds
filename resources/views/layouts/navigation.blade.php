<div x-data="{ mobileMenuOpen: false }" class="relative">
    <nav class="bg-white border-b border-gray-200 shadow-sm sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="/"
                        class="flex items-center gap-2 text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}" class="h-8 w-8">
                        <span class="hidden sm:inline">{{ config('app.name', 'Noteds') }}</span>
                    </a>

                    <!-- Desktop Navigation -->
                    <nav class="hidden lg:flex items-center gap-1">
                        <a href="/"
                            class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('welcome') ? 'text-blue-600 bg-blue-50' : '' }}">
                            {{ __('messages.home') }}
                        </a>
                        @auth
                            @if (auth()->user()->role === 'user_workspaces')
                                <a href="{{ route('workspaces.index') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('workspaces.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    Workspaces
                                </a>
                            @else
                                <a href="{{ route('dashboard') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    {{ __('messages.dashboard') }}
                                </a>
                                @if (auth()->user()->role === 'seller' || auth()->user()->hasRole('admin'))
                                    <a href="{{ route('notes.index') }}"
                                        class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('notes.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                        {{ __('messages.notes') }}
                                    </a>
                                @endif
                                <a href="{{ route('wallet.index') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('wallet.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    {{ __('messages.wallet') }}
                                </a>
                                <a href="{{ route('marketplace.index') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('marketplace.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    {{ __('messages.marketplace') }}
                                </a>
                                <a href="{{ route('forum.index') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ (request()->routeIs('forum.*') && !request()->routeIs('forum.analytics')) ? 'text-blue-600 bg-blue-50' : '' }}">
                                    Forum
                                </a>
                                <a href="{{ route('forum.analytics') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('forum.analytics') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    Forum Analytics
                                </a>
                                <a href="{{ route('forum.preferences.edit') }}"
                                    class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('forum.preferences.*') ? 'text-blue-600 bg-blue-50' : '' }}">
                                    Forum Preferences
                                </a>

                                <!-- More Menu Dropdown -->
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open"
                                        class="px-3 py-2 text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 flex items-center gap-1 {{ request()->routeIs(['featured-notes.*', 'subscription.*', 'referral.*', 'mynoteds.*', 'collections.*', 'buyer-analytics.*', 'reading-history.*', 'batch-download.*', 'simulators.*']) ? 'text-blue-600 bg-blue-50' : '' }}">
                                        More
                                        <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-transition
                                        class="absolute left-0 mt-1 w-56 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                                        @if (auth()->user()->role === 'seller' || auth()->user()->hasRole('admin'))
                                            <a href="{{ route('featured-notes.index') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('featured-notes.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                Featured
                                            </a>
                                        @endif
                                        @if (!auth()->user()->hasRole('admin'))
                                            <a href="{{ route('subscription.index') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('subscription.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                {{ __('messages.subscription') }}
                                            </a>
                                        @endif
                                        <a href="{{ route('referral.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('referral.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                            {{ __('messages.referral') }}
                                        </a>
                                        @if (auth()->user()->hasPremium())
                                            <a href="{{ route('mynoteds.index') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('mynoteds.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                {{ __('messages.mynoteds') }}
                                            </a>
                                            @if (auth()->user()->role === 'buyer' || auth()->user()->hasRole('admin'))
                                                <a href="{{ route('collections.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('collections.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                    Collections
                                                </a>
                                                <a href="{{ route('buyer-analytics.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('buyer-analytics.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                    Analytics
                                                </a>
                                                <a href="{{ route('reading-history.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('reading-history.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                    Reading History
                                                </a>
                                                <a href="{{ route('batch-download.index') }}"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('batch-download.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                    Batch Download
                                                </a>
                                            @endif
                                        @endif
                                        <a href="{{ route('simulators.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('simulators.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                            {{ __('messages.simulators') }}
                                        </a>
                                        @if (auth()->user()->hasRole('admin'))
                                            <div class="border-t border-gray-200 my-1"></div>
                                            <a href="{{ route('admin.dashboard') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('admin.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                {{ __('messages.admin') }}
                                            </a>
                                            <a href="{{ route('admin.forum.moderation.index') }}"
                                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 {{ request()->routeIs('admin.forum.moderation.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                                Forum Moderation
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endauth
                    </nav>

                    <!-- Mobile Menu Button -->
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                        class="lg:hidden p-2 text-gray-700 hover:text-blue-600 hover:bg-gray-100 rounded-md transition-colors duration-200">
                        <svg x-show="!mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
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
                        <div x-show="open" @click.away="open = false" x-transition
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
                            <div class="relative" x-data="{ open: false }">
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
                                            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full">{{ $unreadCount }}</span>
                                    @endif
                                </button>

                                <!-- Notifications Dropdown -->
                                <div x-show="open" @click.away="open = false" x-transition
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
                                        $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                                    @endphp
                                    @forelse($notifications as $notification)
                                        <a href="{{ $notification->link ?? '#' }}"
                                            class="block px-4 py-3 hover:bg-gray-50 transition-colors duration-150 {{ !$notification->is_read ? 'bg-blue-50' : '' }}">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    @if ($notification->type === 'purchase')
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-green-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </div>
                                                    @elseif($notification->type === 'sale')
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-blue-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($notification->type === 'review')
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-yellow-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($notification->type === 'ticket_response')
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-purple-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                        </div>
                                                    @elseif($notification->type === 'subscription')
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-indigo-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    @else
                                                        <div
                                                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                                            <svg class="w-5 h-5 text-gray-600" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-3 flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ $notification->title }}</p>
                                                    <p class="text-xs text-gray-500 truncate">{{ $notification->message }}
                                                    </p>
                                                    <p class="text-xs text-gray-400 mt-1">
                                                        {{ $notification->created_at->diffForHumans() }}</p>
                                                </div>
                                                @if (!$notification->is_read)
                                                    <div class="flex-shrink-0">
                                                        <span class="w-2 h-2 bg-blue-600 rounded-full block"></span>
                                                    </div>
                                                @endif
                                            </div>
                                        </a>
                                    @empty
                                        <div class="px-4 py-8 text-center">
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
                            </div>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open"
                                    class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md px-3 py-1.5">
                                    <div
                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                        @if (auth()->user()->avatar)
                                            <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}"
                                                class="w-8 h-8 rounded-full object-cover">
                                        @else
                                            <span
                                                class="text-xs font-semibold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <span class="hidden sm:inline">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            {{ __('messages.my_profile') }}
                                        </div>
                                    </a>
                                    @if (!auth()->user()->hasRole('admin'))
                                        <a href="{{ route('subscription.index') }}"
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                                {{ __('messages.subscription') }}
                                            </div>
                                        </a>
                                    @endif
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <a href="{{ route('support-tickets.create') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
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
                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
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
                                    <div class="border-t border-gray-200 my-1"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left block px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
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

    <!-- Mobile Menu -->
    <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1" @click.away="mobileMenuOpen = false"
        class="lg:hidden absolute top-full left-0 right-0 bg-white border-b border-gray-200 shadow-lg z-50 max-h-[calc(100vh-4rem)] overflow-y-auto">
        <div class="px-4 py-4 space-y-1">
            <a href="/"
                class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('welcome') ? 'text-blue-600 bg-blue-50' : '' }}"
                @click="mobileMenuOpen = false">
                {{ __('messages.home') }}
            </a>
            @auth
                @if (auth()->user()->role === 'user_workspaces')
                    <a href="{{ route('workspaces.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('workspaces.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        Workspaces
                    </a>
                @else
                    <a href="{{ route('dashboard') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('dashboard') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        {{ __('messages.dashboard') }}
                    </a>
                    @if (auth()->user()->role === 'seller' || auth()->user()->hasRole('admin'))
                        <a href="{{ route('notes.index') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('notes.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            {{ __('messages.notes') }}
                        </a>
                    @endif
                    <a href="{{ route('wallet.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('wallet.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        {{ __('messages.wallet') }}
                    </a>
                    <a href="{{ route('marketplace.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('marketplace.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        {{ __('messages.marketplace') }}
                    </a>
                    <a href="{{ route('forum.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ (request()->routeIs('forum.*') && !request()->routeIs('forum.analytics')) ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        Forum
                    </a>
                    <a href="{{ route('forum.analytics') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('forum.analytics') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        Forum Analytics
                    </a>
                    <a href="{{ route('forum.preferences.edit') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('forum.preferences.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        Forum Preferences
                    </a>

                    <div class="border-t border-gray-200 my-2"></div>
                    <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">More</div>

                    @if (auth()->user()->role === 'seller' || auth()->user()->hasRole('admin'))
                        <a href="{{ route('featured-notes.index') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('featured-notes.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            Featured
                        </a>
                    @endif
                    @if (!auth()->user()->hasRole('admin'))
                        <a href="{{ route('subscription.index') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('subscription.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            {{ __('messages.subscription') }}
                        </a>
                    @endif
                    <a href="{{ route('referral.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('referral.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        {{ __('messages.referral') }}
                    </a>
                    @if (auth()->user()->hasPremium())
                        <a href="{{ route('mynoteds.index') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('mynoteds.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            {{ __('messages.mynoteds') }}
                        </a>
                        @if (auth()->user()->role === 'buyer' || auth()->user()->hasRole('admin'))
                            <a href="{{ route('collections.index') }}"
                                class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('collections.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                                @click="mobileMenuOpen = false">
                                Collections
                            </a>
                            <a href="{{ route('buyer-analytics.index') }}"
                                class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('buyer-analytics.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                                @click="mobileMenuOpen = false">
                                Analytics
                            </a>
                        @endif
                    @endif
                    <a href="{{ route('simulators.index') }}"
                        class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('simulators.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                        @click="mobileMenuOpen = false">
                        {{ __('messages.simulators') }}
                    </a>
                    @if (auth()->user()->hasRole('admin'))
                        <div class="border-t border-gray-200 my-2"></div>
                        <a href="{{ route('admin.dashboard') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('admin.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            {{ __('messages.admin') }}
                        </a>
                        <a href="{{ route('admin.forum.moderation.index') }}"
                            class="block px-3 py-2 text-base font-medium text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-all duration-200 {{ request()->routeIs('admin.forum.moderation.*') ? 'text-blue-600 bg-blue-50' : '' }}"
                            @click="mobileMenuOpen = false">
                            Forum Moderation
                        </a>
                    @endif
                @endif
            @endauth
        </div>
    </div>
</div>
