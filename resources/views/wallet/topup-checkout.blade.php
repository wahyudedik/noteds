@extends('layouts.app')

@section('title', __('messages.topup_checkout'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.topup_checkout') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.complete_wallet_topup_payment') }}</p>
        </div>

        <!-- Payment Details Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.payment_summary') }}</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ __('messages.topup_amount') }}</p>
                            <p class="text-3xl font-bold text-green-600">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium text-gray-900">{{ __('messages.secure_payment_by_midtrans') }}</span>
                    </div>
                    <p class="text-xs text-gray-500 ml-7">{{ __('messages.redirected_to_secure_payment') }}</p>
                </div>

                <div id="snap-container" class="min-h-[200px] flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200 border-dashed">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        <p class="mt-4 text-sm text-gray-600">{{ __('messages.loading_payment_gateway') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800 mb-1">{{ __('messages.payment_instructions') }}</h3>
                    <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                        <li>{{ __('messages.select_payment_method') }}</li>
                        <li>{{ __('messages.complete_payment_instructed') }}</li>
                        <li>{{ __('messages.wallet_balance_updated_automatically') }}</li>
                        <li>{{ __('messages.if_payment_pending_wait') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Define variables and functions in global scope FIRST
    const snapToken = '{{ $snapToken }}';
    const clientKey = '{{ config('services.midtrans.client_key') }}';
    let snapLoaded = false;
    let retryCount = 0;
    const maxRetries = 10;

    // Define functions in window scope so they're available when onload fires
    window.initSnapPayment = function() {
        snapLoaded = true;
        
        // Wait a bit for Snap to fully initialize
        setTimeout(function() {
            if (typeof window.snap !== 'undefined' && window.snap.pay) {
                try {
                    window.snap.pay(snapToken, {
                        onSuccess: function(result) {
                            console.log('Payment success:', result);
                            window.location.href = '{{ route('wallet.index') }}?status=success';
                        },
                        onPending: function(result) {
                            console.log('Payment pending:', result);
                            window.location.href = '{{ route('wallet.index') }}?status=pending';
                        },
                        onError: function(result) {
                            console.log('Payment error:', result);
                            window.location.href = '{{ route('wallet.index') }}?status=error';
                        },
                        onClose: function() {
                            console.log('Customer closed payment popup');
                            window.location.href = '{{ route('wallet.index') }}?status=cancelled';
                        }
                    });
                } catch (error) {
                    console.error('Error initializing Snap payment:', error);
                    document.getElementById('snap-container').innerHTML = `
                        <div class="text-center p-6">
                            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">Gagal memuat payment gateway</h3>
                            <p class="mt-2 text-sm text-gray-500">${error.message}</p>
                            <a href="{{ route('wallet.index') }}" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Kembali ke Wallet
                            </a>
                        </div>
                    `;
                }
            } else {
                // Retry if snap is not yet available
                if (retryCount < maxRetries) {
                    retryCount++;
                    setTimeout(window.initSnapPayment, 500);
                } else {
                    window.handleSnapError();
                }
            }
        }, 500);
    };

    window.handleSnapError = function() {
        document.getElementById('snap-container').innerHTML = `
            <div class="text-center p-6">
                <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Gagal memuat payment gateway</h3>
                <p class="mt-2 text-sm text-gray-500">Tidak dapat memuat Midtrans Snap.js. Silakan coba lagi atau hubungi support.</p>
                <div class="mt-4 space-y-2">
                    <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Muat Ulang Halaman
                    </button>
                    <a href="{{ route('wallet.index') }}" class="block mt-2 text-sm text-blue-600 hover:text-blue-700">
                        Kembali ke Wallet
                    </a>
                </div>
            </div>
        `;
    };

    // Load Snap.js script AFTER functions are defined
    (function() {
        const script = document.createElement('script');
        script.src = '{{ config('services.midtrans.is_production', false) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}';
        script.setAttribute('data-client-key', clientKey);
        script.onload = function() {
            if (typeof window.initSnapPayment === 'function') {
                window.initSnapPayment();
            }
        };
        script.onerror = function() {
            if (typeof window.handleSnapError === 'function') {
                window.handleSnapError();
            }
        };
        document.head.appendChild(script);
    })();

    // Fallback: Check if snap is loaded after page load
    document.addEventListener('DOMContentLoaded', function() {
        if (!snapLoaded) {
            setTimeout(function() {
                if (typeof window.snap === 'undefined') {
                    if (retryCount < maxRetries) {
                        retryCount++;
                        setTimeout(window.initSnapPayment, 500);
                    } else {
                        window.handleSnapError();
                    }
                } else {
                    window.initSnapPayment();
                }
            }, 1000);
        }
    });

    // Check client key
    if (!clientKey || clientKey === '') {
        console.error('Midtrans Client Key is not configured!');
        window.handleSnapError();
    }
</script>
@endpush
@endsection
