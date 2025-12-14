@extends('40-shared/layouts/app')

@section('title', __('messages.admin_dashboard') ?? 'Admin Dashboard')

@section('content')
    <div class="py-10">
        <div class="max-w-7xl mx-auto space-y-10 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Admin Dashboard</h1>
                    <p class="text-sm text-slate-600 mt-1">Overview of platform metrics and recent activity.</p>
                </div>
                <div class="text-sm text-slate-500">Updated: {{ now()->format('Y-m-d H:i') }}</div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white shadow-sm rounded-xl p-4">
                    <div class="text-sm text-slate-500">Users</div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ number_format(data_get($stats ?? [], 'users.total', 0)) }}</div>
                    <div class="text-xs text-slate-500">Active:
                        {{ number_format(data_get($stats ?? [], 'users.active', 0)) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4">
                    <div class="text-sm text-slate-500">Notes</div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ number_format(data_get($stats ?? [], 'notes.total', 0)) }}</div>
                    <div class="text-xs text-slate-500">Public:
                        {{ number_format(data_get($stats ?? [], 'notes.public', 0)) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4">
                    <div class="text-sm text-slate-500">Transactions</div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ number_format(data_get($stats ?? [], 'transactions.total', 0)) }}</div>
                    <div class="text-xs text-slate-500">Success:
                        {{ number_format(data_get($stats ?? [], 'transactions.success', 0)) }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-xl p-4">
                    <div class="text-sm text-slate-500">Platform Balance</div>
                    <div class="text-2xl font-semibold text-slate-900">
                        {{ currency(data_get($platformBalance ?? [], 'total', 0)) }}</div>
                    <div class="text-xs text-slate-500">Pending withdraw:
                        {{ number_format(data_get($stats ?? [], 'withdraws.pending', 0)) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Recent Transactions</h2>
                        <span class="text-xs text-slate-500">Latest 10</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse(($recentTransactions ?? []) as $tx)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $tx->buyer?->name ?? 'N/A' }} →
                                        {{ $tx->seller?->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-500">{{ $tx->created_at?->format('Y-m-d H:i') }} ·
                                        {{ ucfirst($tx->status ?? 'unknown') }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-900">{{ currency($tx->amount ?? 0) }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No transactions yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Top Wallets</h2>
                        <span class="text-xs text-slate-500">Balance leaderboard</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse(($topWallets ?? []) as $wallet)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $wallet['name'] ?? ($wallet['email'] ?? 'N/A') }}</div>
                                    <div class="text-xs text-slate-500">User ID: {{ $wallet['id'] ?? '-' }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-900">
                                    {{ currency($wallet['wallet_balance'] ?? 0) }}</div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No wallet data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Referrals</h2>
                        <span class="text-xs text-slate-500">Paid & pending</span>
                    </div>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Paid (count)</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ number_format(data_get($referralStats ?? [], 'paid_count', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Paid (amount)</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ currency(data_get($referralStats ?? [], 'paid_amount', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Pending (count)</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ number_format(data_get($referralStats ?? [], 'pending_count', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Pending (amount)</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ currency(data_get($referralStats ?? [], 'pending_amount', 0)) }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Withdraws</h2>
                        <span class="text-xs text-slate-500">Pending vs total</span>
                    </div>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Pending</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ number_format(data_get($stats ?? [], 'withdraws.pending', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Total</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ number_format(data_get($stats ?? [], 'withdraws.total', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Approved amount</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ currency(data_get($stats ?? [], 'withdraws.approved_amount', 0)) }}</dd>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <dt class="text-slate-500">Pending amount</dt>
                            <dd class="text-lg font-semibold text-slate-900">
                                {{ currency(data_get($stats ?? [], 'withdraws.pending_amount', 0)) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Top Sellers</h2>
                        <span class="text-xs text-slate-500">By revenue</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse(($topSellersByRevenue ?? []) as $seller)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $seller['name'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-500">Notes sold:
                                        {{ number_format($seller['notes_sold'] ?? 0) }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-900">{{ currency($seller['revenue'] ?? 0) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No seller data.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-slate-900">Top Buyers</h2>
                        <span class="text-xs text-slate-500">By spending</span>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse(($topBuyersBySpending ?? []) as $buyer)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-slate-900">{{ $buyer['name'] ?? 'N/A' }}</div>
                                    <div class="text-xs text-slate-500">Purchases:
                                        {{ number_format($buyer['purchases'] ?? 0) }}</div>
                                </div>
                                <div class="text-sm font-semibold text-slate-900">{{ currency($buyer['spending'] ?? 0) }}
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">No buyer data.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900">Pending Affiliate Payouts</h2>
                    <span class="text-xs text-slate-500">Latest requests</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse(($pendingPayouts ?? []) as $payout)
                        <div class="py-3 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-slate-900">{{ $payout['affiliate_name'] ?? 'N/A' }}
                                </div>
                                <div class="text-xs text-slate-500">{{ $payout['method'] ?? '-' }} ·
                                    {{ $payout['requested_at'] ?? '-' }}</div>
                            </div>
                            <div class="text-sm font-semibold text-slate-900">{{ currency($payout['amount'] ?? 0) }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">No pending payouts.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
