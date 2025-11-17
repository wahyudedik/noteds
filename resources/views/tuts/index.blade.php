@extends('layouts.app')

@section('title', __('messages.tuts') . ' — ' . __('messages.education_creative_coding'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.tuts') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.tuts_description') ?? 'Platform edukasi berisi tutorial & kursus tentang desain, coding, fotografi, dan kreativitas digital.' }}</p>
        </div>

        <!-- Search and Filter Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('tuts.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                            placeholder="Search tutorials..."
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" id="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">All Categories</option>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="featured" class="block text-sm font-medium text-gray-700 mb-2">Filter</label>
                        <select name="featured" id="featured" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">All Tutorials</option>
                            <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured Only</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'category', 'featured']))
                            <a href="{{ route('tuts.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <!-- Categories Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Categories</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @foreach($categories as $key => $label)
                    <a href="{{ route('tuts.index', ['category' => $key]) }}" 
                       class="p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                <h3 class="font-semibold text-gray-900">{{ $label }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            @php
                                $count = \App\Models\Tutorial::published()->category($key)->count();
                            @endphp
                            {{ $count }} {{ $count === 1 ? 'tutorial' : 'tutorials' }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Tutorials Grid -->
        @if($tutorials->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($tutorials as $tutorial)
                    <a href="{{ route('tuts.show', $tutorial) }}" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 overflow-hidden group">
                        @if($tutorial->thumbnail)
                            <div class="aspect-video w-full overflow-hidden bg-gray-100">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($tutorial->thumbnail) }}" alt="{{ $tutorial->title }}" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @else
                            <div class="aspect-video w-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                    {{ $tutorial->category_label }}
                                </span>
                                @if($tutorial->featured)
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                                @endif
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                {{ $tutorial->title }}
                            </h3>
                            @if($tutorial->description)
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    {{ $tutorial->description }}
                                </p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span>{{ $tutorial->author->name }}</span>
                                <div class="flex items-center gap-2">
                                    <span>{{ number_format($tutorial->views_count) }} views</span>
                                    <span>•</span>
                                    <span>{{ $tutorial->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $tutorials->links() }}
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">No tutorials found</h3>
                <p class="text-gray-600">Try adjusting your search or filter criteria.</p>
            </div>
        @endif
    </div>
</div>
@endsection
