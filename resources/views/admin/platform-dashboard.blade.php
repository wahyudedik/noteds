@extends('40-shared/layouts/app')

@section('title', 'Platform Dashboard - Advanced Metrics')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8" x-data="dashboardData()" x-init="initDashboard()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header with Live Status -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Platform Dashboard</h1>
                    <p class="mt-2 text-sm text-gray-600">Real-time metrics and system health monitoring</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center space-x-2 bg-white px-4 py-2 rounded-lg shadow">
                        <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm text-gray-600">Live Data</span>
                    </div>
                    <span class="text-xs text-gray-500" x-text="`Updated: ${new Date().toLocaleTimeString()}`"></span>
                </div>
            </div>

            <!-- Health Metrics - Animated Cards -->
            <div class="mb-12">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M5.5 13a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.3A4.5 4.5 0 1113.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" />
                    </svg>
                    Health Metrics
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Total Users Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border-l-4 border-blue-500">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-600">Total Users</h3>
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($healthMetrics['total_users']) }}</p>
                        <p class="text-xs text-blue-600 mt-2 font-medium">
                            <span class="inline-block bg-blue-50 px-2 py-1 rounded">
                                {{ number_format($healthMetrics['active_users_today']) }} active today
                            </span>
                        </p>
                    </div>

                    <!-- Total Revenue Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border-l-4 border-green-500">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-600">Total Revenue</h3>
                            <div class="p-2 bg-green-100 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">
                            Rp {{ number_format($healthMetrics['total_revenue']) }}
                        </p>
                        <p class="text-xs text-green-600 mt-2 font-medium">
                            <span class="inline-block bg-green-50 px-2 py-1 rounded">
                                {{ number_format($healthMetrics['total_transactions']) }} transactions
                            </span>
                        </p>
                    </div>

                    <!-- Total Notes Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border-l-4 border-purple-500">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-600">Total Notes</h3>
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($healthMetrics['total_notes']) }}</p>
                        <p class="text-xs text-purple-600 mt-2 font-medium">
                            <span class="inline-block bg-purple-50 px-2 py-1 rounded">
                                {{ number_format($healthMetrics['published_notes']) }} published
                            </span>
                        </p>
                    </div>

                    <!-- Content Creators Card -->
                    <div
                        class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-medium text-gray-600">Creators</h3>
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-3xl font-bold text-gray-900">{{ number_format($healthMetrics['content_creators']) }}
                        </p>
                        <p class="text-xs text-yellow-600 mt-2 font-medium">
                            <span class="inline-block bg-yellow-50 px-2 py-1 rounded">
                                {{ number_format($healthMetrics['active_users_week']) }} active this week
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Business Metrics - KPI Cards -->
            <div class="mb-12">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    </svg>
                    Business KPIs
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Daily Signups Card -->
                    <div
                        class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl shadow-sm p-6 border border-indigo-100">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Daily Signups</h3>
                        <div class="flex items-baseline justify-between">
                            <p class="text-3xl font-bold text-indigo-900">
                                {{ number_format($businessMetrics['daily_signups']) }}</p>
                            @php
                                $signupDiff =
                                    $businessMetrics['daily_signups'] - $businessMetrics['daily_signups_yesterday'];
                                $signupPercent =
                                    $businessMetrics['daily_signups_yesterday'] > 0
                                        ? round(($signupDiff / $businessMetrics['daily_signups_yesterday']) * 100, 1)
                                        : 0;
                            @endphp
                            <div class="flex items-center space-x-1">
                                @if ($signupDiff > 0)
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-semibold text-green-600">+{{ $signupPercent }}%</span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $signupPercent }}%</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 mt-3">vs yesterday</p>
                    </div>

                    <!-- Daily GMV Card -->
                    <div
                        class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl shadow-sm p-6 border border-green-100">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Daily GMV</h3>
                        <div class="flex items-baseline justify-between">
                            <p class="text-3xl font-bold text-green-900">Rp
                                {{ number_format($businessMetrics['daily_gmv']) }}</p>
                            @php
                                $gmvDiff = $businessMetrics['daily_gmv'] - $businessMetrics['daily_gmv_yesterday'];
                                $gmvPercent =
                                    $businessMetrics['daily_gmv_yesterday'] > 0
                                        ? round(($gmvDiff / $businessMetrics['daily_gmv_yesterday']) * 100, 1)
                                        : 0;
                            @endphp
                            <div class="flex items-center space-x-1">
                                @if ($gmvDiff > 0)
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-sm font-semibold text-green-600">+{{ $gmvPercent }}%</span>
                                @else
                                    <span class="text-sm text-gray-500">{{ $gmvPercent }}%</span>
                                @endif
                            </div>
                        </div>
                        <p class="text-xs text-gray-600 mt-3">vs yesterday</p>
                    </div>

                    <!-- Avg Order Value Card -->
                    <div
                        class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl shadow-sm p-6 border border-orange-100">
                        <h3 class="text-sm font-medium text-gray-700 mb-4">Avg Order Value</h3>
                        <p class="text-3xl font-bold text-orange-900">Rp
                            {{ number_format($businessMetrics['avg_order_value']) }}</p>
                        <div class="mt-4 pt-4 border-t border-orange-200">
                            <p class="text-xs text-gray-600">Commission earned today</p>
                            <p class="text-lg font-semibold text-orange-700 mt-1">
                                Rp {{ number_format($businessMetrics['platform_commission_today']) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-12">
                <!-- User Growth Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">User Growth</h3>
                            <p class="text-sm text-gray-500 mt-1">Last 30 days</p>
                        </div>
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                        </div>
                    </div>
                    <canvas id="userGrowthChart" class="w-full" style="max-height: 300px;"></canvas>
                </div>

                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Revenue Breakdown</h3>
                            <p class="text-sm text-gray-500 mt-1">By payment method</p>
                        </div>
                        <div class="p-2 bg-green-100 rounded-lg">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <canvas id="revenueChart" class="w-full" style="max-height: 300px;"></canvas>
                </div>
            </div>
            <div class="mb-12">
                <h2 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                            clip-rule="evenodd" />
                    </svg>
                    System Status
                </h2>
                <div class="bg-white rounded-xl shadow-sm p-8 border border-gray-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Database Status -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                @if ($systemStatus['database_connection'])
                                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-green-100">
                                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-red-100">
                                        <svg class="h-6 w-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Database</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    @if ($systemStatus['database_connection'])
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Connected
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Connection Failed
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Cache Status -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                @if ($systemStatus['cache_status'])
                                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-green-100">
                                        <svg class="h-6 w-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @else
                                    <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-yellow-100">
                                        <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Cache</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    @if ($systemStatus['cache_status'])
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Operational
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Degraded
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Queue Status -->
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-blue-100">
                                    <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                        <path fill-rule="evenodd"
                                            d="M4 5a2 2 0 012-2 1 1 0 000-2H3a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V3a1 1 0 00-1-1h-2a1 1 0 000 2h1v11H4V5z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Job Queue</p>
                                <p class="text-sm text-gray-500 mt-1">
                                    <span
                                        class="font-medium text-gray-900">{{ $systemStatus['queue_status']['pending_jobs'] }}</span>
                                    pending •
                                    <span
                                        class="font-medium text-red-600">{{ $systemStatus['queue_status']['failed_jobs'] }}</span>
                                    failed
                                </p>
                            </div>
                        </div>

                        <!-- Storage Usage -->
                        <div class="flex items-start space-x-4 md:col-span-2 lg:col-span-1">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center h-12 w-12 rounded-lg bg-purple-100">
                                    <svg class="h-6 w-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Storage</p>
                                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-purple-600 h-2 rounded-full"
                                        style="width: {{ $systemStatus['storage_usage']['percentage'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $systemStatus['storage_usage']['used_readable'] }} /
                                    {{ $systemStatus['storage_usage']['percentage'] }}% used</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-4 mb-8">
                <a href="{{ route('admin.platform.export-metrics') }}"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export as CSV
                </a>
                <button @click="refreshData()"
                    class="inline-flex items-center px-6 py-3 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                    <svg class="w-5 h-5 mr-2 -ml-1" :class="{ 'animate-spin': loading }" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span x-text="loading ? 'Refreshing...' : 'Refresh'"></span>
                </button>
                <div class="flex-1"></div>
                <div class="text-sm text-gray-500">
                    Last updated: <span x-text="lastUpdated" class="font-medium text-gray-900"></span>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Chart.js Library -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

        <script>
            function dashboardData() {
                return {
                    loading: false,
                    lastUpdated: new Date().toLocaleTimeString(),

                    initDashboard() {
                        this.initUserGrowthChart();
                        this.initRevenueChart();

                        // Auto-refresh every 60 seconds
                        setInterval(() => this.refreshData(), 60000);
                    },

                    initUserGrowthChart() {
                        const userGrowthData = @json($userGrowth);
                        const ctx = document.getElementById('userGrowthChart');

                        if (!ctx) return;

                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: userGrowthData.map(d => new Date(d.date).toLocaleDateString('id-ID', {
                                    month: 'short',
                                    day: 'numeric'
                                })),
                                datasets: [{
                                    label: 'Cumulative Users',
                                    data: userGrowthData.map(d => d.total),
                                    borderColor: '#3B82F6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: '#3B82F6',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'top',
                                        labels: {
                                            font: {
                                                size: 12
                                            },
                                            padding: 15,
                                            usePointStyle: true
                                        }
                                    },
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        borderRadius: 8,
                                        titleFont: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        bodyFont: {
                                            size: 12
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                return 'Users: ' + context.parsed.y.toLocaleString('id-ID');
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: 'rgba(0,0,0,0.05)',
                                            drawBorder: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 11
                                            },
                                            callback: function(value) {
                                                return value.toLocaleString('id-ID');
                                            }
                                        }
                                    },
                                    x: {
                                        grid: {
                                            display: false,
                                            drawBorder: false
                                        },
                                        ticks: {
                                            font: {
                                                size: 11
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },

                    initRevenueChart() {
                        const revenueData = @json($revenueMetrics['payment_methods'] ?? []);
                        const ctx = document.getElementById('revenueChart');

                        if (!ctx || !revenueData || revenueData.length === 0) return;

                        const colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899'];

                        new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: revenueData.map(r => r.payment_method || 'Unknown'),
                                datasets: [{
                                    data: revenueData.map(r => r.total || 0),
                                    backgroundColor: colors.slice(0, revenueData.length),
                                    borderColor: '#fff',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            font: {
                                                size: 12
                                            },
                                            padding: 15,
                                            usePointStyle: true
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(0,0,0,0.8)',
                                        padding: 12,
                                        borderRadius: 8,
                                        titleFont: {
                                            size: 13,
                                            weight: 'bold'
                                        },
                                        bodyFont: {
                                            size: 12
                                        },
                                        callbacks: {
                                            label: function(context) {
                                                const value = context.parsed;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `Rp ${value.toLocaleString('id-ID')} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },

                    async refreshData() {
                        this.loading = true;
                        try {
                            const response = await fetch('{{ route('admin.platform.metrics') }}');
                            const data = await response.json();
                            this.lastUpdated = new Date().toLocaleTimeString();
                            // Reload page to show fresh data
                            setTimeout(() => location.reload(), 500);
                        } catch (error) {
                            console.error('Error refreshing data:', error);
                        } finally {
                            this.loading = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
