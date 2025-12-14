@extends('40-shared/layouts/app')

@section('title', $series->title)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('series.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_series') }}
            </a>
        </div>

        <!-- Series Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $series->title }}</h1>
                    @if($series->description)
                        <p class="text-gray-700 mb-4">{{ $series->description }}</p>
                    @endif
                </div>
                @if($series->is_active)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ __('messages.active') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                        {{ __('messages.inactive') }}
                    </span>
                @endif
            </div>
            <div class="text-sm text-gray-600">
                {{ __('messages.created_by') }} <a href="{{ route('public.profile.show', $series->user->username) }}" class="text-blue-600 hover:text-blue-800">
                    {{ $series->user->name }}
                </a>
            </div>
        </div>

        <!-- Notes in Series -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                {{ __('messages.notes_in_series') }} ({{ $series->notes->count() }})
            </h2>
            @if($series->notes->count() > 0)
                <div class="space-y-4">
                    @foreach($series->notes->sortBy('series_order') as $note)
                        <div class="flex items-start justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-xs font-medium text-gray-500">#{{ $note->series_order }}</span>
                                    <a href="{{ route('marketplace.show', $note) }}"
                                        class="text-lg font-medium text-gray-900 hover:text-blue-600">
                                        {{ $note->title }}
                                    </a>
                                </div>
                                @if($note->summary)
                                    <p class="text-sm text-gray-600 line-clamp-2">
                                        {{ Str::limit(strip_tags($note->summary), 150) }}
                                    </p>
                                @endif
                            </div>
                            <a href="{{ route('marketplace.show', $note) }}"
                                class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                                {{ __('messages.view') }}
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-gray-500 py-8">{{ __('messages.no_notes_in_series') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection


