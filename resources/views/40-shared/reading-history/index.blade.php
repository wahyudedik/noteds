@extends('40-shared.layouts.app')

@section('title', __('messages.reading_history'))

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.reading_history') }}</h1>
                <p class="mt-2 text-gray-600">Track all the notes you've viewed and accessed</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Views</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalViews) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Unique Notes</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($uniqueNotes) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">This Month</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($viewsThisMonth) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reading History List -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Your Reading History</h2>
                </div>

                @if ($viewHistory->count() > 0)
                    <div class="divide-y divide-gray-200">
                        @foreach ($viewHistory as $history)
                            @php
                                $note = $history->note;
                            @endphp
                            <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('marketplace.show', $note) }}" class="group">
                                            <h3
                                                class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                                {{ $note->title }}
                                            </h3>
                                        </a>

                                        @if ($note->description)
                                            <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                                                {{ Str::limit($note->description, 150) }}
                                            </p>
                                        @endif

                                        <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                                {{ $note->user->name }}
                                            </div>

                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Viewed {{ $history->viewed_at->diffForHumans() }}
                                            </div>

                                            @if ($note->price > 0)
                                                <div class="flex items-center font-semibold text-blue-600">
                                                    Rp {{ number_format($note->price, 0, ',', '.') }}
                                                </div>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Free
                                                </span>
                                            @endif

                                            @if ($note->tags->count() > 0)
                                                <div class="flex items-center gap-2">
                                                    @foreach ($note->tags->take(2) as $tag)
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                            {{ $tag->name }}
                                                        </span>
                                                    @endforeach
                                                    @if ($note->tags->count() > 2)
                                                        <span
                                                            class="text-xs text-gray-500">+{{ $note->tags->count() - 2 }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        @if ($note->reviews_count > 0)
                                            <div class="mt-2 flex items-center text-sm">
                                                <div class="flex items-center text-yellow-500">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        @if ($i <= floor($note->average_rating))
                                                            <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                                            </svg>
                                                        @else
                                                            <svg class="w-4 h-4 text-gray-300 fill-current"
                                                                viewBox="0 0 20 20">
                                                                <path
                                                                    d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
                                                            </svg>
                                                        @endif
                                                    @endfor
                                                    <span class="ml-2 text-gray-600">
                                                        {{ number_format($note->average_rating, 1) }}
                                                        ({{ $note->reviews_count }})
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="ml-4 flex-shrink-0">
                                        <a href="{{ route('marketplace.show', $note) }}"
                                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                            View Note
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $viewHistory->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No Reading History Yet</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Start exploring the marketplace to build your reading history
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('marketplace.index') }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Browse Marketplace
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
