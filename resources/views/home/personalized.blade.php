@extends('40-shared/layouts/app')

@section('title', __('messages.home') ?? 'Home')

@section('content')
    <div class="py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            @if ($featuredHero)
                <div
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-2xl shadow-lg p-8 flex flex-col md:flex-row items-center gap-6">
                    <div class="flex-1">
                        <p class="text-sm uppercase tracking-wide font-semibold opacity-80">Featured</p>
                        <h1 class="text-2xl md:text-3xl font-bold mt-1">{{ $featuredHero->note?->title ?? 'Featured note' }}
                        </h1>
                        <p class="mt-2 text-white/90 text-sm">{{ Str::limit($featuredHero->note?->description, 140) }}</p>
                        <div class="mt-4 flex items-center gap-3 text-sm">
                            <span
                                class="px-3 py-1 rounded-full bg-white/15 border border-white/20">{{ $featuredHero->note?->user?->name ?? 'Creator' }}</span>
                            @if ($featuredHero->note?->avg_rating)
                                <span class="px-3 py-1 rounded-full bg-white/15 border border-white/20">⭐
                                    {{ number_format($featuredHero->note->avg_rating, 1) }}
                                    ({{ $featuredHero->note->reviews_count ?? 0 }})</span>
                            @endif
                        </div>
                        <div class="mt-6 flex gap-3">
                            @if ($featuredHero->note)
                                <a href="{{ route('notes.show', $featuredHero->note) }}"
                                    class="inline-flex items-center px-4 py-2 rounded-lg bg-white text-blue-700 font-semibold hover:bg-blue-50 shadow">View
                                    note</a>
                            @endif
                            <a href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center px-4 py-2 rounded-lg border border-white/40 text-white font-semibold hover:bg-white/10">Browse
                                more</a>
                        </div>
                    </div>
                    @if ($featuredHero->note?->cover_image)
                        <div class="w-40 h-40 rounded-xl overflow-hidden shadow-lg">
                            <img src="{{ asset('storage/' . ltrim($featuredHero->note->cover_image, '/')) }}"
                                alt="{{ $featuredHero->note->title }}" class="w-full h-full object-cover">
                        </div>
                    @endif
                </div>
            @endif

            @if (($featuredCarousel ?? collect())->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Featured picks</h2>
                        <a href="{{ route('marketplace.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">See all</a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($featuredCarousel as $featured)
                            <a href="{{ route('notes.show', $featured->note) }}"
                                class="block rounded-xl border border-slate-100 hover:border-blue-200 hover:shadow transition p-4">
                                <div class="flex items-start gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-slate-100 overflow-hidden flex-shrink-0">
                                        @if ($featured->note?->cover_image)
                                            <img src="{{ asset('storage/' . ltrim($featured->note->cover_image, '/')) }}"
                                                alt="{{ $featured->note->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                                Note</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">
                                            {{ $featured->note?->title ?? 'Untitled' }}</p>
                                        <p class="text-xs text-slate-500 truncate">
                                            {{ $featured->note?->user?->name ?? 'Creator' }}</p>
                                        @if ($featured->note?->avg_rating)
                                            <p class="text-xs text-amber-600 mt-1">⭐
                                                {{ number_format($featured->note->avg_rating, 1) }}
                                                ({{ $featured->note->reviews_count ?? 0 }})
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-slate-900">Recommended for you</h2>
                        <a href="{{ route('marketplace.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">More</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recommendations ?? [] as $note)
                            <a href="{{ route('notes.show', $note) }}"
                                class="block p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-blue-100 transition">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-md bg-slate-100 overflow-hidden flex-shrink-0">
                                        @if ($note->cover_image)
                                            <img src="{{ asset('storage/' . ltrim($note->cover_image, '/')) }}"
                                                alt="{{ $note->title }}" class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                                Note</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">{{ $note->title }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $note->user?->name ?? 'Creator' }}
                                        </p>
                                        @if ($note->avg_rating)
                                            <p class="text-xs text-amber-600 mt-1">⭐
                                                {{ number_format($note->avg_rating, 1) }}
                                                ({{ $note->reviews_count ?? 0 }})
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">No recommendations yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-lg font-semibold text-slate-900">Recently viewed</h2>
                        <a href="{{ route('notes.index') }}"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium">Browse</a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recentlyViewed ?? [] as $note)
                            <a href="{{ route('notes.show', $note) }}"
                                class="block p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-blue-100 transition">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-md bg-slate-100 overflow-hidden flex-shrink-0">
                                        @if ($note?->cover_image)
                                            <img src="{{ asset('storage/' . ltrim($note->cover_image, '/')) }}"
                                                alt="{{ $note?->title ?? 'Note' }}" class="w-full h-full object-cover">
                                        @else
                                            <div
                                                class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                                Note</div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate">
                                            {{ $note?->title ?? 'Untitled' }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $note?->user?->name ?? 'Creator' }}
                                        </p>
                                        @if ($note?->avg_rating)
                                            <p class="text-xs text-amber-600 mt-1">⭐
                                                {{ number_format($note->avg_rating, 1) }}
                                                ({{ $note?->reviews_count ?? 0 }})
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-slate-500">No history yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
