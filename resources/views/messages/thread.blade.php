@extends('layouts.app')

@section('title', "Chat with {$user->name}")

@section('content')
    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex flex-col h-[600px]">
                <!-- Header -->
                <div class="border-b border-gray-200 dark:border-gray-700 p-6 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                            alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $user->name }}</h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400">@{{ $user - > username }}</p>
                        </div>
                    </div>
                    <a href="{{ route('messages.index') }}"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messages-container">
                    @forelse($messages as $message)
                        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-xs">
                                <div
                                    class="rounded-lg px-4 py-2 {{ $message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' }}">
                                    <p class="text-sm">{{ $message->message }}</p>
                                </div>
                                <p
                                    class="text-xs {{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }} text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                    @if ($message->sender_id === auth()->id())
                                        • {{ $message->isRead() ? 'Read' : 'Sent' }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                            <p>No messages yet. Start the conversation!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Input -->
                <div class="border-t border-gray-200 dark:border-gray-700 p-6">
                    <form action="{{ route('messages.store') }}" method="POST" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="recipient_id" value="{{ $user->id }}">
                        <input type="text" name="message" placeholder="Type a message..." maxlength="2000" required
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Send
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    </script>
@endsection
