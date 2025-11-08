@php
    $currentUser = auth()->user();
    $canReply = $currentUser && ($currentUser->id === $review->user_id || $currentUser->id === $review->note->user_id || $currentUser->hasRole('admin'));
    $canDelete = $currentUser && ($currentUser->id === $reply->user_id || $currentUser->hasRole('admin'));
@endphp

<div class="pl-10 mt-4">
    <div class="flex gap-3" x-data="{ replyOpen: false }">
        <div class="flex-shrink-0">
            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                @if ($reply->user?->avatar)
                    @if (str_starts_with($reply->user->avatar, 'http'))
                        <img src="{{ $reply->user->avatar }}" alt="{{ $reply->user->name }}"
                            class="w-9 h-9 rounded-full object-cover">
                    @else
                        <img src="{{ Storage::url($reply->user->avatar) }}" alt="{{ $reply->user->name }}"
                            class="w-9 h-9 rounded-full object-cover">
                    @endif
                @else
                    <span class="text-xs font-semibold text-gray-600">{{ substr($reply->user?->name ?? 'U', 0, 1) }}</span>
                @endif
            </div>
        </div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ $reply->user?->name ?? 'Pengguna' }}</p>
                    <p class="text-xs text-gray-500">{{ localized_diff_for_humans($reply->created_at) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    @if ($canReply)
                        <button type="button" @click="replyOpen = !replyOpen"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                            Balas
                        </button>
                    @endif
                    @if ($canDelete)
                        <form action="{{ route('reviews.replies.destroy', $reply) }}" method="POST"
                            onsubmit="return confirm('Hapus balasan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="text-xs text-red-600 hover:text-red-700 transition-colors">{{ __('messages.delete') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <p class="text-sm text-gray-700 whitespace-pre-wrap mt-2">{{ $reply->message }}</p>

            @if ($canReply)
                <form x-show="replyOpen" x-cloak action="{{ route('reviews.replies.store', $review) }}" method="POST"
                    class="mt-3 space-y-2">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $reply->id }}">
                    <textarea name="message" rows="2" required maxlength="2000"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm"
                        placeholder="Tulis balasan"></textarea>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                            Kirim
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    @if ($reply->children && $reply->children->count() > 0)
        <div class="space-y-4 mt-4">
            @foreach ($reply->children as $childReply)
                @include('marketplace.partials.review-reply', ['reply' => $childReply, 'review' => $review])
            @endforeach
        </div>
    @endif
</div>

