@extends('layouts.app')

@section('title', $documentation->title)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="{{ route('docs.index') }}" class="hover:text-blue-600 transition-colors duration-200">Documentation</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li><a href="{{ route('docs.category', $documentation->category) }}" class="hover:text-blue-600 transition-colors duration-200">{{ $documentation->category_label }}</a></li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </li>
                <li class="text-gray-900 font-medium">{{ Str::limit($documentation->title, 50) }}</li>
            </ol>
        </nav>

        <!-- Article Header -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <span class="inline-flex items-center px-3 py-1 rounded text-sm font-medium bg-blue-100 text-blue-800 mb-3">
                        {{ $documentation->category_label }}
                    </span>
                    <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $documentation->title }}</h1>
                    @if($documentation->summary)
                        <p class="text-lg text-gray-600 mb-4">{{ $documentation->summary }}</p>
                    @endif
                </div>
                @if($documentation->icon)
                    <span class="text-4xl">{{ $documentation->icon }}</span>
                @endif
            </div>

            <!-- Meta Info -->
            <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-gray-200 text-sm text-gray-600">
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ $documentation->creator->name ?? 'Admin' }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ localized_time($documentation->created_at, 'date') }}
                </div>
                <div class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    {{ number_format($documentation->view_count) }} views
                </div>
            </div>

            <!-- Tags -->
            @if($documentation->tags && count($documentation->tags) > 0)
                <div class="flex flex-wrap gap-2 mt-4">
                    @foreach($documentation->tags as $tag)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Article Content -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="prose max-w-none">
                <div class="ql-editor text-gray-900 leading-relaxed">
                    {!! $documentation->content !!}
                </div>
            </div>
        </div>

        <!-- Links Section -->
        @if($documentation->links && count($documentation->links) > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    Related Links
                </h3>
                <ul class="space-y-2">
                    @foreach($documentation->links as $link)
                        <li>
                            <a href="{{ $link['url'] ?? '#' }}" target="_blank" rel="noopener noreferrer" class="flex items-center text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                {{ $link['title'] ?? $link['url'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Video URLs Section -->
        @if($documentation->video_urls && count($documentation->video_urls) > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Video Tutorials
                </h3>
                <div class="space-y-4">
                    @foreach($documentation->video_urls as $videoUrl)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer" class="flex items-center text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $videoUrl }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Helpful Section -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">Was this helpful?</h3>
                    <p class="text-sm text-gray-600">{{ number_format($documentation->helpful_count) }} people found this helpful</p>
                </div>
                @auth
                    <form action="{{ route('docs.helpful', [$documentation->category, $documentation->slug]) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5" />
                            </svg>
                            Yes, it helped!
                        </button>
                    </form>
                @endauth
            </div>
        </div>

        <!-- Related Documentation -->
        @if($relatedDocs->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Related Documentation</h3>
                <div class="space-y-3">
                    @foreach($relatedDocs as $related)
                        <a href="{{ route('docs.show', [$related->category, $related->slug]) }}" class="block p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-all duration-200">
                            <h4 class="font-medium text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                {{ $related->title }}
                            </h4>
                            @if($related->summary)
                                <p class="text-sm text-gray-600 mt-1 line-clamp-1">
                                    {!! Str::limit(strip_tags($related->summary), 80) !!}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

