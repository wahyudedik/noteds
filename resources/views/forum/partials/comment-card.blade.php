@php use Illuminate\Support\Str; @endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <div class="flex items-start space-x-3">
        <!-- User Avatar -->
        <a href="{{ route('public.profile.show', $comment->user->username) }}" class="flex-shrink-0">
            @if($comment->user->avatar)
                @php
                    $commentAvatar = $comment->user->avatar;
                    $commentAvatarUrl = Str::startsWith($commentAvatar, ['http://', 'https://']) ? $commentAvatar : Storage::url($commentAvatar);
                @endphp
                <img src="{{ $commentAvatarUrl }}" 
                     alt="{{ $comment->user->name }}"
                     class="w-10 h-10 rounded-full object-cover">
            @else
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-semibold text-sm">
                    {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                </div>
            @endif
        </a>

        <!-- Comment Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center space-x-2 mb-1">
                <a href="{{ route('public.profile.show', $comment->user->username) }}" 
                   class="font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200 text-sm">
                    {{ $comment->user->name }}
                </a>
                <span class="text-gray-500 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
            </div>
            <div id="commentContent-{{ $comment->id }}">
                <p class="text-gray-900 text-sm whitespace-pre-wrap break-words">{{ $comment->content }}</p>
            </div>

            <!-- Edit Comment Form (Hidden by default) -->
            @auth
                @if(auth()->id() === $comment->user_id)
                    <div id="editCommentForm-{{ $comment->id }}" class="mt-2 hidden">
                        <form onsubmit="return submitEditComment(event, '{{ $comment->id }}')">
                            @csrf
                            @method('PUT')
                            <textarea name="content" 
                                      rows="3"
                                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                      required>{{ $comment->content }}</textarea>
                            <div class="mt-2 flex items-center justify-end space-x-2">
                                <button type="button" 
                                        onclick="cancelEditComment('{{ $comment->id }}')"
                                        class="px-3 py-1 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="px-3 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endauth

            <!-- Comment Actions -->
            <div class="mt-2 flex items-center space-x-4">
                @auth
                    <button type="button" 
                            onclick="likeComment('{{ $comment->id }}')"
                            class="text-xs text-gray-600 hover:text-red-600 transition-colors duration-200 {{ !empty($comment->is_liked) ? 'text-red-600' : '' }}"
                            id="likeCommentBtn-{{ $comment->id }}">
                        <svg class="w-4 h-4 inline mr-1" fill="{{ !empty($comment->is_liked) ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span id="commentLikesCount-{{ $comment->id }}">{{ $comment->likes_count }}</span>
                    </button>
                @else
                    <span class="text-xs text-gray-600">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        {{ $comment->likes_count }}
                    </span>
                @endauth
                @auth
                    <button type="button" 
                            onclick="showReplyForm('{{ $comment->id }}')"
                            class="text-xs text-gray-600 hover:text-blue-600 transition-colors duration-200">
                        Reply
                    </button>
                    @if(auth()->id() === $comment->user_id)
                        <button type="button" 
                                onclick="editComment('{{ $comment->id }}')"
                                class="text-xs text-gray-600 hover:text-blue-600 transition-colors duration-200">
                            Edit
                        </button>
                        <button type="button" 
                                onclick="deleteComment('{{ $comment->id }}')"
                                class="text-xs text-gray-600 hover:text-red-600 transition-colors duration-200">
                            Delete
                        </button>
                    @endif
                @endauth
            </div>

            <!-- Reply Form (Hidden by default) -->
            @auth
                <div id="replyForm-{{ $comment->id }}" class="mt-3 hidden">
                    <form onsubmit="return submitReplyToComment(event, '{{ $comment->post_id }}', '{{ $comment->id }}')">
                        @csrf
                        <textarea name="content" 
                                  rows="2"
                                  class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                  placeholder="Write a reply..."
                                  required></textarea>
                        <div class="mt-2 flex items-center justify-end space-x-2">
                            <button type="button" 
                                    onclick="hideReplyForm('{{ $comment->id }}')"
                                    class="px-3 py-1 text-xs text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" 
                                    class="px-3 py-1 text-xs text-white bg-blue-600 rounded hover:bg-blue-700">
                                Reply
                            </button>
                        </div>
                    </form>
                </div>
            @endauth

            <!-- Nested Replies -->
            @if($comment->replies->count() > 0)
                <div class="mt-4 ml-6 space-y-3 border-l-2 border-gray-200 pl-4">
                    @foreach($comment->replies as $reply)
                        @include('forum.partials.comment-card', ['comment' => $reply])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

