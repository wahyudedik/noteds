<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Seller Dashboard') }}
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
                    Manage your notes and track your sales performance
                </p>
            </div>

            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Total Revenue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                Rp {{ number_format($metrics['total_revenue'], 0, ',', '.') }}
                            </p>
                        </div>
                        <div class="text-green-500 text-3xl">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>

                <!-- Notes Published -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Notes Published</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ $metrics['notes_published'] }}
                            </p>
                        </div>
                        <div class="text-blue-500 text-3xl">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Total Sales</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ $metrics['total_sales'] }}
                            </p>
                        </div>
                        <div class="text-purple-500 text-3xl">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>

                <!-- Average Rating -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-medium">Average Rating</p>
                            <p class="text-2xl font-bold text-gray-900 mt-2">
                                {{ number_format($metrics['average_rating'], 1) }}
                                <span class="text-yellow-500 text-lg">★</span>
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
                    <a href="{{ route('notes.create') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 hover:shadow-md transition">
                        <i class="fas fa-plus text-blue-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Create Note</p>
                            <p class="text-sm text-gray-600">Publish new content</p>
                        </div>
                    </a>

                    <a href="{{ route('seller-analytics.index') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-green-50 to-green-100 hover:shadow-md transition">
                        <i class="fas fa-chart-bar text-green-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">View Analytics</p>
                            <p class="text-sm text-gray-600">Track performance</p>
                        </div>
                    </a>

                    <a href="{{ route('affiliate.index') }}"
                        class="flex items-center gap-3 p-4 rounded-lg bg-gradient-to-r from-purple-50 to-purple-100 hover:shadow-md transition">
                        <i class="fas fa-link text-purple-600 text-xl"></i>
                        <div>
                            <p class="font-semibold text-gray-900">Affiliate Program</p>
                            <p class="text-sm text-gray-600">Share & earn</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Affiliate/Share Program Stats -->
            @if ($affiliateStats['affiliate_code'])
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Your Affiliate Program</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="border-l-4 border-green-500 pl-4">
                            <p class="text-gray-600 text-sm">Your Affiliate Code</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1 font-mono">
                                {{ $affiliateStats['affiliate_code'] }}
                            </p>
                        </div>
                        <div class="border-l-4 border-blue-500 pl-4">
                            <p class="text-gray-600 text-sm">Referrals</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                {{ $affiliateStats['affiliate_referrals'] }}
                            </p>
                        </div>
                        <div class="border-l-4 border-yellow-500 pl-4">
                            <p class="text-gray-600 text-sm">Affiliate Earnings</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">
                                Rp {{ number_format($affiliateStats['affiliate_earnings'], 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Best Performing Notes -->
            @if ($bestPerforming->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Best Performing Notes</h4>
                    <div class="space-y-3">
                        @foreach ($bestPerforming as $note)
                            <div
                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $note->title }}</p>
                                    <div class="flex gap-4 mt-1 text-sm text-gray-600">
                                        <span><i class="fas fa-shopping-cart mr-1"></i>{{ $note->sales->count() }}
                                            sales</span>
                                        <span><i
                                                class="fas fa-star mr-1"></i>{{ number_format($note->ratings->avg('rating') ?? 0, 1) }}
                                            rating</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-green-600">
                                        Rp {{ number_format($note->sales->sum('amount'), 0, ',', '.') }}
                                    </p>
                                    <a href="{{ route('notes.edit', $note) }}"
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit →
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6 text-center py-8">
                    <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                    <p class="text-gray-600">No published notes yet. <a href="#"
                            class="text-blue-600 hover:text-blue-800 font-medium">Create your first note →</a></p>
                </div>
            @endif

            <!-- Recent Sales -->
            @if ($recentSales->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h4 class="text-lg font-bold text-gray-900 mb-4">Recent Sales</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Note</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Buyer</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Amount</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($recentSales as $sale)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-900">{{ $sale->note->title }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $sale->buyer->name }}</td>
                                        <td class="px-4 py-3 font-semibold text-green-600">
                                            Rp {{ number_format($sale->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $sale->created_at->format('d M Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
