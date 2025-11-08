@php
    $isLiked = $post->is_liked ?? false;
    $isBookmarked = $post->is_bookmarked ?? (auth()->check() ? auth()->user()->hasBookmarked($post) : false);
@endphp

@include('forum.partials.quill-assets')

<div class="bg-white rounded-lg shadow-sm border {{ $post->is_pinned ? 'border-yellow-400 border-2' : 'border-gray-200' }} p-6 hover:shadow-md transition-shadow duration-200">
    <!-- Post Header -->
    <div class="flex items-start justify-between mb-4">
        @if($post->is_pinned)
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z" />
                </svg>
                <span class="text-xs font-medium text-yellow-600">Pinned</span>
            </div>
        @endif
        @if($post->is_hidden)
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-5a1 1 0 112 0v2a1 1 0 11-2 0v-2zm0-6a1 1 0 112 0v3a1 1 0 11-2 0V7z" clip-rule="evenodd" />
                </svg>
                <span class="text-xs font-medium text-red-600">Hidden (visible to you and admins only)</span>
            </div>
        @endif
        @if(!$post->is_published && $post->scheduled_at)
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l3 3" />
                    <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                </svg>
                <span class="text-xs font-medium text-amber-600">Scheduled for {{ $post->scheduled_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
            </div>
        @endif
        <div class="flex items-start space-x-3 flex-1">
            <!-- User Avatar -->
            <a href="{{ route('public.profile.show', $post->user->username) }}" class="flex-shrink-0">
                @if($post->user->avatar)
                    <img src="{{ Storage::url($post->user->avatar) }}" 
                         alt="{{ $post->user->name }}"
                         class="w-12 h-12 rounded-full object-cover">
                @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($post->user->name, 0, 1)) }}
                    </div>
                @endif
            </a>

            <!-- User Info & Post Meta -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 flex-wrap">
                    <a href="{{ route('public.profile.show', $post->user->username) }}" 
                       class="font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                        {{ $post->user->name }}
                    </a>
                    <span class="text-gray-500 text-sm">@{{ $post->user->username }}</span>
                    <span class="text-gray-400">·</span>
                    <span class="text-gray-500 text-sm">{{ $post->created_at->diffForHumans() }}</span>
                    @if($post->visibility === 'followers')
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-blue-700 bg-blue-100 rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-3-3h-1M9 20h6M9 20H4v-2a3 3 0 013-3h1m2-6a3 3 0 116 0 3 3 0 01-6 0z" />
                            </svg>
                            Followers only
                        </span>
                    @elseif($post->visibility === 'private')
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium text-purple-700 bg-purple-100 rounded-full">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.657-1.343-3-3-3S6 9.343 6 11v2H4v8h16v-8h-2v-2c0-1.657-1.343-3-3-3s-3 1.343-3 3v2h-2v-2z" />
                            </svg>
                            Private
                        </span>
                    @endif
                </div>

                <!-- Post Content -->
                <div class="mt-2 text-gray-900 whitespace-pre-wrap break-words" id="postContent-{{ $post->id }}">
                    {!! app(\App\Services\HashtagMentionService::class)->formatContent($post->content) !!}
                </div>

                <!-- Hashtags -->
                @if ($post->hashtags && $post->hashtags->count() > 0)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($post->hashtags as $hashtag)
                            <a href="{{ route('forum.hashtag', $hashtag->slug) }}" 
                               class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors duration-200">
                                #{{ $hashtag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Post Media -->
                @if ($post->media && $post->media->count() > 0)
                    <div class="mt-4 grid grid-cols-{{ min($post->media->count(), 3) }} gap-2">
                        @foreach ($post->media as $media)
                            <div class="relative">
                                <img src="{{ Storage::url($media->file_path) }}" 
                                     alt="Post media" 
                                     class="w-full h-48 object-cover rounded-lg cursor-pointer hover:opacity-90 transition-opacity"
                                     onclick="openImageModal('{{ Storage::url($media->file_path) }}')">
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Edit Post Form (Hidden by default) -->
                @auth
                    @if(auth()->id() === $post->user_id)
                        <div id="editPostForm-{{ $post->id }}" class="mt-2 hidden">
                            <form action="{{ route('forum.update', $post) }}" method="POST" onsubmit="return submitEditPost(event, '{{ $post->id }}')">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="content" id="editContentInput-{{ $post->id }}" value="{{ $post->content }}">
                                <div id="editContentEditor-{{ $post->id }}" class="forum-quill-editor border border-gray-300 rounded-lg bg-white"></div>
                                @if(is_null($post->parent_id))
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Post Visibility</label>
                                        <select name="visibility" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="public" {{ $post->visibility === 'public' ? 'selected' : '' }}>Public — everyone can see</option>
                                            <option value="followers" {{ $post->visibility === 'followers' ? 'selected' : '' }}>Followers Only</option>
                                            <option value="private" {{ $post->visibility === 'private' ? 'selected' : '' }}>Private — only you</option>
                                        </select>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Post (optional)</label>
                                        <input type="datetime-local"
                                               name="scheduled_for"
                                               value="{{ optional($post->scheduled_at)->format('Y-m-d\TH:i') }}"
                                               min="{{ now()->format('Y-m-d\TH:i') }}"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Leave empty to publish immediately. Set a future time to reschedule this post.</p>
                                    </div>
                                @endif
                                @if($post->note_id)
                                    <input type="hidden" name="note_id" value="{{ $post->note_id }}">
                                @endif
                                <div class="mt-2 flex items-center justify-end space-x-2">
                                    <button type="button" 
                                            onclick="cancelEditPost('{{ $post->id }}')"
                                            class="px-4 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="px-4 py-2 text-sm text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                @endauth

                <!-- Shared Note Card -->
                @if($post->note)
                    <div class="mt-4 border border-gray-200 rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition-colors duration-200">
                        <div class="flex items-start space-x-4">
                            @if($post->note->hasThumbnails())
                                <img src="{{ Storage::url($post->note->thumbnails[0]) }}" 
                                     alt="{{ $post->note->title }}"
                                     class="w-20 h-20 rounded-lg object-cover flex-shrink-0">
                            @else
                                <div class="w-20 h-20 rounded-lg bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 truncate">{{ $post->note->title }}</h4>
                                @if($post->note->summary)
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $post->note->summary }}</p>
                                @endif
                                <div class="mt-2 flex items-center space-x-4 text-xs text-gray-500">
                                    <span>{{ __('messages.by_label') }} {{ $post->note->user->name }}</span>
                                    @if($post->note->price > 0)
                                        <span class="font-semibold text-green-600">{{ currency($post->note->price) }}</span>
                                    @else
                                        <span class="font-semibold text-gray-600">{{ __('messages.free') }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('marketplace.show', $post->note) }}" 
                                   class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    {{ __('messages.view_note') }} →
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Post Actions Menu -->
        @auth
            @if(auth()->id() === $post->user_id)
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-400 hover:text-gray-600 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <button onclick="togglePin('{{ $post->id }}', {{ $post->is_pinned ? 'true' : 'false' }})" 
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            {{ $post->is_pinned ? 'Unpin' : 'Pin' }} Post
                        </button>
                        <button onclick="editPost('{{ $post->id }}')" 
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150">
                            Edit
                        </button>
                        <form action="{{ route('forum.destroy', $post) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                onclick="return confirm('Are you sure you want to delete this post?')"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- Report Button for other users -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="text-gray-400 hover:text-red-600 transition-colors duration-200" title="Report post">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50">
                        <button onclick="showReportModal('{{ $post->id }}')" 
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors duration-150">
                            Report Post
                        </button>
                    </div>
                </div>
            @endif
        @endauth
    </div>

    <!-- Post Actions -->
    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
        <div class="flex items-center space-x-6">
            <!-- Like Button -->
            @auth
                <button type="button" 
                        onclick="likePost('{{ $post->id }}')"
                        class="flex items-center space-x-2 text-gray-600 hover:text-red-600 transition-colors duration-200 {{ $isLiked ? 'text-red-600' : '' }}"
                        id="likeBtn-{{ $post->id }}">
                    <svg class="w-5 h-5" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span class="text-sm font-medium" id="likesCount-{{ $post->id }}">{{ $post->likes_count }}</span>
                </button>
            @else
                <a href="{{ route('login') }}" class="flex items-center space-x-2 text-gray-600 hover:text-red-600 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    <span class="text-sm font-medium">{{ $post->likes_count }}</span>
                </a>
            @endauth

            <!-- Comment Button -->
            <a href="{{ route('forum.show', $post) }}" 
               class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <span class="text-sm font-medium">{{ $post->comments_count }}</span>
            </a>

            <!-- Reply Button -->
            @auth
                <a href="{{ route('forum.show', $post) }}#reply" 
                   class="flex items-center space-x-2 text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                    </svg>
                    <span class="text-sm font-medium">Reply</span>
                </a>
            @endauth
        </div>

        <!-- Bookmark Button -->
        @auth
            <button type="button" 
                    onclick="toggleBookmark('{{ $post->id }}')"
                    class="flex items-center space-x-1 text-gray-600 hover:text-yellow-600 transition-colors duration-200"
                    title="Bookmark post"
                    id="bookmarkBtn-{{ $post->id }}">
                <svg class="w-5 h-5" fill="{{ ($post->is_bookmarked ?? false) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                </svg>
            </button>
        @endauth

        <!-- Share Button -->
        <button type="button" 
                onclick="sharePost('{{ $post->id }}')"
                class="flex items-center space-x-1 text-gray-600 hover:text-blue-600 transition-colors duration-200"
                title="Share post">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
            </svg>
            <span class="text-sm font-medium" id="sharesCount-{{ $post->id }}">{{ $post->shares_count ?? 0 }}</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        if (window.forumScriptsInitialized) {
            return;
        }

        window.forumScriptsInitialized = true;
        window.forumQuillToolbar = window.forumQuillToolbar || [
            ['bold', 'italic', 'underline', 'strike'],
            [{ header: [1, 2, 3, false] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block'],
            ['link'],
            ['clean']
        ];
        window.forumQuillEditors = window.forumQuillEditors || {};
        window.forumQuillMaxLength = window.forumQuillMaxLength || 5000;

        function ensureEditQuill(postId) {
            if (!window.Quill) {
                return null;
            }

            if (window.forumQuillEditors[postId]) {
                return window.forumQuillEditors[postId];
            }

            const editorElement = document.getElementById(`editContentEditor-${postId}`);
            const hiddenInput = document.getElementById(`editContentInput-${postId}`);

            if (!editorElement || !hiddenInput) {
                return null;
            }

            const quill = new Quill(editorElement, {
                theme: 'snow',
                modules: {
                    toolbar: window.forumQuillToolbar,
                },
            });

            quill.root.innerHTML = hiddenInput.value || '<p><br></p>';

            quill.on('text-change', function () {
                hiddenInput.value = quill.root.innerHTML;
            });

            window.forumQuillEditors[postId] = quill;

            return quill;
        }

        function likePost(postId) {
            fetch(`/forum/post/${postId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.getElementById(`likeBtn-${postId}`);
                    const likesCount = document.getElementById(`likesCount-${postId}`);

                    if (data.liked) {
                        likeBtn.classList.add('text-red-600');
                        likeBtn.classList.remove('text-gray-600');
                    } else {
                        likeBtn.classList.remove('text-red-600');
                        likeBtn.classList.add('text-gray-600');
                    }

                    if (likesCount) {
                        likesCount.textContent = data.likes_count;
                    }
                }
            })
            .catch(error => {
                console.error('Error liking post:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Failed to like post. Please try again.', 'error');
                }
            });
        }

        function sharePost(postId) {
            const url = `${window.location.origin}/forum/post/${postId}`;

            fetch(`/forum/post/${postId}/share`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const sharesCount = document.getElementById(`sharesCount-${postId}`);
                    if (sharesCount) {
                        sharesCount.textContent = data.shares_count;
                    }
                }
            })
            .catch(error => {
                console.error('Error tracking share:', error);
            });

            if (navigator.share) {
                navigator.share({
                    title: 'Check out this post on Noteds',
                    url: url
                }).catch(() => {});
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Copied!', 'Post link copied to clipboard', 'success');
                    } else {
                        alert('Link copied to clipboard!');
                    }
                });
            }
        }

        function openImageModal(imageUrl) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center';
            modal.innerHTML = `
                <div class="relative max-w-4xl max-h-full p-4">
                    <img src="${imageUrl}" alt="Full size" class="max-w-full max-h-screen rounded-lg">
                    <button onclick="this.closest('.fixed').remove()" class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full w-10 h-10 flex items-center justify-center hover:bg-opacity-75">
                        ×
                    </button>
                </div>
            `;
            modal.onclick = function (e) {
                if (e.target === modal) {
                    modal.remove();
                }
            };
            document.body.appendChild(modal);
        }

        function togglePin(postId) {
            fetch(`/forum/post/${postId}/pin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: data.pinned ? 'Pinned!' : 'Unpinned!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', data.message || 'Failed to pin/unpin post.', 'error');
                    } else {
                        alert(data.message || 'Failed to pin/unpin post.');
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling pin:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Failed to pin/unpin post. Please try again.', 'error');
                } else {
                    alert('Failed to pin/unpin post. Please try again.');
                }
            });
        }

        function showReportModal(postId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Report Post',
                    html: `
                        <form id="reportForm" class="text-left">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                                <select id="reportReason" name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                                    <option value="">Select a reason</option>
                                    <option value="spam">Spam</option>
                                    <option value="harassment">Harassment</option>
                                    <option value="inappropriate">Inappropriate Content</option>
                                    <option value="copyright">Copyright Violation</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description (Optional)</label>
                                <textarea id="reportDescription" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="Please provide more details..." maxlength="1000"></textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Submit Report',
                    confirmButtonColor: '#dc2626',
                    cancelButtonText: 'Cancel',
                    focusConfirm: false,
                    preConfirm: () => {
                        const reason = document.getElementById('reportReason').value;
                        const description = document.getElementById('reportDescription').value;

                        if (!reason) {
                            Swal.showValidationMessage('Please select a reason');
                            return false;
                        }

                        return { reason, description };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitReport(postId, result.value.reason, result.value.description);
                    }
                });
            } else {
                const reason = prompt('Reason for reporting (spam, harassment, inappropriate, copyright, other):');
                if (reason) {
                    const description = prompt('Additional details (optional):');
                    submitReport(postId, reason, description || '');
                }
            }
        }

        function submitReport(postId, reason, description) {
            fetch(`/forum/post/${postId}/report`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ reason, description })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Report Submitted',
                            text: data.message || 'Thank you for reporting. Our team will review it shortly.',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(data.message || 'Report submitted successfully.');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', data.message || 'Failed to submit report.', 'error');
                    } else {
                        alert(data.message || 'Failed to submit report.');
                    }
                }
            })
            .catch(error => {
                console.error('Error submitting report:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Failed to submit report. Please try again.', 'error');
                } else {
                    alert('Failed to submit report. Please try again.');
                }
            });
        }

        function toggleBookmark(postId) {
            fetch(`/forum/post/${postId}/bookmark`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const bookmarkBtn = document.getElementById(`bookmarkBtn-${postId}`);
                    const svg = bookmarkBtn.querySelector('svg');

                    if (data.bookmarked) {
                        bookmarkBtn.classList.remove('text-gray-600');
                        bookmarkBtn.classList.add('text-yellow-600');
                        svg.setAttribute('fill', 'currentColor');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Bookmarked!',
                                text: 'Post saved to your bookmarks',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    } else {
                        bookmarkBtn.classList.remove('text-yellow-600');
                        bookmarkBtn.classList.add('text-gray-600');
                        svg.setAttribute('fill', 'none');
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'info',
                                title: 'Removed!',
                                text: 'Post removed from bookmarks',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling bookmark:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Failed to bookmark post. Please try again.', 'error');
                }
            });
        }

        function toggleFollow(userId) {
            const followBtn = document.getElementById(`followBtn-${userId}`);
            const isFollowing = followBtn.textContent.trim() === 'Following';

            const url = isFollowing ? `/follow/${userId}` : `/follow/${userId}`;
            const method = isFollowing ? 'DELETE' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    followBtn.textContent = data.following ? 'Following' : 'Follow';
                    if (data.following) {
                        followBtn.classList.add('font-semibold');
                    } else {
                        followBtn.classList.remove('font-semibold');
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling follow:', error);
            });
        }

        function editPost(postId) {
            const contentDiv = document.getElementById(`postContent-${postId}`);
            const editForm = document.getElementById(`editPostForm-${postId}`);

            if (contentDiv && editForm) {
                contentDiv.classList.add('hidden');
                editForm.classList.remove('hidden');
                ensureEditQuill(postId);
            }
        }

        function cancelEditPost(postId) {
            const contentDiv = document.getElementById(`postContent-${postId}`);
            const editForm = document.getElementById(`editPostForm-${postId}`);

            if (contentDiv && editForm) {
                contentDiv.classList.remove('hidden');
                editForm.classList.add('hidden');
            }
        }

        function submitEditPost(event, postId) {
            event.preventDefault();

            const quill = ensureEditQuill(postId);
            const hiddenInput = document.getElementById(`editContentInput-${postId}`);

            if (quill) {
                const textLength = quill.getText().trim().length;

                if (textLength === 0) {
                    alert('Please enter some content before saving.');
                    return false;
                }

                if (textLength > window.forumQuillMaxLength) {
                    alert('Post content may not be greater than 5000 characters.');
                    return false;
                }

                hiddenInput.value = quill.root.innerHTML;
            }

            const form = event.target;
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PUT',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (response.ok) {
                    window.location.reload();
                } else {
                    alert('Failed to update post. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error updating post:', error);
                alert('Failed to update post. Please try again.');
            });

            return false;
        }

        window.likePost = likePost;
        window.sharePost = sharePost;
        window.openImageModal = openImageModal;
        window.togglePin = togglePin;
        window.showReportModal = showReportModal;
        window.submitReport = submitReport;
        window.toggleBookmark = toggleBookmark;
        window.toggleFollow = toggleFollow;
        window.editPost = editPost;
        window.cancelEditPost = cancelEditPost;
        window.submitEditPost = submitEditPost;
    })();
</script>
@endpush

