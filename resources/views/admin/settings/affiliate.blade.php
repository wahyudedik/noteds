@extends('40-shared/layouts/app')

@section('title', __('Affiliate Settings'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('Affiliate Settings') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Configure affiliate commission tiers and payout settings') }}
                </p>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Settings Form -->
            <form action="{{ route('admin.affiliate-settings.update') }}" method="POST" class="space-y-8">
                @csrf
                @method('PUT')

                <!-- Commission Tiers Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Commission Tiers') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('Set commission percentages based on conversion milestones') }}</p>

                    <div class="space-y-6">
                        <!-- Tier 1 -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-200">
                            <div>
                                <label for="affiliate_commission_tier_1"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 1 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('0-9 conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_1" name="affiliate_commission_tier_1"
                                    value="{{ $settings['affiliate_commission_tier_1'] ?? 0.5 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_1')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_1'] ?? 0.5 }}%</div>
                            </div>
                        </div>

                        <!-- Tier 2 -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-200">
                            <div>
                                <label for="affiliate_commission_tier_2"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 2 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('10-49 conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_2" name="affiliate_commission_tier_2"
                                    value="{{ $settings['affiliate_commission_tier_2'] ?? 1 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_2')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_2'] ?? 1 }}%</div>
                            </div>
                        </div>

                        <!-- Tier 3 -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-200">
                            <div>
                                <label for="affiliate_commission_tier_3"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 3 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('50-99 conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_3" name="affiliate_commission_tier_3"
                                    value="{{ $settings['affiliate_commission_tier_3'] ?? 2 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_3')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_3'] ?? 2 }}%</div>
                            </div>
                        </div>

                        <!-- Tier 4 -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-200">
                            <div>
                                <label for="affiliate_commission_tier_4"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 4 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('100-249 conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_4" name="affiliate_commission_tier_4"
                                    value="{{ $settings['affiliate_commission_tier_4'] ?? 5 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_4')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_4'] ?? 5 }}%</div>
                            </div>
                        </div>

                        <!-- Tier 5 -->
                        <div class="grid grid-cols-2 gap-4 pb-6 border-b border-gray-200">
                            <div>
                                <label for="affiliate_commission_tier_5"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 5 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('250-499 conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_5" name="affiliate_commission_tier_5"
                                    value="{{ $settings['affiliate_commission_tier_5'] ?? 10 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_5')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_5'] ?? 10 }}%</div>
                            </div>
                        </div>

                        <!-- Tier 6 -->
                        <div class="grid grid-cols-2 gap-4 pb-6">
                            <div>
                                <label for="affiliate_commission_tier_6"
                                    class="block text-sm font-semibold text-gray-900 mb-2">
                                    {{ __('Tier 6 Commission (%)') }}
                                </label>
                                <p class="text-sm text-gray-600 mb-2">{{ __('500+ conversions') }}</p>
                                <input type="number" id="affiliate_commission_tier_6" name="affiliate_commission_tier_6"
                                    value="{{ $settings['affiliate_commission_tier_6'] ?? 15 }}" step="0.01"
                                    min="0" max="100"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                @error('affiliate_commission_tier_6')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-end">
                                <div class="text-3xl font-bold text-blue-600">
                                    {{ $settings['affiliate_commission_tier_6'] ?? 15 }}%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conversion Thresholds Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Conversion Thresholds') }}</h2>
                    <p class="text-gray-600 mb-6">
                        {{ __('Set conversion milestones that unlock higher commission tiers') }}</p>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label for="affiliate_conversion_threshold_1"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Threshold to Tier 2') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_1"
                                name="affiliate_conversion_threshold_1"
                                value="{{ $settings['affiliate_conversion_threshold_1'] ?? 10 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_1')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="affiliate_conversion_threshold_2"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Threshold to Tier 3') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_2"
                                name="affiliate_conversion_threshold_2"
                                value="{{ $settings['affiliate_conversion_threshold_2'] ?? 50 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_2')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="affiliate_conversion_threshold_3"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Threshold to Tier 4') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_3"
                                name="affiliate_conversion_threshold_3"
                                value="{{ $settings['affiliate_conversion_threshold_3'] ?? 100 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_3')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="affiliate_conversion_threshold_4"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Threshold to Tier 5') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_4"
                                name="affiliate_conversion_threshold_4"
                                value="{{ $settings['affiliate_conversion_threshold_4'] ?? 250 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_4')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="affiliate_conversion_threshold_5"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Threshold to Tier 6') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_5"
                                name="affiliate_conversion_threshold_5"
                                value="{{ $settings['affiliate_conversion_threshold_5'] ?? 500 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_5')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="affiliate_conversion_threshold_6"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Maximum Threshold') }}
                            </label>
                            <input type="number" id="affiliate_conversion_threshold_6"
                                name="affiliate_conversion_threshold_6"
                                value="{{ $settings['affiliate_conversion_threshold_6'] ?? 1000 }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_conversion_threshold_6')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payout Settings Section -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Payout Settings') }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Minimum Payout -->
                        <div>
                            <label for="affiliate_min_payout_amount"
                                class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Minimum Payout Amount') }}
                            </label>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Minimum amount affiliates can request for payout') }}</p>
                            <div class="flex items-center">
                                <span class="text-gray-600 mr-2">{{ settings('currency_symbol') ?? '$' }}</span>
                                <input type="number" id="affiliate_min_payout_amount" name="affiliate_min_payout_amount"
                                    value="{{ $settings['affiliate_min_payout_amount'] ?? 50 }}" step="0.01"
                                    min="0.01"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                            </div>
                            @error('affiliate_min_payout_amount')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Payout Day -->
                        <div>
                            <label for="affiliate_payout_day" class="block text-sm font-semibold text-gray-900 mb-2">
                                {{ __('Payout Day of Month') }}
                            </label>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ __('Day of the month when payouts are processed (1-31)') }}</p>
                            <input type="number" id="affiliate_payout_day" name="affiliate_payout_day"
                                value="{{ $settings['affiliate_payout_day'] ?? 1 }}" min="1" max="31"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            @error('affiliate_payout_day')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ url()->previous() }}"
                        class="px-6 py-3 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300 transition">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        {{ __('Save Settings') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
