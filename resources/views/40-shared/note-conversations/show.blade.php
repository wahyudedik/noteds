@extends('40-shared/layouts/app')

@section('title', __('messages.product_conversations'))

@section('content')
<div class="py-10 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('note-conversations.index') }}"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_conversations') }}
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $conversation->note->title ?? __('messages.product_not_available') }}
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ __('messages.conversation_between') }}
                            <strong>{{ $conversation->buyer->name }}</strong> ({{ __('messages.buyer') }})
                            {{ __('messages.and') }}
                            <strong>{{ $conversation->seller->name }}</strong> ({{ __('messages.seller') }})
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm">
                        @if ($conversation->note)
                            <a href="{{ route('marketplace.show', $conversation->note) }}"
                                class="px-3 py-2 bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors text-xs font-medium">
                                {{ __('messages.view_product') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto bg-gray-50 dark:bg-gray-900" id="message-container">
                @forelse ($conversation->messages as $message)
                    <div class="flex {{ $message->sender_id === $user->id ? 'justify-end' : 'justify-start' }}" x-data="{ showTranslation{{ $message->id }}: false, translatedText{{ $message->id }}: null, translating{{ $message->id }}: false }">
                        <div class="max-w-xs sm:max-w-md">
                            <div class="rounded-2xl px-4 py-3 shadow-sm
                                {{ $message->sender_id === $user->id ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-tl-sm border border-gray-200 dark:border-gray-700' }}">
                                <p class="text-sm leading-relaxed whitespace-pre-line" x-show="!showTranslation{{ $message->id }}">
                                    {{ $message->message }}
                                </p>
                                <p class="text-sm leading-relaxed whitespace-pre-line" x-show="showTranslation{{ $message->id }}" x-cloak>
                                    <span x-text="translatedText{{ $message->id }} || '{{ __('chat.translating') }}...'"></span>
                                </p>
                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div class="text-[11px] {{ $message->sender_id === $user->id ? 'text-blue-100' : 'text-gray-400 dark:text-gray-500' }}">
                                        {{ $message->created_at->format('d M Y, H:i') }}
                                        @if ($message->sender_id === $user->id)
                                            • {{ $message->read_at ? __('messages.read') : __('messages.sent') }}
                                        @endif
                                    </div>
                                    @if ($message->sender_id !== $user->id)
                                        <div class="flex items-center gap-1">
                                            @php
                                                $userLocale = app()->getLocale();
                                                $availableLanguages = ['en', 'id', 'ar'];
                                                $messageLang = $message->original_language ?? 'en';
                                            @endphp
                                            @foreach($availableLanguages as $lang)
                                                @if($lang !== $messageLang)
                                                    <button type="button" 
                                                        @click="
                                                            if (!translatedText{{ $message->id }}) {
                                                                translating{{ $message->id }} = true;
                                                                fetch('{{ route('note-conversations.translate', $message) }}', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                    },
                                                                    body: JSON.stringify({ target_language: '{{ $lang }}' })
                                                                })
                                                                .then(response => response.json())
                                                                .then(data => {
                                                                    translatedText{{ $message->id }} = data.translated_message;
                                                                    showTranslation{{ $message->id }} = true;
                                                                    translating{{ $message->id }} = false;
                                                                })
                                                                .catch(error => {
                                                                    console.error('Translation error:', error);
                                                                    translating{{ $message->id }} = false;
                                                                });
                                                            } else {
                                                                showTranslation{{ $message->id }} = !showTranslation{{ $message->id }};
                                                            }
                                                        "
                                                        class="text-[10px] px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                                        :disabled="translating{{ $message->id }}">
                                                        {{ strtoupper($lang) }}
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.start_conversation') }}
                    </div>
                @endforelse
            </div>

            <!-- Chat Rating Section (if conversation has messages and not rated yet) -->
            @if($conversation->messages->count() > 0 && !$hasRated)
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <form action="{{ route('chat-ratings.store', $conversation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                {{ __('chat.rate_conversation') }}
                            </label>
                            <div class="flex items-center gap-2 mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" required>
                                        <svg class="w-6 h-6 text-gray-300 dark:text-gray-600 peer-checked:text-yellow-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                @endfor
                            </div>
                            <textarea name="comment" rows="2" placeholder="{{ __('chat.rating_comment_placeholder') }}"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                maxlength="500"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                            {{ __('chat.submit_rating') }}
                        </button>
                    </form>
                </div>
            @endif

            <!-- User Rating Display (if rated) -->
            @if($hasRated && $userRating)
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/20">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong>{{ __('chat.you_rated') }}:</strong>
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 inline {{ $i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        @if($userRating->comment)
                            <br><span class="text-gray-600 dark:text-gray-400 mt-1 block">{{ $userRating->comment }}</span>
                        @endif
                    </p>
                </div>
            @endif

            <div class="px-6 py-5 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                @if (session('success'))
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-3 py-2 rounded-lg mb-3 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Quick Replies -->
                @if($quickReplies->count() > 0)
                    <div class="mb-3">
                        <details class="group">
                            <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                {{ __('chat.quick_replies') }}
                            </summary>
                            <div class="mt-2 space-y-2">
                                @foreach($quickReplies as $quickReply)
                                    <button type="button" 
                                        onclick="document.getElementById('message').value = '{{ addslashes($quickReply->message) }}'"
                                        class="block w-full text-left px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <strong>{{ $quickReply->title }}:</strong>
                                        <span class="text-gray-600 dark:text-gray-400">{{ \Illuminate\Support\Str::limit($quickReply->message, 50) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </details>
                    </div>
                @endif

                <form action="{{ route('note-conversations.store', $conversation) }}" method="POST" class="space-y-3" id="message-form">
                    @csrf
                    <div>
                        <label for="message" class="sr-only">{{ __('messages.message_label') }}</label>
                        <textarea name="message" id="message" rows="3" required maxlength="2000"
                            placeholder="{{ __('messages.write_message_placeholder') }}"
                            class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('message') border-red-500 @enderror">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
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

        // Auto-scroll when new messages arrive
        const observer = new MutationObserver(function() {
            container.scrollTop = container.scrollHeight;
        });
        
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    </script>
@endpush
@endsection

