@extends('layouts.app')

@section('title', __('affiliate.title'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900">{{ __('affiliate.title') }}</h1>
                    <p class="mt-3 text-lg text-gray-600">{{ __('affiliate.description') }}</p>
                </div>

                <!-- Alert Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800 font-medium">✓ {{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800 font-medium">✕ {{ session('error') }}</p>
                    </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.total_links') }}</p>
                        <p class="text-4xl font-bold text-gray-900 mt-3">{{ $stats['total_links'] ?? 0 }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.total_clicks') }}</p>
                        <p class="text-4xl font-bold text-gray-900 mt-3">{{ number_format($stats['total_clicks'] ?? 0) }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.total_conversions') }}</p>
                        <p class="text-4xl font-bold text-gray-900 mt-3">
                            {{ number_format($stats['total_conversions'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.total_commissions') }}</p>
                        <p class="text-4xl font-bold text-indigo-600 mt-3">{{ currency($stats['total_commissions'] ?? 0) }}
                        </p>
                    </div>
                </div>

                <!-- Earnings Summary -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.available_balance') }}</p>
                        <p class="text-4xl font-bold text-green-600 mt-3">{{ currency($stats['available_balance'] ?? 0) }}
                        </p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.approved_commissions') }}</p>
                        <p class="text-4xl font-bold text-blue-600 mt-3">
                            {{ currency($stats['approved_commissions'] ?? 0) }}</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6 border border-gray-200 hover:shadow-lg transition-shadow">
                        <p class="text-gray-600 text-sm font-semibold uppercase tracking-wide">
                            {{ __('affiliate.total_payouts') }}</p>
                        <p class="text-4xl font-bold text-purple-600 mt-3">{{ currency($stats['total_payouts'] ?? 0) }}</p>
                    </div>
                </div>

                <!-- Landing Page Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('affiliate.landing_page') }}</h2>
                        <button onclick="document.getElementById('edit-landing-global-modal').classList.remove('hidden')"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm">
                            {{ __('affiliate.setup_landing_page') }}
                        </button>
                    </div>

                    <!-- Landing Page Display -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        @if ($userLandingPage)
                            <div class="p-6">
                                <div class="mb-6">
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.landing_page_slug') }}</label>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-sm text-gray-600 bg-gray-50 px-3 py-2 rounded-lg flex-1">{{ url('/a') }}/{{ $userLandingPage->slug }}</span>
                                        <button type="button"
                                            onclick="copyLink('{{ url('/a') }}/{{ $userLandingPage->slug }}')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm">
                                            {{ __('affiliate.copy') }}
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3">
                                            {{ __('affiliate.assigned_links') }}</h4>
                                        <div class="space-y-2 max-h-64 overflow-y-auto">
                                            @forelse ($userLandingPage->affiliateLinks as $link)
                                                <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                                                    <span class="text-gray-700 text-sm">{{ $link->name }}</span>
                                                    <span
                                                        class="text-gray-500 text-xs ml-auto">{{ url('/') }}{{ $link->slug }}</span>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">{{ __('affiliate.no_links_assigned') }}
                                                </p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('affiliate.preview') }}
                                        </h4>
                                        <div id="landing-page-preview-display"
                                            class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto bg-white">
                                            {!! $userLandingPage->content ?? '<p class="text-gray-400">No content yet</p>' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-12">
                                <p class="text-gray-500 mb-4">{{ __('affiliate.no_landing_page') }}</p>
                                <button
                                    onclick="document.getElementById('edit-landing-global-modal').classList.remove('hidden')"
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg transition-colors font-medium">
                                    {{ __('affiliate.create_landing_page') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Affiliate Links Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('affiliate.affiliate_links') }}</h2>
                        <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors font-medium text-sm">
                            {{ __('affiliate.create_link') }}
                        </button>
                    </div>

                    <!-- Links Container -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        @if ($affiliateLinks->count() > 0)
                            <div class="divide-y divide-gray-200">
                                @foreach ($affiliateLinks as $link)
                                    <div class="p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h4 class="text-lg font-bold text-gray-900">
                                                    {{ $link->name ?: __('affiliate.link') }}</h4>
                                                @if ($link->description)
                                                    <p class="text-sm text-gray-600 mt-1">{{ $link->description }}</p>
                                                @endif
                                            </div>
                                            @if ($link->is_active)
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ __('affiliate.active') }}</span>
                                            @else
                                                <span
                                                    class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ __('affiliate.inactive') }}</span>
                                            @endif
                                        </div>

                                        <!-- Link URL -->
                                        <div class="bg-gray-50 rounded-lg p-3 mb-4 flex items-center justify-between">
                                            <code class="text-xs text-gray-600 break-all">{{ $link->full_url }}</code>
                                            <button onclick="copyLink('{{ $link->full_url }}')"
                                                class="ml-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium whitespace-nowrap transition-colors">
                                                {{ __('affiliate.copy') }}
                                            </button>
                                        </div>

                                        <!-- Stats -->
                                        <div class="grid grid-cols-3 gap-4 mb-4">
                                            <div class="bg-blue-50 rounded p-3">
                                                <p class="text-xs text-gray-600">{{ __('affiliate.clicks') }}</p>
                                                <p class="text-lg font-bold text-blue-600 mt-1">
                                                    {{ number_format($link->clicks ?? 0) }}</p>
                                            </div>
                                            <div class="bg-green-50 rounded p-3">
                                                <p class="text-xs text-gray-600">{{ __('affiliate.conversions') }}</p>
                                                <p class="text-lg font-bold text-green-600 mt-1">
                                                    {{ number_format($link->conversions ?? 0) }}</p>
                                            </div>
                                            <div class="bg-purple-50 rounded p-3">
                                                <p class="text-xs text-gray-600">{{ __('affiliate.commission') }}</p>
                                                <p class="text-lg font-bold text-purple-600 mt-1">
                                                    {{ currency($link->total_commission ?? 0) }}</p>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex gap-2">
                                            <button onclick="editLink('{{ $link->id }}')"
                                                class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                                                ✏️ {{ __('affiliate.edit') }}
                                            </button>
                                            <button onclick="managPromotionalMaterials('{{ $link->id }}')"
                                                class="px-3 py-1 text-sm bg-orange-600 text-white rounded hover:bg-orange-700">
                                                📦 {{ __('affiliate.materials') }}
                                            </button>
                                            <form action="{{ route('affiliate.links.delete', $link) }}" method="POST"
                                                class="inline"
                                                onsubmit="return confirm('{{ __('affiliate.delete_confirm') }}');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                                    🗑️ {{ __('affiliate.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-16">
                                <p class="text-gray-600 mb-4">{{ __('affiliate.no_links') }}</p>
                                <button onclick="document.getElementById('create-link-modal').classList.remove('hidden')"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition-colors font-medium">
                                    {{ __('affiliate.create_first_link') }}
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Commission Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Commission by Tier -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_tier') }}</h3>
                        </div>
                        <div class="p-6">
                            @if ($commissionByTier->count() > 0)
                                <div class="space-y-4">
                                    @foreach ([1, 2, 3] as $tier)
                                        @php
                                            $tierData = $commissionByTier->firstWhere('tier', $tier);
                                            $amount = $tierData ? $tierData->total : 0;
                                            $count = $tierData ? $tierData->count : 0;
                                        @endphp
                                        @if ($amount > 0 || $count > 0)
                                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ __('affiliate.tier') }}
                                                        {{ $tier }}</p>
                                                    <p class="text-sm text-gray-500">{{ $count }}
                                                        {{ __('affiliate.conversions') }}</p>
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

                    <!-- Commission by Status -->
                    <div class="bg-white rounded-lg shadow border border-gray-200">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.commission_by_status') }}
                            </h3>
                        </div>
                        <div class="p-6">
                            @if ($commissionByStatus->count() > 0)
                                <div class="space-y-4">
                                    @foreach (['pending' => 'yellow', 'approved' => 'blue', 'paid' => 'green'] as $status => $color)
                                        @php
                                            $statusData = $commissionByStatus->firstWhere('status', $status);
                                            $amount = $statusData ? $statusData->total : 0;
                                            $count = $statusData ? $statusData->count : 0;
                                        @endphp
                                        @if ($amount > 0 || $count > 0)
                                            <div
                                                class="flex items-center justify-between p-3 bg-{{ $color }}-50 rounded-lg border border-{{ $color }}-200">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ ucfirst($status) }}</p>
                                                    <p class="text-sm text-gray-500">{{ $count }}
                                                        {{ __('affiliate.commissions') }}</p>
                                                </div>
                                                <p class="text-lg font-bold text-{{ $color }}-600">
                                                    {{ currency($amount) }}</p>
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
                    <div class="bg-white rounded-lg shadow border border-gray-200">
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
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $conversion->converter->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold {{ $conversion->conversion_type === 'purchase' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                    {{ ucfirst($conversion->conversion_type ?? 'signup') }}
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
                    <div class="bg-white rounded-lg shadow border border-gray-200">
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
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ __('affiliate.tier') }}
                                                {{ $commission->tier }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ $commission->commission_rate }}%</td>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ currency($commission->commission_amount) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold {{ $commission->status === 'paid' ? 'bg-green-100 text-green-800' : ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($commission->status) }}
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
                    <div class="bg-white rounded-lg shadow border border-gray-200 mb-8">
                        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('affiliate.request_payout') }}</h3>
                        </div>
                        <div class="p-6">
                            <form action="{{ route('affiliate.payouts.request') }}" method="POST" id="payout-form">
                                @csrf
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="amount"
                                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.amount') }}
                                            <span class="text-red-500">*</span></label>
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
                                        <label for="payout_method"
                                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.payout_method') }}
                                            <span class="text-red-500">*</span></label>
                                        <select name="payout_method" id="payout_method" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                            <option value="wallet">{{ __('affiliate.payout_methods.wallet') }}</option>
                                            <option value="bank_transfer">
                                                {{ __('affiliate.payout_methods.bank_transfer') }}</option>
                                            <option value="paypal">{{ __('affiliate.payout_methods.paypal') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mt-6 flex items-center justify-end">
                                    <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors font-medium">
                                        {{ __('affiliate.submit_payout_request') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- Recent Payouts -->
                @if ($recentPayouts->count() > 0)
                    <div class="bg-white rounded-lg shadow border border-gray-200">
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
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ currency($payout->amount) }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                {{ __('affiliate.payout_methods.' . $payout->payout_method) }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="px-2 py-1 rounded-full text-xs font-semibold {{ $payout->status === 'completed' ? 'bg-green-100 text-green-800' : ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($payout->status ?? 'pending') }}
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
    </div>

    <!-- Create Link Modal -->
    <div id="create-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.create_link') }}</h3>
            <form action="{{ route('affiliate.links.store') }}" method="POST" class="space-y-4">
                @csrf
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
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('create-link-modal').classList.add('hidden')"
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

    <!-- Edit Link Modal -->
    <div id="edit-link-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.edit_link') }}</h3>
            <form id="edit-link-form" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
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
                        <input type="checkbox" name="is_active" id="edit-is_active" value="1" class="rounded">
                        <span class="text-sm font-medium text-gray-700">{{ __('affiliate.active') }}</span>
                    </label>
                </div>
                <div class="flex items-center justify-end gap-3 mt-6">
                    <button type="button" onclick="document.getElementById('edit-link-modal').classList.add('hidden')"
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

    <!-- Global Landing Page Editor Modal -->
    <div id="edit-landing-global-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('affiliate.landing_page') }}</h3>
            <form id="landing-global-form" method="POST" action="{{ route('affiliate.landing-page.update') }}"
                class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="global-landing-page-slug"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.landing_page_slug') }}</label>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">{{ url('/a') }}/</span>
                        <input type="text" id="global-landing-page-slug" name="slug" placeholder="my-landing-page"
                            value="{{ $userLandingPage?->slug ?? '' }}"
                            class="flex-1 rounded-lg border-gray-300 shadow-sm">
                    </div>
                    <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.slug_hint') }}</p>
                </div>

                <div>
                    <label for="global-landing-page-content"
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.landing_page_content') }}</label>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <textarea id="global-landing-page-content" name="content" rows="10" onchange="updateGlobalLandingPagePreview()"
                            oninput="updateGlobalLandingPagePreview()" placeholder="{{ __('affiliate.landing_page_html_hint') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm">{{ $userLandingPage?->content ?? '' }}</textarea>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.preview') }}</label>
                            <div id="global-landing-page-preview"
                                class="border border-gray-300 rounded-lg p-4 bg-gray-50 overflow-auto h-64 prose prose-sm">
                                {!! $userLandingPage?->content ?? '<p class="text-gray-400">Preview</p>' !!}
                            </div>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ __('affiliate.html_content_allowed') }}</p>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 mb-2">{{ __('affiliate.assign_links') }}</label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-64 overflow-y-auto space-y-2">
                        @forelse ($affiliateLinks as $link)
                            <label class="flex items-center gap-2 p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="affiliate_links[]" value="{{ $link->id }}"
                                    class="rounded" @if ($userLandingPage && $userLandingPage->affiliateLinks->contains($link->id)) checked @endif>
                                <span class="text-sm text-gray-700">{{ $link->name }}</span>
                                <span
                                    class="text-xs text-gray-500 ml-auto">{{ url('/') }}{{ $link->slug }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">{{ __('affiliate.no_links_available') }}</p>
                        @endforelse
                    </div>
                </div>

                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <button type="button"
                        onclick="document.getElementById('edit-landing-global-modal').classList.add('hidden')"
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

    <!-- Promotional Materials Modal -->
    <div id="promotional-materials-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-6">{{ __('affiliate.promotional_materials') }}</h3>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">
                        {{ __('affiliate.create_promotional_material') }}</h4>
                    <form id="promo-materials-form" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" id="promo-link-id" name="link_id" value="">

                        <div>
                            <label for="promo-material-name"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.material_name') }}
                                *</label>
                            <input type="text" id="promo-material-name" name="name" required
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label for="promo-material-type"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.material_type') }}
                                *</label>
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
                            <label for="promo-material-size"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.material_size') }}</label>
                            <select id="promo-material-size" name="size"
                                class="w-full rounded-lg border-gray-300 shadow-sm">
                                <option value="">Select Size</option>
                                <option value="728x90">Leaderboard (728x90)</option>
                                <option value="300x250">Medium Rectangle (300x250)</option>
                                <option value="468x60">Banner (468x60)</option>
                                <option value="custom">Custom</option>
                            </select>
                        </div>

                        <div id="promo-image-field" class="hidden">
                            <label for="promo-material-image"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.banner_image') }}</label>
                            <input type="file" id="promo-material-image" name="image" accept="image/*"
                                class="w-full">
                            <p class="mt-1 text-xs text-gray-500">{{ __('affiliate.max_2mb') }}</p>
                        </div>

                        <div id="promo-code-field" class="hidden">
                            <label for="promo-material-code"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('affiliate.html_code') }}</label>
                            <textarea id="promo-material-code" name="html_code" rows="4" placeholder="<a href='...'>Click here</a>"
                                class="w-full rounded-lg border-gray-300 shadow-sm font-mono text-sm"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">{{ __('affiliate.create') }}</button>
                    </form>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-4">{{ __('affiliate.existing_materials') }}</h4>
                    <div id="existing-materials-list" class="space-y-3 max-h-96 overflow-y-auto">
                        <p class="text-gray-500 text-center py-8">{{ __('affiliate.no_materials') }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 mt-6 pt-6 border-t border-gray-200">
                <button type="button"
                    onclick="document.getElementById('promotional-materials-modal').classList.add('hidden')"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                    {{ __('affiliate.close') }}
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function showComingSoon() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Coming Soon',
                        text: 'This feature will be available soon',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert('Coming Soon');
                }
            }

            function copyLink(url) {
                // Use modern Clipboard API if available, fallback to older method
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        showCopySuccess();
                    }).catch(() => {
                        fallbackCopyToClipboard(url);
                    });
                } else {
                    fallbackCopyToClipboard(url);
                }
            }

            function fallbackCopyToClipboard(text) {
                const textarea = document.createElement('textarea');
                textarea.value = text;
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    showCopySuccess();
                } catch (err) {
                    alert('Failed to copy');
                }
                document.body.removeChild(textarea);
            }

            function showCopySuccess() {
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
            }

            function editLink(linkId) {
                fetch(`/api/affiliate-links/${linkId}`).then(r => r.json()).then(data => {
                    document.getElementById('edit-name').value = data.name || '';
                    document.getElementById('edit-description').value = data.description || '';
                    document.getElementById('edit-destination_url').value = data.destination_url || '';
                    document.getElementById('edit-is_active').checked = data.is_active;
                    document.getElementById('edit-link-form').action = `/affiliate/links/${linkId}`;
                    document.getElementById('edit-link-modal').classList.remove('hidden');
                }).catch(e => {
                    console.error(e);
                    alert('Error loading link');
                });
            }

            function updateGlobalLandingPagePreview() {
                const content = document.getElementById('global-landing-page-content').value;
                document.getElementById('global-landing-page-preview').innerHTML = content ||
                    '<p class="text-gray-400">Preview</p>';
            }

            function managPromotionalMaterials(linkId) {
                document.getElementById('promo-link-id').value = linkId;
                document.getElementById('promo-materials-form').action = `/affiliate/links/${linkId}/promotional-materials`;

                fetch(`/affiliate/links/${linkId}/promotional-materials`).then(r => r.json()).then(materials => {
                    const container = document.getElementById('existing-materials-list');
                    if (materials.length > 0) {
                        container.innerHTML = materials.map(m => `
                            <div class="border border-gray-200 rounded-lg p-4 flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900">${m.name}</h4>
                                    <p class="text-sm text-gray-600 mt-1">${m.type}</p>
                                    <button type="button" onclick="deletePromoMaterial('${m.id}')" class="text-xs text-red-600 hover:text-red-800 mt-2">Delete</button>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-medium ${m.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                    ${m.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-gray-500 text-center py-8">No materials</p>';
                    }
                }).catch(e => console.error(e));

                document.getElementById('promotional-materials-modal').classList.remove('hidden');
            }

            function deletePromoMaterial(materialId) {
                if (!confirm('Are you sure?')) return;
                fetch(`/affiliate/promotional-materials/${materialId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => {
                        if (r.ok) location.reload();
                    }).catch(e => console.error(e));
            }

            function updatePromoMaterialFields() {
                const type = document.getElementById('promo-material-type').value;
                document.getElementById('promo-size-field').classList.toggle('hidden', type !== 'banner');
                document.getElementById('promo-image-field').classList.toggle('hidden', type !== 'banner');
                document.getElementById('promo-code-field').classList.toggle('hidden', type !== 'link' && type !== 'text');
            }
        </script>
    @endpush

@endsection
