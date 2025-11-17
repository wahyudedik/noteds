@extends('layouts.app')

@section('title', __('messages.product_conversations'))

@section('content')
<div class="py-10 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('note-conversations.index') }}"
                class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_conversations') }}
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 bg-gray-50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">
                            {{ $conversation->note->title ?? __('messages.product_not_available') }}
                        </h1>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ __('messages.conversation_between') }}
                            <strong>{{ $conversation->buyer->name }}</strong> ({{ __('messages.buyer') }})
                            {{ __('messages.and') }}
                            <strong>{{ $conversation->seller->name }}</strong> ({{ __('messages.seller') }})
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm">
                        @if ($conversation->note)
                            <a href="{{ route('marketplace.show', $conversation->note) }}"
                                class="px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition-colors text-xs font-medium">
                                {{ __('messages.view_product') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto bg-gray-50" id="message-container">
                @forelse ($conversation->messages as $message)
                    <div class="flex {{ $message->sender_id === $user->id ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs sm:max-w-md rounded-2xl px-4 py-3 shadow-sm
                            {{ $message->sender_id === $user->id ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-white text-gray-900 rounded-tl-sm border border-gray-200' }}">
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ $message->message }}</p>
                            <div class="mt-2 text-[11px] {{ $message->sender_id === $user->id ? 'text-blue-100' : 'text-gray-400' }}">
                                {{ $message->created_at->format('d M Y, H:i') }}
                                @if ($message->sender_id === $user->id)
                                    • {{ $message->read_at ? __('messages.read') : __('messages.sent') }}
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-500">
                        {{ __('messages.start_conversation') }}
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-5 border-t border-gray-200 bg-white">
                @if (session('success'))
                    <div class="bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg mb-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('note-conversations.store', $conversation) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label for="message" class="sr-only">{{ __('messages.message_label') }}</label>
                        <textarea name="message" id="message" rows="3" required maxlength="2000"
                            placeholder="{{ __('messages.write_message_placeholder') }}"
                            class="block w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            {{ __('messages.send_message') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const container = document.getElementById('message-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    </script>
@endpush
@endsection

