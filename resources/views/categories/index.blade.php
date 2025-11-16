@extends('layouts.app')

@section('title', __('Categories'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Categories') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('Browse notes by category') }}</p>
        </div>

        <!-- Categories Grid -->
        @if($categories->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow p-6 block">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            {{ $category->name }}
                        </h3>
                        @if($category->description)
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                {{ Str::limit($category->description, 100) }}
                            </p>
                        @endif
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                {{ $category->notes->count() }} {{ __('notes') }}
                            </span>
                            @if($category->children->count() > 0)
                                <span class="text-xs text-blue-600">
                                    {{ $category->children->count() }} {{ __('subcategories') }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $categories->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No categories found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('No categories are available at the moment.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

