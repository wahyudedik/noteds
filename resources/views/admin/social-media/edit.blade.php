@extends('layouts.app')

@section('title', 'Add Social Media Link')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Social Media Link</h1>
                    <p class="mt-2 text-base text-gray-600">Update social media link information</p>
                </div>
                <a href="{{ route('admin.social-media.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    ← Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('admin.social-media.update', $socialMedia) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                <!-- Platform -->
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700 mb-2">
                        Platform <span class="text-red-500">*</span>
                    </label>
                    <select name="platform" id="platform" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('platform') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Select Platform</option>
                        @foreach($platforms as $key => $label)
                            <option value="{{ $key }}" {{ old('platform', $socialMedia->platform) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose the social media platform</p>
                    @error('platform')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Display Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        id="name"
                        name="name"
                        value="{{ old('name', $socialMedia->name) }}"
                        required
                        placeholder="e.g., Facebook Page"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- URL -->
                <div>
                    <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                        URL <span class="text-red-500">*</span>
                    </label>
                    <input type="url" 
                        id="url"
                        name="url"
                        value="{{ old('url', $socialMedia->url) }}"
                        required
                        placeholder="https://facebook.com/yourpage"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('url') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Custom Icon (Optional) -->
                <div>
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Custom Icon (Optional)
                    </label>
                    <textarea name="icon" id="icon" rows="4"
                        placeholder="<svg>...</svg> or leave empty to use default icon"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 font-mono text-sm">{{ old('icon', $socialMedia->icon) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">Leave empty to use default platform icon</p>
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                        Icon Color (Optional)
                    </label>
                    <input type="text" 
                        id="color"
                        name="color"
                        value="{{ old('color', $socialMedia->color) }}"
                        placeholder="#1877f2 or currentColor"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Hex color (e.g., #1877f2) or 'currentColor' for theme color</p>
                </div>

                <!-- Order -->
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        Display Order
                    </label>
                    <input type="number" 
                        id="order"
                        name="order"
                        value="{{ old('order', $socialMedia->order) }}"
                        min="0"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" 
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $socialMedia->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active (visible in footer)
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.social-media.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Update Link
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

