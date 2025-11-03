@extends('layouts.app')

@section('title', 'Settings - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Dashboard
            </a>
            <h2 class="text-2xl font-bold text-gray-900">System Settings</h2>
            <p class="text-gray-600 mt-1">Configure system-wide settings including S3 backup storage</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
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
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
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

        <!-- S3 Backup Configuration -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            S3 Cloud Backup Configuration
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Configure Amazon S3 or S3-compatible storage for automated backups</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Enable S3 -->
                    <div class="flex items-center">
                        <input type="checkbox" name="s3_enabled" id="s3_enabled" value="1"
                            {{ ($s3Settings->get('s3_enabled')?->value ?? false) ? 'checked' : '' }}
                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="s3_enabled" class="ml-3 text-sm font-medium text-gray-700">
                            Enable S3 Cloud Backup
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 ml-7">When enabled, backups will be automatically uploaded to S3</p>

                    <div id="s3-config" class="space-y-4 {{ ($s3Settings->get('s3_enabled')?->value ?? false) ? '' : 'hidden' }}">
                        <!-- S3 Provider -->
                        <div>
                            <label for="s3_provider" class="block text-sm font-medium text-gray-700 mb-2">
                                S3 Provider
                            </label>
                            <select name="s3_provider" id="s3_provider"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="aws" {{ ($s3Settings->get('s3_provider')?->value ?? 'aws') === 'aws' ? 'selected' : '' }}>Amazon S3 (AWS)</option>
                                <option value="digitalocean" {{ ($s3Settings->get('s3_provider')?->value) === 'digitalocean' ? 'selected' : '' }}>DigitalOcean Spaces</option>
                                <option value="wasabi" {{ ($s3Settings->get('s3_provider')?->value) === 'wasabi' ? 'selected' : '' }}>Wasabi</option>
                                <option value="other" {{ ($s3Settings->get('s3_provider')?->value) === 'other' ? 'selected' : '' }}>Other S3-Compatible</option>
                            </select>
                        </div>

                        <!-- S3 Access Key -->
                        <div>
                            <label for="s3_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Access Key ID <span class="text-red-500">*</span>
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
                                Secret Access Key <span class="text-red-500">*</span>
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
                                Region <span class="text-red-500">*</span>
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
                                Bucket Name <span class="text-red-500">*</span>
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
                                Endpoint URL (Optional - for non-AWS providers)
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
                                Path Prefix (Optional)
                            </label>
                            <input type="text" name="s3_path_prefix" id="s3_path_prefix" 
                                value="{{ old('s3_path_prefix', $s3Settings->get('s3_path_prefix')?->value ?? 'backups') }}"
                                placeholder="backups"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Folder prefix in bucket (default: backups)</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                Save S3 Settings
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
                <form id="test-s3-form" action="{{ route('admin.settings.test-s3') }}" method="POST" class="hidden">
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
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    value="{{ old('premium_price_monthly', $premiumPrice) }}"
                                    min="0" step="1000" required
                                    placeholder="25000"
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
                                <p class="text-xs text-purple-700 font-medium mb-1">Preview</p>
                                <p class="text-lg font-bold text-purple-900" id="price-preview">
                                    {{ \App\Models\Setting::formatPremiumPrice(true) }}
                                </p>
                                <p class="text-xs text-purple-600 mt-1">This is how it will appear to users</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors">
                            Save Premium Price
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
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Referral Reward Settings
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">Configure referral program rewards (signup bonus and transaction commission)</p>
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
                                    value="{{ old('referral_reward_signup', $referralSignupReward) }}"
                                    min="0" step="1000" required
                                    placeholder="5000"
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
                            <label for="referral_reward_commission_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                Transaction Commission (%) <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" name="referral_reward_commission_percent" id="referral_reward_commission_percent" 
                                    value="{{ old('referral_reward_commission_percent', $referralCommissionPercent) }}"
                                    min="0" max="100" step="0.1" required
                                    placeholder="5"
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
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-green-800 mb-1">Current Settings</p>
                                <ul class="text-xs text-green-700 space-y-1">
                                    <li>• Signup Reward: <strong>Rp {{ number_format($referralSignupReward, 0, ',', '.') }}</strong></li>
                                    <li>• Transaction Commission: <strong>{{ $referralCommissionPercent }}%</strong></li>
                                    <li>• Perubahan akan berlaku untuk referral baru dan transaksi baru</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                            Save Referral Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Documentation -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-sm font-semibold text-blue-900 mb-2 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                S3 Setup Guide
            </h4>
            <div class="text-sm text-blue-800 space-y-2">
                <p><strong>1. Create S3 Bucket:</strong> Log in to your cloud provider and create a bucket for backups.</p>
                <p><strong>2. Generate Access Keys:</strong> Create IAM user with S3 read/write permissions and generate access keys.</p>
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

