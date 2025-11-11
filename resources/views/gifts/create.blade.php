@extends('layouts.app')

@section('title', __('Send Gift Note'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('marketplace.show', $note) }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Note') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Send Gift Note') }}</h1>
        </div>

        <!-- Note Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Note Details') }}</h2>
            <div class="space-y-3">
                <div>
                    <span class="text-sm text-gray-600">{{ __('Title') }}:</span>
                    <span class="text-sm font-medium text-gray-900 ml-2">{{ $note->title }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">{{ __('Price') }}:</span>
                    <span class="text-sm font-medium text-gray-900 ml-2">{{ currency($note->price) }}</span>
                </div>
                <div>
                    <span class="text-sm text-gray-600">{{ __('Seller') }}:</span>
                    <span class="text-sm font-medium text-gray-900 ml-2">{{ $note->user->name }}</span>
                </div>
            </div>
        </div>

        <!-- Gift Form -->
        <form action="{{ route('gifts.store', $note) }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Recipient Email -->
            <div class="mb-6">
                <label for="recipient_email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Recipient Email') }} <span class="text-red-500">*</span>
                </label>
                <input type="email" name="recipient_email" id="recipient_email" required value="{{ old('recipient_email') }}"
                    placeholder="{{ __('Enter recipient email address') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('recipient_email') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">
                    {{ __('The recipient must have an account on this platform.') }}
                </p>
                @error('recipient_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Message -->
            <div class="mb-6">
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Gift Message') }} ({{ __('Optional') }})
                </label>
                <textarea name="message" id="message" rows="4"
                    placeholder="{{ __('Add a personal message...') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wallet Balance Check -->
            @php
                $wallet = auth()->user()->wallet;
                $hasEnoughBalance = $wallet && $wallet->balance >= $note->price;
            @endphp

            @if(!$hasEnoughBalance)
                <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">{{ __('Insufficient Balance') }}</p>
                            <p class="mt-1">
                                {{ __('Your wallet balance is') }} {{ currency($wallet->balance ?? 0) }}.
                                {{ __('You need') }} {{ currency($note->price) }} {{ __('to send this gift.') }}
                            </p>
                            <a href="{{ route('wallet.index') }}" class="mt-2 inline-block text-sm font-medium text-yellow-900 underline">
                                {{ __('Top up your wallet') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('marketplace.show', $note) }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" {{ !$hasEnoughBalance ? 'disabled' : '' }}
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('Send Gift') }}
                </button>
            </div>
        </form>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">{{ __('Gift Note Information') }}</p>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        <li>{{ __('The recipient will receive an email notification') }}</li>
                        <li>{{ __('Gift expires in 30 days if not claimed') }}</li>
                        <li>{{ __('Once claimed, the note will be added to recipient\'s library') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

