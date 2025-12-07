@extends('layouts.app')

@section('title', isset($pointsPricingConfig) ? 'Edit Pricing Configuration' : 'Create Pricing Configuration')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('admin.points-pricing.index') }}" class="text-blue-600 hover:text-blue-900">&larr; Back to
                Pricing</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">
                {{ isset($pointsPricingConfig) ? 'Edit Pricing Configuration' : 'Create New Pricing Configuration' }}
            </h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="font-semibold text-red-900 mb-2">Please fix the following errors:</h3>
                <ul class="text-sm text-red-800">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="max-w-2xl mx-auto">
            <form
                action="{{ isset($pointsPricingConfig) ? route('admin.points-pricing.update', $pointsPricingConfig) : route('admin.points-pricing.store') }}"
                method="POST" class="bg-white rounded-lg shadow p-8">
                @csrf
                @if (isset($pointsPricingConfig))
                    @method('PUT')
                @endif

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" id="name"
                        value="{{ old('name', $pointsPricingConfig->name ?? '') }}"
                        placeholder="e.g., 10% Discount, Premium Feature" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Redemption Type *</label>
                    <select name="type" id="type" required onchange="updateTypeFields()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Type</option>
                        <option value="discount"
                            {{ old('type', $pointsPricingConfig->type ?? '') === 'discount' ? 'selected' : '' }}>Discount
                            (on purchases)</option>
                        <option value="premium_feature"
                            {{ old('type', $pointsPricingConfig->type ?? '') === 'premium_feature' ? 'selected' : '' }}>
                            Premium Feature (days)</option>
                    </select>
                    @error('type')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Points Required -->
                <div class="mb-6">
                    <label for="points_required" class="block text-sm font-medium text-gray-700 mb-2">Points Required
                        *</label>
                    <input type="number" name="points_required" id="points_required"
                        value="{{ old('points_required', $pointsPricingConfig->points_required ?? 100) }}" min="1"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('points_required')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Amount (for discount type) -->
                <div class="mb-6" id="discount_amount_field"
                    style="display: {{ old('type', $pointsPricingConfig->type ?? '') === 'discount' ? 'block' : 'none' }};">
                    <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount
                        (Rupiah)</label>
                    <input type="number" name="discount_amount" id="discount_amount"
                        value="{{ old('discount_amount', $pointsPricingConfig->discount_amount ?? '') }}" step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 50000">
                    <p class="text-gray-600 text-sm mt-1">Leave empty if using percentage discount instead</p>
                    @error('discount_amount')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Discount Percent (for discount type) -->
                <div class="mb-6" id="discount_percent_field"
                    style="display: {{ old('type', $pointsPricingConfig->type ?? '') === 'discount' ? 'block' : 'none' }};">
                    <label for="discount_percent" class="block text-sm font-medium text-gray-700 mb-2">Discount Percent
                        (%)</label>
                    <input type="number" name="discount_percent" id="discount_percent"
                        value="{{ old('discount_percent', $pointsPricingConfig->discount_percent ?? '') }}" min="0"
                        max="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 10">
                    <p class="text-gray-600 text-sm mt-1">Leave empty if using fixed discount amount instead</p>
                    @error('discount_percent')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Premium Days (for premium_feature type) -->
                <div class="mb-6" id="premium_days_field"
                    style="display: {{ old('type', $pointsPricingConfig->type ?? '') === 'premium_feature' ? 'block' : 'none' }};">
                    <label for="premium_days" class="block text-sm font-medium text-gray-700 mb-2">Premium Days *</label>
                    <input type="number" name="premium_days" id="premium_days"
                        value="{{ old('premium_days', $pointsPricingConfig->premium_days ?? '') }}" min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 30">
                    @error('premium_days')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Describe this offer...">{{ old('description', $pointsPricingConfig->description ?? '') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Limits Section -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-4">⚠️ Safety Limits (Recommended)</h3>

                    <div class="mb-4">
                        <label for="daily_limit" class="block text-sm font-medium text-gray-700 mb-2">Daily Redemption Limit
                            (across all users)</label>
                        <input type="number" name="daily_limit" id="daily_limit"
                            value="{{ old('daily_limit', $pointsPricingConfig->daily_limit ?? '') }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., 50">
                        <p class="text-gray-600 text-sm mt-1">Maximum times this offer can be redeemed in a single day.
                            Leave empty for unlimited.</p>
                        @error('daily_limit')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="user_limit" class="block text-sm font-medium text-gray-700 mb-2">Per-User Redemption
                            Limit</label>
                        <input type="number" name="user_limit" id="user_limit"
                            value="{{ old('user_limit', $pointsPricingConfig->user_limit ?? '') }}" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., 3">
                        <p class="text-gray-600 text-sm mt-1">Maximum times a single user can redeem this offer. Leave
                            empty for unlimited.</p>
                        @error('user_limit')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Expiration -->
                <div class="mb-6">
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                        value="{{ old('expires_at', isset($pointsPricingConfig) && $pointsPricingConfig->expires_at ? $pointsPricingConfig->expires_at->format('Y-m-d\TH:i') : '') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-gray-600 text-sm mt-1">Leave empty for no expiration date.</p>
                    @error('expires_at')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $pointsPricingConfig->is_active ?? true) ? 'checked' : '' }}
                            class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 font-medium">Active</span>
                    </label>
                    <p class="text-gray-600 text-sm mt-1">Inactive offers won't be shown to users.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        {{ isset($pointsPricingConfig) ? 'Update' : 'Create' }} Configuration
                    </button>
                    <a href="{{ route('admin.points-pricing.index') }}"
                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateTypeFields() {
            const type = document.getElementById('type').value;
            const discountAmountField = document.getElementById('discount_amount_field');
            const discountPercentField = document.getElementById('discount_percent_field');
            const premiumDaysField = document.getElementById('premium_days_field');

            if (type === 'discount') {
                discountAmountField.style.display = 'block';
                discountPercentField.style.display = 'block';
                premiumDaysField.style.display = 'none';
            } else if (type === 'premium_feature') {
                discountAmountField.style.display = 'none';
                discountPercentField.style.display = 'none';
                premiumDaysField.style.display = 'block';
            } else {
                discountAmountField.style.display = 'none';
                discountPercentField.style.display = 'none';
                premiumDaysField.style.display = 'none';
            }
        }
    </script>
@endsection
