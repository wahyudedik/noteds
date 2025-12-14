@extends('40-shared/layouts/app')

@section('title', __('Forum'))

@section('content')
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">{{ __('Forum') }}</h1>
                <p class="text-lg text-gray-600">{{ __('Join the discussion with our community') }}</p>
            </div>

            <!-- Create Post Button -->
            @auth
                <div class="mb-8">
                    <button type="button" data-modal-target="create-post-modal"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        {{ __('Create a Post') }}
                    </button>
                </div>
            @else
                <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <p class="text-blue-900">
                        {{ __('Please') }}
                        <a href="{{ route('login') }}"
                            class="font-semibold underline hover:no-underline">{{ __('login') }}</a>
                        {{ __('to create a post') }}
                    </p>
                </div>
            @endauth

            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col sm:flex-row gap-4">
                        <div class="flex-1">
                            <form action="{{ route('forum.index') }}" method="GET" class="flex gap-2">
                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="{{ __('Search posts...') }}"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <button type="submit"
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    {{ __('Search') }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <a href="{{ route('forum.index', ['filter' => 'latest']) }}"
                            class="px-4 py-2 rounded-lg transition {{ $filter === 'latest' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Latest') }}
                        </a>
                        <a href="{{ route('forum.index', ['filter' => 'trending']) }}"
                            class="px-4 py-2 rounded-lg transition {{ $filter === 'trending' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Trending') }}
                        </a>
                        <a href="{{ route('forum.index', ['filter' => 'following']) }}"
                            class="px-4 py-2 rounded-lg transition {{ $filter === 'following' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('Following') }}
                        </a>
                        <a href="{{ route('forum.index') }}"
                            class="px-4 py-2 rounded-lg transition {{ !$filter || $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                            {{ __('All Posts') }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Posts List -->
            @if ($posts->count() > 0)
                <div class="space-y-4">
                    @foreach ($posts as $post)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                            <!-- Post Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-start gap-4 flex-1">
                                    <!-- User Avatar -->
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

                                    <!-- User Info and Post Title -->
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-semibold text-gray-900">
                                                {{ $post->user->name }}
                                            </span>
                                            <span class="text-gray-500">@</span>
                                            <span class="text-gray-600">
                                                {{ $post->user->username }}
                                            </span>
                                            <span class="text-gray-400 text-sm">
                                                {{ $post->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <a href="{{ route('forum.show', $post) }}" class="block">
                                            @php
                                                $postTitle = (string) ($post->title ?? '');
                                            @endphp
                                            <h3 class="text-xl font-bold text-gray-900 hover:text-blue-600 mb-2">
                                                {{ $postTitle }}
                                            </h3>
                                        </a>
                                    </div>
                                </div>

                                <!-- More Options -->
                                @if (auth()->check() && (auth()->id() === $post->user_id || auth()->user()->hasRole('admin')))
                                    <div class="flex-shrink-0">
                                        <!-- Edit/Delete options can be added here when routes are defined -->
                                    </div>
                                @endif
                            </div>

                            <!-- Post Content -->
                            <a href="{{ route('forum.show', $post) }}" class="block">
                                @php
                                    $postContent = is_string($post->content)
                                        ? $post->content
                                        : (is_array($post->content)
                                            ? implode(' ', $post->content)
                                            : '');
                                @endphp
                                <p class="text-gray-700 mb-4 line-clamp-3">
                                    {{ Str::limit(strip_tags($postContent), 300) }}
                                </p>
                            </a>

                            <!-- Post Media -->
                            @if ($post->media && $post->media->count() > 0)
                                <div class="mb-4">
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                        @foreach ($post->media->take(3) as $media)
                                            @if ($media->type === 'image')
                                                <a href="{{ route('forum.show', $post) }}"
                                                    class="rounded-lg overflow-hidden">
                                                    <img src="{{ $media->url }}" alt="Post media"
                                                        class="w-full h-32 object-cover hover:opacity-75 transition">
                                                </a>
                                            @elseif ($media->type === 'video')
                                                <a href="{{ route('forum.show', $post) }}"
                                                    class="rounded-lg overflow-hidden bg-gray-900 flex items-center justify-center h-32 relative">
                                                    <svg class="w-12 h-12 text-white" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path
                                                            d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z">
                                                        </path>
                                                    </svg>
                                                </a>
                                            @endif
                                        @endforeach
                                        @if ($post->media->count() > 3)
                                            <a href="{{ route('forum.show', $post) }}"
                                                class="rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center h-32 font-semibold text-gray-700 hover:bg-gray-300 transition">
                                                +{{ $post->media->count() - 3 }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Hashtags -->
                            @if ($post->hashtags && $post->hashtags->count() > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach ($post->hashtags as $hashtag)
                                        <a href="{{ route('forum.index', ['search' => '#' . $hashtag->name]) }}"
                                            class="text-blue-600 hover:text-blue-800">
                                            #{{ $hashtag->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Post Stats and Actions -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
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

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-4">
                                    @auth
                                        <button type="button"
                                            class="js-like-btn text-gray-500 hover:text-red-600 transition {{ $post->is_liked ? 'text-red-600' : '' }}"
                                            data-like-url="{{ route('forum.like', $post) }}"
                                            data-liked="{{ $post->is_liked ? '1' : '0' }}">
                                            <svg class="w-5 h-5 {{ $post->is_liked ? 'fill-current' : '' }}" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14 10h4.764a2 2 0 011.789 2.894l-3.646 7.23a2 2 0 01-1.789 1.106H7a2 2 0 01-2-2V9a2 2 0 012-2h6a2 2 0 012 2v5">
                                                </path>
                                            </svg>
                                        </button>
                                        <button type="button"
                                            class="js-bookmark-btn text-gray-500 hover:text-yellow-600 transition {{ $post->is_bookmarked ? 'text-yellow-600' : '' }}"
                                            data-bookmark-url="{{ route('forum.bookmark', $post) }}"
                                            data-bookmarked="{{ $post->is_bookmarked ? '1' : '0' }}">
                                            <svg class="w-5 h-5 {{ $post->is_bookmarked ? 'fill-current' : '' }}"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 5a2 2 0 012-2h6a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                            </svg>
                                        </button>
                                    @endauth
                                    <a href="{{ route('forum.show', $post) }}"
                                        class="text-blue-600 hover:text-blue-800 font-semibold">
                                        {{ __('View') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2h-3l-4 4z">
                        </path>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ __('No posts found') }}</h3>
                    <p class="text-gray-600 mb-6">
                        @if ($search)
                            {{ __('No posts match your search for ":query"', ['query' => $search]) }}
                        @else
                            {{ __('Be the first to start a conversation!') }}
                        @endif
                    </p>
                    @auth
                        <button type="button" data-modal-target="create-post-modal"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            {{ __('Create a Post') }}
                        </button>
                    @endauth
                </div>
            @endif
        </div>
    </div>

    @auth
        <!-- Create Post Modal -->
        <div id="create-post-modal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-xl">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">{{ __('Create a Post') }}</h3>
                </div>
                <form id="create-post-form" action="{{ route('forum.store') }}" method="POST" class="px-6 py-4">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Title') }}</label>
                        <input type="text" name="title" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Content') }}</label>
                        <textarea name="content" rows="5" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300" data-modal-close>
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ __('Post') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endauth

    @push('scripts')
        <script>
            // Modal open/close
            document.querySelectorAll('[data-modal-target="create-post-modal"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById('create-post-modal');
                    if (modal) {
                        modal.classList.remove('hidden');
                        modal.classList.add('flex');
                    }
                });
            });
            document.querySelectorAll('[data-modal-close]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const modal = document.getElementById('create-post-modal');
                    if (modal) {
                        modal.classList.add('hidden');
                        modal.classList.remove('flex');
                    }
                });
            });

            // Like handler
            document.querySelectorAll('.js-like-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const url = btn.getAttribute('data-like-url');
                    if (!url) return;
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        if (res.ok) {
                            btn.classList.toggle('text-red-600');
                            btn.querySelector('svg')?.classList.toggle('fill-current');
                        } else {
                            console.warn('Like route not available');
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            });

            // Bookmark handler
            document.querySelectorAll('.js-bookmark-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const url = btn.getAttribute('data-bookmark-url');
                    if (!url) return;
                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        });
                        if (res.ok) {
                            btn.classList.toggle('text-yellow-600');
                            btn.querySelector('svg')?.classList.toggle('fill-current');
                        } else {
                            console.warn('Bookmark route not available');
                        }
                    } catch (e) {
                        console.error(e);
                    }
                });
            });
        </script>
    @endpush
@endsection
