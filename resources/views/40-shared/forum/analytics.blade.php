@extends('40-shared/layouts/app')

@section('title', __('Forum Analytics'))

@section('content')
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('Forum Analytics') }}</h1>
                <p class="text-lg text-gray-600">{{ __('Track your posts performance and engagement') }}</p>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Total Posts -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 font-medium">{{ __('Total Posts') }}</h3>
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ $summary['total_posts'] ?? 0 }}</p>
                </div>

                <!-- Total Views -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 font-medium">{{ __('Total Views') }}</h3>
                        <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($summary['total_views'] ?? 0) }}</p>
                </div>

                <!-- Total Likes -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 font-medium">{{ __('Total Likes') }}</h3>
                        <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v5">
                            </path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($summary['total_likes'] ?? 0) }}</p>
                </div>

                <!-- Total Comments -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 font-medium">{{ __('Total Comments') }}</h3>
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($summary['total_comments'] ?? 0) }}</p>
                </div>

                <!-- Total Shares -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-gray-600 font-medium">{{ __('Total Shares') }}</h3>
                        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C9.589 12.938 10 11.914 10 10.5c0-1.933-1.348-3.5-3-3.5s-3 1.567-3 3.5c0 1.414.411 2.438 1.316 2.842m0 0h0m0 0c.711.337 1.316.991 1.316 1.658v2c0 .823-.373 1.555-.904 2.05m0 0h0m0 0c1.316.49 2.904.49 4.208 0m0 0h0m0 0c-.52-.495-.904-1.227-.904-2.05v-2c0-.667.605-1.321 1.316-1.658">
                            </path>
                        </svg>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($summary['total_shares'] ?? 0) }}</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Views Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Views Over Last 30 Days') }}</h2>
                    @if (count($chartData ?? []) > 0)
                        <div class="h-80 flex items-end gap-1">
                            @php
                                $maxData = max($chartData ?? [0]);
                                $maxData = $maxData > 0 ? $maxData : 1;
                            @endphp
                            @foreach ($chartData ?? [] as $index => $value)
                                <div class="flex-1 flex flex-col items-center gap-2">
                                    <div class="relative w-full bg-blue-500 rounded-t-lg transition hover:bg-blue-600"
                                        style="height: {{ ($value / $maxData) * 100 }}%; min-height: {{ $value > 0 ? '4px' : '0px' }};">
                                    </div>
                                    <span class="text-xs text-gray-600 text-center">{{ $chartLabels[$index] ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-80 flex items-center justify-center">
                            <p class="text-gray-500">{{ __('No data available') }}</p>
                        </div>
                    @endif
                </div>

                <!-- Engagement Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Engagement Summary') }}</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Avg Views/Post') }}</span>
                            <span class="font-bold text-lg text-gray-900">
                                {{ $summary['total_posts'] > 0 ? number_format(intval($summary['total_views'] / $summary['total_posts'])) : 0 }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Avg Likes/Post') }}</span>
                            <span class="font-bold text-lg text-gray-900">
                                {{ $summary['total_posts'] > 0 ? number_format(intval($summary['total_likes'] / $summary['total_posts'])) : 0 }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Avg Comments/Post') }}</span>
                            <span class="font-bold text-lg text-gray-900">
                                {{ $summary['total_posts'] > 0 ? number_format(intval($summary['total_comments'] / $summary['total_posts'])) : 0 }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Avg Shares/Post') }}</span>
                            <span class="font-bold text-lg text-gray-900">
                                {{ $summary['total_posts'] > 0 ? number_format(intval($summary['total_shares'] / $summary['total_posts'])) : 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Posts -->
            @if (($topPosts ?? collect())->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Top Performing Posts') }}</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-900">{{ __('Post') }}</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-900">{{ __('Views') }}</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-900">{{ __('Likes') }}</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-900">{{ __('Comments') }}</th>
                                    <th class="text-right py-3 px-4 font-semibold text-gray-900">{{ __('Shares') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topPosts ?? [] as $post)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                        <td class="py-3 px-4">
                                            <a href="{{ route('forum.show', $post) }}"
                                                class="text-blue-600 hover:text-blue-800 font-medium line-clamp-2">
                                                {{ $post?->summary ?? __('Untitled Post') }}
                                            </a>
                                        </td>
                                        <td class="text-right py-3 px-4 text-gray-900">
                                            {{ number_format($post?->views_count ?? 0) }}</td>
                                        <td class="text-right py-3 px-4 text-gray-900">
                                            {{ number_format($post?->likes_count ?? 0) }}</td>
                                        <td class="text-right py-3 px-4 text-gray-900">
                                            {{ number_format($post?->comments_count ?? 0) }}</td>
                                        <td class="text-right py-3 px-4 text-gray-900">
                                            {{ number_format($post?->shares_count ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                        </path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No analytics data') }}</h3>
                    <p class="text-gray-600">{{ __('Create and publish posts to see your analytics') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
