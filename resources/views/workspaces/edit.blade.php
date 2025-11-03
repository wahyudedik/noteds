@extends('layouts.app')

@section('title', 'Edit Workspace')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ route('workspaces.index') }}" class="text-gray-500 hover:text-gray-700 inline-flex items-center mb-4">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Workspaces
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Edit Workspace</h1>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <form action="{{ route('workspaces.update', $workspace) }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Workspace Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $workspace->name) }}" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            Workspace Type <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                            <option value="personal" {{ old('type', $workspace->type) === 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="team" {{ old('type', $workspace->type) === 'team' ? 'selected' : '' }}>Team</option>
                            <option value="organization" {{ old('type', $workspace->type) === 'organization' ? 'selected' : '' }}>Organization</option>
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description (Optional)
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">{{ old('description', $workspace->description) }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Active Status -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $workspace->is_active) ? 'checked' : '' }}
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <span class="ml-3 text-sm font-medium text-gray-700">Active Workspace</span>
                        </label>
                        <p class="mt-1 text-xs text-gray-500">Inactive workspaces won't appear in most lists</p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('workspaces.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            Update Workspace
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

