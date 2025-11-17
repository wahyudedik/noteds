@extends('layouts.app')

@section('title', __('messages.repurchase_statistics_report'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.repurchase_statistics_report') }}</h2>
                <p class="text-sm text-gray-600 mt-1">{{ __('messages.repurchase_statistics_description') }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_dashboard') }}</a>
        </div>

        <!-- Date Filter -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.repurchase-report') }}" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.start_date') }}</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.end_date') }}</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                        {{ __('messages.filter') }}
                    </button>
                </div>
                <div>
                    <a href="{{ route('admin.repurchase-report') }}" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md">
                        {{ __('messages.reset') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.total_repurchases') }}</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_repurchases']) }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ __('messages.repurchase_rate') }}: {{ number_format($stats['repurchase_rate'], 2) }}%</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.total_revenue') }}</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ currency($stats['total_revenue']) }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ __('messages.from_repurchases') }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.average_time') }}</div>
                <div class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($stats['avg_time_days'], 1) }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ __('messages.days_to_repurchase') }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.average_price') }}</div>
                <div class="text-3xl font-bold text-purple-600 mt-2">{{ currency($stats['avg_price']) }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ __('messages.per_repurchase') }}</div>
            </div>
        </div>

        <!-- Grace Period Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-green-900">{{ __('messages.within_grace_period') }}</h3>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ number_format($stats['within_grace_period']) }} {{ __('messages.repurchases') }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-green-900 mb-2">
                    {{ currency($stats['within_grace_period_revenue']) }}
                </div>
                <div class="text-sm text-green-700">
                    {{ __('messages.revenue_from_repurchases_at_original_price') }}
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-yellow-900">{{ __('messages.after_grace_period') }}</h3>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                        {{ number_format($stats['after_grace_period']) }} {{ __('messages.repurchases') }}
                    </span>
                </div>
                <div class="text-2xl font-bold text-yellow-900 mb-2">
                    {{ currency($stats['after_grace_period_revenue']) }}
                </div>
                <div class="text-sm text-yellow-700">
                    {{ __('messages.revenue_from_repurchases_at_premium_price') }}
                </div>
            </div>
        </div>

        <!-- Top Notes by Repurchases -->
        @if($repurchasesByNote->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.top_notes_by_repurchases') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.note') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.repurchases') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.total_revenue') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.total_amount') }}</th>
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
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.top_buyers_by_repurchases') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.buyer') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.repurchases') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.total_spent') }}</th>
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
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.all_repurchase_transactions') }}</h3>
            @if($repurchaseTransactions->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.note') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.buyer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.seller') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">{{ __('messages.total_revenue') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('messages.grace_period') }}</th>
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
                                                {{ __('messages.within') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                {{ __('messages.after') }}
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
                    <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_repurchases_found') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.no_repurchase_transactions_found') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

