@extends('layouts.app')

@section('title', __('messages.my_wallet'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $baseCurrency = $currencyService->getBaseCurrency();
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $walletCurrency = $wallet->currency ?? $baseCurrency;
    $currencyInfo = \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency);
    $topupMinBase = 10000;
    $topupMaxBase = 100000000;
    $topupMinDisplay = $currencyService->convert($topupMinBase, $baseCurrency, $userCurrency);
    $topupMaxDisplay = $currencyService->convert($topupMaxBase, $baseCurrency, $userCurrency);
    $withdrawMinBase = 50000;
    $withdrawMinDisplay = $currencyService->convert($withdrawMinBase, $baseCurrency, $userCurrency);
    $decimalPlaces = $currencyInfo['decimal_places'] ?? 0;
    $stepValue = $decimalPlaces > 0 ? 1 / (10 ** $decimalPlaces) : 1;
    $stepAttribute = number_format($stepValue, $decimalPlaces, '.', '');
    $minAttribute = number_format($topupMinDisplay, $decimalPlaces, '.', '');
    $maxAttribute = number_format($topupMaxDisplay, $decimalPlaces, '.', '');
    $currencySymbol = $currencyInfo['symbol'] ?? '';
@endphp
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.my_wallet') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.manage_balance_view_history') }}</p>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Wallet Balance Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="px-6 py-8">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex-shrink-0 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg p-3">
                                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-medium text-gray-500 mb-1">{{ __('messages.wallet_balance_title') }}</h3>
                                <p class="text-4xl font-bold text-green-600">
                                    {{ currency($wallet->balance, $userCurrency, $walletCurrency) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <form action="{{ route('wallet.topup') }}" method="POST" class="flex gap-2 w-full sm:w-auto">
                            @csrf
                            <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 text-sm">{{ $currencySymbol }}</span>
                            </div>
                            <input
                                type="number"
                                name="amount"
                                min="{{ $minAttribute }}"
                                max="{{ $maxAttribute }}"
                                step="{{ $stepAttribute }}"
                                value="{{ old('amount') }}"
                                placeholder="{{ __('messages.enter_amount') }}"
                                required
                                class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('messages.minimum_topup_amount', ['amount' => currency($topupMinBase, $userCurrency, $baseCurrency)]) }}
                            </p>
                            </div>
                            <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200 whitespace-nowrap">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                {{ __('messages.top_up') }}
                            </button>
                        </form>
                        @if($wallet->balance >= 50000)
                            <a href="{{ route('wallet.withdraw.create') }}" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ __('messages.withdraw') }}
                            </a>
                        @else
                            <button type="button" disabled title="{{ __('messages.minimum_withdraw', ['amount' => currency($withdrawMinBase, $userCurrency, $baseCurrency)]) }}" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-lg text-white bg-gray-400 cursor-not-allowed shadow-sm transition-all duration-200 opacity-60">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                {{ __('messages.withdraw') }}
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.transaction_history') }}</h2>
            </div>
            <div class="p-6">
                @if($transactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.date') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.type') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.description') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.amount') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($transactions as $transaction)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                {{ $transaction->created_at->format('d M Y, H:i') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($transaction->buyer_id === auth()->id() && $transaction->seller_id === auth()->id())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Top-up
                                                </span>
                                            @elseif($transaction->buyer_id === auth()->id())
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Purchase
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Sale
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            @if($transaction->note)
                                                <a href="{{ route('marketplace.show', $transaction->note) }}" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors duration-200 font-medium">
                                                    {{ $transaction->note->title }}
                                                </a>
                                            @else
                                                <span class="text-gray-600">{{ $transaction->notes ?? '-' }}</span>
                                            @endif
                                            @if($transaction->buyer_id !== auth()->id() && $transaction->seller_id === auth()->id())
                                                <p class="text-xs text-gray-500 mt-1">Buyer: {{ $transaction->buyer->name }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $transactionCurrency = $transaction->currency ?? $baseCurrency;
                                                $formattedAmount = currency($transaction->amount, $userCurrency, $transactionCurrency);
                                            @endphp
                                            @if($transaction->buyer_id === auth()->id() && $transaction->seller_id === auth()->id())
                                                <span class="text-sm font-bold text-green-600">+{{ $formattedAmount }}</span>
                                            @elseif($transaction->buyer_id === auth()->id())
                                                <span class="text-sm font-bold text-red-600">-{{ $formattedAmount }}</span>
                                            @else
                                                @php
                                                    $netAmount = currency($transaction->amount - $transaction->commission, $userCurrency, $transactionCurrency);
                                                    $commissionAmount = currency($transaction->commission, $userCurrency, $transactionCurrency);
                                                @endphp
                                                <div>
                                                    <span class="text-sm font-bold text-green-600">+{{ $netAmount }}</span>
                                                    <p class="text-xs text-gray-500">Commission: {{ $commissionAmount }}</p>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($transaction->status === 'success')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Success
                                                </span>
                                            @elseif($transaction->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    Pending
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Failed
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $transactions->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_transactions_yet') }}</h3>
                        <p class="mt-2 text-sm text-gray-500">Start by topping up your wallet or purchasing notes from the marketplace.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
