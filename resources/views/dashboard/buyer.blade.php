<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buyer Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-2xl font-bold text-gray-900">
                    Welcome back, {{ $user->name }}!
                </h3>
                <p class="text-gray-600 mt-2">
                    Discover and purchase quality notes from expert sellers
                </p>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Total Spent -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                Rp {{ number_format($metrics['total_spent'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-blue-500 text-3xl">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>

                <!-- Notes Purchased -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Notes Purchased</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ $metrics['notes_purchased'] }}
                            </p>
                        </div>
                        <div class="text-green-500 text-3xl">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>

                <!-- Collections -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Collections</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ $metrics['collections_count'] }}
                            </p>
                        </div>
                        <div class="text-purple-500 text-3xl">
                            <i class="fas fa-bookmark"></i>
                        </div>
                    </div>
                </div>

                <!-- Ratings Given -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Ratings Given</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ $metrics['total_ratings'] }}
                            </p>
                        </div>
                        <div class="text-yellow-500 text-3xl">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h4 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('marketplace.index') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 hover:shadow-md transition">
                        <i class="fas fa-search text-blue-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Explore Notes</p>
                            <p class="text-sm text-gray-600">Browse new notes</p>
                        </div>
                    </a>

                    <a href="{{ route('referral.index') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-green-50 to-green-100 hover:shadow-md transition">
                        <i class="fas fa-link text-green-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Referral Program</p>
                            <p class="text-sm text-gray-600">Earn commissions</p>
                        </div>
                    </a>

                    <a href="{{ route('collections.index') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-purple-50 to-purple-100 hover:shadow-md transition">
                        <i class="fas fa-list text-purple-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">My Collections</p>
                            <p class="text-sm text-gray-600">View saved items</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Referral Stats -->
            @if ($referralStats['referral_code'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Your Referral Program</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="text-gray-600 text-sm">Your Referral Code</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1 font-mono">
                                {{ $referralStats['referral_code'] }}
                            </p>
                        </div>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="text-gray-600 text-sm">People Referred</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ $referralStats['referrals_count'] }}
                            </p>
                        </div>
                        <div class="border-l-4 border-yellow-500 pl-4">
                            <p class="text-gray-600 text-sm">Referral Earnings</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                Rp {{ number_format($referralStats['referral_earnings'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Purchases -->
            @if ($recentPurchases->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Recently Purchased</h4>
                    <div class="space-y-3">
                        @foreach ($recentPurchases as $purchase)
                            <div
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $purchase->title }}</p>
                                    <p class="text-sm text-gray-600">by {{ $purchase->seller->name }}</p>
                                </div>
                                <a href="{{ route('notes.show', $purchase) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No purchases yet. <a href="{{ route('marketplace.index') }}"
                            class="text-blue-600 hover:text-blue-800 font-medium">Start exploring notes →</a></p>
                </div>
            @endif

            <!-- Wishlist -->
            @if ($wishlisted->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Collections</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach ($wishlisted as $collection)
                            <div class="border rounded-lg p-4 hover:shadow-md transition">
                                <h5 class="font-semibold text-gray-900 mb-2">{{ $collection->name ?? 'Collection' }}
                                </h5>
                                <p class="text-sm text-gray-600 mb-3">{{ $collection->items_count ?? 0 }} items</p>
                                <a href="{{ route('collections.show', $collection) }}"
                                    class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    View Collection →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
