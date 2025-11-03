@extends('layouts.app')

@section('title', 'Create Workspace')

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
            <h1 class="text-3xl font-bold text-gray-900">Create New Workspace</h1>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <form action="{{ route('workspaces.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Workspace Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="e.g., My Team, Company Notes"
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
                            <option value="">Select type...</option>
                            <option value="personal" {{ old('type') === 'personal' ? 'selected' : '' }}>Personal</option>
                            <option value="team" {{ old('type') === 'team' ? 'selected' : '' }}>Team</option>
                            <option value="organization" {{ old('type') === 'organization' ? 'selected' : '' }}>Organization</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">
                            <strong>Personal:</strong> For your own use<br>
                            <strong>Team:</strong> For small teams and collaboration<br>
                            <strong>Organization:</strong> For larger organizations
                        </p>
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
                            placeholder="Describe the purpose of this workspace"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('workspaces.index') }}" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            Cancel
                        </a>
                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            Create Workspace
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

