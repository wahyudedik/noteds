@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('header', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('messages.total_users') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('messages.total_notes') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_notes'] }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ $stats['public_notes'] }} {{ __('messages.public') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('messages.platform_revenue') }}</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ currency($stats['total_revenue']) }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ __('messages.balance') }}:
                        {{ currency($platformBalance) }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('messages.pending_withdraws') }}</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_withdraws'] }}</div>
                    <a href="{{ route('admin.withdraws.index') }}"
                        class="text-xs text-blue-600 hover:underline">{{ __('messages.view_all') }}
                        →</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">{{ __('messages.pending_subs') }}</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['pending_subscriptions'] }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ $stats['active_subscriptions'] }}
                        {{ __('messages.active') }}</div>
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="text-xs text-blue-600 hover:underline">{{ __('messages.manage') }}
                        →</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.quick_links') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-900">{{ __('messages.users') }}</span>
                    </a>
                    @php
                        $pendingVerificationCount = \App\Models\User::whereNotNull('ktp_path')
                            ->whereNotNull('selfie_path')
                            ->where(function ($query) {
                                $query->where('verification_status', 'pending')->orWhereNull('verification_status');
                            })
                            ->whereDoesntHave('roles', function ($query) {
                                $query->where('name', 'admin');
                            })
                            ->count();
                    @endphp
                    <a href="{{ route('admin.users.pending-verification') }}"
                        class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors relative">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span
                            class="text-sm font-medium text-yellow-900">{{ __('messages.verification_pending_title') }}</span>
                        @if ($pendingVerificationCount > 0)
                            <span
                                class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full absolute top-0 right-0">
                                {{ $pendingVerificationCount }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.notes.index') }}"
                        class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-green-900">{{ __('messages.notes') }}</span>
                    </a>
                    <a href="{{ route('admin.exchange-rates.index') }}"
                        class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium text-yellow-900">{{ __('messages.exchange_rates') }}</span>
                    </a>
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span class="text-sm font-medium text-purple-900">{{ __('messages.subscriptions') }}</span>
                    </a>
                    <a href="{{ route('admin.withdraws.index') }}"
                        class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm font-medium text-red-900">{{ __('messages.withdraws') }}</span>
                    </a>
                    <a href="{{ route('admin.tickets.index') }}"
                        class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-indigo-900">{{ __('messages.support_tickets') }}</span>
                    </a>
                    <a href="{{ route('admin.featured-notes.index') }}"
                        class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <span class="text-sm font-medium text-orange-900">{{ __('messages.featured_notes') }}</span>
                    </a>
                    <a href="{{ route('admin.points-pricing.index') }}"
                        class="flex items-center p-4 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-pink-900">Points Pricing</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}"
                        class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">{{ __('messages.settings') }}</span>
                    </a>
                    <a href="{{ route('admin.documentations.index') }}"
                        class="flex items-center p-4 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-sm font-medium text-teal-900">{{ __('messages.documentations') }}</span>
                    </a>
                    <a href="{{ route('admin.cms-pages.index') }}"
                        class="flex items-center p-4 bg-sky-50 rounded-lg hover:bg-sky-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 4H8a2 2 0 00-2 2v14l6-3 6 3V6a2 2 0 00-2-2z" />
                        </svg>
                        <span class="text-sm font-medium text-sky-900">{{ __('messages.cms_pages') }}</span>
                    </a>
                    <a href="{{ route('admin.tutorials.index') }}"
                        class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-sm font-medium text-indigo-900">Tutorials</span>
                    </a>
                    <a href="{{ route('admin.workspaces.index') }}"
                        class="flex items-center p-4 bg-cyan-50 rounded-lg hover:bg-cyan-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-cyan-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span class="text-sm font-medium text-cyan-900">Workspaces</span>
                    </a>
                    <a href="{{ route('admin.view-history.index') }}"
                        class="flex items-center p-4 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-emerald-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span class="text-sm font-medium text-emerald-900">View History</span>
                    </a>
                    <a href="{{ route('admin.landing-page.index') }}"
                        class="flex items-center p-4 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span class="text-sm font-medium text-pink-900">{{ __('messages.landing_page') }}</span>
                    </a>
                    <a href="{{ route('admin.social-media.index') }}"
                        class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm font-medium text-orange-900">{{ __('messages.social_media') }}</span>
                    </a>
                    <a href="{{ route('admin.leaderboard-settings.index') }}"
                        class="flex items-center p-4 bg-violet-50 rounded-lg hover:bg-violet-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-violet-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span class="text-sm font-medium text-violet-900">{{ __('messages.leaderboard') }}</span>
                    </a>
                    <a href="{{ route('admin.share-settings.index') }}"
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-900">Share Analytics Settings</span>
                    </a>
                    <a href="{{ route('admin.affiliate.index') }}"
                        class="flex items-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 6a3 3 0 11-6 0 3 3 0 016 0zM16 12a4 4 0 11-8 0 4 4 0 018 0zM9 20H5v-2a3 3 0 015.856-1.487" />
                        </svg>
                        <span class="text-sm font-medium text-amber-900">Affiliate Management</span>
                    </a>
                    <a href="{{ route('admin.affiliate-settings.index') }}"
                        class="flex items-center p-4 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        <span class="text-sm font-medium text-rose-900">Affiliate Settings</span>
                    </a>
                </div>
            </div>

            <!-- Share Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.684 13.342C9.539 12.053 9.939 11.413 10.682 11.413c.743 0 1.143.64 1.998 1.927.859 1.287 1.259 1.927 2.002 1.927.743 0 1.143-.64 1.998-1.927M15 12a3 3 0 11-6 0 3 3 0 016 0zm6 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Share Analytics
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-cyan-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-cyan-600 uppercase">Total Shares</div>
                        <div class="text-xl font-bold text-cyan-900">
                            {{ number_format($shareStats['total_share_referrals']) }}
                        </div>
                    </div>
                    <div class="bg-sky-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-sky-600 uppercase">Share Clicks</div>
                        <div class="text-xl font-bold text-sky-900">{{ number_format($shareStats['total_share_clicks']) }}
                        </div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase">Purchases</div>
                        <div class="text-xl font-bold text-blue-900">
                            {{ number_format($shareStats['total_share_purchases']) }}</div>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-emerald-600 uppercase">Commission Earned</div>
                        <div class="text-xl font-bold text-emerald-900">
                            {{ currency($shareStats['total_share_commission_earned']) }}</div>
                    </div>
                    <div class="bg-teal-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-teal-600 uppercase">Revenue Generated</div>
                        <div class="text-xl font-bold text-teal-900">
                            {{ currency($shareStats['total_share_revenue_generated']) }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-indigo-600 uppercase">Referrals</div>
                        <div class="text-xl font-bold text-indigo-900">
                            {{ number_format($shareStats['total_share_referrals']) }}</div>
                    </div>
                </div>

                <!-- Share Commission Status -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-4 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-yellow-600 uppercase">Pending Commissions</div>
                                <div class="text-2xl font-bold text-yellow-900">
                                    {{ $shareCommissionStats['pending_commissions'] }}</div>
                                <div class="text-sm text-yellow-700 mt-1">
                                    {{ currency($shareCommissionStats['pending_amount']) }}</div>
                            </div>
                            <svg class="w-8 h-8 text-yellow-500 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4 border-l-4 border-green-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-green-600 uppercase">Paid Commissions</div>
                                <div class="text-2xl font-bold text-green-900">
                                    {{ $shareCommissionStats['paid_commissions'] }}</div>
                                <div class="text-sm text-green-700 mt-1">
                                    {{ currency($shareCommissionStats['paid_amount']) }}</div>
                            </div>
                            <svg class="w-8 h-8 text-green-500 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-4 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium text-purple-600 uppercase">Total Commissions</div>
                                <div class="text-2xl font-bold text-purple-900">
                                    {{ $shareCommissionStats['total_commissions'] }}</div>
                                <div class="text-sm text-purple-700 mt-1">
                                    {{ currency($shareCommissionStats['total_commission_amount']) }}</div>
                            </div>
                            <svg class="w-8 h-8 text-purple-500 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Top Shared Notes -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Top 10 Shared Notes</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Note</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Shares</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Commission
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($topSharedNotes as $index => $item)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 truncate">
                                                {{ Str::limit($item->note->title, 20) }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-600">{{ $item->share_count }}</td>
                                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">
                                                {{ currency($item->total_commission) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-center text-gray-500">No share data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top Share Earners -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">Top 10 Share Earners</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">User</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Shares</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Earnings</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($topShareEarners as $index => $item)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 truncate">
                                                <a href="{{ route('admin.users.show', $item->sharer) }}"
                                                    class="text-blue-600 hover:underline">
                                                    {{ $item->sharer->name }}
                                                </a>
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-600">{{ $item->share_count }}</td>
                                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">
                                                {{ currency($item->total_commission) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-center text-gray-500">No earner data
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Affiliate Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('affiliate.affiliate_stats') }}
                </h3>

                <!-- Affiliate Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-purple-600 uppercase">{{ __('affiliate.total_affiliates') }}
                        </div>
                        <div class="text-2xl font-bold text-purple-900">{{ $affiliateStats['total_affiliates'] }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-indigo-600 uppercase">{{ __('messages.active') }}
                            {{ __('affiliate.link') }}</div>
                        <div class="text-2xl font-bold text-indigo-900">{{ $affiliateStats['active_links'] }}</div>
                    </div>
                    <div class="bg-pink-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-pink-600 uppercase">{{ __('affiliate.total_conversions') }}
                        </div>
                        <div class="text-2xl font-bold text-pink-900">{{ $affiliateStats['total_conversions'] }}</div>
                    </div>
                    <div class="bg-rose-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-rose-600 uppercase">{{ __('affiliate.commissions') }}</div>
                        <div class="text-2xl font-bold text-rose-900">{{ currency($affiliateStats['total_commissions']) }}
                        </div>
                    </div>
                    <div class="bg-orange-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-orange-600 uppercase">{{ __('affiliate.pending') }}</div>
                        <div class="text-2xl font-bold text-orange-900">{{ currency($affiliateStats['pending_payouts']) }}
                        </div>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-emerald-600 uppercase">{{ __('affiliate.payout_completed') }}
                        </div>
                        <div class="text-2xl font-bold text-emerald-900">
                            {{ currency($affiliateStats['completed_payouts']) }}</div>
                    </div>
                </div>

                <!-- Top Affiliates & Pending Payouts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Top Affiliates -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('affiliate.top_affiliates') }}</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.user') }}</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.total_conversions') }}</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.commissions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($topAffiliates as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 truncate">
                                                <a href="{{ route('admin.users.show', $item['user']) }}"
                                                    class="text-blue-600 hover:underline">
                                                    {{ $item['user']->username }}
                                                </a>
                                            </td>
                                            <td class="px-3 py-2 text-right text-gray-600">{{ $item['conversions'] }}</td>
                                            <td class="px-3 py-2 text-right text-purple-600 font-medium">
                                                {{ currency($item['commission']) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-center text-gray-500">
                                                {{ __('affiliate.no_links') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pending Payouts -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('affiliate.recent_payouts') }}
                            ({{ __('affiliate.pending') }})</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.user') }}</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.amount') }}</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">
                                            {{ __('affiliate.method') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($pendingPayouts as $payout)
                                        <tr>
                                            <td class="px-3 py-2 text-gray-900 truncate">{{ $payout['affiliate_name'] }}
                                            </td>
                                            <td class="px-3 py-2 text-right text-emerald-600 font-medium">
                                                {{ currency($payout['amount']) }}</td>
                                            <td class="px-3 py-2 text-right text-gray-600 capitalize">
                                                {{ $payout['method'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-3 py-2 text-center text-gray-500">
                                                {{ __('affiliate.no_payouts') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('messages.wallet_analytics') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase">{{ __('messages.total_balance') }}</div>
                        <div class="text-xl font-bold text-blue-900">
                            {{ currency($walletStats['total_wallet_balance']) }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-green-600 uppercase">{{ __('messages.avg_balance') }}</div>
                        <div class="text-xl font-bold text-green-900">
                            {{ currency($walletStats['avg_wallet_balance']) }}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-purple-600 uppercase">
                            {{ __('messages.total_transactions') }}
                        </div>
                        <div class="text-xl font-bold text-purple-900">
                            {{ currency($walletStats['total_successful_transactions']) }}</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-red-600 uppercase">{{ __('messages.total_withdrawals') }}
                        </div>
                        <div class="text-xl font-bold text-red-900">
                            {{ currency($walletStats['total_withdrawals']) }}</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-yellow-600 uppercase">{{ __('messages.total_wallets') }}
                        </div>
                        <div class="text-xl font-bold text-yellow-900">{{ $walletStats['total_wallets'] }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-indigo-600 uppercase">{{ __('messages.with_balance') }}
                        </div>
                        <div class="text-xl font-bold text-indigo-900">{{ $walletStats['wallets_with_balance'] }}</div>
                    </div>
                </div>

                <!-- Top Wallets -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.top_10_wallets') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.rank') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.email') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.balance') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topWallets as $index => $user)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-green-600">
                                            {{ currency($user->wallet_balance) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_wallets_with_balance') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Referral Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    {{ __('messages.referral_analytics') }}
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-purple-600 uppercase">{{ __('messages.total_referrals') }}
                        </div>
                        <div class="text-xl font-bold text-purple-900">{{ $referralStats['total_referrals'] }}</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase">{{ __('messages.signup_rewards') }}
                        </div>
                        <div class="text-xl font-bold text-blue-900">
                            {{ currency($referralStats['total_signup_rewards']) }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-green-600 uppercase">
                            {{ __('messages.transaction_commissions') }}</div>
                        <div class="text-xl font-bold text-green-900">
                            {{ currency($referralStats['total_transaction_commission']) }}</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-yellow-600 uppercase">{{ __('messages.total_payout') }}
                        </div>
                        <div class="text-xl font-bold text-yellow-900">
                            {{ currency($referralStats['total_referral_payout']) }}</div>
                    </div>
                </div>

                <!-- Referral Leaderboard -->
                <div class="mt-6 mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.referral_leaderboard') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.rank') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.referrals') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.signup') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.transaction') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.total_commission') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($referralLeaderboard as $index => $refData)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $refData['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center">{{ $refData['total_referrals'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $refData['signup_count'] }} ({{ currency($refData['signup_total']) }})
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center text-green-600">
                                            {{ $refData['transaction_count'] }}
                                            ({{ currency($refData['transaction_total']) }})
                                        </td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-purple-600">
                                            {{ currency($refData['total_commission']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_referral_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Per-User Referral -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.detailed_referral_data') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.total_signups') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.signup_rewards_label') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.referred_buyers') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.transaction_rewards') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.total_commission') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($userReferralDetails as $refDetail)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $refDetail['user']->name }}<br><span
                                                class="text-xs text-gray-500">{{ $refDetail['user']->email }}</span></td>
                                        <td class="px-4 py-2 text-sm text-center">{{ $refDetail['total_signups'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $refDetail['signup_count'] }} ×
                                            {{ currency($refDetail['signup_total']) }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-purple-600">
                                            {{ $refDetail['referred_buyers_count'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-green-600">
                                            {{ $refDetail['transaction_count'] }} ×
                                            {{ currency($refDetail['transaction_total']) }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-purple-900">
                                            {{ currency($refDetail['total_commission']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_detailed_referral_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Note Creation Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('messages.note_creation_analytics') }}
                </h3>

                <!-- Daily Note Creation (Last 30 days) -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.daily_note_creation') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.date') }}</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.notes_created') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.unique_users') }}
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.avg_per_user') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dailyNotes as $day)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-center font-medium">{{ $day->count }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">{{ $day->unique_users }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center text-gray-500">
                                            {{ $day->unique_users > 0 ? number_format($day->count / $day->unique_users, 1) : 0 }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_note_creation_data') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Note Creators -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.top_10_note_creators') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.rank') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.email') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.total_notes_label') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topNoteCreators as $index => $user)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-indigo-600">
                                            {{ $user->notes_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_note_creators') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daily Notes Per User (Last 7 days) -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('messages.daily_notes_per_user') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.date') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.notes_created') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($userNoteActivity as $date => $activities)
                                    @foreach ($activities as $activity)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $activity->user->name }}<br><span
                                                    class="text-xs text-gray-500">{{ $activity->user->email }}</span>
                                            </td>
                                            <td class="px-4 py-2 text-sm font-bold text-right text-indigo-600">
                                                {{ $activity->note_count }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_note_activity') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('messages.revenue_analytics') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.date') }}</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.total_amount') }}
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.commission') }}
                                </th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.transactions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($revenueData as $day)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-right">
                                        {{ currency($day->total_amount) }}</td>
                                    <td class="px-4 py-2 text-sm font-bold text-right text-green-600">
                                        {{ currency($day->total_commission) }}</td>
                                    <td class="px-4 py-2 text-sm text-center text-blue-600">{{ $day->transaction_count }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                        {{ __('messages.no_revenue_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sale Mode Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Sale Mode Analytics
                </h3>

                <!-- Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <!-- Scarcity Mode -->
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-blue-800 uppercase">Scarcity Mode</span>
                            <span
                                class="bg-blue-100 text-blue-800 px-2 py-0.5 rounded text-xs font-medium">{{ $saleModeStats['scarcity_notes'] }}
                                Notes</span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transactions:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($saleModeStats['scarcity_transactions']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Revenue:</span>
                                <span
                                    class="font-semibold text-blue-900">{{ currency($saleModeStats['scarcity_revenue']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Amount:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ currency($saleModeStats['scarcity_total_amount']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Avg Price:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ currency($saleModeStats['scarcity_avg_price']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Standard Mode -->
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold text-green-800 uppercase">Standard Mode</span>
                            <span
                                class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs font-medium">{{ $saleModeStats['standard_notes'] }}
                                Notes</span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Transactions:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($saleModeStats['standard_transactions']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Revenue:</span>
                                <span
                                    class="font-semibold text-green-900">{{ currency($saleModeStats['standard_revenue']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Amount:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ currency($saleModeStats['standard_total_amount']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Avg Price:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ currency($saleModeStats['standard_avg_price']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Scarcity Features -->
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <div class="text-xs font-semibold text-purple-800 uppercase mb-2">Scarcity Features</div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Creator Commission:</span>
                                <span
                                    class="font-semibold text-purple-900">{{ currency($saleModeStats['scarcity_creator_commission']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Resales:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($saleModeStats['resale_count']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Resale Revenue:</span>
                                <span
                                    class="font-semibold text-purple-900">{{ currency($saleModeStats['resale_revenue']) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Repurchases:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($saleModeStats['repurchase_count']) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Comparison -->
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="text-xs font-semibold text-gray-800 uppercase mb-2">Comparison</div>
                        <div class="space-y-1 text-sm">
                            @php
                                $totalRevenue = $saleModeStats['scarcity_revenue'] + $saleModeStats['standard_revenue'];
                                $scarcityPercent =
                                    $totalRevenue > 0 ? ($saleModeStats['scarcity_revenue'] / $totalRevenue) * 100 : 0;
                                $standardPercent =
                                    $totalRevenue > 0 ? ($saleModeStats['standard_revenue'] / $totalRevenue) * 100 : 0;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Revenue:</span>
                                <span class="font-semibold text-gray-900">{{ currency($totalRevenue) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-blue-600">Scarcity:</span>
                                <span
                                    class="font-semibold text-blue-900">{{ number_format($scarcityPercent, 1) }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-green-600">Standard:</span>
                                <span
                                    class="font-semibold text-green-900">{{ number_format($standardPercent, 1) }}%</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-gray-300">
                                <span class="text-gray-600">Total Notes:</span>
                                <span
                                    class="font-semibold text-gray-900">{{ number_format($saleModeStats['total_with_sale_mode']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Detailed Report Link -->
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('admin.repurchase-report') }}"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        View Detailed Repurchase Report
                    </a>
                </div>
            </div>

            <!-- Top Sellers & Buyers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Sellers -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        {{ __('messages.top_10_sellers') }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.rank') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.sales') }}
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.revenue') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topSellers as $index => $seller)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $seller['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $seller['sales_count'] }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-green-600">
                                            {{ currency($seller['total_sales']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_sellers') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Buyers -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('messages.top_10_buyers') }}
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.rank') }}</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.purchases') }}
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.total_spent') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topBuyers as $index => $buyer)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $buyer['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-purple-600">
                                            {{ $buyer['purchase_count'] }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-red-600">
                                            {{ currency($buyer['total_spent']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                            {{ __('messages.no_buyers') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- User Growth -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('messages.user_growth') }}
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.date') }}</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                    {{ __('messages.new_users') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($userGrowth as $day)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2 text-sm text-center font-medium text-teal-600">
                                        {{ $day->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">
                                        {{ __('messages.no_user_growth_data') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Topup & Midtrans Statistics -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Topup Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.topup_statistics') }}</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.total_topups') }}</div>
                            <div class="text-2xl font-bold text-blue-600">{{ $topupStats['total_topups'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $topupStats['successful_topups'] }} {{ __('messages.successful') }}
                            </div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.total_topup_amount') }}</div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ currency($topupStats['total_topup_amount']) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ __('messages.all_time') }}
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.today') }}</div>
                            <div class="text-xl font-bold text-yellow-600">
                                {{ currency($topupStats['total_topup_today']) }}
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.this_month') }}</div>
                            <div class="text-xl font-bold text-purple-600">
                                {{ currency($topupStats['total_topup_this_month']) }}
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 text-xs text-gray-600">
                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                            {{ $topupStats['pending_topups'] }} {{ __('messages.pending') }}
                        </span>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                            {{ $topupStats['failed_topups'] }} {{ __('messages.failed') }}
                        </span>
                    </div>
                </div>

                <!-- Midtrans Statistics -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.midtrans_statistics') }}</h3>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.total_transactions') }}</div>
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ $midtransStats['total_midtrans_transactions'] }}</div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $midtransStats['successful_midtrans_transactions'] }} {{ __('messages.successful') }}
                            </div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.total_midtrans_amount') }}
                            </div>
                            <div class="text-2xl font-bold text-green-600">
                                {{ currency($midtransStats['total_midtrans_amount']) }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ __('messages.all_time') }}
                            </div>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.today') }}</div>
                            <div class="text-xl font-bold text-yellow-600">
                                {{ currency($midtransStats['total_midtrans_today']) }}
                            </div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm font-medium text-gray-600">{{ __('messages.this_month') }}</div>
                            <div class="text-xl font-bold text-purple-600">
                                {{ currency($midtransStats['total_midtrans_this_month']) }}
                            </div>
                        </div>
                    </div>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="text-sm font-medium text-gray-600">{{ __('messages.total_commission') }}</div>
                        <div class="text-xl font-bold text-blue-600">
                            {{ currency($midtransStats['total_midtrans_commission']) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ __('messages.from_midtrans_transactions') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Topup History -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.topup_history') }}</h3>
                @if ($topupHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.order_id') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($topupHistory as $topup)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $topup->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $topup->buyer->name }}
                                            <div class="text-xs text-gray-500">{{ $topup->buyer->email }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                            {{ $topup->midtrans_order_id ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-green-600">
                                            {{ currency($topup->amount) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($topup->status === 'success')
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.success') }}</span>
                                            @elseif($topup->status === 'pending')
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                            @else
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.failed') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-600 text-center py-4">{{ __('messages.no_topup_history') }}</p>
                @endif
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_transactions') }}</h3>
                @if ($recentTransactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.buyer') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.seller') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.commission') }}
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->buyer->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->seller->name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            {{ currency($transaction->amount, null, $transaction->currency ?? config('currency.base_currency')) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-green-600">
                                            {{ currency($transaction->commission, null, $transaction->currency ?? config('currency.base_currency')) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($transaction->status === 'success')
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.success') }}</span>
                                            @elseif($transaction->status === 'pending')
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                            @else
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.failed') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.transactions.index') }}"
                            class="text-blue-600 hover:text-blue-800 text-sm">{{ __('messages.view_all_transactions') }}
                            →</a>
                    </div>
                @else
                    <p class="text-gray-600 text-center py-4">{{ __('messages.no_transactions') }}</p>
                @endif
            </div>

            <!-- Recent Withdraws -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_withdraws') }}</h3>
                @if ($recentWithdraws->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.date') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.bank') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentWithdraws as $withdraw)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $withdraw->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $withdraw->user->name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">
                                            {{ currency($withdraw->amount) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $withdraw->bank_name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($withdraw->status === 'approved')
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.approved') }}</span>
                                            @elseif($withdraw->status === 'rejected')
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.rejected') }}</span>
                                            @else
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('admin.withdraws.show', $withdraw) }}"
                                                class="text-blue-600 hover:text-blue-800">{{ __('messages.view') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.withdraws.index') }}"
                            class="text-blue-600 hover:text-blue-800 text-sm">{{ __('messages.view_all_withdraws') }}
                            →</a>
                    </div>
                @else
                    <p class="text-gray-600 text-center py-4">{{ __('messages.no_withdraws') }}</p>
                @endif
            </div>
        </div>
    </div>
@endsection
