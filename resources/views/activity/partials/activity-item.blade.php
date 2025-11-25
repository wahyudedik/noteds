@php
    $user = auth()->user();
    $isLiked = $activity->isLikedBy($user);
    $likesCount = $activity->likes_count;
    $commentsCount = $activity->comments_count;
    $sharesCount = $activity->shares_count;
@endphp

<div class="bg-white rounded-lg shadow-sm p-6" data-activity-id="{{ $activity->id }}">
    <!-- Activity Header -->
    <div class="flex items-start gap-4 mb-4">
        <a href="{{ route('public.profile.show', $activity->user->username) }}" class="flex-shrink-0">
            <img src="{{ $activity->user->avatar ? Storage::url($activity->user->avatar) : asset('images/default-avatar.png') }}" 
                 alt="{{ $activity->user->name }}" 
                 class="w-12 h-12 rounded-full object-cover">
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <a href="{{ route('public.profile.show', $activity->user->username) }}" class="font-semibold text-gray-900 hover:text-blue-600">
                    {{ $activity->user->name }}
                </a>
                <span class="text-sm text-gray-500">
                    {{ $activity->created_at->diffForHumans() }}
                </span>
            </div>
            <div class="text-sm text-gray-600 mt-1">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ ucfirst(str_replace('.', ' ', $activity->type)) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Activity Content -->
    <div class="mb-4">
        @if($activity->type === 'note.created')
            <div class="flex items-start gap-4">
                @if($activity->subject && $activity->subject->thumbnail)
                    <img src="{{ Storage::url($activity->subject->thumbnail) }}" alt="{{ $activity->subject->title }}" 
                         class="w-24 h-24 rounded-lg object-cover flex-shrink-0">
                @elseif($activity->subject && isset($activity->subject->attachments) && is_array($activity->subject->attachments) && count($activity->subject->attachments) > 0)
                    <img src="{{ Storage::url($activity->subject->attachments[0]) }}" alt="{{ $activity->subject->title }}" 
                         class="w-24 h-24 rounded-lg object-cover flex-shrink-0">
                @endif
                <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 mb-1">
                        @if($activity->subject)
                            <a href="{{ route('marketplace.show', $activity->subject) }}" class="hover:text-blue-600">
                                {{ $activity->properties['title'] ?? $activity->subject->title ?? 'New Note' }}
                            </a>
                        @else
                            {{ $activity->properties['title'] ?? 'New Note' }}
                        @endif
                    </h3>
                    @if(isset($activity->properties['price']))
                        <p class="text-sm text-gray-600">
                            Price: {{ currency($activity->properties['price']) }}
                        </p>
                    @endif
                </div>
            </div>
        @elseif($activity->type === 'note.purchased')
            <p class="text-gray-700">
                purchased 
                <a href="{{ route('marketplace.show', $activity->subject) }}" class="font-semibold text-blue-600 hover:underline">
                    {{ $activity->properties['note_title'] ?? 'a note' }}
                </a>
            </p>
        @elseif($activity->type === 'review.created')
            <div class="flex items-start gap-4">
                <div class="flex-1">
                    <p class="text-gray-700 mb-2">
                        reviewed 
                        <a href="{{ route('marketplace.show', $activity->subject) }}" class="font-semibold text-blue-600 hover:underline">
                            {{ $activity->properties['note_title'] ?? 'a note' }}
                        </a>
                    </p>
                    @if(isset($activity->properties['rating']))
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $activity->properties['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>
        @elseif($activity->type === 'user.followed')
            <p class="text-gray-700">
                started following 
                <a href="{{ route('public.profile.show', $activity->subject->username ?? '') }}" class="font-semibold text-blue-600 hover:underline">
                    {{ $activity->properties['following_name'] ?? $activity->subject->name ?? 'someone' }}
                </a>
            </p>
        @else
            <p class="text-gray-700">{{ ucfirst(str_replace('.', ' ', $activity->type)) }}</p>
        @endif
    </div>

    <!-- Activity Actions -->
    <div class="flex items-center gap-6 pt-4 border-t border-gray-200">
        <!-- Like Button -->
        <button onclick="likeActivity('{{ $activity->id }}')" 
                id="like-btn-{{ $activity->id }}"
                class="flex items-center gap-2 text-sm font-medium {{ $isLiked ? 'text-red-600' : 'text-gray-400 hover:text-red-600' }} transition-colors">
            <svg class="w-5 h-5" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            <span id="like-count-{{ $activity->id }}">{{ $likesCount }}</span>
        </button>

        <!-- Comment Button -->
        <button onclick="toggleComments('{{ $activity->id }}')" 
                class="flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-blue-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <span id="comment-count-{{ $activity->id }}">{{ $commentsCount }}</span>
        </button>

        <!-- Share Button -->
        <div class="relative">
            <button onclick="toggleShareMenu('{{ $activity->id }}')" 
                    class="flex items-center gap-2 text-sm font-medium text-gray-400 hover:text-blue-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
                <span id="share-count-{{ $activity->id }}">{{ $sharesCount }}</span>
            </button>
            
            <!-- Share Menu -->
            <div id="share-menu-{{ $activity->id }}" class="hidden absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                <div class="py-1">
                    <button onclick="shareActivity('{{ $activity->id }}', 'copy_link')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Copy Link
                    </button>
                    <button onclick="shareActivity('{{ $activity->id }}', 'facebook')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Share on Facebook
                    </button>
                    <button onclick="shareActivity('{{ $activity->id }}', 'twitter')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Share on Twitter
                    </button>
                    <button onclick="shareActivity('{{ $activity->id }}', 'linkedin')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Share on LinkedIn
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div id="comments-section-{{ $activity->id }}" class="hidden mt-4 pt-4 border-t border-gray-200">
        <!-- Comment Form -->
        <div class="mb-4">
            <textarea id="comment-content-{{ $activity->id }}" 
                      rows="2" 
                      placeholder="Write a comment..." 
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            <button onclick="commentActivity('{{ $activity->id }}')" 
                    class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                Post Comment
            </button>
        </div>

        <!-- Comments List -->
        <div class="space-y-4">
            @foreach($activity->comments as $comment)
                @include('activity.partials.comment-item', ['comment' => $comment, 'activity' => $activity])
            @endforeach
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleComments(activityId) {
        const section = document.getElementById(`comments-section-${activityId}`);
        section.classList.toggle('hidden');
    }

    function toggleShareMenu(activityId) {
        const menu = document.getElementById(`share-menu-${activityId}`);
        menu.classList.toggle('hidden');
        
        // Close other menus
        document.querySelectorAll('[id^="share-menu-"]').forEach(m => {
            if (m.id !== `share-menu-${activityId}`) {
                m.classList.add('hidden');
            }
        });
    }

    // Close share menu when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[id^="share-menu-"]') && !event.target.closest('button[onclick*="toggleShareMenu"]')) {
            document.querySelectorAll('[id^="share-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
</script>
@endpush


