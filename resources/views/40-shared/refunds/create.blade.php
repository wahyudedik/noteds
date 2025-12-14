@extends('40-shared/layouts/app')

@section('title', __('Request Refund'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Request Refund') }}</h1>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('You can request a refund within 7 days of purchase.') }}
            </p>
        </div>

        <!-- Transaction Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Transaction Details') }}</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('Note') }}:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $transaction->note->title }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('Amount') }}:</span>
                    <span class="text-sm font-medium text-gray-900">{{ currency($transaction->amount) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600">{{ __('Purchase Date') }}:</span>
                    <span class="text-sm font-medium text-gray-900">{{ $transaction->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Refund Form -->
        <form action="{{ route('refunds.store', $transaction) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Refund Reason') }} <span class="text-red-500">*</span>
                </label>
                <select name="reason" id="reason" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason') border-red-500 @enderror">
                    <option value="">{{ __('Select a reason') }}</option>
                    <option value="not_as_described" {{ old('reason') === 'not_as_described' ? 'selected' : '' }}>
                        {{ __('Not as described') }}
                    </option>
                    <option value="duplicate_purchase" {{ old('reason') === 'duplicate_purchase' ? 'selected' : '' }}>
                        {{ __('Duplicate purchase') }}
                    </option>
                    <option value="technical_issue" {{ old('reason') === 'technical_issue' ? 'selected' : '' }}>
                        {{ __('Technical issue') }}
                    </option>
                    <option value="changed_mind" {{ old('reason') === 'changed_mind' ? 'selected' : '' }}>
                        {{ __('Changed my mind') }}
                    </option>
                    <option value="other" {{ old('reason') === 'other' ? 'selected' : '' }}>
                        {{ __('Other') }}
                    </option>
                </select>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason Description -->
            <div class="mb-6">
                <label for="reason_description" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Please provide more details') }} <span class="text-red-500">*</span>
                </label>
                <textarea name="reason_description" id="reason_description" rows="5" required
                    placeholder="{{ __('Please explain why you are requesting a refund...') }}"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason_description') border-red-500 @enderror">{{ old('reason_description') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">
                    {{ __('Minimum 20 characters. Please provide as much detail as possible.') }}
                </p>
                @error('reason_description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('wallet.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    {{ __('Submit Refund Request') }}
                </button>
            </div>
        </form>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">{{ __('Refund Policy') }}</p>
                    <p class="mt-1">
                        {{ __('Refund requests are reviewed within 24-48 hours. Once approved, the amount will be credited back to your wallet.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


