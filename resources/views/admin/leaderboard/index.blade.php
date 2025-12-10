@extends('layouts.app')

@section('title', 'Leaderboard Report')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Leaderboard Report</h2>

            <!-- Top Sellers by Revenue -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Sellers by Revenue</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Seller Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Username</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topSellersByRevenue as $seller)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        @if ($seller['rank'] == 1)
                                            🥇
                                        @elseif($seller['rank'] == 2)
                                            🥈
                                        @elseif($seller['rank'] == 3)
                                            🥉
                                        @else
                                            #{{ $seller['rank'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($seller['user']->avatar)
                                                <img class="h-8 w-8 rounded-full mr-3" src="{{ $seller['user']->avatar }}"
                                                    alt="">
                                            @else
                                                <div
                                                    class="h-8 w-8 rounded-full mr-3 bg-gray-300 flex items-center justify-center">
                                                    <span
                                                        class="text-gray-600 text-xs font-semibold">{{ substr($seller['user']->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $seller['user']->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ $seller['user'] - > username }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="text-sm font-semibold text-green-600">{{ currency($seller['total_revenue']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Sellers by Ratings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Sellers by Ratings</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Seller Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Username</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Rating</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Reviews</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topSellersByRatings as $seller)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        @if ($seller['rank'] == 1)
                                            🥇
                                        @elseif($seller['rank'] == 2)
                                            🥈
                                        @elseif($seller['rank'] == 3)
                                            🥉
                                        @else
                                            #{{ $seller['rank'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($seller['user']->avatar)
                                                <img class="h-8 w-8 rounded-full mr-3" src="{{ $seller['user']->avatar }}"
                                                    alt="">
                                            @else
                                                <div
                                                    class="h-8 w-8 rounded-full mr-3 bg-gray-300 flex items-center justify-center">
                                                    <span
                                                        class="text-gray-600 text-xs font-semibold">{{ substr($seller['user']->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $seller['user']->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ $seller['user'] - > username }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-semibold text-yellow-600">
                                            ⭐ {{ number_format($seller['average_rating'], 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm text-gray-600">{{ $seller['review_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Buyers by Spending -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Buyers by Spending</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Buyer Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Username</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Total Spending</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topBuyersBySpending as $buyer)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        @if ($buyer['rank'] == 1)
                                            🥇
                                        @elseif($buyer['rank'] == 2)
                                            🥈
                                        @elseif($buyer['rank'] == 3)
                                            🥉
                                        @else
                                            #{{ $buyer['rank'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($buyer['user']->avatar)
                                                <img class="h-8 w-8 rounded-full mr-3" src="{{ $buyer['user']->avatar }}"
                                                    alt="">
                                            @else
                                                <div
                                                    class="h-8 w-8 rounded-full mr-3 bg-gray-300 flex items-center justify-center">
                                                    <span
                                                        class="text-gray-600 text-xs font-semibold">{{ substr($buyer['user']->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $buyer['user']->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ $buyer['user'] - > username }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="text-sm font-semibold text-blue-600">{{ currency($buyer['total_spending']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Contributors by Reviews -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 5 Contributors by Reviews</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Rank</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Contributor Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Username</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Total Reviews</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($topContributorsByReviews as $contributor)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        @if ($contributor['rank'] == 1)
                                            🥇
                                        @elseif($contributor['rank'] == 2)
                                            🥈
                                        @elseif($contributor['rank'] == 3)
                                            🥉
                                        @else
                                            #{{ $contributor['rank'] }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if ($contributor['user']->avatar)
                                                <img class="h-8 w-8 rounded-full mr-3"
                                                    src="{{ $contributor['user']->avatar }}" alt="">
                                            @else
                                                <div
                                                    class="h-8 w-8 rounded-full mr-3 bg-gray-300 flex items-center justify-center">
                                                    <span
                                                        class="text-gray-600 text-xs font-semibold">{{ substr($contributor['user']->name, 0, 1) }}</span>
                                                </div>
                                            @endif
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $contributor['user']->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">@{{ $contributor['user'] - > username }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="text-sm font-semibold text-purple-600">{{ $contributor['review_count'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
