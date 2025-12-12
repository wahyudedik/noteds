@extends('layouts.app')

@section('title', 'Contest Settings')

@section('content')
    <div class="min-h-screen bg-gray-50 p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md">
            <!-- Header -->
            <div class="border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Contest Settings</h1>
                        <p class="mt-2 text-gray-600">Configure contest feature parameters and rules</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back</a>
                </div>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.contests.settings.update') }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-red-800 mb-2">Validation Errors:</h3>
                        <ul class="text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-sm text-green-800">✓ {{ session('success') }}</p>
                    </div>
                @endif

                <!-- Enable/Disable Contests -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="enabled" value="1" {{ $setting->enabled ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-3 text-sm font-medium text-gray-700">Enable Contest Feature</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">Disable to prevent all contest creation and participation</p>
                </div>

                <hr class="my-6">

                <!-- Platform Fee Percentage -->
                <div>
                    <label for="platform_fee_percentage" class="block text-sm font-semibold text-gray-900 mb-2">
                        Platform Fee Percentage (%)
                    </label>
                    <input type="number" id="platform_fee_percentage" name="platform_fee_percentage" min="0"
                        max="100" step="0.01" value="{{ $setting->platform_fee_percentage }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <p class="mt-1 text-sm text-gray-500">Fee deducted from prizes when contest is created (0-100%)</p>
                </div>

                <!-- Max Contests Per Buyer -->
                <div>
                    <label for="max_contests_per_buyer" class="block text-sm font-semibold text-gray-900 mb-2">
                        Max Active Contests Per Buyer
                    </label>
                    <input type="number" id="max_contests_per_buyer" name="max_contests_per_buyer" min="1"
                        value="{{ $setting->max_contests_per_buyer }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <p class="mt-1 text-sm text-gray-500">Maximum number of draft/open/voting contests a buyer can create
                        simultaneously</p>
                </div>

                <!-- Max Prize Amount -->
                <div>
                    <label for="max_prize_amount" class="block text-sm font-semibold text-gray-900 mb-2">
                        Max Prize Amount (Optional)
                    </label>
                    <input type="number" id="max_prize_amount" name="max_prize_amount" min="0" step="0.01"
                        value="{{ $setting->max_prize_amount ?? '' }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Leave empty for no limit">
                    <p class="mt-1 text-sm text-gray-500">Maximum total prize amount allowed per contest (leave empty for no
                        limit)</p>
                </div>

                <hr class="my-6">

                <!-- KYC Requirement -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="require_kyc" value="1"
                            {{ $setting->require_kyc ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-3 text-sm font-medium text-gray-700">Require KYC Verification</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">Force buyers to complete KYC verification before creating contests
                    </p>
                </div>

                <!-- Auto Distribute Prizes -->
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_distribute_prizes" value="1"
                            {{ $setting->auto_distribute_prizes ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-3 text-sm font-medium text-gray-700">Auto-Distribute Prizes</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">Automatically distribute prizes to winners when they are selected.
                        If unchecked, admin must manually approve distribution.</p>
                </div>

                <hr class="my-6">

                <!-- Terms and Conditions -->
                <div>
                    <label for="terms_and_conditions" class="block text-sm font-semibold text-gray-900 mb-2">
                        Terms and Conditions (Optional)
                    </label>
                    <textarea id="terms_and_conditions" name="terms_and_conditions" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="Enter terms and conditions for contest participation">{{ $setting->terms_and_conditions }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Displayed to buyers when creating contests</p>
                </div>

                <!-- Approval Guidelines -->
                <div>
                    <label for="approval_guidelines" class="block text-sm font-semibold text-gray-900 mb-2">
                        Approval Guidelines (Optional)
                    </label>
                    <textarea id="approval_guidelines" name="approval_guidelines" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 font-mono text-sm"
                        placeholder="Enter guidelines for reviewing and approving contest entries">{{ $setting->approval_guidelines }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Guidelines for moderators when reviewing and approving entries</p>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pt-6 border-t border-gray-200">
                    <button type="submit"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200">
                        Save Settings
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-md transition duration-200 text-center">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
