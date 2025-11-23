@extends('layouts.app')

@section('title', __('affiliate.admin_title'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('affiliate.admin_title') }}</h2>
                <p class="text-gray-600 mt-1">{{ __('affiliate.affiliate_stats') }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">
                ← {{ __('messages.back_to_dashboard') }}
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_affiliates') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($totalAffiliates) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_links') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($totalLinks) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_clicks') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($totalClicks) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_conversions') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ number_format($totalConversions) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_commissions') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency($totalCommissions) }}</p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_payouts') }}</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency($totalPayouts) }}</p>
            </div>
        </div>

        <!-- Navigation -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <a href="{{ route('admin.affiliate.payouts') }}" 
                class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="font-semibold text-gray-900">{{ __('affiliate.recent_payouts') }}</p>
                        <p class="text-sm text-gray-500">{{ __('messages.manage') }} {{ __('affiliate.recent_payouts') }}</p>
                    </div>
                </div>
            </a>
            <a href="{{ route('admin.affiliate.commissions') }}" 
                class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="font-semibold text-gray-900">{{ __('affiliate.recent_commissions') }}</p>
                        <p class="text-sm text-gray-500">{{ __('messages.manage') }} {{ __('affiliate.recent_commissions') }}</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Top Affiliates -->
        @if($topAffiliates->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.top_affiliates') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.user') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.total_commissions') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.total_links') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.total_conversions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($topAffiliates as $affiliate)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $affiliate->name }}<br>
                                        <span class="text-xs text-gray-500">{{ $affiliate->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ currency($affiliate->affiliate_commissions_sum_commission_amount ?? 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $affiliate->affiliateLinks()->count() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $affiliate->affiliateConversions()->count() }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Conversions -->
            @if($recentConversions->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_conversions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($recentConversions as $conversion)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $conversion->converter->name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded text-xs font-medium">
                                                {{ __('affiliate.conversion_type.' . $conversion->conversion_type) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($conversion->transaction_amount) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Pending Payouts -->
            @if($pendingPayouts->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.pending_payouts') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('affiliate.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($pendingPayouts as $payout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $payout->affiliate->name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ currency($payout->amount) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded text-xs font-medium 
                                                {{ $payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ __('affiliate.payout_status.' . $payout->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <a href="{{ route('admin.affiliate.payouts', ['status' => 'pending']) }}" 
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            {{ __('messages.view_all') }} →
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

