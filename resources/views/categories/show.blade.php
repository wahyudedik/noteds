@extends('layouts.app')

@section('title', $category->name)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('categories.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Categories') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
            @if($category->description)
                <p class="mt-2 text-sm text-gray-600">{{ $category->description }}</p>
            @endif
        </div>

        <!-- Subcategories -->
        @if($category->children->count() > 0)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Subcategories') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($category->children as $subcategory)
                        <a href="{{ route('categories.show', $subcategory) }}"
                            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h3 class="font-medium text-gray-900">{{ $subcategory->name }}</h3>
                            <p class="text-xs text-gray-500 mt-1">{{ $subcategory->notes->count() }} {{ __('notes') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Notes in Category -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                {{ __('Notes') }} ({{ $notes->total() }})
            </h2>
            @if($notes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($notes as $note)
                        <div class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow overflow-hidden">
                            <a href="{{ route('marketplace.show', $note) }}" class="block">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        {{ $note->title }}
                                    </h3>
                                    @if($note->summary)
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                            {{ Str::limit(strip_tags($note->summary), 100) }}
                                        </p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ currency($note->price) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ __('By') }} {{ $note->user->name }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $notes->links() }}
                </div>
            @else
                <p class="text-center text-gray-500 py-8">{{ __('No notes in this category yet.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

