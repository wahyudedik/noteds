@extends('layouts.app')

@section('title', __('featured.request_title'))

@section('content')
@php
    $currencyService = app(\App\Services\CurrencyService::class);
    $displayCurrency = $currencyService->getUserCurrency(auth()->user());
    $currencyInfo = \App\Helpers\CurrencyHelper::getCurrencyInfo($displayCurrency) ?? [
        'symbol' => '',
        'decimal_places' => 0,
        'locale' => app()->getLocale() === 'id' ? 'id_ID' : 'en_US',
    ];
    $pricingFormatted = [];
    foreach ($pricing as $locationKey => $durations) {
        foreach ($durations as $durationKey => $price) {
            $pricingFormatted[$locationKey][$durationKey] = currency($price);
        }
    }
@endphp
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('featured-notes.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('featured.request_title') }}</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">{{ __('featured.request_subtitle') }}</p>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                @if(session('insufficient_balance'))
                    <a href="{{ route('wallet.index') }}" class="mt-2 inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700">
                        {{ __('messages.top_up') }} →
                    </a>
                @endif
            </div>
        @endif

        <form action="{{ route('featured-notes.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Select Note -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <label for="note_id" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('featured.select_note_label') }} <span class="text-red-500">*</span>
                </label>
                @if($notes->count() > 0)
                    <select name="note_id" id="note_id" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('featured.select_note_placeholder') }}</option>
                        @foreach($notes as $note)
                            <option value="{{ $note->id }}" {{ old('note_id', $selectedNote?->id) == $note->id ? 'selected' : '' }}>
                                {{ $note->title }} ({{ $note->is_public ? __('messages.public') : __('messages.private') }})
                            </option>
                        @endforeach
                    </select>
                    @error('note_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-sm text-yellow-800 mb-3">{{ __('featured.no_notes_available') }}</p>
                        <a href="{{ route('notes.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            {{ __('featured.create_note_cta') }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Select Location -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('featured.location_label') }} <span class="text-red-500">*</span>
                </label>
                <select name="location" id="location" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('featured.location_placeholder') }}</option>
                    <option value="marketplace_grid" {{ old('location') == 'marketplace_grid' ? 'selected' : '' }}>{{ __('featured.locations.marketplace_grid') }} ({{ currency($pricing['marketplace_grid'][7] ?? 50000) }}/{{ __('messages.day_count', ['count' => 7]) }})</option>
                    <option value="marketplace_banner" {{ old('location') == 'marketplace_banner' ? 'selected' : '' }}>{{ __('featured.locations.marketplace_banner') }} ({{ currency($pricing['marketplace_banner'][7] ?? 75000) }}/{{ __('messages.day_count', ['count' => 7]) }})</option>
                    <option value="landing_hero" {{ old('location') == 'landing_hero' ? 'selected' : '' }}>{{ __('featured.locations.landing_hero') }} ({{ currency($pricing['landing_hero'][7] ?? 150000) }}/{{ __('messages.day_count', ['count' => 7]) }})</option>
                    <option value="landing_carousel" {{ old('location') == 'landing_carousel' ? 'selected' : '' }}>{{ __('featured.locations.landing_carousel') }} ({{ currency($pricing['landing_carousel'][7] ?? 100000) }}/{{ __('messages.day_count', ['count' => 7]) }})</option>
                </select>
                @error('location')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-2 text-xs text-gray-500">{{ __('featured.location_required') }}</p>
            </div>

            <!-- Select Duration -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('featured.duration_label') }} <span class="text-red-500">*</span>
                </label>
                <select name="duration_days" id="duration_days" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <option value="7" {{ old('duration_days') == 7 ? 'selected' : '' }}>{{ __('messages.day_count', ['count' => 7]) }}</option>
                    <option value="14" {{ old('duration_days') == 14 ? 'selected' : '' }}>{{ __('messages.day_count', ['count' => 14]) }}</option>
                    <option value="30" {{ old('duration_days') == 30 ? 'selected' : '' }}>{{ __('messages.day_count', ['count' => 30]) }}</option>
                </select>
                @error('duration_days')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Wallet Balance & Price Preview -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-700">{{ __('featured.wallet_balance') }}</p>
                        <p class="text-2xl font-bold {{ $wallet->balance >= 50000 ? 'text-green-600' : 'text-red-600' }}">{{ currency($wallet->balance) }}</p>
                    </div>
                    <div class="text-right" id="price-preview">
                        <p class="text-sm text-gray-600">{{ __('featured.preview_prompt') }}</p>
                    </div>
                </div>
            </div>

            <!-- Price Calculation Script -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const locationSelect = document.getElementById('location');
                    const durationSelect = document.getElementById('duration_days');
                    const pricePreview = document.getElementById('price-preview');
                    
                    const pricing = @json($pricing);
                    const pricingFormatted = @json($pricingFormatted);
                    const walletBalance = {{ $wallet->balance }};
                    const previewPrompt = @json(__('featured.preview_prompt'));
                    const totalPriceLabel = @json(__('featured.total_price'));
                    const insufficientText = @json(__('featured.insufficient_balance'));
                    const sufficientText = @json(__('featured.sufficient_balance'));
                    
                    function updatePrice() {
                        const location = locationSelect.value;
                        const duration = parseInt(durationSelect.value);
                        
                        if (location && duration) {
                            const price = pricing[location] && pricing[location][duration] ? pricing[location][duration] : 0;
                            const sufficient = walletBalance >= price;
                            
                            const formattedPrice = pricingFormatted[location] && pricingFormatted[location][duration]
                                ? pricingFormatted[location][duration]
                                : '{{ currency(0) }}';

                            pricePreview.innerHTML = `
                                <div>
                                    <p class="text-sm font-medium text-gray-700">${totalPriceLabel}</p>
                                    <p class="text-xl font-bold ${sufficient ? 'text-green-600' : 'text-red-600'}">${formattedPrice}</p>
                                    ${!sufficient ? `<p class="text-xs text-red-600 mt-1">${insufficientText}</p>` : `<p class="text-xs text-green-600 mt-1">${sufficientText}</p>`}
                                </div>
                            `;
                        } else {
                            pricePreview.innerHTML = `<p class="text-sm text-gray-600">${previewPrompt}</p>`;
                        }
                    }
                    
                    locationSelect.addEventListener('change', updatePrice);
                    durationSelect.addEventListener('change', updatePrice);
                    
                    // Initial update
                    updatePrice();
                });
            </script>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('featured-notes.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

