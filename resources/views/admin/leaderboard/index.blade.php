@extends('40-shared/layouts/app')

@section('title', __('Admin Leaderboard'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Leaderboard') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Track top performers across your platform') }}</p>
            </div>

            <!-- Leaderboards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Sellers by Revenue -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">{{ __('Top Sellers by Revenue') }}</h2>
                        <p class="text-green-100 text-sm mt-1">{{ __('All-time ranking') }}</p>
                    </div>

                    <div class="p-6">
                        @php
                            $topSellersByRevenue = collect($topSellersByRevenue ?? []);
                        @endphp
                        @if ($topSellersByRevenue->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topSellersByRevenue as $index => $seller)
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                                        <!-- Rank Badge -->
                                        <div
                                            class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full font-bold text-sm
                                            @if ($index === 0) bg-yellow-100 text-yellow-800
                                            @elseif ($index === 1) bg-gray-300 text-gray-800
                                            @elseif ($index === 2) bg-amber-100 text-amber-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($index === 0)
                                                🥇
                                            @elseif ($index === 1)
                                                🥈
                                            @elseif ($index === 2)
                                                🥉
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>

                                        <!-- User Info -->
                                        <div class="flex-1 min-w-0">
                                            @php
                                                $avatar = is_array($seller)
                                                    ? $seller['avatar'] ?? null
                                                    : $seller->avatar ?? null;
                                                $name = is_array($seller) ? $seller['name'] ?? '' : $seller->name ?? '';
                                                $username = is_array($seller)
                                                    ? $seller['username'] ?? ''
                                                    : $seller->username ?? '';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                @if ($avatar)
                                                    <img src="{{ $avatar }}" alt="{{ $name }}"
                                                        class="w-8 h-8 rounded-full">
                                                @else
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600">
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate">{{ $name }}</p>
                                                    <p class="text-xs text-gray-500">@{{ $username }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Revenue -->
                                        <div class="flex-shrink-0 text-right">
                                            @php
                                                $revenue = is_array($seller)
                                                    ? $seller['total_revenue'] ?? 0
                                                    : $seller->total_revenue ?? 0;
                                            @endphp
                                            <p class="text-lg font-bold text-green-600">{{ currency($revenue) }}</p>
                                            <p class="text-xs text-gray-500">{{ __('revenue') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">{{ __('No data available') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Top Sellers by Ratings -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">{{ __('Top Sellers by Ratings') }}</h2>
                        <p class="text-blue-100 text-sm mt-1">{{ __('All-time ranking (5+ reviews)') }}</p>
                    </div>

                    <div class="p-6">
                        @php
                            $topSellersByRatings = collect($topSellersByRatings ?? []);
                        @endphp
                        @if ($topSellersByRatings->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topSellersByRatings as $index => $seller)
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                                        <!-- Rank Badge -->
                                        <div
                                            class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full font-bold text-sm
                                            @if ($index === 0) bg-yellow-100 text-yellow-800
                                            @elseif ($index === 1) bg-gray-300 text-gray-800
                                            @elseif ($index === 2) bg-amber-100 text-amber-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($index === 0)
                                                🥇
                                            @elseif ($index === 1)
                                                🥈
                                            @elseif ($index === 2)
                                                🥉
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>

                                        <!-- User Info -->
                                        <div class="flex-1 min-w-0">
                                            @php
                                                $avatar = is_array($seller)
                                                    ? $seller['avatar'] ?? null
                                                    : $seller->avatar ?? null;
                                                $name = is_array($seller) ? $seller['name'] ?? '' : $seller->name ?? '';
                                                $username = is_array($seller)
                                                    ? $seller['username'] ?? ''
                                                    : $seller->username ?? '';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                @if ($avatar)
                                                    <img src="{{ $avatar }}" alt="{{ $name }}"
                                                        class="w-8 h-8 rounded-full">
                                                @else
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600">
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate">{{ $name }}</p>
                                                    <p class="text-xs text-gray-500">@{{ $username }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Rating -->
                                        <div class="flex-shrink-0 text-right">
                                            @php
                                                $rating = is_array($seller)
                                                    ? $seller['average_rating'] ?? 0
                                                    : $seller->average_rating ?? 0;
                                                $reviewCount = is_array($seller)
                                                    ? $seller['review_count'] ?? 0
                                                    : $seller->review_count ?? 0;
                                            @endphp
                                            <div class="flex items-center gap-1 justify-end mb-1">
                                                @for ($i = 0; $i < 5; $i++)
                                                    <svg class="w-3 h-3 @if ($i < floor($rating)) text-yellow-400 @else text-gray-300 @endif"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            <p class="text-sm font-bold text-gray-900">{{ number_format($rating, 1) }}</p>
                                            <p class="text-xs text-gray-500">{{ $reviewCount }} {{ __('reviews') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">{{ __('No data available') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Top Buyers by Spending -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">{{ __('Top Buyers by Spending') }}</h2>
                        <p class="text-purple-100 text-sm mt-1">{{ __('All-time ranking') }}</p>
                    </div>

                    <div class="p-6">
                        @php
                            $topBuyersBySpending = collect($topBuyersBySpending ?? []);
                        @endphp
                        @if ($topBuyersBySpending->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topBuyersBySpending as $index => $buyer)
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                                        <!-- Rank Badge -->
                                        <div
                                            class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full font-bold text-sm
                                            @if ($index === 0) bg-yellow-100 text-yellow-800
                                            @elseif ($index === 1) bg-gray-300 text-gray-800
                                            @elseif ($index === 2) bg-amber-100 text-amber-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($index === 0)
                                                🥇
                                            @elseif ($index === 1)
                                                🥈
                                            @elseif ($index === 2)
                                                🥉
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>

                                        <!-- User Info -->
                                        <div class="flex-1 min-w-0">
                                            @php
                                                $avatar = is_array($buyer)
                                                    ? $buyer['avatar'] ?? null
                                                    : $buyer->avatar ?? null;
                                                $name = is_array($buyer) ? $buyer['name'] ?? '' : $buyer->name ?? '';
                                                $username = is_array($buyer)
                                                    ? $buyer['username'] ?? ''
                                                    : $buyer->username ?? '';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                @if ($avatar)
                                                    <img src="{{ $avatar }}" alt="{{ $name }}"
                                                        class="w-8 h-8 rounded-full">
                                                @else
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-400 to-purple-600">
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate">{{ $name }}</p>
                                                    <p class="text-xs text-gray-500">@{{ $username }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Spending -->
                                        <div class="flex-shrink-0 text-right">
                                            @php
                                                $spending = is_array($buyer)
                                                    ? $buyer['total_spending'] ?? 0
                                                    : $buyer->total_spending ?? 0;
                                            @endphp
                                            <p class="text-lg font-bold text-purple-600">{{ currency($spending) }}</p>
                                            <p class="text-xs text-gray-500">{{ __('spent') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">{{ __('No data available') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Top Reviewers -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4">
                        <h2 class="text-2xl font-bold text-white">{{ __('Top Reviewers') }}</h2>
                        <p class="text-pink-100 text-sm mt-1">{{ __('All-time ranking') }}</p>
                    </div>

                    <div class="p-6">
                        @php
                            $topContributorsByReviews = collect($topContributorsByReviews ?? []);
                        @endphp
                        @if ($topContributorsByReviews->count() > 0)
                            <div class="space-y-3">
                                @foreach ($topContributorsByReviews as $index => $contributor)
                                    <div
                                        class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition border border-gray-100">
                                        <!-- Rank Badge -->
                                        <div
                                            class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-full font-bold text-sm
                                            @if ($index === 0) bg-yellow-100 text-yellow-800
                                            @elseif ($index === 1) bg-gray-300 text-gray-800
                                            @elseif ($index === 2) bg-amber-100 text-amber-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @if ($index === 0)
                                                🥇
                                            @elseif ($index === 1)
                                                🥈
                                            @elseif ($index === 2)
                                                🥉
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>

                                        <!-- User Info -->
                                        <div class="flex-1 min-w-0">
                                            @php
                                                $avatar = is_array($contributor)
                                                    ? $contributor['avatar'] ?? null
                                                    : $contributor->avatar ?? null;
                                                $name = is_array($contributor)
                                                    ? $contributor['name'] ?? ''
                                                    : $contributor->name ?? '';
                                                $username = is_array($contributor)
                                                    ? $contributor['username'] ?? ''
                                                    : $contributor->username ?? '';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                @if ($avatar)
                                                    <img src="{{ $avatar }}" alt="{{ $name }}"
                                                        class="w-8 h-8 rounded-full">
                                                @else
                                                    <div
                                                        class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-400 to-pink-600">
                                                    </div>
                                                @endif
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-gray-900 truncate">{{ $name }}</p>
                                                    <p class="text-xs text-gray-500">@{{ $username }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Review Count -->
                                        <div class="flex-shrink-0 text-right">
                                            @php
                                                $reviewCount = is_array($contributor)
                                                    ? $contributor['review_count'] ?? 0
                                                    : $contributor->review_count ?? 0;
                                            @endphp
                                            <p class="text-lg font-bold text-pink-600">{{ $reviewCount }}</p>
                                            <p class="text-xs text-gray-500">{{ __('reviews') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-center py-8">{{ __('No data available') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('About the Leaderboard') }}</h3>
                <p class="text-blue-800">
                    {{ __('This leaderboard displays top performers across different metrics including sales revenue, customer ratings, buyer spending, and review contributions. Rankings are updated in real-time based on transaction and review data.') }}
                </p>
            </div>
        </div>
    </div>
@endsection
