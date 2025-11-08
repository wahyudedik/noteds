@extends('layouts.app')

@section('title', __('messages.admin_transactions'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.transactions') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_dashboard') }}</a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.total_revenue') }}</div>
                <div class="text-2xl font-bold text-green-600">{{ currency($totalRevenue) }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">{{ __('messages.total_transaction_value') }}</div>
                <div class="text-2xl font-bold text-blue-600">{{ currency($totalTransactions) }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.transactions.index') }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" :placeholder="__('messages.search_buyer_seller')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>{{ __('messages.success') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>{{ __('messages.failed') }}</option>
                </select>
                <select name="payment_method" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_payment_methods') }}</option>
                    <option value="wallet" {{ request('payment_method') === 'wallet' ? 'selected' : '' }}>{{ __('messages.wallet') }}</option>
                    <option value="withdraw" {{ request('payment_method') === 'withdraw' ? 'selected' : '' }}>{{ __('messages.withdraw') }}</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.filter') }}
                </button>
                @if(request()->hasAny(['search', 'status', 'payment_method']))
                    <a href="{{ route('admin.transactions.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('messages.clear') }}
                    </a>
                @endif
            </form>
        </div>

        @if($transactions->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.buyer') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.seller') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.note') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.commission') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.method') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->buyer->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $transaction->seller->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($transaction->note)
                                            {{ Str::limit($transaction->note->title, 30) }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{ currency($transaction->amount, null, $transaction->currency ?? config('currency.base_currency')) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        {{ currency($transaction->commission, null, $transaction->currency ?? config('currency.base_currency')) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaction->payment_method ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($transaction->status === 'success')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.success') }}</span>
                                        @elseif($transaction->status === 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.failed') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    {{ $transactions->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">{{ __('messages.no_transactions_found') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

