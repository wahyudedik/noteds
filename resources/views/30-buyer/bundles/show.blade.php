@extends('40-shared/layouts/app')

@section('title', $bundle->title)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('bundles.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Bundles') }}
            </a>
        </div>

        <!-- Bundle Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $bundle->title }}</h1>
            
            @if($bundle->description)
                <p class="text-gray-700 mb-6">{{ $bundle->description }}</p>
            @endif

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1">{{ __('Created by') }}</div>
                    <div class="flex items-center">
                        <a href="{{ route('public.profile.show', $bundle->user->username) }}"
                            class="font-medium text-gray-900 hover:text-blue-600">
                            {{ $bundle->user->name }}
                        </a>
                    </div>
                </div>
                <div class="text-right">
                    @if($bundle->discount_percentage > 0)
                        <div class="text-sm text-gray-500 line-through mb-1">
                            {{ currency($bundle->total_original_price) }}
                        </div>
                        <div class="text-xs text-red-600 font-medium mb-1">
                            {{ number_format($bundle->discount_percentage, 0) }}% {{ __('off') }}
                        </div>
                    @endif
                    <div class="text-3xl font-bold text-green-600">
                        {{ currency($bundle->price) }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ __('for') }} {{ $bundle->items->count() }} {{ __('notes') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Notes in Bundle -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                {{ __('Notes in this Bundle') }} ({{ $bundle->items->count() }})
            </h2>
            <div class="space-y-4">
                @foreach($bundle->items as $item)
                    <div class="flex items-start justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex-1">
                            <a href="{{ route('marketplace.show', $item->note) }}"
                                class="text-lg font-medium text-gray-900 hover:text-blue-600">
                                {{ $item->note->title }}
                            </a>
                            @if($item->note->summary)
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    {{ Str::limit(strip_tags($item->note->summary), 150) }}
                                </p>
                            @endif
                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                <span>{{ __('Price') }}: {{ currency($item->note->price) }}</span>
                                <span>{{ __('By') }}: {{ $item->note->user->name }}</span>
                            </div>
                        </div>
                        <a href="{{ route('marketplace.show', $item->note) }}"
                            class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                            {{ __('View') }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Purchase Button -->
        @auth
            @if(auth()->id() !== $bundle->user_id)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <form action="{{ route('bundles.purchase', $bundle) }}" method="POST">
                        @csrf
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-sm text-gray-600">{{ __('Total Price') }}</div>
                                <div class="text-2xl font-bold text-gray-900">{{ currency($bundle->price) }}</div>
                            </div>
                            <button type="submit"
                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                                {{ __('Purchase Bundle') }}
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">
                            {{ __('All notes will be added to your library after purchase.') }}
                        </p>
                    </form>
                </div>
            @endif
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-sm text-blue-800 mb-4">{{ __('Please log in to purchase this bundle.') }}</p>
                <a href="{{ route('login') }}"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('Log In') }}
                </a>
            </div>
        @endauth
    </div>
</div>
@endsection


