@extends('layouts.app')

@section('title', __('messages.settings_admin'))

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}"
                    class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_dashboard') }}
                </a>
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.system_settings') }}</h2>
                <p class="text-gray-600 mt-1">{{ __('messages.configure_system_wide_settings') }}</p>
            </div>

            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- S3 Backup Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                {{ __('messages.s3_cloud_backup_configuration') }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">{{ __('messages.configure_s3_backups') }}</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Enable S3 -->
                        <div class="flex items-center">
                            <input type="checkbox" name="s3_enabled" id="s3_enabled" value="1"
                                {{ $s3Settings->get('s3_enabled')?->value ?? false ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="s3_enabled" class="ml-3 text-sm font-medium text-gray-700">
                                {{ __('messages.enable_s3_cloud_backup') }}
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 ml-7">{{ __('messages.when_enabled_backups_uploaded') }}</p>

                        <div id="s3-config"
                            class="space-y-4 {{ $s3Settings->get('s3_enabled')?->value ?? false ? '' : 'hidden' }}">
                            <!-- S3 Provider -->
                            <div>
                                <label for="s3_provider" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_provider') }}
                                </label>
                                <select name="s3_provider" id="s3_provider"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="aws"
                                        {{ ($s3Settings->get('s3_provider')?->value ?? 'aws') === 'aws' ? 'selected' : '' }}>
                                        Amazon S3 (AWS)</option>
                                    <option value="digitalocean"
                                        {{ $s3Settings->get('s3_provider')?->value === 'digitalocean' ? 'selected' : '' }}>
                                        DigitalOcean Spaces</option>
                                    <option value="wasabi"
                                        {{ $s3Settings->get('s3_provider')?->value === 'wasabi' ? 'selected' : '' }}>
                                        Wasabi</option>
                                    <option value="other"
                                        {{ $s3Settings->get('s3_provider')?->value === 'other' ? 'selected' : '' }}>Other
                                        S3-Compatible</option>
                                </select>
                            </div>

                            <!-- S3 Access Key -->
                            <div>
                                <label for="s3_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_key') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="s3_key" id="s3_key"
                                    value="{{ old('s3_key', $s3Settings->get('s3_key')?->value) }}"
                                    placeholder="AKIAIOSFODNN7EXAMPLE"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Your S3 access key ID</p>
                            </div>

                            <!-- S3 Secret Key -->
                            <div>
                                <label for="s3_secret" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_secret') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="password" name="s3_secret" id="s3_secret"
                                    value="{{ old('s3_secret', $s3Settings->get('s3_secret')?->value) }}"
                                    placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Your S3 secret access key (hidden for security)</p>
                            </div>

                            <!-- S3 Region -->
                            <div>
                                <label for="s3_region" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_region') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="s3_region" id="s3_region"
                                    value="{{ old('s3_region', $s3Settings->get('s3_region')?->value ?? 'us-east-1') }}"
                                    placeholder="us-east-1"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">S3 region (e.g., us-east-1, ap-southeast-1)</p>
                            </div>

                            <!-- S3 Bucket -->
                            <div>
                                <label for="s3_bucket" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_bucket') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="s3_bucket" id="s3_bucket"
                                    value="{{ old('s3_bucket', $s3Settings->get('s3_bucket')?->value) }}"
                                    placeholder="my-backup-bucket"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">S3 bucket name (must already exist)</p>
                            </div>

                            <!-- S3 Endpoint (for non-AWS providers) -->
                            <div id="s3-endpoint-group">
                                <label for="s3_endpoint" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_endpoint') }} ({{ __('messages.optional') }})
                                </label>
                                <input type="url" name="s3_endpoint" id="s3_endpoint"
                                    value="{{ old('s3_endpoint', $s3Settings->get('s3_endpoint')?->value) }}"
                                    placeholder="https://sgp1.digitaloceanspaces.com"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Required for DigitalOcean Spaces, Wasabi, etc.</p>
                            </div>

                            <!-- S3 Path Prefix -->
                            <div>
                                <label for="s3_path_prefix" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.s3_path_prefix') }} ({{ __('messages.optional') }})
                                </label>
                                <input type="text" name="s3_path_prefix" id="s3_path_prefix"
                                    value="{{ old('s3_path_prefix', $s3Settings->get('s3_path_prefix')?->value ?? 'backups') }}"
                                    placeholder="backups"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Folder prefix in bucket (default: backups)</p>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                    {{ __('messages.save_settings') }}
                                </button>
                                <a href="{{ route('admin.settings.test-s3') }}"
                                    onclick="event.preventDefault(); document.getElementById('test-s3-form').submit();"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                                    Test Connection
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Test S3 Form -->
                    <form id="test-s3-form" action="{{ route('admin.settings.test-s3') }}" method="POST"
                        class="hidden">
                        @csrf
                    </form>
                </div>
            </div>

            <!-- Premium Price Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Premium Subscription Price
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Set the monthly price for premium subscription plan</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="premium_price_monthly" class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly Price (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="premium_price_monthly" id="premium_price_monthly"
                                        value="{{ old('premium_price_monthly', $premiumPrice) }}" min="0"
                                        step="1000" required placeholder="25000"
                                        class="block w-full pl-10 pr-3 py-3 border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500 @error('premium_price_monthly') border-red-500 @enderror">
                                </div>
                                @error('premium_price_monthly')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    Current price: <strong>{{ \App\Models\Setting::formatPremiumPrice(false) }}</strong>
                                </p>
                            </div>

                            <div class="flex items-end">
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 w-full">
                                    <p class="text-xs text-purple-700 font-medium mb-1">{{ __('messages.preview') }}</p>
                                    <p class="text-lg font-bold text-purple-900" id="price-preview">
                                        {{ \App\Models\Setting::formatPremiumPrice(true) }}
                                    </p>
                                    <p class="text-xs text-purple-600 mt-1">
                                        {{ __('messages.this_is_how_it_will_appear') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                                Save Premium Price
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Marketplace Commission Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Marketplace Commission Settings
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Configure platform fee and creator commission for note
                                transactions</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Platform Commission -->
                            <div>
                                <label for="platform_commission_percent"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Platform Commission (%) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="platform_commission_percent"
                                        id="platform_commission_percent"
                                        value="{{ old('platform_commission_percent', $platformCommissionPercent) }}"
                                        min="0" max="100" step="0.1" required placeholder="20"
                                        class="block w-full pr-10 py-3 border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500 @error('platform_commission_percent') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                </div>
                                @error('platform_commission_percent')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    Platform fee yang di-deduct dari <strong>setiap transaksi</strong> (meskipun 20x
                                    terjual)
                                </p>
                            </div>

                            <!-- Creator Commission -->
                            <div>
                                <label for="creator_commission_percent"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Creator Commission (%) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="creator_commission_percent"
                                        id="creator_commission_percent"
                                        value="{{ old('creator_commission_percent', $creatorCommissionPercent) }}"
                                        min="0" max="100" step="0.1" required placeholder="0"
                                        class="block w-full pr-10 py-3 border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500 @error('creator_commission_percent') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                </div>
                                @error('creator_commission_percent')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    Komisi untuk <strong>original creator</strong> di setiap penjualan (penjual kedua dan
                                    seterusnya tidak dapat komisi)
                                </p>
                            </div>

                            <!-- Premium Buyer Discount -->
                            <div>
                                <label for="premium_buyer_discount_percent"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Premium Buyer Discount (%) <span class="text-orange-500">*</span>
                                    <span class="text-xs text-gray-500">(Exclusive discount for premium buyers)</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="premium_buyer_discount_percent"
                                        id="premium_buyer_discount_percent"
                                        value="{{ old('premium_buyer_discount_percent', $premiumBuyerDiscountPercent ?? 10) }}"
                                        min="0" max="50" step="0.1" required placeholder="10"
                                        class="block w-full pr-10 py-3 border-gray-300 rounded-lg shadow-sm focus:border-orange-500 focus:ring-2 focus:ring-orange-500 @error('premium_buyer_discount_percent') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Diskon eksklusif untuk <strong>premium buyers</strong> pada semua pembelian note (default: 10%)
                                </p>
                                @error('premium_buyer_discount_percent')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-orange-800 mb-2">Commission Rules:</p>
                                    <ul class="text-xs text-orange-700 space-y-1">
                                        <li>• <strong>Platform Fee</strong>: Deducted dari setiap transaksi (wajib)</li>
                                        <li>• <strong>Creator Commission</strong>: Selalu untuk original creator di setiap
                                            penjualan</li>
                                        <li>• <strong>Penjual kedua dan seterusnya</strong>: Tidak dapat komisi (hanya
                                            original creator)</li>
                                        <li>• <strong>Setiap user hanya bisa beli note 1x</strong>, tapi note bisa dijual ke
                                            user berbeda</li>
                                        <li>• <strong>Original creator selalu dapat komisi</strong> di setiap transaksi
                                            (jika di-setting)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition-colors">
                                Save Commission Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Referral Reward Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Referral Reward Settings
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Configure referral program rewards (signup bonus and
                                transaction commission)</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Signup Reward -->
                            <div>
                                <label for="referral_reward_signup" class="block text-sm font-medium text-gray-700 mb-2">
                                    Signup Reward (Rp) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="referral_reward_signup" id="referral_reward_signup"
                                        value="{{ old('referral_reward_signup', $referralSignupReward) }}" min="0"
                                        step="1000" required placeholder="5000"
                                        class="block w-full pl-10 pr-3 py-3 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 @error('referral_reward_signup') border-red-500 @enderror">
                                </div>
                                @error('referral_reward_signup')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    Reward diberikan kepada referrer ketika referral mendaftar
                                </p>
                            </div>

                            <!-- Transaction Commission -->
                            <div>
                                <label for="referral_reward_commission_percent"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Transaction Commission (%) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" name="referral_reward_commission_percent"
                                        id="referral_reward_commission_percent"
                                        value="{{ old('referral_reward_commission_percent', $referralCommissionPercent) }}"
                                        min="0" max="100" step="0.1" required placeholder="5"
                                        class="block w-full pr-10 py-3 border-gray-300 rounded-lg shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 @error('referral_reward_commission_percent') border-red-500 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                </div>
                                @error('referral_reward_commission_percent')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    Persentase commission dari setiap transaksi yang dilakukan referral
                                </p>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-green-800 mb-1">Current Settings</p>
                                    <ul class="text-xs text-green-700 space-y-1">
                                        <li>• Signup Reward: <strong>{{ currency($referralSignupReward) }}</strong></li>
                                        <li>• Transaction Commission: <strong>{{ $referralCommissionPercent }}%</strong>
                                        </li>
                                        <li>• Perubahan akan berlaku untuk referral baru dan transaksi baru</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                Save Referral Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Featured Notes Pricing Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                </svg>
                                {{ __('messages.featured_notes') }} Pricing Settings
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Configure pricing for featured notes advertising per
                                location and duration</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="space-y-6">
                            @foreach ($featuredLocationLabels as $location => $label)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <h4 class="text-md font-semibold text-gray-900 mb-4">{{ $label }}</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        @foreach ($featuredDurations as $duration)
                                            <div>
                                                <label for="featured_price_{{ $location }}_{{ $duration }}"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    {{ $duration }} Hari <span class="text-red-500">*</span>
                                                </label>
                                                <div class="relative">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 text-sm">Rp</span>
                                                    </div>
                                                    <input type="number"
                                                        name="featured_price[{{ $location }}][{{ $duration }}]"
                                                        id="featured_price_{{ $location }}_{{ $duration }}"
                                                        value="{{ old("featured_price.{$location}.{$duration}", $featuredPricing[$location][$duration] ?? 0) }}"
                                                        min="0" step="1000" required
                                                        class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500 @error("featured_price.{$location}.{$duration}") border-red-500 @enderror">
                                                </div>
                                                @error("featured_price.{$location}.{$duration}")
                                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Info Box -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-yellow-800 mb-1">Pricing Information</p>
                                    <ul class="text-xs text-yellow-700 space-y-1">
                                        <li>• Pricing akan digunakan untuk semua request featured notes baru</li>
                                        <li>• Request yang sudah dibuat tidak akan terpengaruh perubahan harga</li>
                                        <li>• Harga dapat diubah kapan saja melalui halaman ini</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                Save Featured Notes Pricing
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Documentation -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    S3 Setup Guide
                </h4>
                <div class="text-sm text-blue-800 space-y-2">
                    <p><strong>1. Create S3 Bucket:</strong> Log in to your cloud provider and create a bucket for backups.
                    </p>
                    <p><strong>2. Generate Access Keys:</strong> Create IAM user with S3 read/write permissions and generate
                        access keys.</p>
                    <p><strong>3. Configure Settings:</strong> Fill in the form above with your credentials.</p>
                    <p><strong>4. Test Connection:</strong> Click "Test Connection" to verify your settings are correct.</p>
                    <p><strong>5. Enable Backups:</strong> Once tested, enable S3 and backups will run automatically.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Show/hide S3 config based on enabled checkbox
            document.getElementById('s3_enabled').addEventListener('change', function() {
                const configDiv = document.getElementById('s3-config');
                if (this.checked) {
                    configDiv.classList.remove('hidden');
                } else {
                    configDiv.classList.add('hidden');
                }
            });

            // Show/hide endpoint field based on provider
            document.getElementById('s3_provider').addEventListener('change', function() {
                const endpointGroup = document.getElementById('s3-endpoint-group');
                if (this.value === 'aws') {
                    endpointGroup.style.display = 'none';
                } else {
                    endpointGroup.style.display = 'block';
                }
            });

            // Trigger on page load
            document.getElementById('s3_provider').dispatchEvent(new Event('change'));

            // Premium price preview
            const priceInput = document.getElementById('premium_price_monthly');
            const pricePreview = document.getElementById('price-preview');

            function updatePricePreview() {
                const price = parseFloat(priceInput.value) || 0;
                if (price >= 1000) {
                    const kPrice = price / 1000;
                    pricePreview.textContent = 'Rp' + Math.round(kPrice) + 'k/mo';
                } else {
                    pricePreview.textContent = 'Rp' + price.toLocaleString('id-ID') + '/mo';
                }
            }

            priceInput.addEventListener('input', updatePricePreview);
            updatePricePreview(); // Initial preview
        </script>
    @endpush
@endsection
