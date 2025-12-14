@extends('40-shared/layouts/app')

@section('title', 'Create Collection - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Create New Collection</h1>
                <p class="text-gray-600 mt-2">Create a personal collection to organize and save your favorite notes.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-red-900 mb-2">Please fix the errors below:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-700">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('collections.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Collection Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-900 mb-2">Collection Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., My Favorites, To Read, Business" required>
                        <p class="mt-1 text-xs text-gray-500">Maximum 255 characters</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                        <textarea id="description" name="description" rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="What's this collection about?">{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
                    </div>

                    <!-- Color Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-3">Collection Color</label>
                        <div class="flex flex-wrap gap-3">
                            @php
                                $colors = [
                                    '#3B82F6' => 'Blue',
                                    '#EF4444' => 'Red',
                                    '#10B981' => 'Green',
                                    '#F59E0B' => 'Amber',
                                    '#8B5CF6' => 'Purple',
                                    '#EC4899' => 'Pink',
                                    '#06B6D4' => 'Cyan',
                                    '#6B7280' => 'Gray',
                                ];
                            @endphp
                            @foreach ($colors as $hex => $name)
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="color" value="{{ $hex }}"
                                        @if (old('color') === $hex || $loop->first) checked @endif class="sr-only">
                                    <span class="inline-block w-8 h-8 rounded-full border-2 transition-transform"
                                        style="background-color: {{ $hex }};"
                                        :style="{ borderColor: checked ? '#000' : '{{ $hex }}' }">
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition-colors duration-200">
                            Create Collection
                        </button>
                        <a href="{{ route('collections.index') }}"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-medium py-2 rounded-lg text-center transition-colors duration-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tips -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-3">Tips for Collections</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>Create collections for different topics or purposes to stay organized</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>You can add notes to your collection after creation</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>Choose a color that helps you quickly identify your collections</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
