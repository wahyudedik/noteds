@extends('40-shared.layouts.app')

@section('title', 'Contest Settings')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Contest Settings</h1>
                <p class="mt-2 text-gray-600">Configure contest platform settings, fees, and rules</p>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-red-800 font-semibold mb-2">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Settings Form -->
            <form action="{{ route('admin.contests.settings.update') }}" method="POST" class="bg-white rounded-lg shadow">
                @csrf
                @method('PUT')

                <div class="p-6 space-y-6">
                    <!-- Platform Status -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Platform Status</h3>

                        <div class="flex items-center">
                            <input type="checkbox" name="enabled" id="enabled" value="1"
                                {{ $setting->enabled ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <label for="enabled" class="ml-3 text-gray-700 font-medium">
                                Enable Contest Platform
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 ml-7">When disabled, users cannot create or participate in
                            contests</p>
                    </div>

                    <!-- Fee Configuration -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Fee Configuration</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <!-- Platform Fee Percentage -->
                            <div>
                                <label for="platform_fee_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                    Platform Fee Percentage (%)
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="platform_fee_percentage" id="platform_fee_percentage"
                                    value="{{ old('platform_fee_percentage', $setting->platform_fee_percentage) }}"
                                    min="0" max="100" step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    required>
                                <p class="mt-1 text-xs text-gray-600">Fee charged on contest prize amounts (0-100%)</p>
                                @error('platform_fee_percentage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Max Prize Amount -->
                            <div>
                                <label for="max_prize_amount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Maximum Prize Amount (Optional)
                                </label>
                                <input type="number" name="max_prize_amount" id="max_prize_amount"
                                    value="{{ old('max_prize_amount', $setting->max_prize_amount) }}" min="0"
                                    step="0.01"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    placeholder="No limit">
                                <p class="mt-1 text-xs text-gray-600">Leave blank for no limit</p>
                                @error('max_prize_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Contest Limits -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contest Limits</h3>

                        <div>
                            <label for="max_contests_per_buyer" class="block text-sm font-medium text-gray-700 mb-2">
                                Maximum Contests Per Buyer
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="max_contests_per_buyer" id="max_contests_per_buyer"
                                value="{{ old('max_contests_per_buyer', $setting->max_contests_per_buyer) }}" min="1"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                required>
                            <p class="mt-1 text-xs text-gray-600">Maximum number of active contests a buyer can have</p>
                            @error('max_contests_per_buyer')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Verification Requirements -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Verification Requirements</h3>

                        <div class="flex items-center">
                            <input type="checkbox" name="require_kyc" id="require_kyc" value="1"
                                {{ $setting->require_kyc ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <label for="require_kyc" class="ml-3 text-gray-700 font-medium">
                                Require KYC Verification for Contest Creation
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 ml-7">Users must complete KYC before creating contests</p>
                    </div>

                    <!-- Automated Prize Distribution -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Automated Prize Distribution</h3>

                        <div class="flex items-center">
                            <input type="checkbox" name="auto_distribute_prizes" id="auto_distribute_prizes" value="1"
                                {{ $setting->auto_distribute_prizes ? 'checked' : '' }}
                                class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500">
                            <label for="auto_distribute_prizes" class="ml-3 text-gray-700 font-medium">
                                Automatically Distribute Prizes
                            </label>
                        </div>
                        <p class="mt-2 text-sm text-gray-600 ml-7">Automatically release prizes to winners when contest ends
                        </p>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="border-b pb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Terms & Guidelines</h3>

                        <div class="mb-6">
                            <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700 mb-2">
                                Terms and Conditions
                            </label>
                            <textarea name="terms_and_conditions" id="terms_and_conditions" rows="6"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter terms and conditions for contests...">{{ old('terms_and_conditions', $setting->terms_and_conditions) }}</textarea>
                            <p class="mt-1 text-xs text-gray-600">Displayed to users when creating contests</p>
                            @error('terms_and_conditions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="approval_guidelines" class="block text-sm font-medium text-gray-700 mb-2">
                                Approval Guidelines
                            </label>
                            <textarea name="approval_guidelines" id="approval_guidelines" rows="6"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Enter guidelines for approving contests...">{{ old('approval_guidelines', $setting->approval_guidelines) }}</textarea>
                            <p class="mt-1 text-xs text-gray-600">Internal guidelines for moderators when reviewing
                                contests</p>
                            @error('approval_guidelines')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-between items-center">
                    <a href="{{ url('/admin') }}" class="text-gray-700 hover:text-gray-900 font-medium">
                        ← Back to Admin
                    </a>
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        Save Contest Settings
                    </button>
                </div>
            </form>

            <!-- Info Box -->
            <div class="mt-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-blue-900 text-sm">
                    <strong>Note:</strong> Changes to contest settings will affect all new contests created after the
                    update.
                    Existing contests will not be retroactively affected by fee percentage or limit changes.
                </p>
            </div>
        </div>
    </div>
@endsection
