@extends('40-shared/layouts/app')

@section('title', __('Marketplace'))

@section('content')
    <div class="bg-gray-50 min-h-screen">
        <!-- Hero Banner -->
        @if ($featuredBanner && $featuredBanner->note)
            @php
                $bannerSummary = '';
                $summary = $featuredBanner->note->summary ?? null;
                if (is_string($summary)) {
                    $bannerSummary = $summary;
                } elseif (is_array($summary)) {
                    $bannerSummary = implode(' ', $summary);
                } else {
                    $bannerSummary = '';
                }
                $bannerTitle = (string) ($featuredBanner->note->title ?? '');
                $bannerUserName = (string) ($featuredBanner->note->user->name ?? __('Seller'));
                $bannerPrice = (string) currency($featuredBanner->note->price ?? 0);
                $bannerImageUrl = (string) ($featuredBanner->note->primary_thumbnail_url ?? asset('placeholder.png'));
            @endphp
            <div class="relative bg-gradient-to-r from-blue-600 to-indigo-600 text-white overflow-hidden">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    @php
                        $bannerNoteId = optional($featuredBanner->note)->id ? (string) $featuredBanner->note->id : '';
                    @endphp
                    <a href="{{ $bannerNoteId ? route('marketplace.show', $bannerNoteId) : '#' }}"
                        class="flex flex-col md:flex-row items-center gap-6 hover:opacity-90 transition">
                        <div class="flex-1">
                            <div
                                class="inline-block px-3 py-1 rounded-full bg-white/20 text-sm font-semibold mb-2 uppercase tracking-wide">
                                {{ __('Featured') }}</div>
                            <h2 class="text-3xl md:text-4xl font-bold mb-3 leading-tight">{{ $bannerTitle }}
                            </h2>
                            <p class="text-lg text-blue-50 mb-4 line-clamp-2">
                                {{ $bannerSummary }}
                            </p>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10.5 1.5H4.75A2.75 2.75 0 0 0 2 4.25v11A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-11A2.75 2.75 0 0 0 15.25 1.5zm7 13.75a1.25 1.25 0 0 1-1.25 1.25H4.75a1.25 1.25 0 0 1-1.25-1.25v-11a1.25 1.25 0 0 1 1.25-1.25h10.5a1.25 1.25 0 0 1 1.25 1.25z" />
                                    </svg>
                                    {{ $bannerUserName }}
                                </span>
                                <span class="font-semibold">{{ $bannerPrice }}</span>
                            </div>
                        </div>
                        @if ($featuredBanner->note->thumbnails)
                            <div class="flex-shrink-0 w-48 h-48">
                                <img src="{{ $bannerImageUrl }}" alt="{{ $bannerTitle }}"
                                    class="w-full h-full object-cover rounded-lg">
                            </div>
                        @endif
                    </a>
                </div>
            </div>
        @endif

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Search and Filters -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <form action="{{ route('marketplace.index') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="search"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Search') }}</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}"
                                placeholder="{{ __('Search notes...') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="ecosystem"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Category') }}</label>
                            <select id="ecosystem" name="ecosystem"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="3d" {{ request('ecosystem') === '3d' ? 'selected' : '' }}>3D</option>
                                <option value="code" {{ request('ecosystem') === 'code' ? 'selected' : '' }}>
                                    {{ __('Code') }}</option>
                                <option value="design" {{ request('ecosystem') === 'design' ? 'selected' : '' }}>
                                    {{ __('Design') }}</option>
                                <option value="ebook" {{ request('ecosystem') === 'ebook' ? 'selected' : '' }}>E-Book
                                </option>
                                <option value="video" {{ request('ecosystem') === 'video' ? 'selected' : '' }}>
                                    {{ __('Video') }}</option>
                                <option value="audio" {{ request('ecosystem') === 'audio' ? 'selected' : '' }}>
                                    {{ __('Audio') }}</option>
                                <option value="theme" {{ request('ecosystem') === 'theme' ? 'selected' : '' }}>
                                    {{ __('Theme') }}</option>
                                <option value="photo" {{ request('ecosystem') === 'photo' ? 'selected' : '' }}>
                                    {{ __('Photo') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="sort"
                                class="block text-sm font-medium text-gray-700 mb-1">{{ __('Sort') }}</label>
                            <select id="sort" name="sort"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>
                                    {{ __('Newest') }}</option>
                                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>
                                    {{ __('Most Popular') }}</option>
                                <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>
                                    {{ __('Highest Rated') }}</option>
                                <option value="trending" {{ request('sort') === 'trending' ? 'selected' : '' }}>
                                    {{ __('Trending') }}</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>
                                    {{ __('Price: Low to High') }}</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>
                                    {{ __('Price: High to Low') }}</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                                {{ __('Search') }}
                            </button>
                        </div>
                    </div>

                    <details class="pt-4 border-t border-gray-200">
                        <summary class="cursor-pointer font-semibold text-gray-900 hover:text-blue-600">
                            {{ __('Advanced Filters') }}</summary>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label for="min_price"
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Min Price') }}</label>
                                <input type="number" id="min_price" name="min_price" value="{{ request('min_price') }}"
                                    placeholder="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label for="max_price"
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Max Price') }}</label>
                                <input type="number" id="max_price" name="max_price" value="{{ request('max_price') }}"
                                    placeholder="999999" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label for="min_rating"
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Min Rating') }}</label>
                                <select id="min_rating" name="min_rating"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">{{ __('Any Rating') }}</option>
                                    <option value="3" {{ request('min_rating') === '3' ? 'selected' : '' }}>3+ ⭐
                                    </option>
                                    <option value="4" {{ request('min_rating') === '4' ? 'selected' : '' }}>4+ ⭐
                                    </option>
                                    <option value="4.5" {{ request('min_rating') === '4.5' ? 'selected' : '' }}>4.5+ ⭐
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="language"
                                    class="block text-sm font-medium text-gray-700 mb-1">{{ __('Language') }}</label>
                                <select id="language" name="language"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                                    <option value="">{{ __('All Languages') }}</option>
                                    <option value="en" {{ request('language') === 'en' ? 'selected' : '' }}>
                                        {{ __('English') }}</option>
                                    <option value="id" {{ request('language') === 'id' ? 'selected' : '' }}>
                                        {{ __('Indonesian') }}</option>
                                </select>
                            </div>
                        </div>
                    </details>
                </form>
            </div>

            <!-- Saved Searches & Search History -->
            @if ($savedSearches->count() > 0 || $searchHistory->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    @if ($savedSearches->count() > 0)
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Saved Searches') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($savedSearches as $saved)
                                    <a href="{{ route('marketplace.index', array_filter(json_decode($saved->filters, true))) }}"
                                        class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 rounded-full text-sm font-medium hover:bg-blue-100">
                                        {{ $saved->name }}
                                        <form action="{{ route('marketplace.delete-saved-search', $saved) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="hover:text-blue-900">×</button>
                                        </form>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($searchHistory->count() > 0)
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">{{ __('Recent Searches') }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($searchHistory as $history)
                                    <a href="{{ route('marketplace.index', ['search' => $history->query]) }}"
                                        class="inline-block px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200">
                                        {{ $history->query }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Featured Notes Grid -->
            @if ($featuredNotes->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-gray-900">{{ __('Featured') }}</h2>
                        <span class="text-sm text-gray-500">{{ __('Handpicked selections') }}</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($featuredNotes as $featured)
                            @if ($featured->note)
                                @php
                                    $featureNoteId = optional($featured->note)->id ? (string) $featured->note->id : '';
                                @endphp
                                <a href="{{ $featureNoteId ? route('marketplace.show', $featureNoteId) : '#' }}"
                                    class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition">
                                    @if ($featured->note->thumbnails)
                                        <div class="relative h-48 bg-gray-100 overflow-hidden">
                                            @php
                                                $featureAltTitle = (string) ($featured->note->title ?? '');
                                                $featureThumbnail = $featured->note->primary_thumbnail_url;
                                            @endphp
                                            <img src="{{ $featureThumbnail }}" alt="{{ $featureAltTitle }}"
                                                class="w-full h-full object-cover">
                                            <div
                                                class="absolute top-2 right-2 px-2 py-1 bg-green-600 text-white text-xs font-semibold rounded">
                                                {{ __('Featured') }}</div>
                                        </div>
                                    @endif
                                    <div class="p-4">
                                        @php
                                            $featureTitle = (string) ($featured->note->title ?? '');
                                            $featureSummary = $featured->note->summary ?? null;
                                            if (!is_string($featureSummary)) {
                                                $featureSummary = is_array($featureSummary)
                                                    ? implode(' ', $featureSummary)
                                                    : '';
                                            }
                                            $featurePrice = (string) currency($featured->note->price ?? 0);
                                            $featureUserName = (string) ($featured->note->user->name ?? __('Seller'));
                                        @endphp
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                                            {{ $featureTitle }}</h3>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                            {{ Str::limit(strip_tags($featureSummary), 100) }}</p>
                                        <div class="flex items-center justify-between">
                                            <span class="font-semibold text-green-600">{{ $featurePrice }}</span>
                                            <span class="text-xs text-gray-500">{{ __('by') }}
                                                {{ $featureUserName }}</span>
                                        </div>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recommended For You (Personalized) -->
            @auth
                @if (!empty($recommendedForYou) && count($recommendedForYou) > 0)
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">✨ {{ __('Recommended For You') }}</h2>
                            <span class="text-sm text-gray-500">{{ __('Based on your activity') }}</span>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="flex gap-4 pb-4" style="scroll-snap-type: x mandatory;">
                                @foreach ($recommendedForYou as $note)
                                    <div class="flex-none w-64" style="scroll-snap-align: start;">
                                        <a href="{{ route('notes.show', $note) }}"
                                            class="block bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition h-full">
                                            @if ($note->thumbnails)
                                                <div class="relative h-40 bg-gray-100">
                                                    @php
                                                        $thumbnailData = json_decode($note->thumbnails, true);
                                                        $thumbnailUrl =
                                                            is_array($thumbnailData) && count($thumbnailData) > 0
                                                                ? Storage::url($thumbnailData[0])
                                                                : asset('placeholder.png');
                                                    @endphp
                                                    <img src="{{ $thumbnailUrl }}" alt="{{ $note->title }}"
                                                        class="w-full h-full object-cover">
                                                    @if ($note->avg_rating ?? 0 > 0)
                                                        <div
                                                            class="absolute bottom-2 right-2 px-2 py-1 bg-yellow-500 text-white text-xs font-semibold rounded">
                                                            ⭐ {{ number_format($note->avg_rating, 1) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div
                                                    class="h-40 bg-gradient-to-br from-blue-100 to-purple-100 flex items-center justify-center">
                                                    <i class="fas fa-book text-4xl text-gray-400"></i>
                                                </div>
                                            @endif
                                            <div class="p-4">
                                                <h3 class="text-md font-semibold text-gray-900 mb-2 line-clamp-2">
                                                    {{ $note->title }}
                                                </h3>
                                                <div class="flex items-center justify-between">
                                                    <span
                                                        class="font-semibold text-blue-600">{{ currency($note->price) }}</span>
                                                    <span class="text-xs text-gray-500">{{ __('by') }}
                                                        {{ $note->user->name ?? 'Seller' }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            <!-- Tags Cloud -->
            @if ($tags->count() > 0)
                <div class="mb-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Browse by Tag') }}</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tags->take(20) as $tag)
                            <a href="{{ route('marketplace.index', ['tags' => [$tag->id]]) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-full border border-gray-200 text-gray-700 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 text-sm transition">
                                {{ $tag->name }}
                                <span class="text-xs text-gray-500">{{ $tag->notes_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Notes Grid -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-2xl font-bold text-gray-900">
                        {{ request('search') ? __('Search Results') : __('All Notes') }}
                    </h2>
                    <span class="text-sm text-gray-500">{{ number_format($notes->total()) }} {{ __('notes') }}</span>
                </div>

                @if ($notes->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                        @foreach ($notes as $note)
                            <div
                                class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition flex flex-col">
                                @if ($note->thumbnails)
                                    <div class="relative h-40 bg-gray-100">
                                        @php
                                            $noteAltTitle = (string) ($note->title ?? '');
                                            $noteThumbnail = $note->primary_thumbnail_url;
                                        @endphp
                                        <img src="{{ $noteThumbnail }}" alt="{{ $noteAltTitle }}"
                                            class="w-full h-full object-cover">
                                        @if ($note->hasDiscount())
                                            <div
                                                class="absolute top-2 right-2 px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded">
                                                {{ __('Sale') }}</div>
                                        @endif
                                    </div>
                                @endif
                                <div class="p-4 flex-1 flex flex-col">
                                    @php
                                        $mainNoteId = optional($note)->id ? (string) $note->id : '';
                                    @endphp
                                    <a href="{{ $mainNoteId ? route('marketplace.show', $mainNoteId) : '#' }}"
                                        class="block">
                                        @php
                                            $noteTitle = (string) ($note->title ?? '');
                                        @endphp
                                        <h3
                                            class="text-base font-semibold text-gray-900 mb-1 line-clamp-2 hover:text-blue-600">
                                            {{ $noteTitle }}</h3>
                                    </a>
                                    @php
                                        $noteSummary = is_string($note->summary)
                                            ? $note->summary
                                            : (is_array($note->summary)
                                                ? implode(' ', $note->summary)
                                                : '');
                                    @endphp
                                    @if ($noteSummary)
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                            {{ Str::limit(strip_tags($noteSummary), 80) }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-1 mb-3 mt-auto">
                                        @foreach ($note->tags->take(2) as $tag)
                                            <span
                                                class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $tag->name }}</span>
                                        @endforeach
                                        @if ($note->tags->count() > 2)
                                            <span
                                                class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">+{{ $note->tags->count() - 2 }}</span>
                                        @endif
                                    </div>
                                    <div class="border-t border-gray-200 pt-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <div>
                                                @if ($note->reviews->count() > 0)
                                                    <span class="text-yellow-500">★</span>
                                                    <span
                                                        class="font-semibold text-gray-900">{{ number_format($note->reviews->avg('rating'), 1) }}</span>
                                                    <span class="text-gray-500">({{ $note->reviews->count() }})</span>
                                                @else
                                                    <span class="text-gray-500">{{ __('No ratings') }}</span>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                @php
                                                    $notePrice = (string) currency($note->price ?? 0);
                                                    $noteDiscountPrice = (string) currency($note->discount_price ?? 0);
                                                    $noteUserName = (string) ($note->user->name ?? __('Seller'));
                                                @endphp
                                                @if ($note->hasDiscount())
                                                    <span
                                                        class="text-gray-400 line-through text-xs">{{ $notePrice }}</span>
                                                    <span
                                                        class="font-semibold text-green-600 block">{{ $noteDiscountPrice }}</span>
                                                @else
                                                    <span
                                                        class="font-semibold text-green-600">{{ $note->price == 0 ? __('Free') : $notePrice }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-2">{{ __('by') }}
                                            {{ $noteUserName }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center">
                        {{ $notes->links() }}
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('No notes found') }}</h3>
                        <p class="text-gray-600 mb-4">{{ __('Try adjusting your search filters or browse all notes.') }}
                        </p>
                        <a href="{{ route('marketplace.index') }}"
                            class="inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                            {{ __('View All Notes') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
