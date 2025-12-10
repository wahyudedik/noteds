@extends('layouts.app')

@section('title', 'Transaction Report - Admin')

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Transaction Report</h1>
                <p class="mt-2 text-base text-gray-600">View and manage all user transactions, purchases, top-ups,
                    withdrawals, and commissions</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Transactions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalTransactions }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Amount</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalAmount, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Commission</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($totalCommission, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pending Amount</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($pendingAmount, 2) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Transactions</h2>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">User Search</label>
                        <input type="text" name="user_search" value="{{ $userSearch }}" placeholder="Name or Email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                        <select name="type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Methods</option>
                            <option value="midtrans" {{ $type === 'midtrans' ? 'selected' : '' }}>Midtrans</option>
                            <option value="bank_transfer" {{ $type === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer
                            </option>
                            <option value="credit_card" {{ $type === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                            <option value="wallet" {{ $type === 'wallet' ? 'selected' : '' }}>Wallet</option>
                            <option value="other" {{ $type === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="flex items-end col-span-1">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            Filter
                        </button>
                    </div>

                    <div class="flex items-end col-span-1">
                        <a href="{{ route('admin.wallet.export', request()->query()) }}"
                            class="w-full px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-colors text-center">
                            Export CSV
                        </a>
                    </div>
                </form>
            </div>

            <!-- Transaction Type Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">By Payment Method</h3>
                    <div class="space-y-3">
                        @forelse($byType as $item)
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-sm font-medium text-gray-700 capitalize">{{ $item->payment_method ?? 'Other' }}</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-600">{{ $item->count }} transactions</span>
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ number_format($item->total, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No transactions found</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">By Status</h3>
                    <div class="space-y-3">
                        @forelse($byStatus as $item)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 capitalize">{{ $item->status }}</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-gray-600">{{ $item->count }} transactions</span>
                                    <span
                                        class="text-sm font-bold text-gray-900">{{ number_format($item->total, 2) }}</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No transactions found</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Payment Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Commission</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                    Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($transactions as $transaction)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $transaction->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center
                                        @if ($transaction->payment_method === 'midtrans') bg-blue-100 text-blue-800
                                        @elseif($transaction->payment_method === 'bank_transfer') bg-green-100 text-green-800
                                        @elseif($transaction->payment_method === 'credit_card') bg-purple-100 text-purple-800
                                        @elseif($transaction->payment_method === 'wallet') bg-indigo-100 text-indigo-800
                                        @else bg-gray-100 text-gray-800 @endif
                                    ">
                                            {{ ucfirst(str_replace('_', ' ', $transaction->payment_method ?? 'other')) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        <div>
                                            <p class="font-medium">
                                                {{ $transaction->buyer?->name ?? ($transaction->seller?->name ?? '-') }}</p>
                                            <p class="text-xs text-gray-500">
                                                {{ $transaction->buyer?->email ?? ($transaction->seller?->email ?? '-') }}
                                            </p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($transaction->amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ number_format($transaction->commission ?? 0, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold inline-flex items-center
                                        @if ($transaction->status === 'completed') bg-green-100 text-green-800
                                        @elseif($transaction->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($transaction->status === 'failed') bg-red-100 text-red-800
                                        @elseif($transaction->status === 'cancelled') bg-gray-100 text-gray-800
                                        @else bg-blue-100 text-blue-800 @endif
                                    ">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $transaction->description ?? ($transaction->note?->title ?? '-') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No transactions found
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
