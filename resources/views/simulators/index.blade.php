@extends('layouts.app')

@section('title', __('messages.marketing_simulators'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $simulatorCurrency = $currencyService->getUserCurrency(auth()->user());
    $localeMap = [
        'IDR' => 'id-ID',
        'USD' => 'en-US',
    ];
    $simulatorLocale = $localeMap[$simulatorCurrency] ?? 'en-US';
@endphp
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('messages.marketing_simulators') }}</h1>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                {{ __('messages.calculate_potential_earnings') }}
            </p>
        </div>

        <!-- Simulator Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            
            <!-- Earnings Calculator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.earnings_calculator') }}</h3>
                            <p class="text-blue-100 text-sm">{{ __('messages.calculate_seller_earnings') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <form id="earnings-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.note_price') }}</label>
                            <input type="number" id="earnings-price" name="price" value="50000" min="0" step="1000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.sales_per_month') }}</label>
                            <input type="number" id="earnings-sales" name="sales" value="10" min="0" step="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <button type="button" id="earnings-calculate" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                            {{ __('messages.calculate_earnings') }}
                        </button>
                    </form>
                    <div id="earnings-result" class="mt-6 hidden">
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-green-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-green-800 mb-2">{{ __('messages.your_potential_earnings') }}</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">{{ __('messages.monthly') }}:</span>
                                            <span class="font-bold text-green-700" id="earnings-monthly"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">{{ __('messages.yearly') }}:</span>
                                            <span class="font-bold text-green-700" id="earnings-yearly"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">{{ __('messages.after_commission') }}:</span>
                                            <span class="font-bold text-green-700" id="earnings-net"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Referral ROI Calculator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.referral_roi') }}</h3>
                            <p class="text-purple-100 text-sm">{{ __('messages.track_referral_earnings') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <form id="referral-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.total_referrals') }}</label>
                            <input type="number" id="referral-count" name="count" value="20" min="0" step="1"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.avg_transaction') }}</label>
                            <input type="number" id="referral-transaction" name="transaction" value="50000" min="0" step="1000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        </div>
                        <button type="button" id="referral-calculate" 
                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                            {{ __('messages.calculate_referral_roi') }}
                        </button>
                    </form>
                    <div id="referral-result" class="mt-6 hidden">
                        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-purple-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h4 class="text-lg font-bold text-purple-800 mb-2">{{ __('messages.your_referral_rewards') }}</h4>
                                    <div class="space-y-2">
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">{{ __('messages.signup_rewards') }}:</span>
                                            <span class="font-bold text-purple-700" id="referral-signup"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-700">{{ __('messages.transaction_commissions') }}:</span>
                                            <span class="font-bold text-purple-700" id="referral-commission"></span>
                                        </div>
                                        <div class="flex justify-between border-t-2 border-purple-200 pt-2">
                                            <span class="text-gray-700">{{ __('messages.total_monthly') }}:</span>
                                            <span class="font-bold text-purple-800" id="referral-total"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Premium vs Basic Comparison -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.plan_comparison') }}</h3>
                            <p class="text-green-100 text-sm">{{ __('messages.compare_features') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-green-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Basic Plan -->
                        <div class="border-2 border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-lg font-bold text-gray-900">{{ __('messages.basic_plan') }}</h4>
                                <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">{{ __('messages.free') }}</span>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">10 {{ __('messages.notes_total') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ __('messages.standard_support') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ __('messages.public_marketplace') }}</span>
                                </div>
                            </div>
                            <a href="{{ route('register') }}" class="block w-full bg-gray-600 hover:bg-gray-700 text-white text-center font-semibold py-2 rounded-lg transition-all duration-200">
                                {{ __('messages.get_started') }}
                            </a>
                        </div>

                        <!-- Premium Plan -->
                        <div class="border-2 border-green-500 rounded-lg p-4 bg-gradient-to-br from-green-50 to-transparent relative">
                            <div class="absolute top-0 right-0 bg-green-500 text-white px-3 py-1 rounded-bl-lg rounded-tr-lg text-xs font-bold">
                                {{ __('messages.best_value') }}
                            </div>
                            <div class="flex items-center justify-between mb-3 mt-2">
                                <h4 class="text-lg font-bold text-gray-900">{{ __('messages.premium_plan') }}</h4>
                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">{{ \App\Models\Setting::formatPremiumPrice(true) }}</span>
                            </div>
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">{{ __('messages.unlimited_notes') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">{{ __('messages.priority_support') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">{{ __('messages.featured_in_marketplace') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">{{ __('messages.advanced_analytics') }}</span>
                                </div>
                            </div>
                            {{-- Subscription removed - all features are now free --}}
                            {{-- <a href="{{ route('subscription.create') }}" class="block w-full bg-green-600 hover:bg-green-700 text-white text-center font-semibold py-2 rounded-lg transition-all duration-200">
                                {{ __('messages.upgrade_now') }}
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Second Row: New Simulators -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            
            <!-- Wallet Simulator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.wallet_simulator') }}</h3>
                            <p class="text-indigo-100 text-sm">{{ __('messages.track_balance_transactions') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <div class="bg-indigo-50 rounded-lg p-4 text-center">
                            <p class="text-sm text-gray-600 mb-1">{{ __('messages.current_balance') }}</p>
                            <p class="text-3xl font-bold text-indigo-700" id="wallet-balance">{{ currency(0) }}</p>
                        </div>
                    </div>
                    <form id="wallet-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.topup_amount') }}</label>
                            <input type="number" id="wallet-topup" name="topup" value="100000" min="10000" step="10000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.withdraw_amount') }}</label>
                            <input type="number" id="wallet-withdraw" name="withdraw" value="50000" min="10000" step="10000"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="wallet-topup-btn" 
                                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 rounded-lg transition-all duration-200">
                                {{ __('messages.topup') }}
                            </button>
                            <button type="button" id="wallet-withdraw-btn" 
                                class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 rounded-lg transition-all duration-200">
                                {{ __('messages.withdraw') }}
                            </button>
                        </div>
                    </form>
                    <div id="wallet-history" class="mt-6 space-y-2 max-h-40 overflow-y-auto">
                        <p class="text-xs text-gray-500 text-center">{{ __('messages.transaction_history_will_appear') }}</p>
                    </div>
                </div>
            </div>

            <!-- Marketplace Preview Demo -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-pink-500 to-pink-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.marketplace_preview') }}</h3>
                            <p class="text-pink-100 text-sm">{{ __('messages.explore_marketplace_features') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-pink-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors cursor-pointer" onclick="window.location.href='{{ route('marketplace.index') }}'">
                            <div class="flex items-start justify-between mb-2">
                                <h4 class="font-semibold text-gray-900">{{ __('messages.sample_note_title') }}</h4>
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs font-medium">{{ __('messages.free') }}</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-2">{{ __('messages.preview_marketplace_notes') }}</p>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="text-sm text-gray-600 ml-1">4.5</span>
                                    </div>
                                    <span class="text-xs text-gray-500">•</span>
                                    <span class="text-xs text-gray-500">{{ __('messages.reviews_with_count', ['count' => 15]) }}</span>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">{{ currency(0) }}</span>
                            </div>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-blue-800">
                                <strong>{{ __('messages.marketplace_features') }}</strong> {{ __('messages.marketplace_features_desc') }}
                            </p>
                        </div>
                        <a href="{{ route('marketplace.index') }}" class="block w-full bg-pink-600 hover:bg-pink-700 text-white text-center font-semibold py-2 rounded-lg transition-all duration-200">
                            {{ __('messages.explore_marketplace') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Transaction Flow Simulator -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.transaction_flow') }}</h3>
                            <p class="text-orange-100 text-sm">{{ __('messages.visualize_payment_process') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-orange-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4" id="transaction-flow">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                <span class="text-orange-600 font-bold text-sm">1</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ __('messages.select_note') }}</p>
                                <p class="text-xs text-gray-500">{{ __('messages.choose_note_to_purchase') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 opacity-50" id="flow-step-2">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 font-bold text-sm">2</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">{{ __('messages.checkout') }}</p>
                                <p class="text-xs text-gray-500">{{ __('messages.review_order_details') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 opacity-50" id="flow-step-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 font-bold text-sm">3</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">{{ __('messages.payment') }}</p>
                                <p class="text-xs text-gray-500">{{ __('messages.secure_payment_via_midtrans') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 opacity-50" id="flow-step-4">
                            <div class="flex-shrink-0 w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-gray-600 font-bold text-sm">4</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">{{ __('messages.complete') }}</p>
                                <p class="text-xs text-gray-500">{{ __('messages.access_granted_to_note') }}</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="flow-simulate" 
                        class="mt-6 w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                        {{ __('messages.simulate_transaction') }}
                    </button>
                </div>
            </div>

            <!-- Price Benchmark Tool -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-violet-500 to-violet-600 p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">{{ __('messages.price_benchmark') }}</h3>
                            <p class="text-violet-100 text-sm">{{ __('messages.compare_note_prices') }}</p>
                        </div>
                        <svg class="w-12 h-12 text-violet-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.your_note_price') }}</label>
                        <input type="number" id="benchmark-price" value="50000" min="0" step="1000"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-2 focus:ring-violet-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.category') }}</label>
                        <select id="benchmark-category" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-violet-500 focus:ring-2 focus:ring-violet-500">
                            <option value="">{{ __('messages.all_categories') }}</option>
                            <option value="tutorial">{{ __('messages.tutorial') }}</option>
                            <option value="template">{{ __('messages.template') }}</option>
                            <option value="guide">{{ __('messages.guide') }}</option>
                            <option value="resource">{{ __('messages.resource') }}</option>
                        </select>
                    </div>
                    <button type="button" id="benchmark-calculate" 
                        class="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 rounded-lg transition-all duration-200">
                        {{ __('messages.compare_note_prices') }}
                    </button>
                    <div id="benchmark-result" class="mt-6 hidden">
                        <div class="bg-violet-50 border-l-4 border-violet-500 p-4 rounded-r-lg">
                            <h4 class="text-lg font-bold text-violet-800 mb-3">{{ __('messages.market_analysis') }}</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-700">{{ __('messages.market_average') }}</span>
                                    <span class="font-bold text-gray-900" id="benchmark-avg"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-700">{{ __('messages.your_price') }}</span>
                                    <span class="font-bold text-violet-700" id="benchmark-yours"></span>
                                </div>
                                <div class="flex justify-between border-t-2 border-violet-200 pt-2">
                                    <span class="text-gray-700">{{ __('messages.position') }}</span>
                                    <span class="font-bold" id="benchmark-position"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA Section -->
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-12 text-center text-white">
            <h2 class="text-3xl font-bold mb-4">{{ __('messages.simulator_ready_to_start') }}</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Join thousands of creators sharing knowledge and monetizing their expertise on Noteds
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-lg hover:shadow-xl">
                    Create Free Account
                </a>
                <a href="{{ route('marketplace.index') }}" 
                    class="inline-flex items-center justify-center px-8 py-4 bg-transparent border-2 border-white text-white font-bold rounded-lg hover:bg-white hover:text-blue-600 transition-all duration-200">
                    Browse Marketplace
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translation strings for JavaScript
    const translations = {
        simulateTransaction: @json(__('messages.simulate_transaction')),
        transactionComplete: @json(__('messages.transaction_complete')),
        processing: @json(__('messages.processing')),
        generating: @json(__('messages.generating')),
        insufficientBalance: @json(__('messages.insufficient_balance')),
        topup: @json(__('messages.topup')),
        withdraw: @json(__('messages.withdraw')),
        marketAnalysis: @json(__('messages.market_analysis')),
        marketAverageLabel: @json(__('messages.market_average')),
        yourPrice: @json(__('messages.your_price')),
        position: @json(__('messages.position')),
        belowMarket: @json(__('messages.below_market')),
        aboveMarket: @json(__('messages.above_market')),
        marketAverageCompetitive: @json(__('messages.market_average_competitive')),
    };
    
    const currencySettings = {
        locale: @json($simulatorLocale),
        currency: @json($simulatorCurrency),
    };

    // Format number to active currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat(currencySettings.locale, {
            style: 'currency',
            currency: currencySettings.currency,
            minimumFractionDigits: 0
        }).format(amount);
    }

    // Earnings Calculator
    const earningsForm = document.getElementById('earnings-calculate');
    earningsForm.addEventListener('click', function() {
        const price = parseFloat(document.getElementById('earnings-price').value) || 0;
        const sales = parseFloat(document.getElementById('earnings-sales').value) || 0;
        
        const grossMonthly = price * sales;
        const grossYearly = grossMonthly * 12;
        const commission = grossMonthly * 0.20; // 20% platform commission
        const netMonthly = grossMonthly - commission;
        
        document.getElementById('earnings-monthly').textContent = formatCurrency(grossMonthly);
        document.getElementById('earnings-yearly').textContent = formatCurrency(grossYearly);
        document.getElementById('earnings-net').textContent = formatCurrency(netMonthly) + '/mo';
        document.getElementById('earnings-result').classList.remove('hidden');
    });

    // Referral ROI Calculator
    const referralForm = document.getElementById('referral-calculate');
    referralForm.addEventListener('click', function() {
        const count = parseFloat(document.getElementById('referral-count').value) || 0;
        const avgTransaction = parseFloat(document.getElementById('referral-transaction').value) || 0;
        
        // Signup reward: 5,000 base currency per referral
        const signupReward = count * 5000;
        
        // Transaction commission: 5% of each transaction
        const commissionPerTransaction = avgTransaction * 0.05;
        const totalCommission = count * commissionPerTransaction;
        
        const totalRewards = signupReward + totalCommission;
        
        document.getElementById('referral-signup').textContent = formatCurrency(signupReward);
        document.getElementById('referral-commission').textContent = formatCurrency(totalCommission);
        document.getElementById('referral-total').textContent = formatCurrency(totalRewards);
        document.getElementById('referral-result').classList.remove('hidden');
    });

    // Auto-calculate on input change
    document.getElementById('earnings-price').addEventListener('input', function() {
        if (document.getElementById('earnings-price').value && document.getElementById('earnings-sales').value) {
            earningsForm.click();
        }
    });
    document.getElementById('earnings-sales').addEventListener('input', function() {
        if (document.getElementById('earnings-price').value && document.getElementById('earnings-sales').value) {
            earningsForm.click();
        }
    });
    document.getElementById('referral-count').addEventListener('input', function() {
        if (document.getElementById('referral-count').value && document.getElementById('referral-transaction').value) {
            referralForm.click();
        }
    });
    document.getElementById('referral-transaction').addEventListener('input', function() {
        if (document.getElementById('referral-count').value && document.getElementById('referral-transaction').value) {
            referralForm.click();
        }
    });

    // Wallet Simulator
    let walletBalance = 0;
    const walletHistory = [];
    
    document.getElementById('wallet-topup-btn').addEventListener('click', function() {
        const amount = parseFloat(document.getElementById('wallet-topup').value) || 0;
        if (amount > 0) {
            walletBalance += amount;
            updateWalletBalance();
            addWalletTransaction(translations.topup, amount, 'success');
        }
    });
    
    document.getElementById('wallet-withdraw-btn').addEventListener('click', function() {
        const amount = parseFloat(document.getElementById('wallet-withdraw').value) || 0;
        if (amount > 0 && amount <= walletBalance) {
            walletBalance -= amount;
            updateWalletBalance();
            addWalletTransaction(translations.withdraw, amount, 'warning');
        } else if (amount > walletBalance) {
            alert(translations.insufficientBalance);
        }
    });
    
    function updateWalletBalance() {
        document.getElementById('wallet-balance').textContent = formatCurrency(walletBalance);
    }
    
    function addWalletTransaction(type, amount, statusClass) {
        const historyDiv = document.getElementById('wallet-history');
        if (walletHistory.length === 0) {
            historyDiv.innerHTML = '';
        }
        
        const timestamp = new Date().toLocaleTimeString('id-ID');
        const transaction = {
            type,
            amount,
            timestamp,
            statusClass
        };
        walletHistory.unshift(transaction);
        
        if (walletHistory.length > 5) walletHistory.pop();
        
        historyDiv.innerHTML = walletHistory.map(t => `
            <div class="flex items-center justify-between text-xs p-2 bg-gray-50 rounded">
                <div>
                    <span class="font-medium ${t.statusClass === 'success' ? 'text-green-600' : 'text-orange-600'}">${t.type}</span>
                    <span class="text-gray-500 ml-2">${t.timestamp}</span>
                </div>
                <span class="font-semibold ${t.statusClass === 'success' ? 'text-green-700' : 'text-orange-700'}">
                    ${t.statusClass === 'success' ? '+' : '-'}${formatCurrency(t.amount)}
                </span>
            </div>
        `).join('');
    }

    // Transaction Flow Simulator
    document.getElementById('flow-simulate').addEventListener('click', function() {
        const steps = document.querySelectorAll('#transaction-flow > div');
        let currentStep = 0;
        
        function activateStep(stepIndex) {
            steps.forEach((step, index) => {
                if (index <= stepIndex) {
                    step.classList.remove('opacity-50');
                    step.querySelector('div').classList.remove('bg-gray-200', 'text-gray-600');
                    step.querySelector('div').classList.add('bg-orange-100', 'text-orange-600');
                } else {
                    step.classList.add('opacity-50');
                    step.querySelector('div').classList.remove('bg-orange-100', 'text-orange-600');
                    step.querySelector('div').classList.add('bg-gray-200', 'text-gray-600');
                }
            });
        }
        
        function nextStep() {
            if (currentStep < steps.length - 1) {
                currentStep++;
                activateStep(currentStep);
                setTimeout(nextStep, 1000);
            } else {
                setTimeout(() => {
                    currentStep = 0;
                    activateStep(0);
                    this.textContent = translations.simulateTransaction;
                    this.disabled = false;
                }, 2000);
                this.textContent = translations.transactionComplete;
                this.disabled = true;
            }
        }
        
        this.textContent = translations.processing;
        this.disabled = true;
        activateStep(0);
        setTimeout(nextStep.bind(this), 1000);
    });

    // Price Benchmark Tool
    document.getElementById('benchmark-calculate').addEventListener('click', function() {
        const yourPrice = parseFloat(document.getElementById('benchmark-price').value) || 0;
        const category = document.getElementById('benchmark-category').value;
        
        // Simulate market analysis (in real app, this would query database)
        const marketAverages = {
            tutorial: 45000,
            template: 75000,
            guide: 55000,
            resource: 60000
        };
        
        const marketAvg = marketAverages[category] || 50000;
        const difference = yourPrice - marketAvg;
        const percentage = ((difference / marketAvg) * 100).toFixed(1);
        
        let position = '';
        let positionClass = '';
        if (difference < -10000) {
            position = translations.belowMarket;
            positionClass = 'text-green-600';
        } else if (difference > 10000) {
            position = translations.aboveMarket;
            positionClass = 'text-orange-600';
        } else {
            position = translations.marketAverageCompetitive;
            positionClass = 'text-blue-600';
        }
        
        document.getElementById('benchmark-avg').textContent = formatCurrency(marketAvg);
        document.getElementById('benchmark-yours').textContent = formatCurrency(yourPrice);
        document.getElementById('benchmark-position').textContent = position;
        document.getElementById('benchmark-position').className = 'font-bold ' + positionClass;
        document.getElementById('benchmark-result').classList.remove('hidden');
    });
});
</script>
@endpush
@endsection

