@extends('40-shared/layouts/app')

@section('title', 'Share Leaderboard - Earn Points')

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
                <p class="mt-2 text-base text-gray-600">Compete with other users and earn monthly rewards!</p>
            </div>

            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="{{ route('share.leaderboard', ['type' => 'monthly', 'month' => $month]) }}"
                            class="{{ $type === 'monthly' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Monthly Leaderboard
                        </a>
                        <a href="{{ route('share.leaderboard', ['type' => 'alltime']) }}"
                            class="{{ $type === 'alltime' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            All-Time Leaderboard
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Month Selector (for monthly view) -->
            @if ($type === 'monthly')
                <div class="mb-6">
                    <form method="GET" action="{{ route('share.leaderboard') }}" class="flex items-center gap-4">
                        <input type="hidden" name="type" value="monthly">
                        <label for="month" class="text-sm font-medium text-gray-700">Select Month:</label>
                        <input type="month" name="month" id="month" value="{{ $month }}"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="this.form.submit()">
                    </form>
                </div>
            @endif

            <!-- User Stats Card -->
            @auth
                <div class="mb-6 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Your Points</p>
                            <p class="text-3xl font-bold mt-1">{{ number_format($userPoints) }}</p>
                        </div>
                        <div class="text-right">
                            @if ($userRank)
                                <p class="text-sm font-medium opacity-90">Your Rank</p>
                                <p class="text-3xl font-bold mt-1">#{{ $userRank }}</p>
                            @else
                                <p class="text-sm font-medium opacity-90">Keep sharing to rank!</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endauth

            <!-- Leaderboard Table -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Points
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($leaderboard as $entry)
                                <tr
                                    class="{{ $entry['user'] && auth()->check() && $entry['user']->id === auth()->id() ? 'bg-blue-50' : 'hover:bg-gray-50' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($entry['rank'] <= 3)
                                                <span class="text-2xl mr-2">
                                                    @if ($entry['rank'] === 1)
                                                        🥇
                                                    @elseif($entry['rank'] === 2)
                                                        🥈
                                                    @elseif($entry['rank'] === 3)
                                                        🥉
                                                    @endif
                                                </span>
                                            @endif
                                            <span class="text-lg font-bold text-gray-900">#{{ $entry['rank'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($entry['user'])
                                            <div class="flex items-center">
                                                @if ($entry['user']->avatar)
                                                    <img class="h-10 w-10 rounded-full mr-3 object-cover"
                                                        src="{{ Storage::url($entry['user']->avatar) }}"
                                                        alt="{{ $entry['user']->name }}"
                                                        onerror="this.style.display='none'; this.nextElementSibling && (this.nextElementSibling.style.display='flex')">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 mr-3 flex items-center justify-center"
                                                        style="display:none">
                                                        <span
                                                            class="text-white font-semibold text-sm">{{ strtoupper(substr($entry['user']->name, 0, 1)) }}</span>
                                                    </div>
                                                @else
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 mr-3 flex items-center justify-center">
                                                        <span
                                                            class="text-white font-semibold text-sm">{{ strtoupper(substr($entry['user']->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('public.profile.show', $entry['user']->username) }}"
                                                            class="hover:text-blue-600">
                                                            {{ $entry['user']->name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">@ {{ $entry['user']->username }}
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Unknown User</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="text-lg font-bold text-gray-900">{{ number_format($entry['total_points']) }}</span>
                                        <span class="text-sm text-gray-500 ml-1">pts</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        No data available for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Section -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">How to Earn Points</h3>
                <ul class="space-y-2 text-blue-800">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Share a note:</strong> Earn {{ number_format($settings['share_points_per_share']) }}
                            points every time you share a note</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Get a click:</strong> Earn {{ number_format($settings['share_points_per_click']) }}
                            points when someone clicks your share link</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Generate a purchase:</strong> Earn
                            {{ number_format($settings['share_points_per_purchase']) }} points when someone buys through
                            your share link</span>
                    </li>
                </ul>
                @if ($settings['leaderboard_monthly_point_cap'] > 0)
                    <p class="mt-4 text-sm text-blue-700 bg-blue-100 p-2 rounded">
                        <strong>Monthly Cap:</strong> Maximum
                        {{ number_format($settings['leaderboard_monthly_point_cap']) }} points per month
                    </p>
                @endif
                @if ($settings['duplicate_share_prevention'])
                    <p class="mt-2 text-sm text-blue-700 bg-blue-100 p-2 rounded">
                        <strong>Duplicate Prevention:</strong> You can only earn points from sharing the same note once
                    </p>
                @endif
            </div>

            <!-- Monthly Rewards Info -->
            @if ($type === 'monthly')
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    @php
                        $currencyService = app(\App\Services\CurrencyService::class);
                        $userCurrency = $currencyService->getUserCurrency(auth()->user());
                        $reward1 = currency($settings['monthly_reward_rank_1'], $userCurrency, 'IDR');
                        $reward2 = currency($settings['monthly_reward_rank_2'], $userCurrency, 'IDR');
                        $reward3 = currency($settings['monthly_reward_rank_3'], $userCurrency, 'IDR');
                        $reward4_10 = currency($settings['monthly_reward_top_10'], $userCurrency, 'IDR');
                        $reward11_50 = currency($settings['monthly_reward_top_50'], $userCurrency, 'IDR');
                    @endphp
                    <h3 class="text-lg font-semibold text-yellow-900 mb-3">Monthly Rewards</h3>
                    <p class="text-yellow-800 mb-3">Top sharers receive monthly cash rewards:</p>
                    <ul class="space-y-2 text-yellow-800">
                        <li>🥇 <strong>Rank 1:</strong> {{ $reward1 }}</li>
                        <li>🥈 <strong>Rank 2:</strong> {{ $reward2 }}</li>
                        <li>🥉 <strong>Rank 3:</strong> {{ $reward3 }}</li>
                        <li>🏆 <strong>Rank 4-10:</strong> {{ $reward4_10 }}</li>
                        <li>⭐ <strong>Rank 11-50:</strong> {{ $reward11_50 }}</li>
                    </ul>
                    <p class="text-sm text-yellow-700 mt-4">Rewards are automatically distributed at the end of each month.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endsection

