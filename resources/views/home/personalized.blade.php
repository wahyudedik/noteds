@extends('layouts.app')

@section('title', __('messages.home') . ' - ' . config('app.name'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ __('messages.welcome_back', ['name' => auth()->user()->name]) }}
            </h1>
            <p class="mt-2 text-base text-gray-600">
                {{ __('messages.personalized_feed_description') }}
            </p>
        </div>

        <!-- Featured Hero -->
        @if(isset($featuredHero) && $featuredHero)
            <div class="mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <span class="inline-block px-3 py-1 bg-white rounded-full text-xs font-semibold text-orange-600 mb-2">⭐ {{ __('messages.featured_note') }}</span>
                        <h3 class="text-2xl font-bold text-white mb-2">
                            <a href="{{ route('marketplace.show', $featuredHero->note) }}" 
                               class="hover:underline">
                                {{ $featuredHero->note->title }}
                            </a>
                        </h3>
                        <p class="text-white/90 mb-3">{{ Str::limit($featuredHero->note->summary ?? strip_tags($featuredHero->note->content), 100) }}</p>
                        <div class="flex items-center gap-4">
                            @if($featuredHero->note->price > 0)
                                <span class="text-white font-semibold">{{ currency($featuredHero->note->price) }}</span>
                            @else
                                <span class="text-white font-semibold">{{ __('messages.free') }}</span>
                            @endif
                            <a href="{{ route('marketplace.show', $featuredHero->note) }}" 
                               class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition">
                                {{ __('messages.view_note') }} →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Recently Viewed Notes -->
        @if(isset($recentlyViewed) && count($recentlyViewed) > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">📚 {{ __('messages.recently_viewed') }}</h2>
                    <a href="{{ route('reading-history.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                        {{ __('messages.view_all') }} →
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recentlyViewed as $note)
                        @include('home.partials.note-card', ['note' => $note])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Personalized Recommendations -->
        @if(isset($recommendations) && count($recommendations) > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">✨ {{ __('messages.recommended_for_you') }}</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('messages.based_on_your_interests') }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($recommendations as $note)
                        @include('home.partials.note-card', ['note' => $note])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Category Preferences -->
        @if(isset($preferences) && $preferences && ($preferences->preferred_categories || $preferences->preferred_tags))
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 {{ __('messages.your_interests') }}</h2>
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    @if($preferences->preferred_categories && count($preferences->preferred_categories) > 0)
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('messages.preferred_categories') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferences->preferred_categories as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ ucfirst($category) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($preferences->preferred_tags && count($preferences->preferred_tags) > 0)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ __('messages.preferred_tags') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $tags = \App\Models\Tag::whereIn('id', $preferences->preferred_tags)->get();
                                @endphp
                                @foreach($tags as $tag)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <div class="mt-4">
                        <a href="{{ route('profile.edit') }}" class="text-sm text-blue-600 hover:text-blue-800">
                            {{ __('messages.update_preferences') }} →
                        </a>
                    </div>
                </div>
            </div>
        @endif

        <!-- Featured Carousel -->
        @if(isset($featuredCarousel) && $featuredCarousel->count() > 0)
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">⭐ {{ __('messages.featured_notes') }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredCarousel as $featured)
                        @php($note = $featured->note)
                        @include('home.partials.note-card', ['note' => $note, 'isFeatured' => true])
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Browse Marketplace CTA -->
        <div class="text-center py-8">
            <a href="{{ route('marketplace.index') }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                {{ __('messages.browse_marketplace') }} →
            </a>
        </div>
    </div>
</div>
@endsection
