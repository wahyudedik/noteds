<div class="flex items-start gap-3" data-comment-id="{{ $comment->id }}">
    <a href="{{ route('public.profile.show', $comment->user->username) }}" class="flex-shrink-0">
        <img src="{{ $comment->user->avatar ? Storage::url($comment->user->avatar) : asset('images/default-avatar.png') }}" 
             alt="{{ $comment->user->name }}" 
             class="w-8 h-8 rounded-full object-cover">
    </a>
    <div class="flex-1 min-w-0">
        <div class="bg-gray-50 rounded-lg p-3">
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('public.profile.show', $comment->user->username) }}" class="font-semibold text-sm text-gray-900 hover:text-blue-600">
                    {{ $comment->user->name }}
                </a>
                <span class="text-xs text-gray-500">
                    {{ $comment->created_at->diffForHumans() }}
                </span>
            </div>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->content }}</p>
        </div>
        
        <!-- Reply Button -->
        <button onclick="toggleReply('{{ $comment->id }}')" 
                class="mt-2 text-xs text-gray-500 hover:text-blue-600">
            Reply
        </button>

        <!-- Reply Form -->
        <div id="reply-form-{{ $comment->id }}" class="hidden mt-2">
            <textarea id="reply-content-{{ $comment->id }}" 
                      rows="2" 
                      placeholder="Write a reply..." 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
            <button onclick="commentActivity('{{ $activity->id }}', '{{ $comment->id }}')" 
                    class="mt-2 px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-md">
                Post Reply
            </button>
        </div>

        <!-- Replies -->
        @if($comment->replies->count() > 0)
            <div class="mt-3 ml-4 space-y-3 border-l-2 border-gray-200 pl-4">
                @foreach($comment->replies as $reply)
                    <div class="flex items-start gap-3">
                        <a href="{{ route('public.profile.show', $reply->user->username) }}" class="flex-shrink-0">
                            <img src="{{ $reply->user->avatar ? Storage::url($reply->user->avatar) : asset('images/default-avatar.png') }}" 
                                 alt="{{ $reply->user->name }}" 
                                 class="w-6 h-6 rounded-full object-cover">
                        </a>
                        <div class="flex-1">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <a href="{{ route('public.profile.show', $reply->user->username) }}" class="font-semibold text-xs text-gray-900 hover:text-blue-600">
                                        {{ $reply->user->name }}
                                    </a>
                                    <span class="text-xs text-gray-500">
                                        {{ $reply->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-700 whitespace-pre-wrap">{{ $reply->content }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function toggleReply(commentId) {
        const form = document.getElementById(`reply-form-${commentId}`);
        form.classList.toggle('hidden');
    }
</script>
@endpush


