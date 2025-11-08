@extends('layouts.app')

@section('title', __('messages.withdraw_wallet'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $baseCurrency = $currencyService->getBaseCurrency();
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $walletCurrency = $wallet->currency ?? $baseCurrency;
    $currencyInfo = \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency);
    $decimalPlaces = $currencyInfo['decimal_places'] ?? 0;
    $currencySymbol = $currencyInfo['symbol'] ?? '';
    $withdrawMinBase = 50000;
    $withdrawMinDisplay = currency($withdrawMinBase, $userCurrency, $baseCurrency);
    $walletBalanceDisplay = currency($wallet->balance, $userCurrency, $walletCurrency);
    $minInputValue = number_format($currencyService->convert($withdrawMinBase, $baseCurrency, $userCurrency), $decimalPlaces, '.', '');
    $maxInputValue = number_format($currencyService->convert($wallet->balance, $walletCurrency, $userCurrency), $decimalPlaces, '.', '');
    $stepValue = $decimalPlaces > 0 ? 1 / (10 ** $decimalPlaces) : 1;
    $stepAttribute = number_format($stepValue, $decimalPlaces, '.', '');
@endphp
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('wallet.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_wallet') }}
            </a>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.withdraw_wallet') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.request_withdrawal_bank') }}</p>
        </div>

        @if($wallet->balance < 50000)
            <!-- Insufficient Balance Warning -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-r-lg mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-base font-medium text-yellow-800 mb-2">{{ __('messages.insufficient_balance') }}</h3>
                        <p class="text-sm text-yellow-700 mb-3">{{ __('messages.minimum_withdraw', ['amount' => $withdrawMinDisplay]) }}</p>
                        <p class="text-sm font-medium text-gray-900">{{ __('messages.your_current_balance') }}:
                            <span class="text-yellow-700">{{ $walletBalanceDisplay }}</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <a href="{{ route('wallet.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_wallet') }}
                </a>
            </div>
        @else
            <!-- Withdraw Form -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6">
                    <!-- Info Box -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-blue-800">{{ __('messages.available_balance') }}</p>
                                <p class="mt-1 text-2xl font-bold text-blue-900">{{ $walletBalanceDisplay }}</p>
                                <p class="mt-2 text-xs text-blue-700">
                                    <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('messages.minimum_withdraw_info', ['amount' => $withdrawMinDisplay]) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Errors -->
                    @if($errors->any())
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800 mb-2">{{ __('messages.please_correct_errors') }}</h3>
                                    <ul class="list-disc list-inside text-sm text-red-700">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('wallet.withdraw.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Amount -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.withdrawal_amount') }} ({{ $currencySymbol ?: $userCurrency }}) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">{{ $currencySymbol }}</span>
                                </div>
                                <input type="number" name="amount" id="amount" 
                                    value="{{ old('amount') }}"
                                    min="{{ $minInputValue }}" 
                                    max="{{ $maxInputValue }}" 
                                    step="{{ $stepAttribute }}" 
                                    required
                                    :placeholder="__('messages.enter_withdrawal_amount')"
                                    class="block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('amount') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            </div>
                            @error('amount')
                                <p class="mt-2 text-sm text-red-600 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="mt-2 text-xs text-gray-500">
                                {{ __('messages.range') }}: {{ $withdrawMinDisplay }} - {{ $walletBalanceDisplay }}
                            </p>
                        </div>

                        <!-- Bank Details -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.bank_name') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bank_name" id="bank_name" 
                                    value="{{ old('bank_name', $user->bank_name ?? '') }}"
                                    placeholder="e.g., BCA, Mandiri, BRI"
                                    required
                                    maxlength="100"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('bank_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('bank_name')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.account_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="account_number" id="account_number" 
                                    value="{{ old('account_number', $user->bank_account_number ?? '') }}"
                                    placeholder="Account number"
                                    required
                                    maxlength="50"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('account_number') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('account_number')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.account_name') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="account_name" id="account_name" 
                                value="{{ old('account_name', $user->bank_account_name ?? '') }}"
                                    :placeholder="__('messages.name_as_appears_bank')"
                                required
                                maxlength="100"
                                class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('account_name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('account_name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Warning Info -->
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-700">
                                        {{ __('messages.please_double_check_bank') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                            <a href="{{ route('wallet.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                {{ __('messages.cancel') }}
                            </a>
                            <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('messages.request_withdraw') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
