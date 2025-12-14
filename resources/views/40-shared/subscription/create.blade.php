@extends('40-shared/layouts/app')

@section('title', __('messages.upgrade_to_premium'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('subscription.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.upgrade_to_premium') }}</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.request_premium_access') }}</p>
        </div>

        <!-- Insufficient Balance Warning -->
        @if(session('insufficient_balance') || session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        @if(session('insufficient_balance'))
                            <div class="mt-3">
                                <a href="{{ route('wallet.index') }}" class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ __('messages.top_up') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <!-- Pricing Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mb-4">
                        {{ __('messages.premium_plan') }}
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('messages.upgrade_now') }}</h2>
                    <p class="text-base text-gray-600 mb-2">{{ __('messages.get_all_premium_features') }}</p>
                    <p class="text-2xl font-bold text-green-600">{{ currency($premiumPrice) }}</p>
                </div>

                <!-- Wallet Balance Info -->
                <div class="mb-6 bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ __('messages.wallet_balance_title') }}</p>
                            <p class="text-2xl font-bold {{ $wallet->balance >= $premiumPrice ? 'text-green-600' : 'text-red-600' }}">
                                {{ currency($wallet->balance) }}
                            </p>
                        </div>
                        <div class="text-right">
                            @if($wallet->balance >= $premiumPrice)
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('messages.sufficient_balance') }}
                                </div>
                            @else
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                    {{ __('messages.insufficient_balance') }}
                                </div>
                                <a href="{{ route('wallet.index') }}" class="mt-2 inline-flex items-center text-sm font-semibold text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                    {{ __('messages.top_up') }} →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <form action="{{ route('subscription.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-blue-900 mb-1">{{ __('messages.payment_info') }}</h3>
                                <p class="text-sm text-blue-800">{{ __('messages.payment_will_be_deducted_from_wallet') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('subscription.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" 
                            class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            {{ $wallet->balance < $premiumPrice ? 'disabled' : '' }}>
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('messages.activate_premium') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


