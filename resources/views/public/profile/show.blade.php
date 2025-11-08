@extends('layouts.app')

@section('title', $user->name . ' - Profile')

@push('meta')
    @php
        $shareUrl = route('public.profile.show', $user->username);
        $shareTitle = $user->name . ' - ' . config('app.name') . ' Seller Profile';
        $shareDescription =
            $user->bio ?:
            'Check out my notes on ' . config('app.name') . '! ' . $stats['total_notes'] . ' notes available.';
        $shareImage = $user->avatar
            ? (str_starts_with($user->avatar, 'http')
                ? $user->avatar
                : url(Storage::url($user->avatar)))
            : null;
    @endphp
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="profile">
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
    <meta name="description" content="{{ $shareDescription }}">
@endpush

@section('content')
    <div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Profile Header -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <!-- Avatar -->
                        <div
                            class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center shadow-lg overflow-hidden">
                            @if ($user->avatar)
                                @if (str_starts_with($user->avatar, 'http'))
                                    <img src="{{ $user->avatar }}" alt="{{ $user->name }}"
                                        class="w-24 h-24 rounded-full object-cover">
                                @else
                                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                        class="w-24 h-24 rounded-full object-cover">
                                @endif
                            @else
                                <span
                                    class="text-4xl font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                            @endif
                        </div>

                        <!-- User Info -->
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                            @if ($user->bio)
                                <p class="text-base text-gray-600 mb-3">{{ $user->bio }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
                                @if ($user->location)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $user->location }}
                                    </div>
                                @endif
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Joined {{ $user->created_at->format('M Y') }}
                                </div>
                            </div>
                        </div>

                        <!-- Role Badge and Share Buttons -->
                        <div class="flex flex-col items-end gap-3">
                            <div class="flex flex-wrap items-center gap-2 justify-end">
                                @if ($user->role === 'admin')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        {{ __('messages.admin') }}
                                    </span>
                                @elseif($user->role === 'seller')
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        {{ __('messages.seller') }}
                                    </span>
                                @endif
                                
                                @if ($user->hasPremium())
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white shadow-md"
                                        title="Premium Buyer - Akses ke fitur premium">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Premium Buyer
                                    </span>
                                @endif
                            </div>

                            <!-- Share Profile Buttons -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 mr-1">Share Profile:</span>
                                @php
                                    $profileUrl = route('public.profile.show', $user->username);
                                    $profileTitle = urlencode(
                                        $user->name . ' - ' . config('app.name') . ' Seller Profile',
                                    );
                                    $profileText = urlencode(
                                        'Check out my notes on ' .
                                            config('app.name') .
                                            '! ' .
                                            $stats['total_notes'] .
                                            ' notes available.',
                                    );
                                @endphp

                                <!-- Facebook -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($profileUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                                    title="Share on Facebook">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>

                                <!-- Twitter -->
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode($profileUrl) }}&text={{ $profileTitle }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors duration-200"
                                    title="Share on Twitter">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                    </svg>
                                </a>

                                <!-- WhatsApp -->
                                <a href="https://wa.me/?text={{ $profileTitle }}%20{{ urlencode($profileUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors duration-200"
                                    title="Share on WhatsApp">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                </a>

                                <!-- LinkedIn -->
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($profileUrl) }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-700 text-white hover:bg-blue-800 transition-colors duration-200"
                                    title="Share on LinkedIn">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                    </svg>
                                </a>

                                <!-- Copy Link -->
                                <button onclick="copyToClipboard('{{ $profileUrl }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors duration-200"
                                    title="Copy profile link">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- AI Chat Button -->
                            @if($stats['total_notes'] > 0)
                                <a href="{{ route('public.profile.ai-chat', $user->username) }}"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white text-sm font-medium rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                    Ask AI About {{ $user->name }}'s Notes
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div
                    class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.public_notes') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_notes'] }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_sales') }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_sales'] }}</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            @if ($stats['average_rating'] > 0)
                                <svg class="h-6 w-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @else
                                <svg class="h-6 w-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.average_rating') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                @if ($stats['average_rating'] > 0)
                                    {{ $stats['average_rating'] }} / 5
                                @else
                                    N/A
                                @endif
                            </p>
                            <p class="text-xs text-gray-500">{{ $stats['total_reviews'] }}
                                {{ $stats['total_reviews'] == 1 ? __('messages.review') : __('messages.reviews_count') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.total_revenue') }}</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ currency($stats['total_revenue']) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px" aria-label="Tabs">
                        <button onclick="showTab('notes')" id="tab-notes"
                            class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                            {{ __('messages.public_notes') }} ({{ $stats['total_notes'] }})
                        </button>
                        <button onclick="showTab('posts')" id="tab-posts"
                            class="tab-button px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Forum Posts ({{ $stats['total_posts'] }})
                        </button>
                    </nav>
                </div>

                <!-- Notes Tab Content -->
                <div id="tab-content-notes" class="tab-content">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.public_notes') }}</h2>
                            <p class="text-sm text-gray-600 mt-1">All notes published by {{ $user->name }}</p>
                        </div>
                        @if ($publicNotes->count() > 0)
                            <a href="{{ route('marketplace.index', ['seller' => $user->id]) }}"
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                View in Marketplace
                            </a>
                        @endif
                    </div>
                    <div class="p-6">
                        @if ($publicNotes->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach ($publicNotes as $note)
                                    <div
                                        class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                                        <!-- Thumbnail -->
                                        @if ($note->hasThumbnails())
                                            <div class="h-48 overflow-hidden">
                                                <img src="{{ Storage::url($note->thumbnails[0]) }}"
                                                    alt="{{ $note->title }}"
                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                            </div>
                                        @endif
                                        <div class="p-6">
                                            <div class="mb-4">
                                                <h3
                                                    class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                                    <a href="{{ route('marketplace.show', $note) }}">{{ $note->title }}</a>
                                                </h3>
                                                <p class="text-sm text-gray-600 line-clamp-3">{!! Str::limit(strip_tags($note->content), 100) !!}</p>
                                            </div>

                                            @if ($note->tags->count() > 0)
                                                <div class="flex flex-wrap gap-2 mb-4">
                                                    @foreach ($note->tags->take(3) as $tag)
                                                        <span
                                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                                @if ($note->average_rating > 0)
                                                    <div class="flex items-center gap-1">
                                                        @for ($i = 1; $i <= 5; $i++)
                                                            <svg class="w-3 h-3 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                                fill="currentColor" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        @endfor
                                                        <span class="text-xs text-gray-600">{{ $note->average_rating }}</span>
                                                    </div>
                                                @endif
                                                @if ($note->price > 0)
                                                    <span class="text-sm font-semibold text-yellow-600">
                                                        {{ currency($note->price) }}</span>
                                                @else
                                                    <span class="text-xs text-gray-500">{{ __('messages.free') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6">
                                {{ $publicNotes->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_public_notes_yet') }}
                                </h3>
                                <p class="mt-2 text-sm text-gray-500">{{ __('messages.user_hasnt_published_notes') }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Posts Tab Content -->
                <div id="tab-content-posts" class="tab-content hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Forum Posts</h2>
                        <p class="text-sm text-gray-600 mt-1">All forum posts by {{ $user->name }}</p>
                    </div>
                    <div class="p-6">
                        @if ($userPosts->count() > 0)
                            <div class="space-y-4">
                                @foreach ($userPosts as $post)
                                    @include('forum.partials.post-card', ['post' => $post])
                                @endforeach
                            </div>

                            <div class="mt-6">
                                {{ $userPosts->links() }}
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">No posts yet</h3>
                                <p class="mt-2 text-sm text-gray-500">This user hasn't posted anything in the forum yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Tab switching
            function showTab(tabName) {
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => {
                    content.classList.add('hidden');
                });
                
                // Remove active state from all tabs
                document.querySelectorAll('.tab-button').forEach(button => {
                    button.classList.remove('border-blue-500', 'text-blue-600');
                    button.classList.add('border-transparent', 'text-gray-500');
                });
                
                // Show selected tab content
                document.getElementById(`tab-content-${tabName}`).classList.remove('hidden');
                
                // Add active state to selected tab
                const activeTab = document.getElementById(`tab-${tabName}`);
                activeTab.classList.remove('border-transparent', 'text-gray-500');
                activeTab.classList.add('border-blue-500', 'text-blue-600');
            }

            // Copy to clipboard function
            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Link Copied!',
                                text: 'Profile link has been copied to your clipboard.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Profile link copied to clipboard!');
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
                            title: 'Link Copied!',
                            text: 'Profile link has been copied to your clipboard.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Profile link copied to clipboard!');
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    alert('Failed to copy link. Please copy manually.');
                }
                document.body.removeChild(textArea);
            }
        </script>
    @endpush
@endsection
