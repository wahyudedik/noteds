@extends('layouts.app')

@section('title', __('Create Webhook'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('webhooks.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Webhooks') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Webhook') }}</h1>
        </div>

        <form action="{{ route('webhooks.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Webhook Name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}"
                    placeholder="{{ __('My Webhook') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- URL -->
            <div class="mb-6">
                <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Webhook URL') }} <span class="text-red-500">*</span>
                </label>
                <input type="url" name="url" id="url" required value="{{ old('url') }}"
                    placeholder="https://example.com/webhook"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('url') border-red-500 @enderror">
                <p class="mt-1 text-xs text-gray-500">{{ __('The URL where webhook events will be sent.') }}</p>
                @error('url')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Event -->
            <div class="mb-6">
                <label for="event" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Event Type') }} <span class="text-red-500">*</span>
                </label>
                <select name="event" id="event" required
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('event') border-red-500 @enderror">
                    <option value="">{{ __('Select an event') }}</option>
                    <option value="note.purchased" {{ old('event') === 'note.purchased' ? 'selected' : '' }}>{{ __('Note Purchased') }}</option>
                    <option value="note.created" {{ old('event') === 'note.created' ? 'selected' : '' }}>{{ __('Note Created') }}</option>
                    <option value="note.updated" {{ old('event') === 'note.updated' ? 'selected' : '' }}>{{ __('Note Updated') }}</option>
                    <option value="transaction.completed" {{ old('event') === 'transaction.completed' ? 'selected' : '' }}>{{ __('Transaction Completed') }}</option>
                    <option value="withdraw.approved" {{ old('event') === 'withdraw.approved' ? 'selected' : '' }}>{{ __('Withdraw Approved') }}</option>
                    <option value="subscription.renewed" {{ old('event') === 'subscription.renewed' ? 'selected' : '' }}>{{ __('Subscription Renewed') }}</option>
                </select>
                @error('event')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('webhooks.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    {{ __('Create Webhook') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

