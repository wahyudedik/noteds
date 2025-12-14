@extends('40-shared/layouts/app')

@section('title', __('Gift Note Details'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('gifts.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Gifts') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Gift Note Details') }}</h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            @if($giftNote->status === 'sent')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    {{ __('Sent') }}
                </span>
            @elseif($giftNote->status === 'claimed')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Claimed') }}
                </span>
            @elseif($giftNote->status === 'expired')
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    {{ __('Expired') }}
                </span>
            @endif
        </div>

        <!-- Gift Info Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Gift Information') }}</h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Note') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="{{ route('marketplace.show', $giftNote->note) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $giftNote->note->title }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Price') }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ currency($giftNote->transaction->amount ?? $giftNote->note->price) }}</dd>
                </div>
                @if($giftNote->gifter_id === auth()->id())
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Recipient') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('public.profile.show', $giftNote->recipient->username) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $giftNote->recipient->name }}
                            </a>
                        </dd>
                    </div>
                @else
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('From') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('public.profile.show', $giftNote->gifter->username) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $giftNote->gifter->name }}
                            </a>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Sent Date') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $giftNote->sent_at->format('M d, Y H:i') }}</dd>
                </div>
                @if($giftNote->expires_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Expires') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $giftNote->expires_at->format('M d, Y H:i') }}
                            @if($giftNote->expires_at->isFuture())
                                <span class="text-gray-500">({{ $giftNote->expires_at->diffForHumans() }})</span>
                            @endif
                        </dd>
                    </div>
                @endif
                @if($giftNote->claimed_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('Claimed Date') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $giftNote->claimed_at->format('M d, Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
        </div>

        <!-- Message -->
        @if($giftNote->message)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Gift Message') }}</h2>
                <p class="text-gray-700 whitespace-pre-wrap">{{ $giftNote->message }}</p>
            </div>
        @endif

        <!-- Note Preview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Note Preview') }}</h2>
            <div class="space-y-3">
                <h3 class="text-xl font-semibold text-gray-900">{{ $giftNote->note->title }}</h3>
                @if($giftNote->note->summary)
                    <p class="text-gray-700">{{ $giftNote->note->summary }}</p>
                @endif
                <a href="{{ route('marketplace.show', $giftNote->note) }}"
                    class="inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                    {{ __('View Full Note') }} →
                </a>
            </div>
        </div>

        <!-- Claim Button (for recipient) -->
        @if($giftNote->recipient_id === auth()->id() && $giftNote->canBeClaimed())
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-2">{{ __('Claim Your Gift!') }}</h3>
                <p class="text-sm text-green-800 mb-4">
                    {{ __('This gift note is ready to be claimed. Once claimed, the note will be added to your library.') }}
                </p>
                <form action="{{ route('gifts.claim', $giftNote) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                        {{ __('Claim Gift Note') }}
                    </button>
                </form>
            </div>
        @elseif($giftNote->recipient_id === auth()->id() && $giftNote->isClaimed())
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-blue-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('Gift Claimed') }}</h3>
                <p class="text-sm text-blue-800">
                    {{ __('This gift has been claimed and added to your library.') }}
                </p>
                <a href="{{ route('notes.index') }}" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                    {{ __('View in My Library') }} →
                </a>
            </div>
        @endif
    </div>
</div>
@endsection


