@extends('layouts.app')

@section('title', 'Sent Messages')

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sent Messages</h1>
            <a href="{{ route('studio.messages.compose') }}" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                New Message
            </a>
        </div>

        @if($conversations->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">To</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Last Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Sent On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($conversations as $conversation)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $conversation['recipient']->profile_photo_url }}" alt="{{ $conversation['recipient']->name }}" class="w-10 h-10 rounded-full">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $conversation['recipient']->name }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">@{{ $conversation['recipient']->username }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                            <p class="truncate max-w-xs">{{ Str::limit($conversation['lastMessage']->message, 50) }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                            {{ $conversation['lastMessage']->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('studio.messages.show', $conversation['recipient']) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($conversations instanceof Illuminate\Pagination\Paginator)
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                {{ $conversations->links() }}
            </div>
            @endif
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
            <p class="text-gray-600 dark:text-gray-400 mb-4">You haven't sent any messages yet.</p>
            <a href="{{ route('studio.messages.compose') }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                Send your first message →
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
