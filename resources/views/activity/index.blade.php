@extends('layouts.app')

@section('title', __('Activity Feed'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Activity Feed') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('See what\'s happening in your network') }}</p>
            </div>
            <a href="{{ route('activity.following') }}"
                class="text-sm text-blue-600 hover:text-blue-800">
                {{ __('Following') }} →
            </a>
        </div>

        <!-- Filter -->
        <form method="GET" action="{{ route('activity.index') }}" class="mb-6">
            <select name="type" onchange="this.form.submit()"
                class="block w-full sm:w-auto rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('All Activities') }}</option>
                <option value="note_created" {{ request('type') === 'note_created' ? 'selected' : '' }}>{{ __('Notes Created') }}</option>
                <option value="note_purchased" {{ request('type') === 'note_purchased' ? 'selected' : '' }}>{{ __('Notes Purchased') }}</option>
                <option value="user_followed" {{ request('type') === 'user_followed' ? 'selected' : '' }}>{{ __('User Followed') }}</option>
            </select>
        </form>

        <!-- Activities List -->
        @if($activities->count() > 0)
            <div class="space-y-4">
                @foreach($activities as $activity)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                    @if($activity->user->avatar)
                                        <img src="{{ Storage::url($activity->user->avatar) }}" alt="{{ $activity->user->name }}" class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <span class="text-sm font-semibold text-gray-600">{{ substr($activity->user->name, 0, 1) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm text-gray-900">
                                            <a href="{{ route('public.profile.show', $activity->user->username) }}" class="font-semibold hover:text-blue-600">
                                                {{ $activity->user->name }}
                                            </a>
                                            @if($activity->type === 'note_created')
                                                {{ __('created a new note') }}
                                            @elseif($activity->type === 'note_purchased')
                                                {{ __('purchased a note') }}
                                            @elseif($activity->type === 'user_followed')
                                                {{ __('followed') }}
                                                <a href="{{ route('public.profile.show', $activity->subject->username ?? '#') }}" class="font-semibold hover:text-blue-600">
                                                    {{ $activity->subject->name ?? '' }}
                                                </a>
                                            @endif
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @if($activity->subject && $activity->type === 'note_created')
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <a href="{{ route('marketplace.show', $activity->subject) }}" class="block">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ $activity->subject->title }}</h4>
                                            @if($activity->subject->summary)
                                                <p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                    {{ Str::limit(strip_tags($activity->subject->summary), 100) }}
                                                </p>
                                            @endif
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">
                {{ $activities->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No activities yet') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('Activity feed will appear here.') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

