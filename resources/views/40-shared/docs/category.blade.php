@extends('40-shared/layouts/app')

@section('title', $categories[$category] ?? 'Documentation')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('docs.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    {{ __('messages.back_to_all_documentation') }}
                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $categories[$category] ?? __('messages.documentation') }}</h1>
            <p class="mt-2 text-base text-gray-600">
                @if($category === 'wiki')
                    {{ __('messages.comprehensive_guides_noteds') }}
                @elseif($category === 'screenshot_guide')
                    {{ __('messages.step_by_step_guides') }}
                @elseif($category === 'link_reference')
                    {{ __('messages.useful_links_resources') }}
                @elseif($category === 'troubleshooting')
                    {{ __('messages.solutions_common_problems') }}
                @elseif($category === 'api_documentation')
                    {{ __('messages.technical_api_documentation') }}
                @else
                    {{ __('messages.video_tutorials_walkthroughs') }}
                @endif
            </p>
        </div>

        <!-- Search -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('docs.category', $category) }}" class="flex gap-4">
                <input type="text" name="search" value="{{ request('search') }}" 
                    :placeholder="__('messages.search_in_category', ['category' => $categories[$category] ?? __('messages.documentation')])"
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    {{ __('messages.search') }}
                </button>
            </form>
        </div>

        <!-- Documentation List -->
        @if($documentations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($documentations as $doc)
                    <a href="{{ route('docs.show', [$doc->category, $doc->slug]) }}" class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                        <div class="p-6">
                            @if($doc->icon)
                                <div class="text-3xl mb-3 text-blue-500">
                                    <iconify-icon icon="{{ $doc->icon }}" width="28" height="28"></iconify-icon>
                                </div>
                            @endif
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
                                <span>{{ $doc->view_count }} {{ $doc->view_count == 1 ? __('messages.view') : __('messages.views') }}</span>
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
        @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_documentation_found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">
                    @if(request('search'))
                        {{ __('messages.try_adjusting_search') }}
                    @else
                        {{ __('messages.no_articles_available') }}
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection


