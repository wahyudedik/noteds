@extends('40-shared/layouts/app')

@section('title', __('messages.edit_folder'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('folders.index') }}" class="text-gray-500 hover:text-gray-700 inline-flex items-center mb-4">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_folders') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.edit_folder') }}</h1>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <form action="{{ route('folders.update', $folder) }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.folder_name') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $folder->name) }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Parent Folder -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.parent_folder') }}
                        </label>
                        <select name="parent_id" id="parent_id"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('messages.none_root_folder') }}</option>
                            @foreach($folders as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id', $folder->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.description') }} ({{ __('messages.optional') }})
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('description', $folder->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.folder_color') }}
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="color" name="color" id="color" value="{{ old('color', $folder->color ?? '#3B82F6') }}"
                                class="h-10 w-20 rounded-lg border border-gray-300 cursor-pointer">
                            <input type="text" name="color_text" id="color_text" value="{{ old('color', $folder->color ?? '#3B82F6') }}"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                placeholder="#3B82F6"
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">{{ __('messages.choose_color_identify') }}</p>
                        @error('color')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('folders.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            {{ __('messages.cancel') }}
                        </a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                            {{ __('messages.update_folder') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Sync color picker and text input
    document.getElementById('color').addEventListener('input', function(e) {
        document.getElementById('color_text').value = e.target.value;
    });
    
    document.getElementById('color_text').addEventListener('input', function(e) {
        if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
            document.getElementById('color').value = e.target.value;
        }
    });
</script>
@endpush
@endsection


