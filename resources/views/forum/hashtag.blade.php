@extends('layouts.app')

@section('title', '#' . $hashtag->name . ' - ' . __('messages.hashtag_forum'))

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">#{{ $hashtag->name }}</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ $hashtag->posts_count }} posts</p>
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

        <!-- Posts Feed -->
        @if($posts->count() > 0)
            <div class="space-y-4">
                @foreach($posts as $post)
                    @include('forum.partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No posts found</h3>
                <p class="mt-1 text-sm text-gray-500">No posts have been tagged with #{{ $hashtag->name }} yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection

