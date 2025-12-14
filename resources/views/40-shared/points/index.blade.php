@extends('40-shared/layouts/app')

@section('title', 'Points & Rewards')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Points & Rewards</h1>
            <p class="mt-2 text-base text-gray-600">Earn points from activities and redeem them for discounts or premium features!</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Available Points</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_points']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Earned</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_earned']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Redeemed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_redeemed']) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Expiring Soon</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['expiring_soon']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Next 30 days</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redemption Options -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Redeem Points</h2>
                <p class="text-sm text-gray-600 mt-1">Convert your points into discounts or premium features</p>
            </div>
            <div class="p-6">
                <!-- Discount Redemptions -->
                <div class="mb-8">
                    <h3 class="text-md font-semibold text-gray-900 mb-4">Discount Codes</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($redemptionOptions['discounts'] as $option)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition-colors">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-bold text-gray-900">{{ currency($option['discount_amount']) }}</span>
                                    <span class="text-sm text-gray-600">{{ number_format($option['points']) }} pts</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $option['label'] }}</p>
                                <form action="{{ route('points.redeem-discount') }}" method="POST" onsubmit="return confirm('Redeem {{ number_format($option['points']) }} points for {{ currency($option['discount_amount']) }} discount?');">
                                    @csrf
                                    <input type="hidden" name="points" value="{{ $option['points'] }}">
                                    <input type="hidden" name="discount_amount" value="{{ $option['discount_amount'] }}">
                                    <button type="submit" 
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors {{ $stats['total_points'] < $option['points'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $stats['total_points'] < $option['points'] ? 'disabled' : '' }}>
                                        Redeem
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Premium Redemptions -->
                <div>
                    <h3 class="text-md font-semibold text-gray-900 mb-4">Premium Access</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($redemptionOptions['premium'] as $option)
                            <div class="border border-gray-200 rounded-lg p-4 hover:border-yellow-300 transition-colors">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-lg font-bold text-gray-900">{{ $option['premium_days'] }} Days</span>
                                    <span class="text-sm text-gray-600">{{ number_format($option['points']) }} pts</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-3">{{ $option['label'] }}</p>
                                <form action="{{ route('points.redeem-premium') }}" method="POST" onsubmit="return confirm('Redeem {{ number_format($option['points']) }} points for {{ $option['premium_days'] }} days premium access?');">
                                    @csrf
                                    <input type="hidden" name="points" value="{{ $option['points'] }}">
                                    <input type="hidden" name="premium_days" value="{{ $option['premium_days'] }}">
                                    <button type="submit" 
                                        class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors {{ $stats['total_points'] < $option['points'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                                        {{ $stats['total_points'] < $option['points'] ? 'disabled' : '' }}>
                                        Redeem
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Points History -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Points History</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($points as $point)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $point->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $point->action)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $point->points > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $point->points > 0 ? '+' : '' }}{{ number_format($point->points) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($point->is_redeemed)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">Redeemed</span>
                                        @elseif($point->isExpired())
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Expired</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($point->expires_at)
                                            {{ $point->expires_at->format('M d, Y') }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No points history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $points->links() }}
                </div>
            </div>
        </div>

        <!-- Redemption History -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Redemption History</h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points Used</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reward</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($redemptions as $redemption)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $redemption->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $redemption->redemption_type)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-600">
                                        -{{ number_format($redemption->points_used) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($redemption->redemption_type === 'discount')
                                            @if($redemption->redemption_code)
                                                <span class="font-mono">{{ $redemption->redemption_code }}</span>
                                            @else
                                                {{ currency($redemption->discount_amount ?? 0) }}
                                            @endif
                                        @elseif($redemption->redemption_type === 'premium_feature')
                                            {{ $redemption->premium_days ?? 0 }} days
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($redemption->status === 'active')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                        @elseif($redemption->status === 'used')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Used</span>
                                        @elseif($redemption->status === 'expired')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Expired</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($redemption->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">No redemptions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $redemptions->links() }}
                </div>
            </div>
        </div>

        <!-- How to Earn Points -->
        <div class="mt-8 bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">How to Earn Points</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li><strong>Purchase Notes:</strong> Get {{ \App\Models\Setting::getSetting('points_purchase', 'points', 10) }} points for every purchase</li>
                <li><strong>Write Reviews:</strong> Earn {{ \App\Models\Setting::getSetting('points_review', 'points', 5) }} points for each review you write</li>
                <li><strong>Share Notes:</strong> Receive {{ \App\Models\Setting::getSetting('points_share', 'points', 3) }} points when you share a note</li>
            </ul>
        </div>
    </div>
</div>
@endsection

