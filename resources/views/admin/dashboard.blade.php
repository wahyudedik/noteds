@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h2>

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Users</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Notes</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['total_notes'] }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ $stats['public_notes'] }} public</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Platform Revenue</div>
                    <div class="text-2xl font-bold text-green-600">Rp
                        {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-600 mt-1">Saldo: Rp {{ number_format($platformBalance, 0, ',', '.') }}
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Withdraws</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending_withdraws'] }}</div>
                    <a href="{{ route('admin.withdraws.index') }}" class="text-xs text-blue-600 hover:underline">View all
                        →</a>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Pending Subs</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $stats['pending_subscriptions'] }}</div>
                    <div class="text-xs text-gray-600 mt-1">{{ $stats['active_subscriptions'] }} active</div>
                    <a href="{{ route('admin.subscriptions.index') }}" class="text-xs text-blue-600 hover:underline">Manage
                        →</a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <a href="{{ route('admin.users.index') }}"
                        class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-sm font-medium text-blue-900">Users</span>
                    </a>
                    <a href="{{ route('admin.notes.index') }}"
                        class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-green-900">Notes</span>
                    </a>
                    <a href="{{ route('admin.exchange-rates.index') }}"
                        class="flex items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium text-yellow-900">Exchange Rates</span>
                    </a>
                    <a href="{{ route('admin.subscriptions.index') }}"
                        class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                        <span class="text-sm font-medium text-purple-900">Subscriptions</span>
                    </a>
                    <a href="{{ route('admin.withdraws.index') }}"
                        class="flex items-center p-4 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-sm font-medium text-red-900">Withdraws</span>
                    </a>
                    <a href="{{ route('admin.tickets.index') }}"
                        class="flex items-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-sm font-medium text-indigo-900">Support Tickets</span>
                    </a>
                    <a href="{{ route('admin.documentations.index') }}"
                        class="flex items-center p-4 bg-teal-50 rounded-lg hover:bg-teal-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-sm font-medium text-teal-900">Documentations</span>
                    </a>
                    <a href="{{ route('admin.landing-page.index') }}"
                        class="flex items-center p-4 bg-pink-50 rounded-lg hover:bg-pink-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                        <span class="text-sm font-medium text-pink-900">Landing Page</span>
                    </a>
                    <a href="{{ route('admin.social-media.index') }}"
                        class="flex items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm font-medium text-orange-900">Social Media</span>
                    </a>
                </div>
            </div>

            <!-- Wallet Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Wallet Analytics
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase">Total Balance</div>
                        <div class="text-xl font-bold text-blue-900">Rp
                            {{ number_format($walletStats['total_wallet_balance'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-green-600 uppercase">Avg Balance</div>
                        <div class="text-xl font-bold text-green-900">Rp
                            {{ number_format($walletStats['avg_wallet_balance'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-purple-600 uppercase">Total Transactions</div>
                        <div class="text-xl font-bold text-purple-900">Rp
                            {{ number_format($walletStats['total_successful_transactions'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-red-600 uppercase">Total Withdrawals</div>
                        <div class="text-xl font-bold text-red-900">Rp
                            {{ number_format($walletStats['total_withdrawals'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-yellow-600 uppercase">Total Wallets</div>
                        <div class="text-xl font-bold text-yellow-900">{{ $walletStats['total_wallets'] }}</div>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-indigo-600 uppercase">With Balance</div>
                        <div class="text-xl font-bold text-indigo-900">{{ $walletStats['wallets_with_balance'] }}</div>
                    </div>
                </div>

                <!-- Top Wallets -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Top 10 Wallets (by Balance)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Balance
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topWallets as $index => $user)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-green-600">Rp
                                            {{ number_format($user->wallet_balance, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No wallets
                                            with balance yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Referral Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    Referral Analytics
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-purple-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-purple-600 uppercase">Total Referrals</div>
                        <div class="text-xl font-bold text-purple-900">{{ $referralStats['total_referrals'] }}</div>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-blue-600 uppercase">Signup Rewards</div>
                        <div class="text-xl font-bold text-blue-900">Rp
                            {{ number_format($referralStats['total_signup_rewards'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-green-600 uppercase">Transaction Commissions</div>
                        <div class="text-xl font-bold text-green-900">Rp
                            {{ number_format($referralStats['total_transaction_commission'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4">
                        <div class="text-xs font-medium text-yellow-600 uppercase">Total Payout</div>
                        <div class="text-xl font-bold text-yellow-900">Rp
                            {{ number_format($referralStats['total_referral_payout'], 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Referral Leaderboard -->
                <div class="mt-6 mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Referral Leaderboard (Top 10)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Referrals
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Signup
                                    </th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Transaction</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Commission</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($referralLeaderboard as $index => $refData)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $refData['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center">{{ $refData['total_referrals'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $refData['signup_count'] }} (Rp
                                            {{ number_format($refData['signup_total'], 0, ',', '.') }})</td>
                                        <td class="px-4 py-2 text-sm text-center text-green-600">
                                            {{ $refData['transaction_count'] }} (Rp
                                            {{ number_format($refData['transaction_total'], 0, ',', '.') }})</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-purple-600">Rp
                                            {{ number_format($refData['total_commission'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">No referral
                                            data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detailed Per-User Referral -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Detailed Referral Data (Top 20)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Total
                                        Signups</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Signup
                                        Rewards</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Referred
                                        Buyers</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                        Transaction Rewards</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Commission</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($userReferralDetails as $refDetail)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $refDetail['user']->name }}<br><span
                                                class="text-xs text-gray-500">{{ $refDetail['user']->email }}</span></td>
                                        <td class="px-4 py-2 text-sm text-center">{{ $refDetail['total_signups'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $refDetail['signup_count'] }} × Rp
                                            {{ number_format($refDetail['signup_total'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-purple-600">
                                            {{ $refDetail['referred_buyers_count'] }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-green-600">
                                            {{ $refDetail['transaction_count'] }} × Rp
                                            {{ number_format($refDetail['transaction_total'], 0, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-purple-900">Rp
                                            {{ number_format($refDetail['total_commission'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">No detailed
                                            referral data yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Note Creation Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Note Creation Analytics
                </h3>

                <!-- Daily Note Creation (Last 30 days) -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Daily Note Creation (Last 30 Days)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Notes
                                        Created</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Unique
                                        Users</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Avg per
                                        User</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($dailyNotes as $day)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                        <td class="px-4 py-2 text-sm text-center font-medium">{{ $day->count }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">{{ $day->unique_users }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-center text-gray-500">
                                            {{ $day->unique_users > 0 ? number_format($day->count / $day->unique_users, 1) : 0 }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No note
                                            creation data in the last 30 days.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Note Creators -->
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Top 10 Note Creators</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Notes</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topNoteCreators as $index => $user)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $user->name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-indigo-600">
                                            {{ $user->notes_count }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No note
                                            creators yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Daily Notes Per User (Last 7 days) -->
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-3">Daily Notes Per User (Last 7 Days)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Notes
                                        Created</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($userNoteActivity as $date => $activities)
                                    @foreach ($activities as $activity)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">
                                                {{ $activity->user->name }}<br><span
                                                    class="text-xs text-gray-500">{{ $activity->user->email }}</span></td>
                                            <td class="px-4 py-2 text-sm font-bold text-right text-indigo-600">
                                                {{ $activity->note_count }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">No note
                                            activity in the last 7 days.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Revenue Analytics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Revenue Analytics (Last 30 Days)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total Amount
                                </th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Commission
                                </th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Transactions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($revenueData as $day)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2 text-sm font-medium text-right">Rp
                                        {{ number_format($day->total_amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-sm font-bold text-right text-green-600">Rp
                                        {{ number_format($day->total_commission, 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-sm text-center text-blue-600">{{ $day->transaction_count }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No revenue data
                                        in the last 30 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Sellers & Buyers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Top Sellers -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Top 10 Sellers
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Sales
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Revenue
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topSellers as $index => $seller)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $seller['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-blue-600">
                                            {{ $seller['sales_count'] }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-green-600">Rp
                                            {{ number_format($seller['total_sales'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No sellers
                                            yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Buyers -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Top 10 Buyers
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Purchases
                                    </th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Total
                                        Spent</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topBuyers as $index => $buyer)
                                    <tr>
                                        <td class="px-4 py-2 text-sm font-medium text-gray-500">#{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">{{ $buyer['user']->name }}</td>
                                        <td class="px-4 py-2 text-sm text-center text-purple-600">
                                            {{ $buyer['purchase_count'] }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right text-red-600">Rp
                                            {{ number_format($buyer['total_spent'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">No buyers
                                            yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- User Growth -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    User Growth (Last 30 Days)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">New Users
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($userGrowth as $day)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($day->date)->format('d M Y') }}</td>
                                    <td class="px-4 py-2 text-sm text-center font-medium text-teal-600">
                                        {{ $day->count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-500">No user growth
                                        data in the last 30 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Transactions</h3>
                @if ($recentTransactions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buyer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commission
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $transaction->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->buyer->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $transaction->seller->name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">Rp
                                            {{ number_format($transaction->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-green-600">Rp
                                            {{ number_format($transaction->commission, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($transaction->status === 'success')
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Success</span>
                                            @elseif($transaction->status === 'pending')
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                                            @else
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.transactions.index') }}"
                            class="text-blue-600 hover:text-blue-800 text-sm">View all transactions →</a>
                    </div>
                @else
                    <p class="text-gray-600 text-center py-4">No transactions yet.</p>
                @endif
            </div>

            <!-- Recent Withdraws -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Withdraws</h3>
                @if ($recentWithdraws->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bank</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($recentWithdraws as $withdraw)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ $withdraw->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $withdraw->user->name }}</td>
                                        <td class="px-4 py-3 text-sm font-medium">Rp
                                            {{ number_format($withdraw->amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $withdraw->bank_name }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @if ($withdraw->status === 'approved')
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Approved</span>
                                            @elseif($withdraw->status === 'rejected')
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Rejected</span>
                                            @else
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('admin.withdraws.show', $withdraw) }}"
                                                class="text-blue-600 hover:text-blue-800">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('admin.withdraws.index') }}"
                            class="text-blue-600 hover:text-blue-800 text-sm">View all withdraws →</a>
                    </div>
                @else
                    <p class="text-gray-600 text-center py-4">No withdraws yet.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
