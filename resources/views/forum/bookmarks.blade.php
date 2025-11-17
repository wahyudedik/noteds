@extends('layouts.app')

@section('title', __('messages.bookmarked_posts_forum'))

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Bookmarked Posts</h1>
                    <p class="mt-2 text-sm text-gray-600">Your saved posts</p>
                </div>
                <a href="{{ route('forum.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Forum
                </a>
            </div>
        </div>

        <!-- Bookmarked Posts -->
        @if($bookmarkedPosts->count() > 0)
            <div class="space-y-4">
                @foreach($bookmarkedPosts as $post)
                    @php
                        $post->is_bookmarked = true;
                    @endphp
                    @include('forum.partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $bookmarkedPosts->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No bookmarked posts</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't bookmarked any posts yet. Start bookmarking posts you want to save!</p>
                <div class="mt-6">
                    <a href="{{ route('forum.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Browse Forum
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

