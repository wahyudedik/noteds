@extends('layouts.app')

@section('title', 'User Detail - Admin')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Users</a>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">User Detail: {{ $user->name }}</h2>
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Edit User
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">User Information</h3>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ $user->name }}</p>
                        <p><strong>Email:</strong> {{ $user->email }}</p>
                        <p><strong>Role:</strong> 
                            @if($user->role === 'admin')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Admin</span>
                            @elseif($user->role === 'seller')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Seller</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Buyer</span>
                            @endif
                        </p>
                        <p><strong>Wallet Balance:</strong> Rp {{ number_format($user->wallet_balance ?? 0, 0, ',', '.') }}</p>
                        <p><strong>Joined:</strong> {{ $user->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistics</h3>
                    <div class="space-y-2">
                        <p><strong>Total Notes:</strong> {{ $user->notes->count() }}</p>
                        <p><strong>Public Notes:</strong> {{ $user->notes->where('is_public', true)->count() }}</p>
                        <p><strong>Total Withdraws:</strong> {{ $user->withdraws->count() }}</p>
                        <p><strong>Pending Withdraws:</strong> {{ $user->withdraws->where('status', 'pending')->count() }}</p>
                        <p><strong>Transactions (Buyer):</strong> {{ $user->transactionsAsBuyer->count() }}</p>
                        <p><strong>Transactions (Seller):</strong> {{ $user->transactionsAsSeller->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Withdraws -->
        @if($user->withdraws->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Withdraws</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->withdraws->take(5) as $withdraw)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $withdraw->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">Rp {{ number_format($withdraw->amount, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($withdraw->status === 'approved')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Approved</span>
                                        @elseif($withdraw->status === 'rejected')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Rejected</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                                        @endif
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
@endsection

