@extends('layouts.app')

@section('title', __('messages.documentation'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.documentation') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.find_guides_tutorials') }}</p>
        </div>

        <!-- Search -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('docs.index') }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                    :placeholder="__('messages.search_documentation')"
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ __('messages.search') }}
                </button>
            </form>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($categories as $key => $label)
                <a href="{{ route('docs.category', $key) }}" class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 p-6 group">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            @if($key === 'wiki')
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-2xl">
                                    📚
                                </div>
                            @elseif($key === 'screenshot_guide')
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center text-2xl">
                                    📸
                                </div>
                            @elseif($key === 'link_reference')
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-2xl">
                                    🔗
                                </div>
                            @elseif($key === 'troubleshooting')
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-2xl">
                                    🔧
                                </div>
                            @elseif($key === 'api_documentation')
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center text-2xl">
                                    ⚡
                                </div>
                            @else
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-2xl">
                                    🎥
                                </div>
                            @endif
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">
                                {{ $label }}
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                {{ $categoryCounts[$key] ?? 0 }} {{ ($categoryCounts[$key] ?? 0) == 1 ? __('messages.article') : __('messages.articles') }}
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Recent Documentation -->
        @if($documentations->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.all_documentation') }}</h2>
                    @if(request('search'))
                        <a href="{{ route('docs.index') }}" class="text-sm text-blue-600 hover:text-blue-700">
                            {{ __('messages.clear_search') }}
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($documentations as $doc)
                        <a href="{{ route('docs.show', [$doc->category, $doc->slug]) }}" class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $doc->category_label }}
                                    </span>
                                    @if($doc->icon)
                                        <iconify-icon icon="{{ $doc->icon }}" width="22" height="22" class="text-blue-500"></iconify-icon>
                                    @endif
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                    {{ $doc->title }}
                                </h3>
                                @if($doc->summary)
                                    <p class="text-sm text-gray-600 line-clamp-3 mb-4">
                                        {!! Str::limit(strip_tags($doc->summary), 100) !!}
                                    </p>
                                @endif
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ $doc->creator->name ?? 'Admin' }}</span>
                                    <span>{{ $doc->view_count }} views</span>
                                </div>
                                @if($doc->tags && count($doc->tags) > 0)
                                    <div class="flex flex-wrap gap-1 mt-3">
                                        @foreach(array_slice($doc->tags, 0, 3) as $tag)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $documentations->links() }}
                </div>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_documentation_found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">
                    @if(request('search'))
                        Try adjusting your search terms.
                    @else
                        Documentation will be available soon.
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection

