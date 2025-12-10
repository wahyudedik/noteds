@extends('admin.layouts.app')

@section('title', 'Withdrawals Management')
@section('header', 'Withdrawals Management')

@section('content')
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Pending</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ count($pending ?? []) }}</p>
            <p class="text-xs text-gray-500 mt-2">Rp {{ number_format($pendingAmount ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Approved</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ count($approved ?? []) }}</p>
            <p class="text-xs text-gray-500 mt-2">Rp {{ number_format($approvedAmount ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Rejected</p>
            <p class="text-3xl font-bold text-red-600 mt-2">{{ count($rejected ?? []) }}</p>
            <p class="text-xs text-gray-500 mt-2">Rp {{ number_format($rejectedAmount ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-sm font-medium text-gray-600">Total Withdrawals</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ count($all ?? []) }}</p>
            <p class="text-xs text-gray-500 mt-2">Rp {{ number_format($totalAmount ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Filter & Action Section -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" id="search" placeholder="Search user or bank..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <select id="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <select id="method" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">All Methods</option>
                <option value="bank">Bank Transfer</option>
                <option value="ewallet">E-Wallet</option>
            </select>
            <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
        </div>
    </div>

    <!-- Pending Withdrawals -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Pending Withdrawals</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Bank Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Method</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Requested</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pending as $withdrawal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <img src="https://ui-avatars.com/api/?name={{ $withdrawal->user->name }}&background=random" alt="{{ $withdrawal->user->name }}" class="w-8 h-8 rounded-full mr-2">
                                <span class="text-sm font-medium text-gray-900">{{ $withdrawal->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $withdrawal->bank_name }}<br>
                            <span class="text-xs text-gray-500">{{ substr($withdrawal->account_number, -4) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Rp {{ number_format($withdrawal->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Bank Transfer</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $withdrawal->created_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <button onclick="approveWithdrawal({{ $withdrawal->id }})" class="text-green-600 hover:text-green-900 mr-3">Approve</button>
                            <button onclick="rejectWithdrawal({{ $withdrawal->id }})" class="text-red-600 hover:text-red-900">Reject</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No pending withdrawals</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Approved Withdrawals -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Approved Withdrawals</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Bank Account</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Approved</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($approved as $withdrawal)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $withdrawal->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $withdrawal->bank_name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">Rp {{ number_format($withdrawal->amount ?? 0, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $withdrawal->approved_at->format('d M Y H:i') ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">No approved withdrawals</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function approveWithdrawal(id) {
    if (confirm('Approve this withdrawal?')) {
        // Handle approval
    }
}

function rejectWithdrawal(id) {
    const reason = prompt('Enter rejection reason:');
    if (reason) {
        // Handle rejection
    }
}
</script>
@endsection
