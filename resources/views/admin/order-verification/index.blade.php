@extends('40-shared/layouts/app')

@section('title', __('Order Verification'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Order Verification') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Verify and process pending service orders') }}</p>
            </div>

            <!-- Stats Cards -->
            @if (isset($stats))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Pending Orders -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 font-medium">{{ __('Pending Verification') }}</h3>
                            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 2m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-gray-900">{{ $stats['pending_count'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ __('Awaiting verification') }}</p>
                    </div>

                    <!-- Total Escrow -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 font-medium">{{ __('Total Escrow') }}</h3>
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-gray-900">
                            {{ settings('currency_symbol') ?? '$' }}{{ number_format($stats['total_escrow'] ?? 0, 2) }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ __('In escrow') }}</p>
                    </div>

                    <!-- Verified Today -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-gray-600 font-medium">{{ __('Verified Today') }}</h3>
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="text-4xl font-bold text-gray-900">{{ $stats['verified_today'] ?? 0 }}</p>
                        <p class="text-sm text-gray-500 mt-2">{{ __('Completed') }}</p>
                    </div>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form method="GET" action="{{ route('admin.order-verification.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <!-- Order ID -->
                        <div>
                            <label for="order_id"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Order ID') }}</label>
                            <input type="text" id="order_id" name="order_id" value="{{ request('order_id', '') }}"
                                placeholder="{{ __('Search by ID...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Buyer Name -->
                        <div>
                            <label for="buyer_name"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Buyer') }}</label>
                            <input type="text" id="buyer_name" name="buyer_name" value="{{ request('buyer_name', '') }}"
                                placeholder="{{ __('Buyer name...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Vendor Name -->
                        <div>
                            <label for="vendor_name"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('Vendor') }}</label>
                            <input type="text" id="vendor_name" name="vendor_name"
                                value="{{ request('vendor_name', '') }}" placeholder="{{ __('Vendor name...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Date From -->
                        <div>
                            <label for="date_from"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('From Date') }}</label>
                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from', '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <!-- Date To -->
                        <div>
                            <label for="date_to"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('To Date') }}</label>
                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to', '') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-4">
                        <button type="submit"
                            class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition">
                            {{ __('Apply Filters') }}
                        </button>
                        @if (request()->has('order_id') ||
                                request()->has('buyer_name') ||
                                request()->has('vendor_name') ||
                                request()->has('date_from') ||
                                request()->has('date_to'))
                            <a href="{{ route('admin.order-verification.index') }}"
                                class="px-6 py-2 bg-gray-200 text-gray-800 font-medium rounded-lg hover:bg-gray-300 transition">
                                {{ __('Clear Filters') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Orders Table -->
            @if (($orders ?? collect())->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Order') }}</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Buyer') }}</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Vendor') }}</th>
                                    <th class="text-right py-3 px-6 font-semibold text-gray-900">{{ __('Amount') }}</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-900">{{ __('Approved') }}</th>
                                    <th class="text-center py-3 px-6 font-semibold text-gray-900">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders ?? [] as $order)
                                    <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                        <td class="py-4 px-6">
                                            <p class="font-semibold text-gray-900">#{{ $order->id }}</p>
                                            <p class="text-sm text-gray-500">{{ $order->service_type ?? __('Service') }}
                                            </p>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center gap-2">
                                                @if ($order->user->avatar_url)
                                                    <img src="{{ $order->user->avatar_url }}"
                                                        alt="{{ $order->user->name }}" class="w-8 h-8 rounded-full">
                                                @else
                                                    <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                                                @endif
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $order->user->name }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ '@' . ($order->user->username ?? '') }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            @if ($order->assignedVendor)
                                                <div class="flex items-center gap-2">
                                                    @if ($order->assignedVendor->avatar_url)
                                                        <img src="{{ $order->assignedVendor->avatar_url }}"
                                                            alt="{{ $order->assignedVendor->name }}"
                                                            class="w-8 h-8 rounded-full">
                                                    @else
                                                        <div class="w-8 h-8 rounded-full bg-gray-300"></div>
                                                    @endif
                                                    <div>
                                                        <p class="font-medium text-gray-900">
                                                            {{ $order->assignedVendor->name }}</p>
                                                        <p class="text-sm text-gray-500">
                                                            {{ '@' . ($order->assignedVendor->username ?? '') }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6 text-right">
                                            <p class="font-semibold text-gray-900">
                                                {{ currency($order->escrow_amount) }}
                                            </p>
                                            <p class="text-sm text-gray-500">{{ __('Escrow') }}</p>
                                        </td>
                                        <td class="py-4 px-6">
                                            <p class="text-sm text-gray-600">
                                                {{ $order->buyer_approved_at?->format('M d, Y H:i') ?? '-' }}</p>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <a href="{{ route('admin.order-verification.show', $order) }}"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">
                                                {{ __('Review') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                @if ($orders->hasPages())
                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No pending verifications') }}</h3>
                    <p class="text-gray-600">{{ __('All orders have been verified!') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
