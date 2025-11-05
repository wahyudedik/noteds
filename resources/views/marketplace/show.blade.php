@extends('layouts.app')

@section('title', $note->title . ' - Marketplace')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="{{ route('marketplace.index') }}" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_marketplace') }}
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Note Details Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="p-6">
                <!-- Badges and Rating -->
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @if($note->is_public)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            {{ __('messages.public') }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        {{ ucfirst($note->status) }}
                    </span>
                    @if($note->average_rating > 0)
                        <div class="inline-flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                            <span class="text-sm font-medium text-gray-700">{{ $note->average_rating }}</span>
                            <span class="text-xs text-gray-500">({{ $note->total_reviews }} {{ $note->total_reviews == 1 ? __('messages.review') : __('messages.reviews_count') }})</span>
                        </div>
                    @endif
                    @if($note->price > 0)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-base font-semibold bg-yellow-100 text-yellow-800">
                            {{ currency($note->price) }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                            {{ __('messages.free') }}
                        </span>
                    @endif
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $note->title }}</h1>

                <!-- Tags -->
                @if($note->tags->count() > 0)
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($note->tags as $tag)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <!-- Author and Meta Info -->
                <div class="mb-6 text-sm text-gray-600 border-b border-gray-200 pb-4">
                    <div class="flex items-center gap-4 flex-wrap">
                        <a href="{{ route('public.profile.show', $note->user->username) }}" class="flex items-center hover:text-blue-600 transition-colors duration-200">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                @if($note->user->avatar)
                                    <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <span class="text-sm font-semibold text-gray-600">{{ substr($note->user->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <span class="font-medium text-gray-900">{{ $note->user->name }}</span>
                                @if($note->user->location)
                                    <span class="text-xs text-gray-500">• {{ $note->user->location }}</span>
                                @endif
                            </div>
                        </a>
                        <div class="text-xs text-gray-500">
                            <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ __('messages.published') }} {{ localized_time($note->created_at, 'date') }}
                        </div>
                    </div>
                </div>

                <!-- Note Content (Protected for paid notes) -->
                @if($showFullContent ?? false)
                    <div class="prose max-w-none mb-6">
                        <div class="ql-editor text-gray-900 leading-relaxed">{!! $note->content !!}</div>
                    </div>

                    <!-- Attachments (if purchased or free) -->
                    @if($note->hasAttachments())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                {{ __('messages.attachments') }} ({{ $note->file_count }})
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($note->attachments as $attachment)
                                    @php
                                        $filename = is_array($attachment) ? ($attachment['filename'] ?? 'Unknown') : basename($attachment);
                                    @endphp
                                    <a href="{{ route('notes.attachments.download', ['note' => $note->id, 'filename' => $filename]) }}" 
                                       class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-300 transition-all duration-200">
                                        <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $filename }}</p>
                                            @if(is_array($attachment) && isset($attachment['size']))
                                                <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 2) }} KB</p>
                                            @endif
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Preview Content (for paid notes, before purchase) -->
                    <div class="prose max-w-none mb-6 relative">
                        <div class="whitespace-pre-wrap text-gray-900 leading-relaxed">
                            {!! \Illuminate\Support\Str::limit(strip_tags($note->preview_content ?: $note->content), 300) !!}
                            @if(strlen(strip_tags($note->content)) > 300)
                                <span class="text-gray-500 italic">...</span>
                            @endif
                        </div>
                        <!-- Blur overlay for paid content -->
                        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/80 to-white backdrop-blur-sm pointer-events-none flex items-end justify-center pb-8">
                            <div class="text-center px-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">{{ __('messages.full_content_available_after_purchase') }}</p>
                                <p class="text-xs text-gray-600">{{ __('messages.buy_note_to_unlock') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- What You'll Get Section -->
                    @if($note->price > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200 bg-blue-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ __('messages.what_youll_get') }}
                            </h3>
                            <ul class="space-y-2 text-sm text-gray-700">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ __('messages.full_note_content') }}</span>
                                </li>
                                @if($note->hasAttachments())
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span>{{ $note->file_count }} {{ __('messages.downloadable_files') }}</span>
                                    </li>
                                @endif
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span>{{ __('messages.lifetime_access') }}</span>
                                </li>
                            </ul>
                        </div>
                    @endif

                    <!-- Trust Indicators -->
                    @if($note->purchase_count > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex flex-wrap items-center gap-4 text-sm">
                                <div class="flex items-center text-gray-700">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="font-semibold">{{ $note->purchase_count }}</span>
                                    <span class="ml-1">{{ $note->purchase_count == 1 ? __('messages.purchase') : __('messages.purchases') }}</span>
                                </div>
                                @if($note->purchase_count >= 10)
                                    <div class="flex items-center text-yellow-600">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
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
                    @if($alreadyPurchased ?? false)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-green-600 font-semibold">{{ __('messages.you_have_purchased') }}</span>
                                </div>
                                <a href="{{ route('notes.show', $note) }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                    View full note →
                                </a>
                            </div>
                        </div>
                    @elseif($canBuy && $note->price > 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <form action="{{ route('marketplace.purchase', $note) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Buy Note ({{ currency($note->price) }})
                                </button>
                            </form>
                            <p class="text-sm text-gray-600 mt-3">
                                Your wallet balance: <strong class="font-semibold text-gray-900">{{ currency(auth()->user()->wallet_balance, auth()->user()->currency) }}</strong>
                                @if(auth()->user()->wallet_balance < $note->price)
                                    <span class="text-red-600 font-medium">(Insufficient: {{ currency($note->price - auth()->user()->wallet_balance, auth()->user()->currency) }})</span>
                                @endif
                            </p>
                        </div>
                    @elseif($note->user_id === auth()->id())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-gray-600 mb-3">This is your note.</p>
                            <a href="{{ route('notes.edit', $note) }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                Edit this note →
                            </a>
                        </div>
                    @elseif($note->price == 0)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <div class="flex items-center bg-green-50 border border-green-200 rounded-lg p-4">
                                <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-green-800 font-semibold">This note is free! Enjoy reading.</span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-gray-600 mb-3">{{ __('messages.to_purchase_please_login') }}</p>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            Login to Continue
                        </a>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Reviews Section -->
        @if(($note->total_reviews > 0) || (auth()->check() && isset($canReview) && $canReview))
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.reviews') }} ({{ $note->total_reviews }})</h2>
                </div>
                <div class="p-6">
                    <!-- Review Form (if user can review) -->
                    @if(auth()->check() && isset($canReview) && $canReview)
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('messages.write_review') }}</h3>
                            <form action="{{ route('reviews.store', $note) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.rating') }}</label>
                                    <div class="flex gap-1" id="rating-container">
                                        @for($i = 1; $i <= 5; $i++)
                                            <button type="button" class="star-rating text-gray-300 hover:text-yellow-400 transition-colors duration-200" data-rating="{{ $i }}">
                                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
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
                                    <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.comment_optional') }}</label>
                                    <textarea name="comment" id="comment" rows="4" placeholder="{{ __('messages.share_thoughts_about_note') }}" 
                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('comment') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"></textarea>
                                    @error('comment')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    {{ __('messages.submit_review') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Reviews List -->
                    @if($note->total_reviews > 0)
                        <div class="space-y-6">
                            @foreach($reviews as $review)
                                <div class="flex gap-4 pb-6 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                    <!-- Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if($review->user->avatar)
                                                <img src="{{ $review->user->avatar }}" alt="{{ $review->user->name }}" class="w-10 h-10 rounded-full object-cover">
                                            @else
                                                <span class="text-sm font-semibold text-gray-600">{{ substr($review->user->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Review Content -->
                                    <div class="flex-1">
                                        <div class="flex items-start justify-between mb-2">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-900">{{ $review->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ localized_diff_for_humans($review->created_at) }}</p>
                                            </div>
                                            @if($review->user_id === auth()->id())
                                                <div class="flex gap-2">
                                                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="delete-review-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:text-red-700 transition-colors duration-200">{{ __('messages.delete') }}</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Rating Stars -->
                                        <div class="flex gap-0.5 mb-2">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>

                                        <!-- Comment -->
                                        @if($review->comment)
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $review->comment }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <!-- Pagination -->
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

@push('scripts')
<script>
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
                s.querySelector('svg').classList.remove('text-gray-300', 'text-yellow-400');
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
                s.querySelector('svg').classList.remove('text-gray-300', 'text-yellow-400');
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
</script>
@endpush
@endsection

