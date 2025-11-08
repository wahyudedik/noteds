@extends('layouts.app')

@section('title', __('buyer.analytics.title'))

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('buyer.analytics.title') }}</h1>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('buyer.analytics.total_purchased') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPurchased }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('buyer.analytics.total_spent') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ currency($totalSpent) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('buyer.analytics.total_downloads') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalDownloads }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">{{ __('buyer.analytics.completion_rate') }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($completionRate, 1) }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Purchases -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">{{ __('buyer.analytics.recent_purchases') }}</h2>
                <a href="{{ route('buyer-analytics.purchase-history') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    {{ __('buyer.analytics.view_all') }}
                </a>
            </div>
            @if($recentPurchases->count() > 0)
                <div class="space-y-4">
                    @foreach($recentPurchases as $purchase)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900">{{ $purchase->note->title }}</h3>
                                    <p class="text-xs text-gray-600 mt-1">
                                        {{ __('buyer.analytics.purchased_on', [
                                            'date' => $purchase->purchased_at->format('d M Y'),
                                            'time' => $purchase->purchased_at->format('H:i')
                                        ]) }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ currency($purchase->purchase_price) }}</p>
                                <a href="{{ route('marketplace.show', $purchase->note) }}" class="text-xs text-blue-600 hover:text-blue-700">
                                    {{ __('buyer.analytics.view_note') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-600 py-8">
                    <p class="font-medium">{{ __('buyer.analytics.empty_recent_title') }}</p>
                    <p class="text-sm mt-1">{{ __('buyer.analytics.empty_recent_message') }}</p>
                </div>
            @endif
        </div>

        <!-- Categories -->
        @if(isset($categories) && $categories->count() > 0 && $totalPurchased > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ __('buyer.analytics.top_categories') }}</h2>
                <div class="space-y-3">
                    @foreach($categories as $category)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700">{{ $category->name }}</span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                    <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($category->count / $totalPurchased) * 100 }}%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 w-8 text-right">{{ $category->count }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
