@extends('layouts.app')

@section('title', 'Leaderboards')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Leaderboards</h1>
            <p class="mt-2 text-base text-gray-600">See who's leading the platform in sales, purchases, and contributions!</p>
        </div>

        <!-- Type Selector -->
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('leaderboard.index', ['type' => 'sellers', 'metric' => $type === 'sellers' ? $metric : 'revenue', 'period' => $period]) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $type === 'sellers' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Top Sellers
            </a>
            <a href="{{ route('leaderboard.index', ['type' => 'buyers', 'metric' => $type === 'buyers' ? $metric : 'purchases', 'period' => $period]) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $type === 'buyers' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Top Buyers
            </a>
            <a href="{{ route('leaderboard.index', ['type' => 'contributors', 'metric' => $type === 'contributors' ? $metric : 'reviews', 'period' => $period]) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $type === 'contributors' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Top Contributors
            </a>
        </div>

        <!-- Metric Selector (based on type) -->
        @if($type === 'sellers')
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('leaderboard.index', ['type' => 'sellers', 'metric' => 'revenue', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'revenue' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Revenue
                </a>
                <a href="{{ route('leaderboard.index', ['type' => 'sellers', 'metric' => 'sales', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'sales' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Sales Count
                </a>
                <a href="{{ route('leaderboard.index', ['type' => 'sellers', 'metric' => 'ratings', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'ratings' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Ratings
                </a>
            </div>
        @elseif($type === 'buyers')
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('leaderboard.index', ['type' => 'buyers', 'metric' => 'purchases', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'purchases' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Purchase Count
                </a>
                <a href="{{ route('leaderboard.index', ['type' => 'buyers', 'metric' => 'spending', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'spending' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Spending
                </a>
            </div>
        @elseif($type === 'contributors')
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="{{ route('leaderboard.index', ['type' => 'contributors', 'metric' => 'reviews', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'reviews' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Reviews
                </a>
                <a href="{{ route('leaderboard.index', ['type' => 'contributors', 'metric' => 'forum', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'forum' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Forum Posts
                </a>
                <a href="{{ route('leaderboard.index', ['type' => 'contributors', 'metric' => 'shares', 'period' => $period]) }}" 
                   class="px-4 py-2 rounded-lg font-semibold {{ $metric === 'shares' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    By Shares
                </a>
            </div>
        @endif

        <!-- Period Selector -->
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="{{ route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'weekly']) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $period === 'weekly' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Weekly
            </a>
            <a href="{{ route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'monthly']) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $period === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                Monthly
            </a>
            <a href="{{ route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'all-time']) }}" 
               class="px-4 py-2 rounded-lg font-semibold {{ $period === 'all-time' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                All-Time
            </a>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">
                    {{ $title }} - {{ ucfirst($period) }}
                    @if($type === 'sellers' && $metric === 'revenue')
                        (By Revenue)
                    @elseif($type === 'sellers' && $metric === 'sales')
                        (By Sales Count)
                    @elseif($type === 'sellers' && $metric === 'ratings')
                        (By Ratings)
                    @elseif($type === 'buyers' && $metric === 'purchases')
                        (By Purchase Count)
                    @elseif($type === 'buyers' && $metric === 'spending')
                        (By Spending)
                    @elseif($type === 'contributors' && $metric === 'reviews')
                        (By Reviews)
                    @elseif($type === 'contributors' && $metric === 'forum')
                        (By Forum Posts)
                    @elseif($type === 'contributors' && $metric === 'shares')
                        (By Shares)
                    @endif
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    @if($type === 'sellers' && $metric === 'revenue')
                                        Total Revenue
                                    @elseif($type === 'sellers' && $metric === 'sales')
                                        Sales Count
                                    @elseif($type === 'sellers' && $metric === 'ratings')
                                        Average Rating
                                    @elseif($type === 'buyers' && $metric === 'purchases')
                                        Purchase Count
                                    @elseif($type === 'buyers' && $metric === 'spending')
                                        Total Spending
                                    @elseif($type === 'contributors' && $metric === 'reviews')
                                        Review Count
                                    @elseif($type === 'contributors' && $metric === 'forum')
                                        Post Count
                                    @elseif($type === 'contributors' && $metric === 'shares')
                                        Share Count
                                    @endif
                                </th>
                                @if($type === 'sellers' && $metric === 'ratings')
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($leaderboard as $entry)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-semibold text-gray-900">
                                            @if($entry['rank'] == 1) 🥇
                                            @elseif($entry['rank'] == 2) 🥈
                                            @elseif($entry['rank'] == 3) 🥉
                                            @endif
                                            #{{ $entry['rank'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($entry['user'])
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="{{ $entry['user']->avatar_url }}" alt="{{ $entry['user']->name }}">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="{{ route('public.profile.show', $entry['user']->username) }}" class="hover:text-blue-600">
                                                            {{ $entry['user']->name }}
                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">@ {{ $entry['user']->username }}</div>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Unknown User</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if(isset($entry['total_revenue']))
                                            {{ currency($entry['total_revenue']) }}
                                        @elseif(isset($entry['sales_count']))
                                            {{ number_format($entry['sales_count']) }} sales
                                        @elseif(isset($entry['average_rating']))
                                            <div class="flex items-center">
                                                <span class="text-lg font-bold">{{ number_format($entry['average_rating'], 1) }}</span>
                                                <svg class="w-5 h-5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </div>
                                        @elseif(isset($entry['purchase_count']))
                                            {{ number_format($entry['purchase_count']) }} purchases
                                        @elseif(isset($entry['total_spending']))
                                            {{ currency($entry['total_spending']) }}
                                        @elseif(isset($entry['review_count']))
                                            {{ number_format($entry['review_count']) }} reviews
                                        @elseif(isset($entry['post_count']))
                                            {{ number_format($entry['post_count']) }} posts
                                        @elseif(isset($entry['share_count']))
                                            {{ number_format($entry['share_count']) }} shares
                                        @endif
                                    </td>
                                    @if($type === 'sellers' && $metric === 'ratings')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($entry['review_count'] ?? 0) }} reviews
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $type === 'sellers' && $metric === 'ratings' ? '4' : '3' }}" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No data available for this period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
