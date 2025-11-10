@extends('layouts.app')

@section('title', 'Jual Kembali Catatan')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('marketplace.show', $note) }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Jual Kembali Catatan</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">Set harga untuk menjual kembali catatan ini ke buyer lain</p>
        </div>

        <!-- Warning Box -->
        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-yellow-800 mb-1">⚠️ Penting: Penjualan Sekali Saja</p>
                    <p class="text-xs text-yellow-700">
                        Setelah Anda menjual catatan ini, Anda akan <strong>kehilangan akses secara permanen</strong>. 
                        Pastikan Anda sudah menyimpan yang diperlukan sebelum menjual.
                    </p>
                </div>
            </div>
        </div>

        <!-- Note Info Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $note->title }}</h2>
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center justify-between">
                        <span>Harga Pembelian Original:</span>
                        <span class="font-semibold text-gray-900">{{ currency($originalPrice) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span>Harga Saat Ini:</span>
                        <span class="font-semibold text-gray-900">{{ currency($currentPrice) }}</span>
                    </div>
                    @if($note->originalCreator)
                        <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                            <span>Original Creator:</span>
                            <span class="font-medium text-blue-600">{{ $note->originalCreator->name }}</span>
                        </div>
                        <p class="text-xs text-blue-600 mt-1">
                            Original creator akan menerima komisi dari setiap penjualan.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Set Harga Resale</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('notes.resale', $note) }}" method="POST">
                    @csrf

                    <!-- Flash Messages -->
                    @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        </div>
                    @endif

                    <!-- Resale Price -->
                    <div class="mb-6">
                        <label for="resale_price" class="block text-sm font-medium text-gray-700 mb-2">
                            Harga Resale <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="resale_price" id="resale_price" 
                                value="{{ old('resale_price', $currentPrice) }}" 
                                min="0" step="0.01" placeholder="0" required
                                class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('resale_price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Set harga yang ingin Anda jual. Harga ini akan menjadi harga jual untuk buyer lain.
                        </p>
                        @error('resale_price')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price Guidance -->
                    @if (!empty($priceGuidance))
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-800 space-y-2">
                            @if (!empty($priceGuidance['min_default']))
                                <p class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5a1 1 0 112 0v1.528a6 6 0 014.472 9.472l.764.764a1 1 0 01-1.414 1.414l-.764-.764A6 6 0 1111 6.528V5z" />
                                    </svg>
                                    <span>Harga minimum: <strong>{{ currency($priceGuidance['min_default']) }}</strong></span>
                                </p>
                            @endif
                            @if (!empty($priceGuidance['recommended_price']))
                                <p class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>Harga rekomendasi: <strong>{{ currency($priceGuidance['recommended_price']) }}</strong></span>
                                </p>
                            @endif
                            @if (!empty($priceGuidance['original_price']))
                                <p class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Harga pembelian Anda: <strong>{{ currency($priceGuidance['original_price']) }}</strong></span>
                                </p>
                            @endif
                        </div>
                    @endif

                    <!-- Info Box -->
                    <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-blue-800 mb-1">Informasi Penting</p>
                                <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                                    <li>Setelah dijual, Anda akan kehilangan akses ke catatan ini secara permanen</li>
                                    <li>Original creator akan menerima komisi dari penjualan ini</li>
                                    <li>Platform akan mengambil platform fee dari setiap transaksi</li>
                                    <li>Anda akan menerima: Harga jual - Platform fee - Creator commission</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('marketplace.show', $note) }}" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                            Batal
                        </a>
                        <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            Pasang untuk Dijual
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

