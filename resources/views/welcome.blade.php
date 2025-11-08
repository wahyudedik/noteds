@extends('layouts.app')

@section('title', config('app.name', 'Noteds'))

@section('content')
    <div class="min-h-screen bg-white">
        <!-- Featured Hero Note -->
        @if (isset($featuredHero) && $featuredHero)
            <section
                class="relative overflow-hidden bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 py-20 lg:py-28">
                <div class="absolute inset-0 bg-grid-pattern opacity-[0.02]"></div>
                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="max-w-4xl mx-auto">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100/80 backdrop-blur-sm rounded-full text-xs font-medium text-blue-700 mb-6">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                </path>
                            </svg>
                            <span>{{ __('messages.featured_note') }}</span>
                        </div>

                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                            <a href="{{ route('marketplace.show', $featuredHero->note) }}"
                                class="hover:text-blue-600 transition-colors duration-300 featured-click-tracking block"
                                data-featured-id="{{ $featuredHero->id }}">
                                {{ $featuredHero->note->title }}
                            </a>
                        </h1>

                        <p class="text-lg sm:text-xl text-gray-600 mb-8 leading-relaxed max-w-2xl">
                            {{ Str::limit($featuredHero->note->summary ?? strip_tags($featuredHero->note->content), 180) }}
                        </p>

                        <div class="flex flex-wrap items-center gap-6 mb-8">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ substr($featuredHero->note->user->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">{{ __('messages.created_by') }}</p>
                                    <a href="{{ route('public.profile.show', $featuredHero->note->user->username) }}"
                                        class="text-sm font-medium text-gray-900 hover:text-blue-600 transition-colors">
                                        {{ $featuredHero->note->user->name }}
                                    </a>
                                </div>
                            </div>

                            <div class="h-8 w-px bg-gray-300"></div>

                            <div>
                                <p class="text-sm text-gray-500">{{ __('messages.price_label') }}</p>
                                @if ($featuredHero->note->price > 0)
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ currency($featuredHero->note->price) }}</p>
                                @else
                                    <p class="text-2xl font-bold text-green-600">{{ __('messages.free') }}</p>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('marketplace.show', $featuredHero->note) }}"
                            class="inline-flex items-center gap-2 px-8 py-4 bg-gray-900 text-white font-medium rounded-lg hover:bg-gray-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 featured-click-tracking"
                            data-featured-id="{{ $featuredHero->id }}">
                            <span>{{ __('messages.view_note') }}</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </section>
        @endif

        <!-- Featured Carousel -->
        @if (isset($featuredCarousel) && $featuredCarousel->count() > 0)
            <section class="py-16 lg:py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="text-center mb-12">
                        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">{{ __('messages.featured_notes') }}
                        </h2>
                        <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ __('messages.discover_premium_notes') }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 lg:gap-8">
                        @foreach ($featuredCarousel as $featured)
                            @php($note = $featured->note)
                            <a href="{{ route('marketplace.show', $note) }}" class="group block featured-click-tracking"
                                data-featured-id="{{ $featured->id }}">
                                <div
                                    class="relative bg-white rounded-2xl border border-gray-200 hover:border-blue-300 transition-all duration-300 overflow-hidden h-full flex flex-col shadow-sm hover:shadow-xl">
                                    <!-- Featured Badge -->
                                    <div class="absolute top-4 right-4 z-10">
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-yellow-400 to-amber-400 rounded-full text-xs font-semibold text-gray-900 shadow-sm">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z">
                                                </path>
                                            </svg>
                                            <span class="hidden sm:inline">{{ __('messages.featured_badge') }}</span>
                                        </span>
                                    </div>

                                    <div class="p-6 flex-1 flex flex-col">
                                        <h3
                                            class="text-lg font-bold text-gray-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200 leading-snug">
                                            {{ $note->title }}
                                        </h3>

                                        <p class="text-sm text-gray-600 line-clamp-3 mb-6 flex-1 leading-relaxed">
                                            {{ Str::limit(strip_tags($note->content), 100) }}
                                        </p>

                                        <div class="pt-6 border-t border-gray-100 mt-auto">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    @if ($note->price > 0)
                                                        <p class="text-lg font-bold text-gray-900">
                                                            {{ currency($note->price) }}</p>
                                                    @else
                                                        <p class="text-lg font-bold text-green-600">{{ __('messages.free') }}</p>
                                                    @endif
                                                </div>
                                                <div
                                                    class="flex items-center gap-2 text-gray-500 group-hover:text-blue-600 transition-colors">
                                                    <span class="text-sm font-medium">{{ $note->user->name }}</span>
                                                    <svg class="w-4 h-4 opacity-0 group-hover:opacity-100 transform group-hover:translate-x-1 transition-all duration-200"
                                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
    </div>

    @push('styles')
        <style>
            .bg-grid-pattern {
                background-image:
                    linear-gradient(to right, rgba(0, 0, 0, 0.03) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
                background-size: 20px 20px;
            }
        </style>
    @endpush

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
