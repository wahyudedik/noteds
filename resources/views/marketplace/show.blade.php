@php
    use Illuminate\Support\Str;
@endphp

@extends('40-shared/layouts/app')

@section('title', $note->title)

@section('content')
    <div class="py-8 sm:py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 sm:mb-10">
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-3">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('Back to Marketplace') }}
                </a>
                <div class="flex flex-col gap-2">
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 leading-tight">{{ $note->title }}</h1>
                    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-gray-600">
                        <span
                            class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold">{{ $note->ecosystem_category ? Str::upper($note->ecosystem_category) : __('MARKETPLACE') }}</span>
                        @if ($note->language)
                            <span class="px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $note->language }}</span>
                        @endif
                        @if ($note->three_d_format)
                            <span class="px-2 py-1 rounded-full bg-indigo-50 text-indigo-700">{{ __('Format') }}:
                                {{ $note->three_d_format }}</span>
                        @endif
                        @if ($note->three_d_type)
                            <span class="px-2 py-1 rounded-full bg-indigo-50 text-indigo-700">{{ __('Type') }}:
                                {{ $note->three_d_type }}</span>
                        @endif
                        <span class="text-gray-500">•</span>
                        <span class="text-gray-700">{{ __('Updated') }}
                            {{ optional($note->updated_at)->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
                            <div class="space-y-2">
                                <p class="text-sm text-gray-600">{{ __('By') }} <span
                                        class="font-semibold text-gray-900">{{ $note->user->name ?? __('Unknown Seller') }}</span>
                                </p>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span>{{ __('Sales') }}: {{ number_format($note->transactions_count ?? 0) }}</span>
                                    <span>•</span>
                                    <span>{{ __('Reviews') }}: {{ number_format($reviews->total()) }}</span>
                                    <span>•</span>
                                    <span>{{ __('Comments') }}: {{ number_format($comments->total()) }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Price') }}</p>
                                <div class="flex items-center gap-2 justify-end">
                                    @if ($note->hasDiscount())
                                        <span
                                            class="text-sm text-gray-400 line-through">{{ currency($note->price) }}</span>
                                        <span
                                            class="text-2xl font-bold text-green-600">{{ currency($note->discount_price) }}</span>
                                    @else
                                        <span
                                            class="text-2xl font-bold text-green-600">{{ $note->price == 0 ? __('Free') : currency($note->price) }}</span>
                                    @endif
                                </div>
                                @if ($subscriptionDiscount > 0)
                                    <p class="text-xs text-emerald-600 mt-1">{{ __('Subscription price') }}:
                                        {{ currency($subscriptionDiscountPrice) }} ({{ $subscriptionDiscount }}%
                                        {{ __('off') }})</p>
                                @endif
                            </div>
                        </div>

                        @if ($note->summary)
                            <p class="text-gray-700 leading-relaxed mb-4">{{ $note->summary }}</p>
                        @endif

                        <!-- Share to Unlock Discount -->
                        @auth
                            @if ($note->price > 0 && !$showFullContent)
                                <div id="share-to-unlock"
                                    class="mb-6 bg-gradient-to-r from-orange-50 to-red-50 border-2 border-orange-200 rounded-lg p-5">
                                    <div class="flex items-start justify-between mb-3">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">🎁
                                                {{ __('Share to Unlock 10% Discount!') }}</h3>
                                            <p class="text-sm text-gray-600">
                                                {{ __('Share this note on 3 platforms to unlock a special discount') }}</p>
                                        </div>
                                        <span id="discount-badge"
                                            class="flex-shrink-0 px-3 py-1 bg-red-500 text-white text-sm font-bold rounded-full hidden">
                                            -10%
                                        </span>
                                    </div>

                                    <div class="mb-4">
                                        <div class="flex items-center justify-between text-sm mb-2">
                                            <span class="text-gray-700 font-medium">{{ __('Progress') }}</span>
                                            <span id="share-progress-text" class="font-semibold text-orange-600">0 / 3
                                                {{ __('shares') }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-3">
                                            <div id="share-progress-bar"
                                                class="bg-gradient-to-r from-orange-500 to-red-500 h-3 rounded-full transition-all"
                                                style="width: 0%"></div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-3 gap-3">
                                        <button onclick="shareOnPlatform('whatsapp', {{ $note->id }})"
                                            class="share-btn px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition flex items-center justify-center gap-2"
                                            data-platform="whatsapp">
                                            <i class="fab fa-whatsapp text-xl"></i>
                                            <span class="text-sm">WhatsApp</span>
                                        </button>
                                        <button onclick="shareOnPlatform('twitter', {{ $note->id }})"
                                            class="share-btn px-4 py-3 bg-blue-400 hover:bg-blue-500 text-white rounded-lg font-medium transition flex items-center justify-center gap-2"
                                            data-platform="twitter">
                                            <i class="fab fa-twitter text-xl"></i>
                                            <span class="text-sm">Twitter</span>
                                        </button>
                                        <button onclick="shareOnPlatform('facebook', {{ $note->id }})"
                                            class="share-btn px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition flex items-center justify-center gap-2"
                                            data-platform="facebook">
                                            <i class="fab fa-facebook text-xl"></i>
                                            <span class="text-sm">Facebook</span>
                                        </button>
                                    </div>

                                    <div id="discount-unlocked"
                                        class="hidden mt-4 p-4 bg-green-100 border-2 border-green-500 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-check-circle text-3xl text-green-600"></i>
                                            <div>
                                                <p class="font-bold text-green-800">{{ __('Discount Unlocked!') }}</p>
                                                <p class="text-sm text-green-700">
                                                    {{ __('Your 10% discount has been applied. Purchase now to save!') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endauth

                        <div class="border-t border-gray-200 pt-4">
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Content') }}</h2>
                            @if ($showFullContent)
                                <div class="prose max-w-none text-gray-800">
                                    {!! $note->content !!}
                                </div>
                            @else
                                <div class="prose max-w-none text-gray-800">
                                    {!! nl2br(e($note->preview_content ?? Str::limit(strip_tags($note->content), 600))) !!}
                                </div>
                                @if ($note->price > 0)
                                    <div
                                        class="mt-4 rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-800">
                                        {{ __('Full content is available after purchase or if you are the current owner.') }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('Engagement') }}</h2>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ __('Reactions') }}</span>
                                <span>•</span>
                                <span>{{ __('Reviews') }} {{ number_format($reviews->total()) }}</span>
                                <span>•</span>
                                <span>{{ __('Questions') }} {{ number_format($questions->total()) }}</span>
                            </div>
                        </div>

                        @if (!empty($reactionsSummary))
                            <div class="flex flex-wrap gap-2 mb-4">
                                @foreach ($reactionsSummary as $type => $count)
                                    <span
                                        class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-sm">
                                        <span class="font-semibold capitalize">{{ $type }}</span>
                                        <span class="text-xs text-gray-500">{{ $count }}</span>
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @php
                            $recentReviews = collect($reviews->items())->take(3);
                            $recentComments = collect($comments->items())->take(3);
                            $recentQuestions = collect($questions->items())->take(3);
                        @endphp

                        <div class="grid md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Recent Reviews') }}</h3>
                                @forelse($recentReviews as $review)
                                    <div class="border border-gray-200 rounded-lg p-3 mb-2">
                                        <p class="text-sm text-gray-900 font-semibold">
                                            {{ $review->user->name ?? __('Anonymous') }}</p>
                                        <p class="text-xs text-gray-500 mb-1">
                                            {{ optional($review->created_at)->diffForHumans() }}</p>
                                        <p class="text-sm text-gray-700">{{ Str::limit($review->comment ?? '', 160) }}</p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('No reviews yet.') }}</p>
                                @endforelse
                            </div>
                            <div class="md:col-span-1">
                                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Comments') }}</h3>
                                @forelse($recentComments as $comment)
                                    <div class="border border-gray-200 rounded-lg p-3 mb-2">
                                        <p class="text-sm text-gray-900 font-semibold">
                                            {{ $comment->user->name ?? __('Anonymous') }}</p>
                                        <p class="text-xs text-gray-500 mb-1">
                                            {{ optional($comment->created_at)->diffForHumans() }}</p>
                                        <p class="text-sm text-gray-700">{{ Str::limit($comment->content ?? '', 160) }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('No comments yet.') }}</p>
                                @endforelse
                            </div>
                            <div class="md:col-span-1">
                                <h3 class="font-semibold text-gray-900 mb-2">{{ __('Questions') }}</h3>
                                @forelse($recentQuestions as $question)
                                    <div class="border border-gray-200 rounded-lg p-3 mb-2">
                                        <p class="text-sm text-gray-900 font-semibold">
                                            {{ $question->user->name ?? __('Anonymous') }}</p>
                                        <p class="text-xs text-gray-500 mb-1">
                                            {{ optional($question->created_at)->diffForHumans() }}</p>
                                        <p class="text-sm text-gray-700">{{ Str::limit($question->question ?? '', 160) }}
                                        </p>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500">{{ __('No questions yet.') }}</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    @if ($relatedNotes->count() > 0)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">{{ __('Related Notes') }}</h2>
                                <span class="text-xs text-gray-500">{{ __('Based on category or tags') }}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($relatedNotes as $related)
                                    <a href="{{ route('marketplace.show', $related) }}"
                                        class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                                        <h3 class="text-base font-semibold text-gray-900 mb-1 line-clamp-2">
                                            {{ $related->title }}</h3>
                                        @if ($related->summary)
                                            <p class="text-sm text-gray-600 line-clamp-2 mb-2">
                                                {{ Str::limit(strip_tags($related->summary), 120) }}</p>
                                        @endif
                                        <div class="flex items-center justify-between text-sm text-gray-500">
                                            <span
                                                class="font-semibold text-green-600">{{ $related->price == 0 ? __('Free') : currency($related->price) }}</span>
                                            <span>{{ $related->user->name ?? __('Seller') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-4">
                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div>
                                <p class="text-sm text-gray-500">{{ __('Current price') }}</p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-3xl font-bold text-gray-900">{{ $note->price == 0 ? __('Free') : currency($basePrice) }}</span>
                                    @if ($note->hasDiscount())
                                        <span
                                            class="text-xs text-gray-500 line-through">{{ currency($note->price) }}</span>
                                    @endif
                                </div>
                                @if ($subscriptionDiscount > 0)
                                    <p class="text-xs text-emerald-600 mt-1">{{ __('With subscription') }}:
                                        {{ currency($subscriptionDiscountPrice) }}</p>
                                @endif
                            </div>
                            @if ($note->isScarcityMode() && $canRepurchase)
                                <span
                                    class="px-2 py-1 text-xs rounded-full bg-orange-50 text-orange-700 font-semibold">{{ __('Scarcity Mode') }}</span>
                            @endif
                        </div>

                        @if ($canRepurchase && $repurchasePrice)
                            <div
                                class="mb-3 rounded-md bg-orange-50 border border-orange-200 px-3 py-2 text-sm text-orange-800">
                                {{ __('You can repurchase this note for') }} {{ currency($repurchasePrice) }}
                                @if ($isWithinGracePeriod && $gracePeriodEndsAt)
                                    <span class="block text-xs text-orange-700 mt-1">{{ __('Grace period ends') }}
                                        {{ $gracePeriodEndsAt->diffForHumans() }}</span>
                                @endif
                            </div>
                        @endif

                        @if ($canBuy)
                            <form action="{{ route('marketplace.purchase', $note) }}" method="POST" class="space-y-3">
                                @csrf
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    {{ $note->price == 0 ? __('Get for Free') : __('Buy Now') }}
                                </button>
                                <p class="text-xs text-gray-500 text-center">
                                    {{ __('Secure checkout with fraud protection and verified delivery') }}</p>
                            </form>
                        @else
                            <div class="rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-sm text-gray-700">
                                @if ($alreadyPurchased)
                                    {{ __('You have already purchased this note.') }}
                                @elseif($isNoteOwner)
                                    {{ __('You are the current owner of this note.') }}
                                @else
                                    {{ __('Purchasing is not available for this note.') }}
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Seller Info') }}</h3>
                        <p class="text-sm text-gray-700">{{ $note->user->name ?? __('Unknown Seller') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Seller rating') }}:
                            {{ number_format($sellerReviewStats['average'] ?? 0, 1) }} / 5
                            ({{ number_format($sellerReviewStats['count'] ?? 0) }} {{ __('reviews') }})</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Share') }}</h3>
                        @if ($shareUrl)
                            <div class="flex items-center gap-2">
                                <input type="text" readonly class="w-full text-sm border-gray-200 rounded-md"
                                    value="{{ $shareUrl }}">
                                <button type="button"
                                    class="px-3 py-2 text-sm font-semibold text-blue-600 border border-blue-200 rounded-md hover:bg-blue-50"
                                    onclick="navigator.clipboard && navigator.clipboard.writeText('{{ $shareUrl }}')">
                                    {{ __('Copy') }}
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">{{ __('Share this link to earn points from referrals.') }}
                            </p>
                        @else
                            <p class="text-sm text-gray-600">{{ __('Login to generate your personal share link.') }}</p>
                        @endif
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Questions?') }}</h3>
                        <p class="text-sm text-gray-700">
                            {{ __('Send the seller a message from the conversations area after purchase or use the Q&A section below.') }}
                        </p>
                        @if ($conversation)
                            <p class="text-xs text-gray-500">{{ __('Active conversation found.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Share to Unlock functionality
        let shareProgress = {
            shares: 0,
            platforms: []
        };
        const noteId = {{ $note->id }};

        async function loadShareProgress() {
            try {
                const response = await fetch(`/api/growth/share-discount/${noteId}`);
                const data = await response.json();

                shareProgress = data;
                updateShareUI();
            } catch (error) {
                console.error('Failed to load share progress:', error);
            }
        }

        function updateShareUI() {
            const progressBar = document.getElementById('share-progress-bar');
            const progressText = document.getElementById('share-progress-text');
            const discountBadge = document.getElementById('discount-badge');
            const discountUnlocked = document.getElementById('discount-unlocked');

            if (!progressBar || !progressText) return;

            const shares = shareProgress.shares || 0;
            const progressPercentage = (shares / 3) * 100;

            progressBar.style.width = `${progressPercentage}%`;
            progressText.textContent = `${shares} / 3 shares`;

            // Mark shared platforms as completed
            (shareProgress.platforms || []).forEach(platform => {
                const btn = document.querySelector(`[data-platform="${platform}"]`);
                if (btn) {
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.disabled = true;
                    btn.innerHTML += ' <i class="fas fa-check ml-1"></i>';
                }
            });

            // Show discount unlocked message
            if (shares >= 3) {
                discountBadge?.classList.remove('hidden');
                discountUnlocked?.classList.remove('hidden');
            }
        }

        async function shareOnPlatform(platform, noteId) {
            const noteUrl = window.location.href;
            const noteTitle = '{{ addslashes($note->title) }}';
            const shareText = `Check out this note: ${noteTitle}`;

            // Open share dialog
            let shareUrl = '';
            switch (platform) {
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + noteUrl)}`;
                    break;
                case 'twitter':
                    shareUrl =
                        `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(noteUrl)}`;
                    break;
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(noteUrl)}`;
                    break;
            }

            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');

                // Track share
                try {
                    const response = await fetch('/api/growth/track-share', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            note_id: noteId,
                            platform: platform
                        })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        shareProgress = data;
                        updateShareUI();
                    }
                } catch (error) {
                    console.error('Failed to track share:', error);
                }
            }
        }

        // Load share progress on page load
        @auth
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('share-to-unlock')) {
                loadShareProgress();
            }
        });
        @endauth
    </script>
@endsection
