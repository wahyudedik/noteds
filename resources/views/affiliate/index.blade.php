@extends('layouts.app')

@section('title', __('affiliate.title'))

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('affiliate.title') }}</h1>
                <p class="mt-2 text-base text-gray-600">{{ __('affiliate.description') }}</p>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Links -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_links') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['total_links'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Clicks -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_clicks') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_clicks']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Conversions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_conversions') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_conversions']) }}
                                </p>
                                @if ($stats['total_clicks'] > 0)
                                    <p class="text-xs text-gray-500 mt-1">{{ __('affiliate.conversion_rate') }}:
                                        {{ number_format($stats['conversion_rate'], 2) }}%</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Commissions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_commissions') }}</p>
                                <p class="text-2xl font-bold text-gray-900">{{ currency($stats['total_commissions']) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ __('affiliate.pending') }}:
                                    {{ currency($stats['pending_commissions']) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings & Payouts -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Available Balance -->
                <div
                    class="bg-gradient-to-r from-green-500 to-emerald-600 overflow-hidden shadow-sm rounded-lg border border-green-500">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-green-100 text-sm font-medium">{{ __('affiliate.available_balance') }}</p>
                                <p class="text-3xl font-bold text-white mt-2">{{ currency($stats['available_balance']) }}
                                </p>
                            </div>
                            <svg class="h-12 w-12 text-green-200 opacity-50" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Approved Commissions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('affiliate.approved_commissions') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency($stats['approved_commissions']) }}
                        </p>
                    </div>
                </div>

                <!-- Total Payouts -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">{{ __('affiliate.total_payouts') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-2">{{ currency($stats['total_payouts']) }}</p>
                        @if ($stats['pending_payouts'] > 0)
                            <p class="text-xs text-yellow-600 mt-1">{{ __('affiliate.pending_payouts') }}:
                                {{ currency($stats['pending_payouts']) }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Affiliate Links Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.affiliate_links') }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ __('affiliate.manage_links_description') }}</p>
                        </div>
                        <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ __('affiliate.create_link') }}
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    @if ($affiliateLinks->count() > 0)
                        <div class="space-y-4">
                            @foreach ($affiliateLinks as $link)
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-3 mb-2">
                                                <h4 class="font-semibold text-gray-900">
                                                    {{ $link->name ?: __('affiliate.link') }} #{{ $link->code }}
                                                </h4>
                                                @if ($link->is_active)
                                                    <span
                                                        class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">{{ __('affiliate.active') }}</span>
                                                @else
                                                    <span
                                                        class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs font-medium">{{ __('affiliate.inactive') }}</span>
                                                @endif
                                            </div>
                                            @if ($link->description)
                                                <p class="text-sm text-gray-600 mb-3">{{ $link->description }}</p>
                                            @endif
                                            <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                                <code class="text-sm text-gray-800 break-all">{{ $link->full_url }}</code>
                                            </div>
                                            @if ($link->landing_page_slug)
                                                <div class="mb-3">
                                                    <a href="{{ $link->landing_page_url }}" target="_blank"
                                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                        {{ __('affiliate.landing_page_url') }}:
                                                        {{ $link->landing_page_url }}
                                                    </a>
                                                </div>
                                            @endif
                                            <div class="grid grid-cols-3 gap-4 text-sm">
                                                <div>
                                                    <p class="text-gray-500">{{ __('affiliate.clicks') }}</p>
                                                    <p class="font-semibold text-gray-900">
                                                        {{ number_format($link->clicks) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">{{ __('affiliate.conversions') }}</p>
                                                    <p class="font-semibold text-gray-900">
                                                        {{ number_format($link->conversions) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-gray-500">{{ __('affiliate.commissions') }}</p>
                                                    <p class="font-semibold text-gray-900">
                                                        {{ currency($link->total_commission) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex flex-col gap-2">
                                            <button onclick="copyLink('{{ $link->full_url }}')"
                                                class="text-blue-600 hover:text-blue-800 px-3 py-1 text-sm font-medium">
                                                {{ __('affiliate.copy') }}
                                            </button>
                                            <button onclick="editLink('{{ $link->id }}')"
                                                class="text-gray-600 hover:text-gray-800 px-3 py-1 text-sm font-medium">
                                                {{ __('affiliate.edit') }}
                                            </button>
                                            <button onclick="editLandingPage('{{ $link->id }}')"
                                                class="text-purple-600 hover:text-purple-800 px-3 py-1 text-sm font-medium">
                                                {{ __('affiliate.edit_landing_page') }}
                                            </button>
                                            <button onclick="managePromotionalMaterials('{{ $link->id }}')"
                                                class="text-indigo-600 hover:text-indigo-800 px-3 py-1 text-sm font-medium">
                                                {{ __('affiliate.promotional_materials') }}
                                            </button>
                                            <form action="{{ route('affiliate.links.delete', $link) }}" method="POST"
                                                onsubmit="return confirm('{{ __('affiliate.delete_confirm') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-800 px-3 py-1 text-sm font-medium">
                                                    {{ __('affiliate.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                            </svg>
                            <p class="mt-4 text-sm text-gray-500">{{ __('affiliate.no_links') }}</p>
                            <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')"
                                class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                                {{ __('affiliate.create_first_link') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Leaderboard Link -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('affiliate.leaderboard_title') }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ __('affiliate.leaderboard_description') }}</p>
                    <a href="{{ route('affiliate.leaderboard') }}"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors">
                        {{ __('affiliate.view_leaderboard') }}
                    </a>
                </div>
            </div>

            <!-- Commission Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Commission by Tier -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_tier') }}</h3>
                    </div>
                    <div class="p-6">
                        @if ($commissionByTier->count() > 0)
                            <div class="space-y-4">
                                @foreach ([1, 2, 3] as $tier)
                                    @php
                                        $tierData = $commissionByTier->get($tier);
                                        $amount = $tierData ? $tierData->total : 0;
                                        $count = $tierData ? $tierData->count : 0;
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                        <div>
                                            <p class="font-medium text-gray-900">{{ __('affiliate.tier') }}
                                                {{ $tier }}</p>
                                            <p class="text-sm text-gray-500">{{ $count }}
                                                {{ __('affiliate.commissions') }}</p>
                                        </div>
                                        <p class="text-lg font-bold text-gray-900">{{ currency($amount) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-8">{{ __('affiliate.no_commissions') }}</p>
                        @endif
                    </div>
                </div>

                <!-- Commission by Status -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_status') }}</h3>
                    </div>
                    <div class="p-6">
                        @if ($commissionByStatus->count() > 0)
                            <div class="space-y-4">
                                @foreach (['pending', 'approved', 'paid'] as $status)
                                    @php
                                        $statusData = $commissionByStatus->get($status);
                                        $amount = $statusData ? $statusData->total : 0;
                                        $count = $statusData ? $statusData->count : 0;
                                    @endphp
                                    @if ($amount > 0 || $count > 0)
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                            <div>
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-medium 
                                                {{ $status === 'paid' ? 'bg-green-100 text-green-800' : ($status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ __('affiliate.statuses.' . $status) }}
                                                </span>
                                                <p class="text-sm text-gray-500 mt-1">{{ $count }}
                                                    {{ __('affiliate.commissions') }}</p>
                                            </div>
                                            <p class="text-lg font-bold text-gray-900">{{ currency($amount) }}</p>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-8">{{ __('affiliate.no_commissions') }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recent Conversions & Commissions -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Recent Conversions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_conversions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.user') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.type') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentConversions as $conversion)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $conversion->converter->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $conversion->conversion_type === 'purchase' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ __('affiliate.conversion_type.' . ($conversion->conversion_type ?? 'signup')) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($conversion->transaction_amount ?? 0) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ ($conversion->converted_at ?? $conversion->created_at)->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('affiliate.no_conversions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Commissions -->
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_commissions') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.tier') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.rate') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentCommissions as $commission)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ __('affiliate.tier') }}
                                            {{ $commission->tier }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $commission->commission_rate }}%
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($commission->commission_amount) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ __('affiliate.statuses.' . $commission->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">
                                            {{ __('affiliate.no_commissions') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Request Payout -->
            @if ($stats['available_balance'] > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.request_payout') }}</h3>
                    </div>
                    <div class="p-6">
                        <form action="{{ route('affiliate.payouts.request') }}" method="POST" id="payout-form">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('affiliate.amount') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" name="amount" id="amount" step="0.01"
                                            min="0.01" max="{{ $stats['available_balance'] }}"
                                            value="{{ old('amount', $stats['available_balance']) }}" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.available') }}:
                                            {{ currency($stats['available_balance']) }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label for="payout_method" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('affiliate.payout_method') }} <span class="text-red-500">*</span>
                                    </label>
                                    <select name="payout_method" id="payout_method" required
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        <option value="wallet">{{ __('affiliate.payout_methods.wallet') }}</option>
                                        <option value="bank_transfer">{{ __('affiliate.payout_methods.bank_transfer') }}
                                        </option>
                                        <option value="paypal">{{ __('affiliate.payout_methods.paypal') }}</option>
                                        <option value="other">{{ __('affiliate.payout_methods.other') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-6 flex items-center justify-end">
                                <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    {{ __('affiliate.submit_payout_request') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            <!-- Recent Payouts -->
            @if ($recentPayouts->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.recent_payouts') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.amount') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.method') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.status') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('affiliate.date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentPayouts as $payout)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ currency($payout->amount) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ __('affiliate.payout_methods.' . $payout->payout_method) }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded text-xs font-medium 
                                            {{ $payout->status === 'completed' ? 'bg-green-100 text-green-800' : ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ __('affiliate.payout_status.' . $payout->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $payout->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Create Link Modal -->
    <div id="create-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.create_link') }}</h3>
                <form action="{{ route('affiliate.links.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.link_name') }}</label>
                            <input type="text" name="name" id="name"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.description') }}</label>
                            <textarea name="description" id="description" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="destination_url"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.destination_url') }}
                                ({{ __('affiliate.optional') }})</label>
                            <input type="url" name="destination_url" id="destination_url"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.destination_url_hint') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button"
                            onclick="document.getElementById('create-link-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ __('affiliate.create') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Link Modal -->
    <div id="edit-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.edit_link') }}</h3>
                <form id="edit-link-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit-name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.link_name') }}</label>
                            <input type="text" name="name" id="edit-name"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="edit-description"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.description') }}</label>
                            <textarea name="description" id="edit-description" rows="3"
                                class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="edit-destination_url"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.destination_url') }}
                                ({{ __('affiliate.optional') }})</label>
                            <input type="url" name="destination_url" id="edit-destination_url"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                            <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.destination_url_hint') }}</p>
                        </div>
                        <div>
                            <label for="edit-is_active" class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="edit-is_active" value="1"
                                    class="rounded">
                                <span class="text-sm font-medium text-gray-700">{{ __('affiliate.active') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button"
                            onclick="document.getElementById('edit-link-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ __('affiliate.update') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function copyLink(url) {
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('affiliate.link_copied') }}',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert('{{ __('affiliate.link_copied') }}');
                    }
                });
            }

            function editLink(linkId) {
                fetch(`/api/affiliate-links/${linkId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit-name').value = data.name || '';
                        document.getElementById('edit-description').value = data.description || '';
                        document.getElementById('edit-destination_url').value = data.destination_url || '';
                        document.getElementById('edit-is_active').checked = data.is_active;

                        const form = document.getElementById('edit-link-form');
                        form.action = `/affiliate/links/${linkId}`;

                        document.getElementById('edit-link-modal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('affiliate.error_loading_link') }}');
                    });
            }

            function editLandingPage(linkId) {
                fetch(`/api/affiliate-links/${linkId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Load landing page data
                        document.getElementById('landing-link-id').value = linkId;
                        document.getElementById('landing-page-slug').value = data.landing_page_slug || '';
                        document.getElementById('landing-page-content').value = data.landing_page_content || '';
                        document.getElementById('landing-page-preview').innerHTML = data.landing_page_content || '';

                        const form = document.getElementById('landing-page-form');
                        form.action = `/affiliate/links/${linkId}/landing`;

                        document.getElementById('landing-page-modal').classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('{{ __('affiliate.error_loading_link') }}');
                    });
            }

            function updateLandingPagePreview() {
                const content = document.getElementById('landing-page-content').value;
                document.getElementById('landing-page-preview').innerHTML = content ||
                    '<p class="text-gray-400">{{ __('messages.preview') }}</p>';
            }

            function managPromotionalMaterials(linkId) {
                // Load promotional materials for this link
                document.getElementById('promo-link-id').value = linkId;
                document.getElementById('promo-materials-form').action = `/affiliate/links/${linkId}/promotional-materials`;

                // Load existing materials
                fetch(`/affiliate/links/${linkId}/promotional-materials`)
                    .then(response => response.json())
                    .then(materials => {
                        const container = document.getElementById('existing-materials-list');
                        if (materials.length > 0) {
                            container.innerHTML = materials.map(material => `
                                <div class="border border-gray-200 rounded-lg p-4 flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">${material.name}</h4>
                                        <p class="text-sm text-gray-600 mt-1">${material.type.charAt(0).toUpperCase() + material.type.slice(1)}</p>
                                        ${material.size ? `<p class="text-xs text-gray-500 mt-1">${material.size}</p>` : ''}
                                        <div class="flex gap-2 mt-3">
                                            <button onclick="editPromoMaterial(${material.id})" class="text-blue-600 text-sm hover:underline">Edit</button>
                                            <button onclick="deletePromoMaterial(${material.id})" class="text-red-600 text-sm hover:underline">Delete</button>
                                            ${material.type === 'banner' && material.image_path ? `<a href="/storage/${material.image_path}" target="_blank" class="text-green-600 text-sm hover:underline">View Image</a>` : ''}
                                            ${material.html_code ? `<button onclick="copyPromoCode('${material.html_code}')" class="text-purple-600 text-sm hover:underline">Copy Code</button>` : ''}
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded text-xs font-medium ${material.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${material.is_active ? '{{ __('affiliate.active') }}' : '{{ __('affiliate.inactive') }}'}
                                    </span>
                                </div>
                            `).join('');
                        } else {
                            container.innerHTML =
                                '<p class="text-gray-500 text-center py-8">{{ __('affiliate.no_materials') }}</p>';
                        }
                    })
                    .catch(error => console.error('Error loading materials:', error));

                document.getElementById('promotional-materials-modal').classList.remove('hidden');
            }

            function copyPromoCode(code) {
                navigator.clipboard.writeText(code).then(() => {
                    alert('{{ __('affiliate.html_code_copied') }}');
                }).catch(err => console.error('Copy failed:', err));
            }

            function deletePromoMaterial(materialId) {
                if (!confirm('{{ __('affiliate.delete_confirm') }}')) return;

                fetch(`/affiliate/promotional-materials/${materialId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => {
                        if (response.ok) {
                            // Reload materials list
                            managPromotionalMaterials(document.getElementById('promo-link-id').value);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }
        </script>
    @endpush

    <!-- Landing Page Builder Modal -->
    <div id="landing-page-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.edit_landing_page') }}</h3>

                <form id="landing-page-form" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="landing-link-id">

                    <!-- Landing Page Slug -->
                    <div>
                        <label for="landing-page-slug" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('affiliate.landing_page_slug') }}
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-600">{{ url('/a') }}/</span>
                            <input type="text" id="landing-page-slug" name="landing_page_slug"
                                placeholder="my-affiliate-link" class="flex-1 rounded-lg border-gray-300 shadow-sm">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.slug_hint') }}</p>
                    </div>

                    <!-- Landing Page Content Editor -->
                    <div>
                        <label for="landing-page-content" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('affiliate.landing_page_content') }}
                        </label>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Content Editor -->
                            <div>
                                <textarea id="landing-page-content" name="landing_page_content" rows="10" onchange="updateLandingPagePreview()"
                                    oninput="updateLandingPagePreview()" placeholder="{{ __('affiliate.landing_page_html_hint') }}"
                                    class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm"></textarea>
                            </div>

                            <!-- Preview -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.preview') }}
                                </label>
                                <div id="landing-page-preview"
                                    class="border border-gray-300 rounded-lg p-4 bg-gray-50 overflow-auto h-64 prose prose-sm">
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('affiliate.html_content_allowed') }}</p>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">{{ __('affiliate.landing_page_info') }}</p>
                                <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                                    <li>{{ __('affiliate.landing_page_info_1') }}</li>
                                    <li>{{ __('affiliate.landing_page_info_2') }}</li>
                                    <li>{{ __('affiliate.landing_page_info_3') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <button type="button"
                            onclick="document.getElementById('landing-page-modal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            {{ __('affiliate.cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            {{ __('affiliate.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Promotional Materials Manager Modal -->
    <div id="promotional-materials-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('affiliate.promotional_materials') }}</h3>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Create New Material -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">
                            {{ __('affiliate.create_promotional_material') }}</h4>
                        <form id="promo-materials-form" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf

                            <div>
                                <label for="promo-material-name" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_name') }} *
                                </label>
                                <input type="text" id="promo-material-name" name="name" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label for="promo-material-type" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_type') }} *
                                </label>
                                <select id="promo-material-type" name="type" required
                                    onchange="updatePromoMaterialFields()"
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Select Type</option>
                                    <option value="banner">{{ __('affiliate.banner_image') }}</option>
                                    <option value="link">{{ __('affiliate.link_code') }}</option>
                                    <option value="text">{{ __('affiliate.text_ad') }}</option>
                                </select>
                            </div>

                            <div id="promo-size-field" class="hidden">
                                <label for="promo-material-size" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.material_size') }}
                                </label>
                                <select id="promo-material-size" name="size"
                                    class="w-full rounded-lg border-gray-300 shadow-sm">
                                    <option value="">Select Size</option>
                                    <option value="728x90">Leaderboard (728x90)</option>
                                    <option value="300x250">Medium Rectangle (300x250)</option>
                                    <option value="468x60">Banner (468x60)</option>
                                    <option value="custom">{{ __('affiliate.custom_size') }}</option>
                                </select>
                            </div>

                            <div id="promo-image-field" class="hidden">
                                <label for="promo-material-image" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.banner_image') }}
                                </label>
                                <input type="file" id="promo-material-image" name="image" accept="image/*"
                                    class="w-full">
                                <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.max_2mb') }}</p>
                            </div>

                            <div id="promo-code-field" class="hidden">
                                <label for="promo-material-code" class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.html_code') }}
                                </label>
                                <textarea id="promo-material-code" name="html_code" rows="4" placeholder="<a href='...'>Click here</a>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm"></textarea>
                            </div>

                            <div>
                                <label for="promo-material-description"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    {{ __('affiliate.description') }}
                                </label>
                                <textarea id="promo-material-description" name="description" rows="2"
                                    class="w-full rounded-lg border-gray-300 shadow-sm"></textarea>
                            </div>

                            <button type="submit"
                                class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                {{ __('affiliate.create') }}
                            </button>
                        </form>
                    </div>

                    <!-- Existing Materials -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-4">{{ __('affiliate.existing_materials') }}
                        </h4>
                        <div id="existing-materials-list" class="space-y-3 max-h-96 overflow-y-auto">
                            <p class="text-gray-500 text-center py-8">{{ __('affiliate.no_materials') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Close Button -->
                <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('promotional-materials-modal').classList.add('hidden')"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        {{ __('affiliate.close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updatePromoMaterialFields() {
            const type = document.getElementById('promo-material-type').value;
            document.getElementById('promo-size-field').classList.toggle('hidden', type !== 'banner');
            document.getElementById('promo-image-field').classList.toggle('hidden', type !== 'banner');
            document.getElementById('promo-code-field').classList.toggle('hidden', type !== 'link' && type !== 'text');
        }

        // Fix function name typo
        function managePromotionalMaterials(linkId) {
            managPromotionalMaterials(linkId);
        }
    </script>

@endsection
