@extends('layouts.app')

@section('title', 'Create Support Ticket')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('support-tickets.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Create Support Ticket</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">Report an issue or request assistance</p>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <form action="{{ route('support-tickets.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        placeholder="Brief description of your issue..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 {{ old('priority') === 'low' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="priority" value="low" {{ old('priority') === 'low' ? 'checked' : '' }} required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900">Low</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 {{ old('priority') === 'medium' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="priority" value="medium" {{ old('priority') === 'medium' ? 'checked' : '' }} required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900">Medium</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 {{ old('priority') === 'high' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="priority" value="high" {{ old('priority') === 'high' ? 'checked' : '' }} required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900">High</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 {{ old('priority') === 'urgent' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300' }}">
                            <input type="radio" name="priority" value="urgent" {{ old('priority') === 'urgent' ? 'checked' : '' }} required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900">Urgent</span>
                            </div>
                        </label>
                    </div>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="8" required
                        placeholder="Provide detailed information about your issue, what you were trying to do, and any error messages..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Minimum 20 characters. Be as detailed as possible to help us assist you better.</p>
                </div>

                <!-- Links -->
                <div class="mb-6">
                    <label for="links" class="block text-sm font-medium text-gray-700 mb-2">
                        Related Links (Optional)
                    </label>
                    <input type="text" name="links" id="links" value="{{ old('links') }}"
                        placeholder="Paste URLs separated by commas..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    @error('links')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Add any relevant links (e.g., screenshots, documents, GitHub issues)</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('support-tickets.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Create Ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

