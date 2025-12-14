@extends('40-shared/layouts/app')

@section('title', __('Post'))

@section('content')
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="mb-6">
                <a href="{{ route('forum.index') }}" class="text-blue-600 hover:text-blue-800">&larr;
                    {{ __('Back to Forum') }}</a>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <div class="flex items-start gap-4 mb-4">
                    <div class="flex-shrink-0">
                        @if ($post->user->avatar)
                            <img src="{{ $post->user->avatar }}" alt="{{ $post->user->name }}"
                                class="w-12 h-12 rounded-full object-cover">
                        @else
                            <div
                                class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">
                                {{ substr($post->user->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-900">{{ $post->user->name }}</span>
                            <span class="text-gray-500">@</span>
                            <span class="text-gray-600">{{ $post->user->username }}</span>
                            <span class="text-gray-400 text-sm">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                        @php
                            $postContent = is_string($post->content)
                                ? $post->content
                                : (is_array($post->content)
                                    ? implode(' ', $post->content)
                                    : '');
                        @endphp
                        <div class="prose max-w-none">{!! nl2br(e($postContent)) !!}</div>
                    </div>
                </div>

                @if ($post->media && $post->media->count() > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 mt-4">
                        @foreach ($post->media as $media)
                            @if ($media->type === 'image')
                                <img src="{{ $media->url }}" alt="Post media" class="w-full h-32 object-cover rounded-lg">
                            @elseif ($media->type === 'video')
                                <div
                                    class="rounded-lg overflow-hidden bg-gray-900 flex items-center justify-center h-32 relative">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z">
                                        </path>
                                    </svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
                    <div class="flex items-center gap-6 text-sm text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg>
                            {{ $post->all_comments_count ?? 0 }}
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v5">
                                </path>
                            </svg>
                            {{ $post->likes_count ?? 0 }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4">
                        @auth
                            <form action="{{ route('forum.like', $post) }}" method="POST">
                                @csrf
                                <button
                                    class="text-gray-500 hover:text-red-600 transition {{ $post->is_liked ? 'text-red-600' : '' }}"
                                    type="submit">
                                    <svg class="w-5 h-5 {{ $post->is_liked ? 'fill-current' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v5">
                                        </path>
                                    </svg>
                                </button>
                            </form>
                            <form action="{{ route('forum.bookmark', $post) }}" method="POST">
                                @csrf
                                <button
                                    class="text-gray-500 hover:text-yellow-600 transition {{ $post->is_bookmarked ? 'text-yellow-600' : '' }}"
                                    type="submit">
                                    <svg class="w-5 h-5 {{ $post->is_bookmarked ? 'fill-current' : '' }}" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                </button>
                            </form>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('Comments') }}</h3>
                @auth
                    <form action="{{ route('forum.comment', $post) }}" method="POST" class="mb-6">
                        @csrf
                        <textarea name="content" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="{{ __('Write a comment...') }}" required></textarea>
                        <div class="flex justify-end mt-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">{{ __('Comment') }}</button>
                        </div>
                    </form>
                @endauth

                @forelse ($comments as $comment)
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-white font-bold">
                                    {{ substr($comment->user->name, 0, 1) }}
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span class="font-semibold text-gray-900">{{ $comment->user->name }}</span>
                                    <span class="text-gray-400">·</span>
                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-gray-700">{{ $comment->content }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600">{{ __('No comments yet.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
