@extends('layouts.app')

@section('title', __('messages.create_collection'))

@section('content')
<div class="py-8">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('collections.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Collections
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Collection</h1>

            <form action="{{ route('collections.store') }}" method="POST">
                @csrf

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Collection Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $collection->name ?? '') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description (Optional)
                    </label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $collection->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div class="mb-6">
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                        Color
                    </label>
                    <div class="flex items-center space-x-4">
                        <input type="color" name="color" id="color" value="{{ old('color', $collection->color ?? '#3B82F6') }}"
                               class="h-10 w-20 rounded border-gray-300">
                        <input type="text" name="color" value="{{ old('color', $collection->color ?? '#3B82F6') }}" readonly
                               id="color-display" class="flex-1 rounded-lg border-gray-300 shadow-sm bg-gray-50">
                    </div>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('collections.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ isset($collection) ? 'Update Collection' : 'Create Collection' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('color').addEventListener('input', function(e) {
        document.getElementById('color-display').value = e.target.value;
    });
</script>
@endpush
@endsection
