@extends('40-shared/layouts/app')

@section('title', $tutorial->title . ' — ' . __('messages.tuts'))

@push('meta')
        @php
        use Illuminate\Support\Facades\Storage;
        $shareUrl = route('tuts.show', $tutorial);
        $shareTitle = $tutorial->title;
        $shareDescription = $tutorial->description ?? Str::limit(strip_tags($tutorial->content), 200);
        $shareImage = $tutorial->thumbnail ? url(Storage::url($tutorial->thumbnail)) : null;
    @endphp
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    @if ($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $shareUrl }}">
    <meta property="twitter:title" content="{{ $shareTitle }}">
    <meta property="twitter:description" content="{{ $shareDescription }}">
    @if ($shareImage)
        <meta property="twitter:image" content="{{ $shareImage }}">
    @endif

    <!-- Additional Meta -->
    <meta name="description" content="{{ $shareDescription }}">
@endpush

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('tuts.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Tutorials
            </a>
        </div>

        <!-- Tutorial Header -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="px-3 py-1 text-sm rounded-full 
                    {{ $tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : '' }}
                    {{ $tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                    {{ $tutorial->category_label }}
                </span>
                @if($tutorial->featured)
                    <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                @endif
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $tutorial->title }}</h1>

            @if($tutorial->description)
                <p class="text-lg text-gray-600 mb-4">{{ $tutorial->description }}</p>
            @endif

            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ $tutorial->author->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span>{{ number_format($tutorial->views_count) }} views</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ $tutorial->created_at->format('d M Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Thumbnail -->
        @if($tutorial->thumbnail)
            <div class="mb-6 rounded-lg overflow-hidden">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($tutorial->thumbnail) }}" alt="{{ $tutorial->title }}" class="w-full h-auto">
            </div>
        @endif

        <!-- Video -->
        @if($tutorial->video_url)
            <div class="mb-6 rounded-lg overflow-hidden bg-gray-900">
                <div class="aspect-video">
                    @php
                        // Parse YouTube/Vimeo URL
                        $videoId = null;
                        $videoType = null;
                        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $tutorial->video_url, $matches)) {
                            $videoId = $matches[1];
                            $videoType = 'youtube';
                        } elseif (preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)/', $tutorial->video_url, $matches)) {
                            $videoId = $matches[1];
                            $videoType = 'vimeo';
                        }
                    @endphp
                    @if($videoType === 'youtube')
                        <iframe src="https://www.youtube.com/embed/{{ $videoId }}" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    @elseif($videoType === 'vimeo')
                        <iframe src="https://player.vimeo.com/video/{{ $videoId }}" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allow="autoplay; fullscreen; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <a href="{{ $tutorial->video_url }}" target="_blank" class="text-white hover:text-blue-400">
                                Watch Video
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Content -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="prose max-w-none quill-content">
                {!! $tutorial->content !!}
            </div>
        </div>

        <!-- Related Tutorials -->
        @if($relatedTutorials->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Related Tutorials</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($relatedTutorials as $related)
                        <a href="{{ route('tuts.show', $related) }}" class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            @if($related->thumbnail)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($related->thumbnail) }}" alt="{{ $related->title }}" class="w-24 h-24 object-cover rounded">
                            @else
                                <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-purple-500 rounded flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1">{{ $related->title }}</h3>
                                <p class="text-xs text-gray-500">{{ number_format($related->views_count) }} views • {{ $related->created_at->diffForHumans() }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
<style>
.quill-content {
    line-height: 1.8;
}

.quill-content h1, .quill-content h2, .quill-content h3 {
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    font-weight: 700;
}

.quill-content h1 {
    font-size: 2em;
}

.quill-content h2 {
    font-size: 1.5em;
}

.quill-content h3 {
    font-size: 1.25em;
}

.quill-content p {
    margin-bottom: 1em;
}

.quill-content ul, .quill-content ol {
    margin-bottom: 1em;
    padding-left: 2em;
}

.quill-content li {
    margin-bottom: 0.5em;
}

.quill-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}

.quill-content pre {
    background-color: #f3f4f6;
    padding: 1em;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1em 0;
}

.quill-content code {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25rem;
    font-size: 0.9em;
}

.quill-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1em;
    margin: 1em 0;
    font-style: italic;
    color: #6b7280;
}

.quill-content a {
    color: #3b82f6;
    text-decoration: underline;
}

.quill-content a:hover {
    color: #2563eb;
}
</style>
@endpush
@endsection


