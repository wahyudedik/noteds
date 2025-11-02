@extends('layouts.app')

@section('title', 'Admin - Create Subscription')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('admin.subscriptions.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Create Subscription</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">Manually create a subscription for a user</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="p-6">
                @csrf

                <!-- User Selection -->
                <div class="mb-6">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Select User</label>
                    <select name="user_id" id="user_id" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        <option value="">Choose a user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') === $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }}) - {{ ucfirst($user->role) }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plan Selection -->
                <div class="mb-6">
                    <label for="plan" class="block text-sm font-medium text-gray-700 mb-2">Plan Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none {{ old('plan') === 'basic' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="plan" value="basic" {{ old('plan') === 'basic' ? 'checked' : '' }} required class="sr-only">
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900">Basic</span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">10 notes total</span>
                                </div>
                            </div>
                            <svg class="{{ old('plan') === 'basic' ? 'text-blue-600' : 'invisible' }} h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none {{ old('plan') === 'premium' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="plan" value="premium" {{ old('plan') === 'premium' ? 'checked' : '' }} required class="sr-only">
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900">Premium</span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">Unlimited notes</span>
                                </div>
                            </div>
                            <svg class="{{ old('plan') === 'premium' ? 'text-blue-600' : 'invisible' }} h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>
                    @error('plan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expiration Date -->
                <div class="mb-6">
                    <label for="expired_at" class="block text-sm font-medium text-gray-700 mb-2">Expiration Date</label>
                    <input type="date" name="expired_at" id="expired_at" value="{{ old('expired_at') }}" required
                        min="{{ date('Y-m-d') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                    @error('expired_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Select when this subscription should expire</p>
                </div>

                <!-- Admin Notes -->
                <div class="mb-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                    <textarea name="admin_notes" id="admin_notes" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200"
                        placeholder="Add any notes or context for this subscription...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.subscriptions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Create Subscription
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

