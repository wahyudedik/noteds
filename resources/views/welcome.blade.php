@extends('layouts.app')

@section('title', config('app.name', 'Noteds'))

@section('content')
    @php
        $landingHero = __('landing.hero');
        $landingFeatures = collect(__('landing.features'));
        $landingCta = __('landing.cta');
        $featuresIcons = [
            'document-text' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m.75 6H6.75A1.75 1.75 0 0 1 5 16.25V7.75A1.75 1.75 0 0 1 6.75 6h5.086a1.75 1.75 0 0 1 1.237.513l3.914 3.914c.328.328.513.775.513 1.237v4.586A1.75 1.75 0 0 1 16.5 18Z"/></svg>',
            'sparkles' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 12h.01M12 6h.01m0 12h.01M18 12h.01M9.172 9.172a4 4 0 0 1 5.656 5.656m-8.486 0a4 4 0 0 1 5.657-5.657"/></svg>',
            'users' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11c1.657 0 3-.895 3-2s-1.343-2-3-2m-8 4c1.657 0 3-.895 3-2s-1.343-2-3-2m0 8c3.314 0 6 1.791 6 4H4c0-2.209 2.686-4 6-4Zm8 0c.803 0 1.555.094 2.236.26A3.5 3.5 0 0 0 18 12c-1.306 0-2.418.835-2.829 2"/></svg>',
        ];
    @endphp

    <div class="min-h-screen bg-white">
        <!-- Hero -->
        <section class="relative overflow-hidden border-b border-gray-100">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-blue-50"></div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div>
                        <span
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                            <span class="inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                            {{ $landingHero['badge'] ?? '' }}
                        </span>
                        <h1
                            class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-semibold tracking-tight text-gray-900 leading-tight">
                            {{ $landingHero['title'] ?? config('app.name', 'Noteds') }}
                        </h1>
                        <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-xl">
                            {{ $landingHero['subtitle'] ?? '' }}
                        </p>
                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <a href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-900 text-white font-medium hover:bg-gray-800 transition">
                                {{ $landingHero['primary_cta'] ?? __('messages.explore_marketplace') }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                                </svg>
                            </a>
                            <a href="{{ auth()->check() ? route('notes.create') : route('register') }}"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl border border-gray-200 text-gray-700 font-medium hover:border-gray-300 transition">
                                {{ $landingHero['secondary_cta'] ?? __('messages.collection_add_purchased_button') }}
                            </a>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="absolute -top-10 -right-10 w-40 h-40 bg-blue-100 rounded-full blur-3xl"></div>
                        <div
                            class="relative rounded-2xl border border-gray-100 shadow-lg shadow-blue-50/40 bg-white/80 backdrop-blur p-6">
                            @if (isset($featuredHero) && $featuredHero)
                                @php($note = $featuredHero->note)
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            {{ __('messages.featured_note') }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $note->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    <a href="{{ route('marketplace.show', $note) }}"
                                        class="block group featured-click-tracking"
                                        data-featured-id="{{ $featuredHero->id }}">
                                        <h3
                                            class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition">
                                            {{ $note->title }}
                                        </h3>
                                        <p class="mt-2 text-sm text-gray-600 line-clamp-3">
                                            {{ Str::limit($note->summary ?? strip_tags($note->content), 140) }}
                                        </p>
                                    </a>
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-semibold">
                                                {{ substr($note->user->name, 0, 1) }}
                                            </span>
                                            <a href="{{ route('public.profile.show', $note->user->username) }}"
                                                class="hover:text-blue-600 transition">
                                                {{ $note->user->name }}
                                            </a>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-400">{{ __('messages.price_label') }}</p>
                                            <p class="text-sm font-semibold text-gray-900">
                                                {{ $note->price > 0 ? currency($note->price) : __('messages.free') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-4">
                                    <span
                                        class="inline-flex items-center gap-2 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700">
                                        <span class="inline-block h-2 w-2 rounded-full bg-blue-500"></span>
                                        {{ __('messages.discover_premium_notes') }}
                                    </span>
                                    <p class="text-sm text-gray-600 leading-relaxed">
                                        {{ __('landing.hero.subtitle') }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-16 lg:py-20 border-b border-gray-100 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid gap-8 md:grid-cols-3">
                    @foreach ($landingFeatures as $feature)
                        <div class="p-6 rounded-2xl border border-gray-100 shadow-sm shadow-blue-50/40 bg-slate-50/40">
                            <div
                                class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-100 text-blue-600 mb-5">
                                {!! $featuresIcons[$feature['icon']] ?? $featuresIcons['sparkles'] !!}
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $feature['title'] }}</h3>
                            <p class="mt-3 text-sm text-gray-600 leading-relaxed">
                                {{ $feature['description'] }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Featured Carousel -->
        @if (isset($featuredCarousel) && $featuredCarousel->count() > 0)
            <section class="py-16 lg:py-20 bg-gradient-to-b from-white to-slate-50 border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
                        <div>
                            <h2 class="text-3xl font-semibold text-gray-900">{{ __('messages.featured_notes') }}</h2>
                            <p class="mt-2 text-sm text-gray-600">{{ __('messages.discover_premium_notes') }}</p>
                        </div>
                        <a href="{{ route('marketplace.index') }}"
                            class="inline-flex items-center gap-2 text-sm font-medium text-blue-600 hover:text-blue-700">
                            {{ __('messages.explore_marketplace') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach ($featuredCarousel as $featured)
                            @php($note = $featured->note)
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="group block rounded-2xl border border-gray-100 bg-white p-6 hover:border-blue-200 hover:shadow-lg transition featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">
                                <div class="flex items-center justify-between mb-5">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                        {{ __('messages.featured_badge') }}
                                    </span>
                                    <span class="text-xs text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                <h3
                                    class="text-lg font-semibold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition">
                                    {{ $note->title }}
                                </h3>
                                <p class="text-sm text-gray-600 line-clamp-3">
                                    {{ Str::limit(strip_tags($note->summary ?? $note->content), 120) }}
                                </p>
                                <div class="mt-6 flex items-center justify-between text-sm text-gray-500">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-600 font-semibold">
                                            {{ substr($note->user->name, 0, 1) }}
                                        </span>
                                        <span>{{ $note->user->name }}</span>
                                    </div>
                                    <span class="font-semibold text-gray-900">
                                        {{ $note->price > 0 ? currency($note->price) : __('messages.free') }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Dynamic Landing Page Sections -->
        @if (isset($groupedSections) && $groupedSections->count() > 0)
            @foreach ($groupedSections as $sectionType => $sections)
                @foreach ($sections as $section)
                    @include('components.landing-section', ['section' => $section])
                @endforeach
            @endforeach
        @endif

        <!-- Call to Action -->
        <section class="py-16 lg:py-24 bg-gray-900">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white">
                <h2 class="text-3xl sm:text-4xl font-semibold leading-tight">
                    {{ $landingCta['title'] }}
                </h2>
                <p class="mt-4 text-base text-gray-300">
                    {{ $landingCta['subtitle'] }}
                </p>
                <a href="{{ route('register') }}"
                    class="mt-8 inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white text-gray-900 font-medium hover:bg-gray-100 transition">
                    {{ $landingCta['button'] }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                    </svg>
                </a>
            </div>
        </section>
    </div>

    @push('scripts')
        <script>
            // Track clicks on featured notes
            document.addEventListener('DOMContentLoaded', function() {
                const featuredLinks = document.querySelectorAll('.featured-click-tracking');
                featuredLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const featuredId = this.getAttribute('data-featured-id');
                        if (featuredId) {
                            // Track click via AJAX
                            fetch(`/api/featured-notes/${featuredId}/click`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).catch(err => console.error('Failed to track click:', err));
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
