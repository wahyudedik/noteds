@extends('layouts.guest')

@section('title', 'Setup Username')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" style="margin-top: -64px;">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}" class="h-16 w-16">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Welcome to Noteds! 🎉
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Please choose a username to continue
            </p>
        </div>

        @if(session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-800">{{ session('warning') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('setup-username.store') }}" method="POST">
            @csrf

            <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                        Choose Your Username <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 text-sm">@</span>
                        </div>
                        <input 
                            type="text" 
                            name="username" 
                            id="username" 
                            value="{{ old('username') }}"
                            required 
                            autofocus
                            minlength="3"
                            maxlength="30"
                            pattern="[a-z0-9_-]+"
                            placeholder="johndoe"
                            class="block w-full pl-8 pr-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                            oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9_-]/g, '')"
                        >
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        <strong>Requirements:</strong>
                        <ul class="list-disc list-inside mt-1 space-y-1">
                            <li>3-30 characters</li>
                            <li>Only lowercase letters, numbers, dashes (-) and underscores (_)</li>
                            <li>This will be your unique identifier on Noteds</li>
                        </ul>
                    </p>
                    @error('username')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800">
                                Your username will be used for:
                            </h3>
                            <div class="mt-2 text-sm text-blue-700">
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Your public profile URL</li>
                                    <li>Sharing your notes and profile</li>
                                    <li>Finding you on Noteds</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Continue to Noteds
                    </button>
                </div>
            </div>
        </form>

        <div class="text-center">
            <p class="text-xs text-gray-500">
                You can change your username later in settings
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-format username input
    document.getElementById('username').addEventListener('input', function(e) {
        // Convert to lowercase and remove invalid characters
        let value = e.target.value.toLowerCase();
        value = value.replace(/[^a-z0-9_-]/g, '');
        e.target.value = value;
    });
</script>
@endpush
@endsection

