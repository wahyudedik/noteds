@extends('layouts.app')

@section('title', 'Share Analytics Settings')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 flex items-center gap-3">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                        Share Analytics Settings
                    </h1>
                    <p class="mt-2 text-gray-600">Configure share commission, payout timing, and fraud prevention</p>
                </div>
                <a href="{{ route('admin.dashboard') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>

            <!-- Alerts -->
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-900">Validation Errors</h3>
                            <ul class="mt-2 space-y-1 text-sm text-red-800">
                                @foreach ($errors->all() as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-green-900">{{ session('success') }}</h3>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.share-settings.update') }}" class="space-y-6">
                @csrf

                <!-- Commission Configuration -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Commission Configuration
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Share Commission Percent -->
                            <div>
                                <label for="share_commission_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                    Share Commission Percentage (%)
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('share_commission_percent') border-red-500 @enderror"
                                        id="share_commission_percent" name="share_commission_percent"
                                        value="{{ $shareCommissionPercent }}" required min="0" max="100"
                                        step="0.01">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">%</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Persentase komisi dari harga catatan yang dibeli via
                                    share link</p>
                                @error('share_commission_percent')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Payment Mode -->
                            <div>
                                <label for="share_commission_payment_mode"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Mode
                                </label>
                                <select
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent @error('share_commission_payment_mode') border-red-500 @enderror"
                                    id="share_commission_payment_mode" name="share_commission_payment_mode" required>
                                    <option value="monthly"
                                        {{ $shareCommissionPaymentMode === 'monthly' ? 'selected' : '' }}>Monthly Payout
                                    </option>
                                    <option value="immediate"
                                        {{ $shareCommissionPaymentMode === 'immediate' ? 'selected' : '' }}>Immediate
                                        Payment</option>
                                </select>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if ($shareCommissionPaymentMode === 'monthly')
                                        Komisi akan diakumulasi dan ditransfer akhir bulan
                                    @else
                                        Komisi langsung ditransfer ke wallet seller
                                    @endif
                                </p>
                                @error('share_commission_payment_mode')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payout Configuration -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Payout Configuration
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="share_monthly_payout_day" class="block text-sm font-medium text-gray-700 mb-2">
                                Monthly Payout Day
                            </label>
                            <select
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent @error('share_monthly_payout_day') border-red-500 @enderror"
                                id="share_monthly_payout_day" name="share_monthly_payout_day" required>
                                @for ($day = 1; $day <= 31; $day++)
                                    <option value="{{ $day }}"
                                        {{ $shareMonthlyPayoutDay == $day ? 'selected' : '' }}>
                                        Day {{ $day }}
                                    </option>
                                @endfor
                            </select>
                            <p class="mt-1 text-sm text-gray-500">
                                Tanggal dalam sebulan untuk mentransfer komisi dari admin wallet ke seller wallet (1-31)
                            </p>
                            @error('share_monthly_payout_day')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-900">
                                <strong>💡 Note:</strong> Sistem akan secara otomatis mengakumulasi komisi selama satu bulan
                                dan mentransfer dari admin wallet ke seller wallet pada tanggal yang ditentukan.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Fraud Prevention -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4v2m0 0a9 9 0 110-18 9 9 0 0110 18zm0-14a.5.5 0 100-1 .5.5 0 000 1zm0 4a.5.5 0 100-1 .5.5 0 000 1z" />
                            </svg>
                            Fraud Prevention
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="share_max_shares_per_user_per_link"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                Max Shares Per User Per Link
                            </label>
                            <div class="relative">
                                <input type="number"
                                    class="w-full pr-9 pl-4 py-2 border rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('share_max_shares_per_user_per_link') border-red-500 @enderror"
                                    id="share_max_shares_per_user_per_link" name="share_max_shares_per_user_per_link"
                                    value="{{ $shareMaxSharesPerUserPerLink }}" required min="1" max="1000">
                                <span
                                    class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">x</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Satu user hanya bisa share link yang sama maksimal berapa
                                kali (set ke 1 untuk one-time share per link)</p>
                            @error('share_max_shares_per_user_per_link')
                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-sm text-yellow-900">
                                <strong>⚠️ Warning:</strong> Setting ke 1 berarti satu user hanya bisa share sekali per
                                link/produk. User harus membuat link baru untuk share lagi produk yang sama. Ini mencegah
                                fraud dan eksploitasi.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end gap-4">
                    <a href="{{ route('admin.dashboard') }}"
                        class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
