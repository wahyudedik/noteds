@extends('layouts.app')

@section('title', __('Gift Notes'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Gift Notes') }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ __('View your sent and received gift notes') }}</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button onclick="showTab('sent')" id="tab-sent"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                    {{ __('Sent') }} ({{ $sentGifts->total() }})
                </button>
                <button onclick="showTab('received')" id="tab-received"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    {{ __('Received') }} ({{ $receivedGifts->total() }})
                </button>
            </nav>
        </div>

        <!-- Sent Gifts -->
        <div id="tab-content-sent" class="tab-content">
            @if($sentGifts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($sentGifts as $gift)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $gift->note->title }}
                                    </h3>
                                    <div class="text-sm text-gray-600">
                                        <p>{{ __('To') }}: {{ $gift->recipient->name }}</p>
                                        <p>{{ __('Sent') }}: {{ $gift->sent_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @if($gift->status === 'sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('Sent') }}
                                    </span>
                                @elseif($gift->status === 'claimed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Claimed') }}
                                    </span>
                                @elseif($gift->status === 'expired')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('Expired') }}
                                    </span>
                                @endif
                            </div>
                            <a href="{{ route('gifts.show', $gift) }}"
                                class="text-sm text-blue-600 hover:text-blue-800">
                                {{ __('View Details') }} →
                            </a>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $sentGifts->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No sent gifts') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('You haven\'t sent any gift notes yet.') }}</p>
                </div>
            @endif
        </div>

        <!-- Received Gifts -->
        <div id="tab-content-received" class="tab-content hidden">
            @if($receivedGifts->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($receivedGifts as $gift)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        {{ $gift->note->title }}
                                    </h3>
                                    <div class="text-sm text-gray-600">
                                        <p>{{ __('From') }}: {{ $gift->gifter->name }}</p>
                                        <p>{{ __('Received') }}: {{ $gift->sent_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                @if($gift->canBeClaimed())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ __('Claim Now') }}
                                    </span>
                                @elseif($gift->isClaimed())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ __('Claimed') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('gifts.show', $gift) }}"
                                    class="flex-1 text-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100">
                                    {{ __('View') }}
                                </a>
                                @if($gift->canBeClaimed())
                                    <form action="{{ route('gifts.claim', $gift) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                                            {{ __('Claim') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $receivedGifts->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No received gifts') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('You haven\'t received any gift notes yet.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    
    // Add active class to selected button
    const button = document.getElementById('tab-' + tab);
    button.classList.remove('border-transparent', 'text-gray-500');
    button.classList.add('border-blue-500', 'text-blue-600');
}
</script>
@endsection

