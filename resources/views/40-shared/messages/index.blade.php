@extends('40-shared.layouts.app')

@section('title', __('messages.messages'))

@section('content')
    <div class="max-w-5xl mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">{{ __('messages.messages') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('messages.coming_soon') }}</p>

        <div class="bg-white shadow rounded p-4">
            <p class="text-sm text-gray-500">This page is a placeholder. Messaging threads, unread counts, and composer will
                appear here.</p>
        </div>
    </div>
    @endsection@extends('40-shared/layouts/app')

@section('title', __('Messages'))

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Messages') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('Your conversations') }}</p>
            </div>

            <!-- Conversations List -->
            @if ($conversations->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach ($conversations as $message)
                            @php
                                $otherUser =
                                    $message->sender_id === auth()->id() ? $message->recipient : $message->sender;
                            @endphp
                            <a href="{{ route('messages.conversation', $otherUser) }}"
                                class="block p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                                            @if ($otherUser->avatar)
                                                <img src="{{ Storage::url($otherUser->avatar) }}"
                                                    alt="{{ $otherUser->name }}"
                                                    class="w-12 h-12 rounded-full object-cover">
                                            @else
                                                <span
                                                    class="text-lg font-semibold text-gray-600">{{ substr($otherUser->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-1">
                                            <h3 class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $otherUser->name }}
                                            </h3>
                                            <span class="text-xs text-gray-500 flex-shrink-0 ml-2">
                                                {{ $message->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 truncate">
                                            {{ Str::limit($message->message, 100) }}
                                        </p>
                                    </div>
                                    @if ($message->recipient_id === auth()->id() && !$message->is_read)
                                        <div class="flex-shrink-0">
                                            <span class="w-2 h-2 bg-blue-600 rounded-full block"></span>
                                        </div>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No messages yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Start a conversation with another user.') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection
