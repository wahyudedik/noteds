@extends('40-shared/layouts/app')

@section('title', __('Recently Viewed Notes'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Recently Viewed Notes') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('Notes you have recently viewed') }}</p>
        </div>

        <!-- Notes Grid -->
        @if($viewedNotes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($viewedNotes as $viewHistory)
                    @php
                        $note = $viewHistory->note;
                    @endphp
                    @if($note)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <a href="{{ route('marketplace.show', $note) }}" class="block">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        {{ $note->title }}
                                    </h3>
                                    @if($note->summary)
                                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                            {{ Str::limit(strip_tags($note->summary), 100) }}
                                        </p>
                                    @endif
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-green-600">
                                            {{ currency($note->price) }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ __('By') }} {{ $note->user->name }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ __('Viewed') }} {{ $viewHistory->viewed_at->diffForHumans() }}
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $viewedNotes->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No viewed notes yet') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('Your recently viewed notes will appear here.') }}</p>
                <a href="{{ route('marketplace.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                    {{ __('Browse Marketplace') }} →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection


