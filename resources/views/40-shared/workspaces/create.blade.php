@extends('40-shared/layouts/app')

@section('title', 'Create Workspace - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('workspaces.index') }}" class="text-blue-600 hover:text-blue-700">← Back</a>
                <h1 class="text-3xl font-bold text-gray-900">Create Workspace</h1>
            </div>

            <div class="bg-white rounded-lg shadow p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        @foreach ($errors->all() as $error)
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Workspace Name</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., My Design Studio" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                        <textarea name="description" rows="5"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 resize-none"
                            placeholder="What is this workspace for?">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 mb-2">Workspace Logo</label>
                        <input type="file" name="logo" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Industry</label>
                            <select name="industry"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select industry</option>
                                <option value="design">Design</option>
                                <option value="development">Development</option>
                                <option value="marketing">Marketing</option>
                                <option value="business">Business</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-900 mb-2">Team Size</label>
                            <select name="team_size"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="">Select size</option>
                                <option value="solo">Solo (Just me)</option>
                                <option value="small">Small (2-5)</option>
                                <option value="medium">Medium (6-20)</option>
                                <option value="large">Large (20+)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Create Workspace
                        </button>
                        <a href="{{ route('workspaces.index') }}"
                            class="bg-gray-200 hover:bg-gray-300 text-gray-900 px-6 py-2 rounded-lg">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="font-semibold text-blue-900 mb-3">Workspace Benefits</h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>Organize and manage your notes with team members</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>Set permissions and control access levels</span>
                    </li>
                    <li class="flex items-start">
                        <span class="mr-3">✓</span>
                        <span>Collaborate on projects and track progress</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection
