@extends('layouts.app')

@section('title', 'Repurchase Statistics Report')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Repurchase Statistics Report</h2>
                <p class="text-sm text-gray-600 mt-1">Detailed analytics for note repurchases in Scarcity Mode</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
        </div>

        <!-- Date Filter -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.repurchase-report') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                        Filter
                    </button>
                </div>
                <div>
                    <a href="{{ route('admin.repurchase-report') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">Total Repurchases</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_repurchases']) }}</div>
                <div class="text-xs text-gray-600 mt-1">Repurchase Rate: {{ number_format($stats['repurchase_rate'], 2) }}%</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">Total Revenue</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ currency($stats['total_revenue']) }}</div>
                <div class="text-xs text-gray-600 mt-1">From repurchases</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">Average Time</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($stats['avg_time_days'], 1) }}</div>
                <div class="text-xs text-gray-600 mt-1">Days to repurchase</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">Average Price</div>
                <div class="text-3xl font-bold text-purple-600 mt-2">{{ currency($stats['avg_price']) }}</div>
                <div class="text-xs text-gray-600 mt-1">Per repurchase</div>
            </div>
        </div>

        <!-- Grace Period Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-green-900">Within Grace Period</h3>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ number_format($stats['within_grace_period']) }} repurchases
                    </span>
                </div>
                <div class="text-2xl font-bold text-green-900 mb-2">
                    {{ currency($stats['within_grace_period_revenue']) }}
                </div>
                <div class="text-sm text-green-700">
                    Revenue from repurchases at original price
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-yellow-900">After Grace Period</h3>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ number_format($stats['after_grace_period']) }} repurchases
                    </span>
                </div>
                <div class="text-2xl font-bold text-yellow-900 mb-2">
                    {{ currency($stats['after_grace_period_revenue']) }}
                </div>
                <div class="text-sm text-yellow-700">
                    Revenue from repurchases at premium price
                </div>
            </div>
        </div>

        <!-- Top Notes by Repurchases -->
        @if($repurchasesByNote->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Notes by Repurchases</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Repurchases</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($repurchasesByNote as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($item['note']->title, 50) }}</div>
                                        <div class="text-xs text-gray-500">by {{ $item['note']->user->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $item['count'] }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-green-600">{{ currency($item['revenue']) }}</td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900">{{ currency($item['total_amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Top Buyers by Repurchases -->
        @if($repurchasesByBuyer->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top Buyers by Repurchases</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Repurchases</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($repurchasesByBuyer as $item)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $item['buyer']->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item['buyer']->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900">{{ $item['count'] }}</td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-blue-600">{{ currency($item['total_spent']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Repurchase Transactions List -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">All Repurchase Transactions</h3>
            @if($repurchaseTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Grace Period</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($repurchaseTransactions as $transaction)
                                @php
                                    $firstPurchase = \App\Models\Transaction::where('note_id', $transaction->note_id)
                                        ->where('buyer_id', $transaction->buyer_id)
                                        ->where('status', 'success')
                                        ->where('id', '<', $transaction->id)
                                        ->orderBy('created_at', 'asc')
                                        ->first();
                                    
                                    $isWithinGracePeriod = false;
                                    if ($firstPurchase && $firstPurchase->grace_period_ends_at) {
                                        $isWithinGracePeriod = $transaction->created_at->lte($firstPurchase->grace_period_ends_at);
                                    }
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($transaction->note->title, 40) }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $transaction->buyer->name }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ $transaction->seller->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        {{ currency($transaction->amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                                        {{ currency($transaction->platform_fee) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @if($isWithinGracePeriod)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Within
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                After
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No repurchases found</h3>
                    <p class="mt-1 text-sm text-gray-500">No repurchase transactions found for the selected date range.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

