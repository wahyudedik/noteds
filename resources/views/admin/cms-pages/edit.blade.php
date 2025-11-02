@extends('layouts.app')

@section('title', 'Admin - Edit CMS Page')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Edit CMS Page</h2>
            <a href="{{ route('admin.cms-pages.index') }}" class="text-gray-600 hover:text-gray-800">← Back to CMS Pages</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.cms-pages.update', $cmsPage) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Slug -->
                <div class="mb-6">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        Slug (URL) <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $cmsPage->slug) }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">URL-friendly identifier. Accessible at: /page/slug</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="{{ old('title', $cmsPage->title) }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Content <span class="text-red-600">*</span>
                    </label>
                    <textarea 
                        id="content"
                        name="content"
                        rows="15"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('content') border-red-500 @enderror">{{ old('content', $cmsPage->content) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">Use new lines for formatting. HTML is supported.</p>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Meta Title -->
                <div class="mb-6">
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                        Meta Title (SEO)
                    </label>
                    <input type="text" 
                        id="meta_title"
                        name="meta_title"
                        value="{{ old('meta_title', $cmsPage->meta_title) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('meta_title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Meta Description -->
                <div class="mb-6">
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                        Meta Description (SEO)
                    </label>
                    <textarea 
                        id="meta_description"
                        name="meta_description"
                        rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('meta_description', $cmsPage->meta_description) }}</textarea>
                    @error('meta_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                            name="is_active"
                            value="1"
                            {{ old('is_active', $cmsPage->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Active (visible on public page)</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.cms-pages.index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors duration-200">
                        Update Page
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

