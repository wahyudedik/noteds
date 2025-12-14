@extends('40-shared/layouts/app')

@section('title', 'Messages')

@section('content')
    <div class="py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                        <div class="mb-6">
                            <a href="{{ route('messages.compose') }}"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-center block">
                                New Message
                            </a>
                        </div>

                        <nav class="space-y-2">
                            <a href="{{ route('messages.index') }}"
                                class="block px-4 py-2 rounded-lg {{ request()->routeIs('messages.index') ? 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition">
                                Inbox
                                @if ($unreadCount > 0)
                                    <span
                                        class="inline-block ml-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 text-center leading-5">{{ $unreadCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('messages.sent') }}"
                                class="block px-4 py-2 rounded-lg {{ request()->routeIs('messages.sent') ? 'bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400 font-semibold' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }} transition">
                                Sent
                            </a>
                        </nav>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                        <div class="border-b border-gray-200 dark:border-gray-700 p-6">
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Inbox</h1>
                        </div>

                        <div class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($conversations as $conversation)
                                @php
                                    $otherUser = $conversation->sender;
                                    $lastMessage = $conversation->messages()->latest()->first();
                                @endphp
                                <a href="{{ route('messages.show', $otherUser) }}"
                                    class="block p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <div class="flex items-start gap-4">
                                        <img src="{{ $otherUser->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) }}"
                                            alt="{{ $otherUser->name }}" class="w-12 h-12 rounded-full">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $otherUser->name }}
                                            </h3>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate mt-1">
                                                {{ Illuminate\Support\Str::limit($lastMessage->message ?? 'No messages', 100) }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $conversation->latest_message_time?->diffForHumans() ?? '' }}
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="p-12 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">No conversations yet</p>
                                    <a href="{{ route('messages.compose') }}"
                                        class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                        Start New Message
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        @if ($conversations->count() > 0)
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                {{ $conversations->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

