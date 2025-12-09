@extends('layouts.app')

@section('title', __('affiliate.settings_title'))

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('affiliate.settings_title') }}</h1>
                <p class="mt-2 text-base text-gray-600">{{ __('affiliate.settings_description') }}</p>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200">
                <form action="{{ route('admin.affiliate-settings.update') }}" method="POST" class="space-y-8 p-6">
                    @csrf
                    @method('PUT')

                    <!-- Commission Tier Section -->
                    <div class="border-b border-gray-200 pb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('affiliate.commission_tiers') }}</h3>
                        <p class="text-sm text-gray-600 mb-6">{{ __('affiliate.commission_tiers_description') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @for ($i = 1; $i <= 6; $i++)
                                @php
                                    $threshold =
                                        $settings['affiliate_conversion_threshold_' . ($i - 1)] ??
                                        ($i === 1 ? 0 : null);
                                    $nextThreshold = $settings['affiliate_conversion_threshold_' . $i] ?? null;
                                    $range =
                                        $i === 1
                                            ? '0 - ' . ($nextThreshold - 1)
                                            : $threshold . ' - ' . ($nextThreshold ? $nextThreshold - 1 : '+');
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <label for="affiliate_commission_tier_{{ $i }}"
                                            class="block text-sm font-medium text-gray-900">
                                            {{ __('affiliate.tier') }} {{ $i }}
                                            <span class="text-xs text-gray-500 ml-2">({{ $range }}
                                                conversions)</span>
                                        </label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="number" name="affiliate_commission_tier_{{ $i }}"
                                            id="affiliate_commission_tier_{{ $i }}" step="0.01"
                                            min="0" max="100"
                                            value="{{ $settings['affiliate_commission_tier_' . $i] }}"
                                            class="flex-1 rounded-lg border shadow-sm @error('affiliate_commission_tier_' . $i) border-red-500 @enderror">
                                        <span class="text-gray-600 font-medium">%</span>
                                    </div>
                                    @error('affiliate_commission_tier_' . $i)
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Conversion Thresholds Section -->
                    <div class="border-b border-gray-200 pb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('affiliate.conversion_thresholds') }}
                        </h3>
                        <p class="text-sm text-gray-600 mb-6">{{ __('affiliate.conversion_thresholds_description') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @for ($i = 1; $i <= 6; $i++)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <label for="affiliate_conversion_threshold_{{ $i }}"
                                        class="block text-sm font-medium text-gray-900 mb-2">
                                        {{ __('affiliate.threshold') }} {{ $i }}
                                        <span class="text-xs text-gray-500 ml-2">(to reach Tier {{ $i + 1 }})</span>
                                    </label>
                                    <input type="number" name="affiliate_conversion_threshold_{{ $i }}"
                                        id="affiliate_conversion_threshold_{{ $i }}" min="1"
                                        value="{{ $settings['affiliate_conversion_threshold_' . $i] }}"
                                        class="w-full rounded-lg border shadow-sm @error('affiliate_conversion_threshold_' . $i) border-red-500 @enderror">
                                    @error('affiliate_conversion_threshold_' . $i)
                                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Payout Settings Section -->
                    <div class="border-b border-gray-200 pb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('affiliate.payout_settings') }}</h3>
                        <p class="text-sm text-gray-600 mb-6">{{ __('affiliate.payout_settings_description') }}</p>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg p-4">
                                <label for="affiliate_min_payout_amount"
                                    class="block text-sm font-medium text-gray-900 mb-2">
                                    {{ __('affiliate.min_payout_amount') }}
                                </label>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-gray-600 font-medium">{{ config('currency.base_currency', 'IDR') }}</span>
                                    <input type="number" name="affiliate_min_payout_amount"
                                        id="affiliate_min_payout_amount" step="0.01" min="0.01"
                                        value="{{ $settings['affiliate_min_payout_amount'] }}"
                                        class="flex-1 rounded-lg border shadow-sm @error('affiliate_min_payout_amount') border-red-500 @enderror">
                                </div>
                                @error('affiliate_min_payout_amount')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-2">{{ __('affiliate.min_payout_amount_hint') }}</p>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-4">
                                <label for="affiliate_payout_day" class="block text-sm font-medium text-gray-900 mb-2">
                                    {{ __('affiliate.payout_day') }}
                                </label>
                                <input type="number" name="affiliate_payout_day" id="affiliate_payout_day" min="1"
                                    max="31" value="{{ $settings['affiliate_payout_day'] }}"
                                    class="w-full rounded-lg border shadow-sm @error('affiliate_payout_day') border-red-500 @enderror">
                                @error('affiliate_payout_day')
                                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                                @enderror
                                <p class="text-xs text-gray-500 mt-2">{{ __('affiliate.payout_day_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Info Alert -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400 mt-0.5 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-medium">{{ __('affiliate.settings_info_title') }}</p>
                                <ul class="mt-2 list-disc list-inside text-xs space-y-1">
                                    <li>{{ __('affiliate.settings_info_1') }}</li>
                                    <li>{{ __('affiliate.settings_info_2') }}</li>
                                    <li>{{ __('affiliate.settings_info_3') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('admin.dashboard') }}" class="text-gray-700 hover:text-gray-900 font-medium">
                            {{ __('affiliate.cancel') }}
                        </a>
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg transition-colors">
                            {{ __('affiliate.save_settings') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
