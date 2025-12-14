@extends('40-shared.layouts.app')

@section('title', __('Activity'))

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Activity') }}</h1>
            <div class="text-sm text-gray-500">
                {{ now()->format('M d, Y') }}
            </div>
        </div>

        @php
            $activities = $activities ?? [];
            $types = $types ?? [];
            $likes = $likes ?? collect();
            $comments = $comments ?? collect();
            $users = $users ?? collect();
        @endphp

        @if(empty($activities))
            <div class="rounded-md bg-blue-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M18 10c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm-8-5a.75.75 0 01.75.75v3.5h3.5a.75.75 0 010 1.5h-4.25A.75.75 0 019 9.5v-4.25A.75.75 0 0110 5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">{{ __('No activity yet') }}</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p>{{ __('When there is activity, it will appear here.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="space-y-4">
                @foreach($activities as $activity)
                    @php
                        $user = $users->firstWhere('id', $activity->user_id ?? null);
                        $note = isset($activity->note_id) ? ($notes->firstWhere('id', $activity->note_id) ?? null) : null;
                        $liked = $likes->where('activity_id', $activity->id ?? null)->isNotEmpty();
                        $activityComments = $comments->where('activity_id', $activity->id ?? null);
                    @endphp
                    <div class="bg-white shadow rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-gray-200"></div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $user->name ?? __('Unknown User') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $activity->created_at?->diffForHumans() ?? '' }}
                                    </div>
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">
                                {{ $activity->type ?? 'event' }}
                            </span>
                        </div>
                        <div class="mt-3 text-sm text-gray-700">
                            {{ $activity->description ?? '' }}
                        </div>
                        @if(!empty($note))
                            <div class="mt-3">
                                <a href="{{ route_exists('notes.show') ? route('notes.show', $note->id) : '#' }}" class="text-indigo-600 hover:text-indigo-800 text-sm">
                                    {{ __('View note') }}
                                </a>
                            </div>
                        @endif
                        <div class="mt-4 flex items-center space-x-4 text-xs text-gray-500">
                            <div class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 9l-1-1m0 0a3 3 0 10-4 4m4-4l6 6m-2 2a9 9 0 11-12-12" />
                                </svg>
                                <span>{{ $liked ? __('Liked') : __('No likes') }}</span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2H7l-4 4V10a2 2 0 012-2h2" />
                                </svg>
                                <span>{{ $activityComments->count() }} {{ __('comments') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @isset($pagination)
            <div class="mt-6">
                {{ $pagination }}
            </div>
        @endisset
    </div>
@endsection
