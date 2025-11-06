@extends('layouts.app')

@section('title', $collection->name)

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('collections.index') }}" class="mr-4 text-gray-600 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $collection->name }}</h1>
                    @if($collection->description)
                        <p class="mt-2 text-sm text-gray-600">{{ $collection->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('collections.edit', $collection) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Edit
                </a>
            </div>
        </div>

        <!-- Notes Grid -->
        @if($collection->notes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($collection->notes as $note)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <a href="{{ route('marketplace.show', $note) }}" class="block">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">{{ $note->title }}</h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ Str::limit(strip_tags($note->summary ?? $note->content), 100) }}</p>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        {{ $note->user->name }}
                                    </div>
                                    @if($note->price > 0)
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ currency($note->hasDiscount() ? $note->discount_price : $note->price) }}
                                        </span>
                                    @else
                                        <span class="text-sm font-semibold text-blue-600">FREE</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                            <form action="{{ route('collections.remove-note', ['collection' => $collection, 'note' => $note]) }}" method="POST" onsubmit="return confirm('Remove this note from collection?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                    Remove from Collection
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No notes in this collection</h3>
                <p class="mt-1 text-sm text-gray-500">Add notes from the marketplace to this collection.</p>
            </div>
        @endif
    </div>
</div>
@endsection
