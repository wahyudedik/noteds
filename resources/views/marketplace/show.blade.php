@extends('layouts.app')

@section('title', __('messages.marketplace_page_title', ['title' => $note->title]))

@push('meta')
    @php
        $shareUrl = route('marketplace.show', $note);
        $shareTitle = $note->title;
        $shareDescription = Str::limit(strip_tags($note->content), 200);
        $shareImage = $note->hasThumbnails() ? url(Storage::url($note->thumbnails[0])) : null;
    @endphp
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $shareUrl }}">
    <meta property="og:title" content="{{ $shareTitle }}">
    <meta property="og:description" content="{{ $shareDescription }}">
    @if ($shareImage)
        <meta property="og:image" content="{{ $shareImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta property="og:site_name" content="{{ config('app.name') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ $shareUrl }}">
    <meta property="twitter:title" content="{{ $shareTitle }}">
    <meta property="twitter:description" content="{{ $shareDescription }}">
    @if ($shareImage)
        <meta property="twitter:image" content="{{ $shareImage }}">
    @endif

    <!-- Additional Meta -->
    <meta name="description" content="{{ Str::limit(strip_tags($note->content), 160) }}">
@endpush

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <a href="{{ route('marketplace.index') }}"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ __('messages.back_to_marketplace') }}
                </a>
            </div>

            <!-- Flash Messages -->
            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            @if ($conversation)
                @php
                    $otherUser = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
                    $lastMessage = $conversation->latestMessage;
                @endphp
                <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide mb-1">{{ __('messages.product_conversation_title') }}</h3>
                        <p class="text-sm text-blue-800">
                            {!! __('messages.product_conversation_description', ['name' => '<strong>' . e($otherUser->name) . '</strong>']) !!}
                            @if ($lastMessage)
                                <span class="block mt-1 text-xs text-blue-700">
                                    {{ $lastMessage->sender_id === auth()->id()
                                        ? __('messages.product_conversation_last_message_you')
                                        : __('messages.product_conversation_last_message_other', ['name' => $lastMessage->sender->name]) }}
                                    “{{ \Illuminate\Support\Str::limit($lastMessage->message, 80) }}”
                                </span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('note-conversations.show', $conversation) }}"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        {{ __('messages.product_conversation_open_chat') }}
                    </a>
                </div>
            @endif

            <!-- Note Details Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6">
                    <!-- Badges and Rating -->
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        @if ($note->is_public)
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                {{ __('messages.public') }}
                            </span>
                        @endif
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ ucfirst($note->status) }}
                        </span>
                        @if ($note->average_rating > 0)
                            <div class="inline-flex items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="text-sm font-medium text-gray-700">{{ $note->average_rating }}</span>
                                <span class="text-xs text-gray-500">({{ $note->total_reviews }}
                                    {{ $note->total_reviews == 1 ? __('messages.review') : __('messages.reviews_count') }})</span>
                            </div>
                        @endif
                        @if ($note->price > 0)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-lg bg-yellow-100 flex-wrap">
                                @php
                                    $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;
                                    $displayPrice = isset($premiumDiscountPrice) ? $premiumDiscountPrice : $basePrice;
                                @endphp
                                
                                @if ($note->hasDiscount())
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs text-gray-500 line-through">{{ currency($note->price) }}</span>
                                        <span class="text-base font-semibold text-green-600">{{ currency($note->discount_price) }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-500 text-white">
                                        -{{ $note->discount_percent }}%
                                    </span>
                                @else
                                    <span class="text-base font-semibold text-yellow-800">{{ currency($basePrice) }}</span>
                                @endif
                                
                                @if(isset($premiumDiscountPercent) && $premiumDiscountPercent > 0)
                                    <div class="flex flex-col items-end">
                                        @if(!$note->hasDiscount())
                                            <span class="text-xs text-gray-500 line-through">{{ currency($basePrice) }}</span>
                                        @endif
                                        <span class="text-base font-semibold text-green-600">{{ currency($displayPrice) }}</span>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        -{{ $premiumDiscountPercent }}% Premium
                                    </span>
                                @endif
                            </div>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                {{ __('messages.free') }}
                            </span>
                        @endif

                        @if ($taxPreview)
                            <div class="w-full mt-3 bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm text-gray-700">
                                <div class="flex justify-between">
                                    <span>{{ __('messages.tax_subtotal_label') }}</span>
                                    <span class="font-medium text-gray-900">{{ currency($taxPreview['price_excluding_tax']) }}</span>
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span>
                                        {{ __('messages.tax_label') }} ({{ $taxPreview['tax_percent'] }}%)
                                        {!! $taxPreview['tax_inclusive'] ? '<span class="text-xs text-emerald-600 font-semibold ml-1">'.__('messages.tax_inclusive_badge').'</span>' : '' !!}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ currency($taxPreview['tax_amount']) }}</span>
                                </div>
                                <div class="flex justify-between mt-2 border-t border-slate-200 pt-2">
                                    <span class="font-semibold text-gray-900">{{ __('messages.tax_total_label') }}</span>
                                    <span class="font-semibold text-gray-900">{{ currency($taxPreview['total_amount']) }}</span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    {{ $taxPreview['tax_inclusive'] ? __('messages.tax_inclusive_help') : __('messages.tax_exclusive_help') }}
                                    @if (!empty($taxPreview['country_code']))
                                        <span class="font-semibold text-gray-600">({{ strtoupper($taxPreview['country_code']) }})</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $note->title }}</h1>

                    <!-- Tags -->
                    @if ($note->tags->count() > 0)
                        <div class="mb-4 flex flex-wrap gap-2">
                            @foreach ($note->tags as $tag)
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <!-- Author, Meta Info, and Share Buttons -->
                    <div class="mb-6 text-sm text-gray-600 border-b border-gray-200 pb-4">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center gap-4 flex-wrap">
                                <a href="{{ route('public.profile.show', $note->user->username) }}"
                                    class="flex items-center hover:text-blue-600 transition-colors duration-200 group"
                                    title="{{ __('messages.view_all_notes_from', ['name' => $note->user->name]) }}">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-2 group-hover:ring-2 group-hover:ring-blue-500 transition-all duration-200">
                                        @if ($note->user->avatar)
                                            @if (str_starts_with($note->user->avatar, 'http'))
                                                <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}"
                                                    class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <img src="{{ Storage::url($note->user->avatar) }}"
                                                    alt="{{ $note->user->name }}"
                                                    class="w-10 h-10 rounded-full object-cover">
                                            @endif
                                        @else
                                            <span
                                                class="text-sm font-semibold text-gray-600">{{ substr($note->user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="font-medium text-gray-900 group-hover:text-blue-600">{{ $note->user->name }}</span>
                                            @if ($note->user->hasPremium())
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white"
                                                    title="{{ __('messages.premium_buyer_badge') }}">
                                                    <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    {{ __('messages.premium_badge_label') }}
                                                </span>
                                            @endif
                                            @if ($note->user->role === 'seller')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    {{ __('messages.seller') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($note->user->location)
                                            <span class="text-xs text-gray-500">• {{ $note->user->location }}</span>
                                        @endif
                                        <div
                                            class="text-xs text-blue-600 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                            {{ __('messages.view_all_notes_arrow') }}
                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            @if (($sellerReviewStats['count'] ?? 0) > 0)
                                                <div class="flex items-center gap-1">
                                                    <div class="flex items-center">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <svg class="w-4 h-4 {{ $i <= round($sellerReviewStats['average']) ? 'text-yellow-400' : 'text-gray-300' }}"
                                                                fill="currentColor" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-800">
                                                        {{ number_format($sellerReviewStats['average'], 1) }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        ({{ trans_choice('messages.rating_count', $sellerReviewStats['count'], ['count' => $sellerReviewStats['count']]) }})
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-500">{{ __('messages.seller_no_ratings_yet') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                                @if (
                                    $note->user->role === 'seller' &&
                                        $note->user->notes()->where('is_public', true)->where('status', 'active')->count() > 0)
                                    <a href="{{ route('public.profile.ai-chat', $note->user->username) }}"
                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow-md"
                                        title="{{ __('messages.ask_ai_about_notes', ['name' => $note->user->name]) }}">
                                        <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                        </svg>
                                        {{ __('messages.ask_ai_button') }}
                                    </a>
                                @endif
                                <div class="text-xs text-gray-500">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('messages.published') }} {{ localized_time($note->created_at, 'date') }}
                                </div>
                            </div>

                            <!-- Share Buttons -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 mr-2">{{ __('messages.share_label') }}</span>
                                @php
                                    $shareUrl = route('marketplace.show', $note);
                                    $shareTitle = urlencode($note->title);
                                    $shareText = urlencode(Str::limit(strip_tags($note->content), 100));
                                @endphp

                                <!-- Facebook -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                                    title="{{ __('messages.share_on_facebook') }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>

                                <!-- Twitter -->
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ $shareTitle }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors duration-200"
                                    title="{{ __('messages.share_on_twitter') }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                    </svg>
                                </a>

                                <!-- WhatsApp -->
                                <a href="https://wa.me/?text={{ $shareTitle }}%20{{ urlencode($shareUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors duration-200"
                                    title="{{ __('messages.share_on_whatsapp') }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                </a>

                                <!-- LinkedIn -->
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-700 text-white hover:bg-blue-800 transition-colors duration-200"
                                    title="{{ __('messages.share_on_linkedin') }}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                    </svg>
                                </a>

                                <!-- Copy Link -->
                                <button onclick="copyToClipboard('{{ $shareUrl }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors duration-200"
                                    title="{{ __('messages.copy_link') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                @auth
                                    <button type="button" onclick="showNoteReportModal()"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                                        title="{{ __('messages.report_note_tooltip') }}">
                                        {{ __('messages.report_note') }}
                                    </button>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                                        title="{{ __('messages.report_note_login_tooltip') }}">
                                        {{ __('messages.report_note') }}
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Note Content (Protected for paid notes) -->
                    @if ($showFullContent ?? false)
                        @auth
                            @if (auth()->user()->hasPremium() && auth()->user()->role === 'buyer' && ($alreadyPurchased ?? false))
                                <!-- Reading Progress Bar -->
                                <div id="reading-progress-container" class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700">{{ __('messages.reading_progress') }}</span>
                                        <span id="progress-percentage" class="text-sm font-semibold text-blue-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="progress-bar"
                                            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                            style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Premium Features Toolbar -->
                                <div
                                    class="mb-4 flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <button type="button" id="add-bookmark-btn" onclick="showAddBookmarkModal()"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            {{ __('messages.add_bookmark') }}
                                        </button>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('export.pdf', $note) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                {{ __('messages.export_pdf') }}
                                            </a>
                                            <a href="{{ route('export.docx', $note) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                {{ __('messages.export_docx') }}
                                            </a>
                                            <a href="{{ route('export.markdown', $note) }}"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                                {{ __('messages.export_md') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bookmarks List -->
                                <div id="bookmarks-section" class="mb-4 hidden">
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-semibold text-purple-900 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                </svg>
                                                {{ __('messages.bookmarks') }}
                                            </h4>
                                            <button type="button" onclick="toggleBookmarks()"
                                                class="text-sm text-purple-700 hover:text-purple-900">
                                                <span id="bookmarks-toggle-text">{{ __('messages.bookmarks_toggle_show') }}</span>
                                            </button>
                                        </div>
                                        <div id="bookmarks-list" class="space-y-2">
                                            <!-- Bookmarks will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endauth

                        <div class="prose max-w-none mb-6" id="note-content">
                            <div class="ql-editor text-gray-900 leading-relaxed">{!! $note->content !!}</div>
                        </div>

                        <!-- Attachments (if purchased or free) -->
                        @if ($note->hasAttachments())
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    {{ __('messages.attachments') }} ({{ $note->file_count }})
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach ($note->attachments as $attachment)
                                        @php
                                            $filename = is_array($attachment)
                                                ? ($attachment['filename'] ?? __('messages.unknown_file_name'))
                                                : basename($attachment);
                                        @endphp
                                        <a href="{{ route('notes.attachments.download', ['note' => $note->id, 'filename' => $filename]) }}"
                                            class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-300 transition-all duration-200">
                                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $filename }}
                                                </p>
                                                @if (is_array($attachment) && isset($attachment['size']))
                                                    <p class="text-xs text-gray-500">
                                                        {{ number_format($attachment['size'] / 1024, 2) }} KB</p>
                                                @endif
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @else
                        <!-- Thumbnail Images -->
                        @if ($note->hasThumbnails())
                            <div class="mb-6">
                                <div class="grid grid-cols-2 md:grid-cols-{{ min($note->getThumbnailCount(), 5) }} gap-4">
                                    @foreach ($note->thumbnails as $thumbnail)
                                        <div class="relative group">
                                            <img src="{{ Storage::url($thumbnail) }}" alt="{{ __('messages.note_thumbnail_alt') }}"
                                                class="w-full h-48 object-cover rounded-lg border-2 border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Preview Content (for paid notes, before purchase) -->
                        <div class="prose max-w-none mb-6 relative">
                            @php
                                // Use preview_percentage if set, otherwise use preview_content
                                if ($note->preview_percentage > 0) {
                                    $previewContent = $note->getPreviewContentByPercentage();
                                    $showBlur = $note->preview_percentage < 100;

                                    // Count total lines for info
                                    $totalLines = count(preg_split('/\r\n|\r|\n/', $note->content));
                                    $visibleLines = (int) ceil($totalLines * ($note->preview_percentage / 100));
                                } else {
                                    $previewContent =
                                        $note->preview_content ?:
                                        \Illuminate\Support\Str::limit(strip_tags($note->content), 300);
                                    $showBlur = true;
                                    $totalLines = null;
                                    $visibleLines = null;
                                }
                            @endphp
                            <div class="prose max-w-none">
                                <div class="ql-editor text-gray-900 leading-relaxed whitespace-pre-wrap">
                                    {!! $previewContent !!}
                                    @if ($note->preview_percentage > 0 && $note->preview_percentage < 100)
                                        <span class="text-gray-500 italic">...</span>
                                    @elseif($note->preview_percentage == 0 && strlen(strip_tags($note->content)) > 300)
                                        <span class="text-gray-500 italic">...</span>
                                    @endif
                                </div>
                            </div>
                            <!-- Blur overlay for paid content -->
                            @if ($showBlur)
                                <div
                                    class="absolute inset-0 bg-gradient-to-b from-transparent via-white/80 to-white backdrop-blur-sm pointer-events-none flex items-end justify-center pb-8">
                                    <div class="text-center px-4">
                                        <p class="text-sm font-semibold text-gray-700 mb-2">
                                            {{ __('messages.full_content_available_after_purchase') }}</p>
                                        <p class="text-xs text-gray-600">{{ __('messages.buy_note_to_unlock') }}</p>
                                        @if ($note->preview_percentage > 0 && isset($visibleLines) && isset($totalLines))
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('messages.preview_lines_detail', ['visible' => $visibleLines, 'total' => $totalLines, 'percentage' => $note->preview_percentage]) }}
                                            </p>
                                        @elseif($note->preview_percentage > 0)
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ __('messages.preview_percentage_detail', ['percentage' => $note->preview_percentage]) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- What You'll Get Section -->
                        @if ($note->price > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200 bg-blue-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ __('messages.what_youll_get') }}
                                </h3>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ __('messages.full_note_content') }}</span>
                                    </li>
                                    @if ($note->hasAttachments())
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span>{{ $note->file_count }} {{ __('messages.downloadable_files') }}
                                                <span class="text-xs text-gray-600">({{ __('messages.locked_until_purchase') }})</span></span>
                                        </li>
                                    @endif
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ __('messages.lifetime_access') }}</span>
                                    </li>
                                </ul>
                            </div>
                        @endif

                        <!-- Trust Indicators -->
                        @if ($note->purchase_count > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center text-gray-700">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-semibold">{{ $note->purchase_count }}</span>
                                        <span
                                            class="ml-1">{{ $note->purchase_count == 1 ? __('messages.purchase') : __('messages.purchases') }}</span>
                                    </div>
                                    @if ($note->purchase_count >= 10)
                                        <div class="flex items-center text-yellow-600">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="font-semibold">{{ __('messages.popular') }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endif

                    <!-- Purchase/Action Buttons -->
                    @auth
                        @if ($alreadyPurchased ?? false)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="text-green-600 font-semibold">{{ __('messages.you_have_purchased') }}</span>
                                    </div>
                                    <a href="{{ route('notes.show', $note) }}"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        {{ __('messages.view_full_note_arrow') }}
                                    </a>
                                </div>

                                @if (auth()->user()->hasPremium() && auth()->user()->role === 'buyer')
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <button type="button" onclick="showCollectionModal('{{ $note->id }}')"
                                            class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            {{ __('messages.add_to_collection') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @elseif($canBuy && $note->price > 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <form action="{{ route('marketplace.purchase', $note) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        @php
                                            $displayPrice = $premiumDiscountPrice ?? ($note->hasDiscount() ? $note->discount_price : $note->price);
                                            $formattedDisplayPrice = currency($displayPrice);
                                        @endphp
                                        {{ __('messages.buy_note_price', ['price' => $formattedDisplayPrice]) }}
                                        @if(isset($premiumDiscountPercent) && $premiumDiscountPercent > 0)
                                            <span class="ml-2 text-xs bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-2 py-0.5 rounded-full">
                                                -{{ $premiumDiscountPercent }}% {{ __('messages.premium_badge_label') }}
                                            </span>
                                        @endif
                                    </button>
                                </form>
                                <p class="text-sm text-gray-600 mt-3">
                                    {{ __('messages.wallet_balance_label') }}
                                    <strong
                                        class="font-semibold text-gray-900">{{ currency(auth()->user()->wallet_balance, auth()->user()->currency) }}</strong>
                                    @php
                                        $finalPrice = $premiumDiscountPrice ?? ($note->hasDiscount() ? $note->discount_price : $note->price);
                                    @endphp
                                    @if(isset($premiumDiscountPercent) && $premiumDiscountPercent > 0)
                                        <div class="mt-2 text-xs text-gray-500">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
                                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                {{ __('messages.premium_discount_badge', ['amount' => currency($basePrice - $finalPrice), 'percent' => $premiumDiscountPercent]) }}
                                            </span>
                                        </div>
                                    @endif
                                    @if (auth()->user()->wallet_balance < $finalPrice)
                                        <span class="text-red-600 font-medium">
                                            {{ __('messages.wallet_insufficient_amount', ['amount' => currency($finalPrice - auth()->user()->wallet_balance, auth()->user()->currency)]) }}
                                        </span>
                                    @endif
                                </p>
                            </div>
                        @elseif(auth()->user()->role === 'seller' && $note->price > 0 && !$alreadyPurchased)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800 mb-1">{{ __('messages.seller_cannot_purchase_title') }}</p>
                                            <p class="text-xs text-yellow-700">{{ __('messages.seller_cannot_purchase_description') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($note->user_id === auth()->id())
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                @if (auth()->user()->role === 'seller')
                                    <p class="text-gray-600 mb-3">{{ __('messages.note_owner_message') }}</p>
                                    <a href="{{ route('notes.edit', $note) }}"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        {{ __('messages.edit_note_arrow') }}
                                    </a>
                                @elseif(auth()->user()->role === 'buyer')
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-blue-800 mb-1">{{ __('messages.buyer_owns_note_title') }}</p>
                                                <p class="text-xs text-blue-700 mb-2">
                                                    {{ __('messages.buyer_owns_note_description') }}
                                                </p>
                                                <div class="bg-yellow-100 border border-yellow-300 rounded p-2 mt-2">
                                                    <p class="text-xs font-semibold text-yellow-800 mb-1">
                                                        {{ __('messages.buyer_resale_warning_title') }}
                                                    </p>
                                                    <p class="text-xs text-yellow-700">
                                                        {!! __('messages.buyer_resale_warning_description') !!}
                                                    </p>
                                                </div>
                                                @if ($note->originalCreator)
                                                    <p class="text-xs text-blue-600 mt-2">
                                                        {!! __('messages.original_creator_notice', ['name' => '<strong>' . e($note->originalCreator->name) . '</strong>']) !!}
                                                        @if ($note->originalCreator->id !== auth()->id())
                                                            {{ __('messages.original_creator_commission_note') }}
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-3">
                                        {!! __('messages.buyer_resale_notice') !!}
                                    </p>
                                @endif
                            </div>
                        @elseif($hasPurchasedBefore && !$isNoteOwner)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800 mb-1">{{ __('messages.access_revoked_title') }}</p>
                                            <p class="text-xs text-yellow-700">
                                                {!! __('messages.access_revoked_description') !!}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($note->price == 0)
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center bg-green-50 border border-green-200 rounded-lg p-4">
                                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-green-800 font-semibold">{{ __('messages.note_free_enjoy') }}</span>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-gray-600 mb-3">{{ __('messages.to_purchase_please_login') }}</p>
                            <a href="{{ route('login') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                Login to Continue
                            </a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Reviews Section -->
            @if ($note->total_reviews > 0 || (auth()->check() && isset($canReview) && $canReview))
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.reviews') }}
                            ({{ $note->total_reviews }})</h2>
                    </div>
                    <div class="p-6">
                        <!-- Review Form (if user can review) -->
                        @if (auth()->check() && isset($canReview) && $canReview)
                            <div class="mb-6 pb-6 border-b border-gray-200">
                                <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('messages.write_review') }}
                                </h3>
                                <form action="{{ route('reviews.store', $note) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="rating"
                                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.rating') }}</label>
                                        <div class="flex gap-1" id="rating-container">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="button"
                                                    class="star-rating text-gray-300 hover:text-yellow-400 transition-colors duration-200"
                                                    data-rating="{{ $i }}">
                                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </button>
                                            @endfor
                                        </div>
                                        <input type="hidden" name="rating" id="rating-input" required>
                                        @error('rating')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="comment"
                                            class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.comment_optional') }}</label>
                                        <textarea name="comment" id="comment" rows="4" placeholder="{{ __('messages.share_thoughts_about_note') }}"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('comment') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                                        @error('comment')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                        {{ __('messages.submit_review') }}
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Reviews List -->
                        @if ($note->total_reviews > 0)
                            <div class="space-y-6">
                                @foreach ($reviews as $review)
                                    @php
                                        $currentUser = auth()->user();
                                        $canReplyToReview =
                                            $currentUser &&
                                            ($currentUser->id === $note->user_id ||
                                                $currentUser->id === $review->user_id ||
                                                $currentUser->hasRole('admin'));
                                        $canDeleteReview = $currentUser && ($currentUser->id === $review->user_id || $currentUser->hasRole('admin'));
                                    @endphp
                                    <div id="review-{{ $review->id }}"
                                        class="pb-6 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                        <div class="flex gap-4" x-data="{ replyOpen: false }">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    @if ($review->user->avatar)
                                                        @if (str_starts_with($review->user->avatar, 'http'))
                                                            <img src="{{ $review->user->avatar }}"
                                                                alt="{{ $review->user->name }}"
                                                                class="w-10 h-10 rounded-full object-cover">
                                                        @else
                                                            <img src="{{ Storage::url($review->user->avatar) }}"
                                                                alt="{{ $review->user->name }}"
                                                                class="w-10 h-10 rounded-full object-cover">
                                                        @endif
                                                    @else
                                                        <span
                                                            class="text-sm font-semibold text-gray-600">{{ substr($review->user->name, 0, 1) }}</span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="flex-1">
                                                <div class="flex items-start justify-between mb-2">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            {{ $review->user->name }}</p>
                                                        <p class="text-xs text-gray-500">
                                                            {{ localized_diff_for_humans($review->created_at) }}</p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        @if ($canReplyToReview)
                                                            <button type="button" @click="replyOpen = !replyOpen"
                                                                class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                                                {{ __('messages.reply') }}
                                                            </button>
                                                        @endif
                                                        @if ($canDeleteReview)
                                                            <form action="{{ route('reviews.destroy', $review) }}"
                                                                method="POST" class="delete-review-form"
                                                                onsubmit="return confirm('{{ __('messages.review_delete_confirmation') }}');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="text-xs text-red-600 hover:text-red-700 transition-colors duration-200">{{ __('messages.delete') }}</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="flex gap-0.5 mb-2">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path
                                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    @endfor
                                                </div>

                                                @if ($review->comment)
                                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">
                                                        {{ $review->comment }}</p>
                                                @endif

                                                @if ($canReplyToReview)
                                                    <form x-show="replyOpen" x-cloak
                                                        action="{{ route('reviews.replies.store', $review) }}"
                                                        method="POST" class="mt-4 space-y-2">
                                                        @csrf
                                                        <textarea name="message" rows="3" required maxlength="2000"
                                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm"
                                                            placeholder="{{ __('messages.review_reply_placeholder') }}"></textarea>
                                                        <div class="flex justify-end">
                                                            <button type="submit"
                                                                class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                                                                {{ __('messages.review_reply_submit') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($review->replies && $review->replies->count() > 0)
                                            <div class="mt-4 space-y-4">
                                                @foreach ($review->replies as $reply)
                                                    @include('marketplace.partials.review-reply', ['reply' => $reply, 'review' => $review])
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach

                                <div class="pt-4">
                                    {{ $reviews->links() }}
                                </div>
                            </div>
                        @else
                            <p class="text-center text-gray-500 py-8">{{ __('messages.no_reviews_yet_be_first') }}</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const copyTranslations = @json([
                'success_title' => __('messages.copy_link_success_title'),
                'success_message' => __('messages.copy_link_success_message'),
                'error_message' => __('messages.copy_link_error_message'),
                'error_title' => __('messages.error'),
            ]);

            const reportTranslations = @json([
                'title' => __('messages.report_modal_title'),
                'reason_label' => __('messages.report_reason_label'),
                'reason_placeholder' => __('messages.report_reason_placeholder'),
                'option_spam' => __('messages.report_reason_spam'),
                'option_harassment' => __('messages.report_reason_harassment'),
                'option_inappropriate' => __('messages.report_reason_inappropriate'),
                'option_fraud' => __('messages.report_reason_fraud'),
                'option_copyright' => __('messages.report_reason_copyright'),
                'option_other' => __('messages.report_reason_other'),
                'description_label' => __('messages.report_description_label'),
                'description_placeholder' => __('messages.report_description_placeholder'),
                'submit' => __('messages.report_submit_button'),
                'cancel' => __('messages.cancel'),
                'validation_reason' => __('messages.report_validation_reason'),
                'success_title' => __('messages.report_success_title'),
                'success_message' => __('messages.report_success_message'),
                'success_alert' => __('messages.report_success_alert'),
                'error_title' => __('messages.error'),
                'error_message' => __('messages.report_error_message'),
                'prompt_reason' => __('messages.report_prompt_reason'),
                'prompt_description' => __('messages.report_prompt_description'),
            ]);

            // Copy to clipboard function
            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: copyTranslations.success_title,
                                text: copyTranslations.success_message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(copyTranslations.success_message);
                        }
                    }).catch(function(err) {
                        console.error('Failed to copy:', err);
                        // Fallback for older browsers
                        fallbackCopyToClipboard(text);
                    });
                } else {
                    fallbackCopyToClipboard(text);
                }
            }

            function fallbackCopyToClipboard(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: copyTranslations.success_title,
                            text: copyTranslations.success_message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(copyTranslations.success_message);
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire(copyTranslations.error_title, copyTranslations.error_message, 'error');
                    } else {
                        alert(copyTranslations.error_message);
                    }
                }
                document.body.removeChild(textArea);
            }

            const noteReportEndpoint = "{{ route('notes.report', $note) }}";

            function showNoteReportModal() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: reportTranslations.title,
                        html: `
                            <form id="noteReportForm" class="text-left">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">${reportTranslations.reason_label}</label>
                                    <select id="noteReportReason" name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                                        <option value="">${reportTranslations.reason_placeholder}</option>
                                        <option value="spam">${reportTranslations.option_spam}</option>
                                        <option value="harassment">${reportTranslations.option_harassment}</option>
                                        <option value="inappropriate">${reportTranslations.option_inappropriate}</option>
                                        <option value="fraud">${reportTranslations.option_fraud}</option>
                                        <option value="copyright">${reportTranslations.option_copyright}</option>
                                        <option value="other">${reportTranslations.option_other}</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">${reportTranslations.description_label}</label>
                                    <textarea id="noteReportDescription" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="${reportTranslations.description_placeholder}" maxlength="1000"></textarea>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: reportTranslations.submit,
                        confirmButtonColor: '#dc2626',
                        cancelButtonText: reportTranslations.cancel,
                        focusConfirm: false,
                        preConfirm: () => {
                            const reason = document.getElementById('noteReportReason').value;
                            const description = document.getElementById('noteReportDescription').value;

                            if (!reason) {
                                Swal.showValidationMessage(reportTranslations.validation_reason);
                                return false;
                            }

                            return { reason, description };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitNoteReport(result.value.reason, result.value.description);
                        }
                    });
                } else {
                    const reason = prompt(reportTranslations.prompt_reason);
                    if (reason) {
                        const description = prompt(reportTranslations.prompt_description);
                        submitNoteReport(reason, description || '');
                    }
                }
            }

            function submitNoteReport(reason, description) {
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfTokenMeta) {
                    console.error('Missing CSRF token meta tag.');
                    return;
                }

                fetch(noteReportEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfTokenMeta.content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ reason, description })
                })
                    .then(async (response) => {
                        if (!response.ok) {
                            let message = reportTranslations.error_message;
                            try {
                                const data = await response.json();
                                message = data.message || message;
                            } catch (e) {
                                // ignore JSON parse errors
                            }
                            throw new Error(message);
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: reportTranslations.success_title,
                                text: data.message || reportTranslations.success_message,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(data.message || reportTranslations.success_alert);
                        }
                    })
                    .catch((error) => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire(reportTranslations.error_title, error.message || reportTranslations.error_message, 'error');
                        } else {
                            alert(error.message || reportTranslations.error_message);
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('rating-container');
                if (!container) return;

                const ratingInput = document.getElementById('rating-input');
                const stars = container.querySelectorAll('.star-rating');
                let selectedRating = 0;

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        selectedRating = parseInt(this.dataset.rating);
                        ratingInput.value = selectedRating;

                        stars.forEach((s, index) => {
                            s.querySelector('svg').classList.remove('text-gray-300',
                                'text-yellow-400');
                            if (index < selectedRating) {
                                s.querySelector('svg').classList.add('text-yellow-400');
                            } else {
                                s.querySelector('svg').classList.add('text-gray-300');
                            }
                        });
                    });

                    star.addEventListener('mouseenter', function() {
                        const hoverRating = parseInt(this.dataset.rating);
                        stars.forEach((s, index) => {
                            s.querySelector('svg').classList.remove('text-gray-300',
                                'text-yellow-400');
                            if (index < hoverRating) {
                                s.querySelector('svg').classList.add('text-yellow-400');
                            } else {
                                s.querySelector('svg').classList.add('text-gray-300');
                            }
                        });
                    });
                });

                container.addEventListener('mouseleave', function() {
                    stars.forEach((s, index) => {
                        s.querySelector('svg').classList.remove('text-gray-300', 'text-yellow-400');
                        if (index < selectedRating) {
                            s.querySelector('svg').classList.add('text-yellow-400');
                        } else {
                            s.querySelector('svg').classList.add('text-gray-300');
                        }
                    });
                });

                // Handle review delete confirmation with SweetAlert2
                document.querySelectorAll('.delete-review-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formElement = this;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '{{ __('messages.are_you_sure') }}',
                                text: '{{ __('messages.delete_confirmation') }}',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc2626',
                                cancelButtonColor: '#6b7280',
                                confirmButtonText: '{{ __('messages.yes_delete') }}',
                                cancelButtonText: '{{ __('messages.no_cancel') }}'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    formElement.submit();
                                }
                            });
                        } else {
                            if (confirm('{{ __('messages.delete_confirmation') }}')) {
                                formElement.submit();
                            }
                        }
                    });
                });
            });

            @if (auth()->check() && auth()->user()->hasPremium() && auth()->user()->role === 'buyer')
                function showCollectionModal(noteId) {
                    const collections = @json(auth()->user()->collections()->get(['id', 'name']));

                    if (collections.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: '{{ __('messages.no_collections_title') }}',
                            text: '{{ __('messages.no_collections_message') }}',
                            showCancelButton: true,
                            confirmButtonText: '{{ __('messages.create_collection_button') }}',
                            cancelButtonText: '{{ __('messages.cancel') }}'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('collections.create') }}';
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        title: '{{ __('messages.collection_modal_title') }}',
                        input: 'select',
                        inputOptions: Object.fromEntries(collections.map(c => [c.id, c.name])),
                        inputPlaceholder: '{{ __('messages.collection_modal_placeholder') }}',
                        showCancelButton: true,
                        confirmButtonText: '{{ __('messages.collection_modal_confirm') }}',
                        cancelButtonText: '{{ __('messages.cancel') }}',
                        inputValidator: (value) => {
                            if (!value) {
                                return '{{ __('messages.collection_modal_validation') }}';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/collections/${result.value}/add-note`;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content');
                            form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="note_id" value="${noteId}">
                `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            @endif
        </script>
        @auth
            @if (auth()->user()->hasPremium() && auth()->user()->role === 'buyer' && ($alreadyPurchased ?? false))
                // Reading Progress Tracking
                const noteId = '{{ $note->id }}';
                let progressUpdateTimeout;
                let currentProgress = 0;

                // Load existing progress
                fetch(`/reading-progress/note/${noteId}`, {
                headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
                }
                })
                .then(response => response.json())
                .then(data => {
                if (data.success && data.progress) {
                currentProgress = data.progress.progress_percentage || 0;
                updateProgressBar(currentProgress);
                }
                })
                .catch(error => console.error('Error loading progress:', error));

                // Track scroll position
                const noteContent = document.getElementById('note-content');
                if (noteContent) {
                const totalHeight = noteContent.scrollHeight;
                const viewportHeight = window.innerHeight;
                const totalScrollable = totalHeight - viewportHeight;

                window.addEventListener('scroll', () => {
                clearTimeout(progressUpdateTimeout);

                progressUpdateTimeout = setTimeout(() => {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const contentTop = noteContent.offsetTop;
                const scrollPosition = Math.max(0, scrollTop - contentTop);

                const progress = totalScrollable > 0
                ? Math.min(100, Math.round((scrollPosition / totalScrollable) * 100))
                : 0;

                if (progress !== currentProgress) {
                currentProgress = progress;
                updateProgressBar(progress);
                saveProgress(progress, scrollPosition, noteContent.textContent.length);
                }
                }, 500); // Debounce: update every 500ms
                });
                }

                function updateProgressBar(percentage) {
                const progressBar = document.getElementById('progress-bar');
                const progressPercentage = document.getElementById('progress-percentage');
                if (progressBar) progressBar.style.width = percentage + '%';
                if (progressPercentage) progressPercentage.textContent = percentage + '%';
                }

                function saveProgress(percentage, position, totalChars) {
                fetch(`/reading-progress/note/${noteId}`, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
                },
                body: JSON.stringify({
                progress_percentage: percentage,
                last_position: position,
                read_characters: Math.round((percentage / 100) * totalChars),
                total_characters: totalChars
                })
                })
                .catch(error => console.error('Error saving progress:', error));
                }

                const bookmarkTranslations = @json([
                    'empty' => __('messages.bookmarks_empty', ['action' => __('messages.add_bookmark')]),
                    'default_title' => __('messages.bookmark_default_title'),
                    'go_to' => __('messages.bookmark_go_to'),
                    'delete' => __('messages.bookmark_delete'),
                    'modal_title' => __('messages.bookmark_modal_title'),
                    'modal_title_placeholder' => __('messages.bookmark_modal_title_placeholder'),
                    'modal_note_placeholder' => __('messages.bookmark_modal_note_placeholder'),
                    'modal_confirm' => __('messages.bookmark_modal_confirm'),
                    'cancel' => __('messages.cancel'),
                    'success_title' => __('messages.bookmark_success_title'),
                    'success_message' => __('messages.bookmark_success_message'),
                    'error_title' => __('messages.bookmark_error_title'),
                    'error_message' => __('messages.bookmark_error_message'),
                    'delete_confirm_title' => __('messages.bookmark_delete_confirm_title'),
                    'delete_confirm_text' => __('messages.bookmark_delete_confirm_text'),
                    'delete_confirm_button' => __('messages.bookmark_delete_confirm_button'),
                    'delete_success_title' => __('messages.bookmark_delete_success_title'),
                    'delete_success_message' => __('messages.bookmark_delete_success_message'),
                    'delete_error_message' => __('messages.bookmark_delete_error_message'),
                    'show' => __('messages.bookmarks_toggle_show'),
                    'hide' => __('messages.bookmarks_toggle_hide'),
                ]);

                // Bookmarks functionality
                let bookmarks = [];
                let bookmarksVisible = false;

                // Load bookmarks
                function loadBookmarks() {
                fetch(`/bookmarks/note/${noteId}`, {
                headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
                }
                })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                bookmarks = data.bookmarks || [];
                renderBookmarks();
                }
                })
                .catch(error => console.error('Error loading bookmarks:', error));
                }

                function renderBookmarks() {
                const bookmarksList = document.getElementById('bookmarks-list');
                if (!bookmarksList) return;

                if (bookmarks.length === 0) {
                bookmarksList.innerHTML = `<p class="text-sm text-gray-600">${bookmarkTranslations.empty}</p>`;
                return;
                }

                bookmarksList.innerHTML = bookmarks.map(bookmark => `
                <div
                    class="flex items-start justify-between p-2 bg-white rounded border border-purple-200 hover:bg-purple-50 transition-colors">
                    <div class="flex-1">
                        <h5 class="text-sm font-medium text-gray-900">${bookmark.title || bookmarkTranslations.default_title}</h5>
                        ${bookmark.section_text ? `<p class="text-xs text-gray-600 mt-1 line-clamp-2">
                            ${bookmark.section_text.substring(0, 100)}...</p>` : ''}
                        ${bookmark.note_text ? `<p class="text-xs text-purple-700 mt-1">${bookmark.note_text}</p>` : ''}
                    </div>
                    <div class="flex items-center space-x-2 ml-3">
                        <button onclick="scrollToBookmark(${bookmark.position})" class="text-xs text-blue-600 hover:text-blue-800"
                            title="${bookmarkTranslations.go_to}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                        <button onclick="deleteBookmark('${bookmark.id}')" class="text-xs text-red-600 hover:text-red-800"
                            title="${bookmarkTranslations.delete}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                `).join('');
                }

                function toggleBookmarks() {
                const section = document.getElementById('bookmarks-section');
                const toggleText = document.getElementById('bookmarks-toggle-text');
                if (section) {
                bookmarksVisible = !bookmarksVisible;
                section.classList.toggle('hidden', !bookmarksVisible);
                if (toggleText) {
                toggleText.textContent = bookmarksVisible ? bookmarkTranslations.hide : bookmarkTranslations.show;
                }
                }
                }

                function showAddBookmarkModal() {
                const noteContent = document.getElementById('note-content');
                const selection = window.getSelection();
                let selectedText = '';
                let position = 0;

                if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                selectedText = range.toString();
                position = range.startOffset;
                } else {
                // Use scroll position as fallback
                position = window.pageYOffset || document.documentElement.scrollTop;
                }

                if (typeof Swal !== 'undefined') {
                Swal.fire({
                title: bookmarkTranslations.modal_title,
                html: `
                <input id="bookmark-title" class="swal2-input" placeholder="${bookmarkTranslations.modal_title_placeholder.replace(/"/g, '&quot;')}"
                    value="${selectedText.substring(0, 50) || ''}">
                <textarea id="bookmark-note" class="swal2-textarea" placeholder="${bookmarkTranslations.modal_note_placeholder.replace(/"/g, '&quot;')}"></textarea>
                `,
                showCancelButton: true,
                confirmButtonText: bookmarkTranslations.modal_confirm,
                cancelButtonText: bookmarkTranslations.cancel,
                preConfirm: () => {
                return {
                title: document.getElementById('bookmark-title').value || bookmarkTranslations.default_title,
                note_text: document.getElementById('bookmark-note').value || null,
                section_text: selectedText || null,
                position: position
                };
                }
                }).then((result) => {
                if (result.isConfirmed) {
                createBookmark(result.value);
                }
                });
                }
                }

                function createBookmark(data) {
                fetch(`/bookmarks/note/${noteId}`, {
                method: 'POST',
                headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
                },
                body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                loadBookmarks();
                if (typeof Swal !== 'undefined') {
                Swal.fire(bookmarkTranslations.success_title, bookmarkTranslations.success_message, 'success');
                }
                } else {
                if (typeof Swal !== 'undefined') {
                Swal.fire(bookmarkTranslations.error_title, data.message || bookmarkTranslations.error_message, 'error');
                }
                }
                })
                .catch(error => {
                console.error('Error creating bookmark:', error);
                if (typeof Swal !== 'undefined') {
                Swal.fire(bookmarkTranslations.error_title, bookmarkTranslations.error_message, 'error');
                }
                });
                }

                function deleteBookmark(bookmarkId) {
                if (typeof Swal !== 'undefined') {
                Swal.fire({
                title: bookmarkTranslations.delete_confirm_title,
                text: bookmarkTranslations.delete_confirm_text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: bookmarkTranslations.delete_confirm_button,
                cancelButtonText: bookmarkTranslations.cancel
                }).then((result) => {
                if (result.isConfirmed) {
                fetch(`/bookmarks/${bookmarkId}`, {
                method: 'DELETE',
                headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
                }
                })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                loadBookmarks();
                Swal.fire(bookmarkTranslations.delete_success_title, bookmarkTranslations.delete_success_message, 'success');
                } else {
                Swal.fire(bookmarkTranslations.error_title, data.message || bookmarkTranslations.delete_error_message, 'error');
                }
                })
                .catch(error => {
                console.error('Error deleting bookmark:', error);
                Swal.fire(bookmarkTranslations.error_title, bookmarkTranslations.delete_error_message, 'error');
                });
                }
                });
                }
                }

                function scrollToBookmark(position) {
                window.scrollTo({
                top: position,
                behavior: 'smooth'
                });
                }

                // Load bookmarks on page load
                loadBookmarks();
            @endif
        @endauth
    @endpush
@endsection
