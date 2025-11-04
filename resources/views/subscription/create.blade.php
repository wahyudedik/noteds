@extends('layouts.app')

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

        <!-- Pricing Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="p-6">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800 mb-4">
                        {{ __('messages.premium_plan') }}
                    </div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ __('messages.upgrade_now') }}</h2>
                    <p class="text-base text-gray-600 mb-2">{{ __('messages.get_all_premium_features') }}</p>
                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\Setting::formatPremiumPrice(false) }}</p>
                </div>

                <form action="{{ route('subscription.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="payment_proof" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.payment_proof_url') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="url" name="payment_proof" id="payment_proof" value="{{ old('payment_proof') }}" required
                            :placeholder="__('messages.enter_payment_proof_url')"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('payment_proof') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <p class="mt-2 text-xs text-gray-500">{{ __('messages.upload_payment_proof') }}</p>
                        @error('payment_proof')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-blue-900 mb-1">{{ __('messages.note') }}</h3>
                                <p class="text-sm text-blue-800">{{ __('messages.after_submitting_request') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('subscription.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            {{ __('messages.submit_request') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

