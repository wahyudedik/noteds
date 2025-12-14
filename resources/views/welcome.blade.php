@extends('40-shared.layouts.app')

@section('title', __('messages.welcome'))

@section('content')
    @php($heroFeaturedNote = $heroFeaturedNote ?? null)
    @php($carouselFeaturedNotes = $carouselFeaturedNotes ?? [])
    @php($sections = $sections ?? [])
    @php($recentCmsPages = $recentCmsPages ?? [])
    <div class="max-w-7xl mx-auto p-6 space-y-8">
        <!-- Hero Featured Note -->
        @if (!empty($heroFeaturedNote))
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="md:flex">
                    <div class="md:w-2/3 p-6">
                        <h1 class="text-3xl font-bold mb-2">{{ $heroFeaturedNote->note->title ?? __('messages.welcome') }}
                        </h1>
                        <p class="text-gray-700 mb-4">
                            {{ Str::limit($heroFeaturedNote->note->excerpt ?? ($heroFeaturedNote->note->description ?? ''), 160) }}
                        </p>
                        <div class="flex items-center gap-3 text-sm text-gray-600 mb-4">
                            <span>{{ $heroFeaturedNote->note->user->username ?? '—' }}</span>
                            <span>•</span>
                            <span>{{ $heroFeaturedNote->note->reviews_count ?? 0 }} reviews</span>
                            <span>•</span>
                            <span>{{ $heroFeaturedNote->note->tags->pluck('name')->take(3)->implode(', ') }}</span>
                        </div>
                        <a href="{{ route('marketplace.show', $heroFeaturedNote->note->id ?? null) }}"
                            class="inline-block bg-blue-600 text-white px-4 py-2 rounded">View Note</a>
                    </div>
                    <div class="md:w-1/3 bg-gray-50 p-6">
                        <h3 class="font-semibold mb-2">Highlights</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>Impressions: {{ $heroFeaturedNote->impressions ?? 0 }}</li>
                            <li>Location: {{ $heroFeaturedNote->location ?? 'landing_hero' }}</li>
                            <li>Active: {{ ($heroFeaturedNote->status ?? 'inactive') === 'active' ? 'Yes' : 'No' }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif
        @isset($heroFeaturedNote)
            @include('partials.landing.hero', ['heroFeaturedNote' => $heroFeaturedNote])
        @endisset

        <!-- Carousel Featured Notes -->
        @if (!empty($carouselFeaturedNotes) && count($carouselFeaturedNotes))
            <div>
                <h2 class="text-xl font-semibold mb-3">Featured</h2>
                <div class="grid md:grid-cols-5 gap-4">
                    @foreach ($carouselFeaturedNotes as $featured)
                        <a href="{{ route('marketplace.show', $featured->note->id ?? null) }}"
                            class="block bg-white shadow rounded p-4 hover:shadow-md transition">
                            <div class="font-semibold">{{ Str::limit($featured->note->title ?? 'Untitled', 40) }}</div>
                            <div class="text-sm text-gray-600">
                                {{ Str::limit($featured->note->excerpt ?? ($featured->note->description ?? ''), 80) }}
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $featured->note->tags->pluck('name')->take(2)->implode(', ') }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
        @if (isset($carouselFeaturedNotes) && count($carouselFeaturedNotes))
            @include('partials.landing.carousel', ['carouselFeaturedNotes' => $carouselFeaturedNotes])
        @endif

        <!-- Dynamic Landing Sections -->
        @if (isset($sections) && count($sections))
            @include('partials.landing.sections', ['sections' => $sections])
        @endif

        <!-- Latest CMS Pages -->
        @if (isset($recentCmsPages) && count($recentCmsPages))
            @include('partials.landing.latest', ['recentCmsPages' => $recentCmsPages])
        @endif

        <!-- Explore Links -->
        <div class="grid md:grid-cols-3 gap-4">
            <a href="{{ route('marketplace.index') }}"
                class="block bg-white shadow rounded p-6 hover:shadow-md transition">
                <h3 class="font-semibold mb-1">{{ __('landing.marketplace') }}</h3>
                <p class="text-sm text-gray-600">{{ __('landing.marketplace_desc') }}</p>
            </a>
            <a href="{{ route('ecosystem.index') }}" class="block bg-white shadow rounded p-6 hover:shadow-md transition">
                <h3 class="font-semibold mb-1">{{ __('landing.ecosystem') }}</h3>
                <p class="text-sm text-gray-600">{{ __('landing.ecosystem_desc') }}</p>
            </a>
            <a href="{{ route('tuts.index') }}" class="block bg-white shadow rounded p-6 hover:shadow-md transition">
                <h3 class="font-semibold mb-1">{{ __('landing.tuts') }}</h3>
                <p class="text-sm text-gray-600">{{ __('landing.tuts_desc') }}</p>
            </a>
        </div>
    </div>
@endsection
