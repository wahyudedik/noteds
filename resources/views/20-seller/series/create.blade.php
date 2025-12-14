@extends('40-shared/layouts/app')

@section('title', __('Create Series'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('series.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Series') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Series') }}</h1>
        </div>

        <form action="{{ route('series.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Series Title') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required value="{{ old('title') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Description') }}
                </label>
                <textarea name="description" id="description" rows="4"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">{{ __('Active') }}</span>
                </label>
                <p class="mt-1 text-xs text-gray-500">{{ __('Active series will be visible to others.') }}</p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('series.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    {{ __('Create Series') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


