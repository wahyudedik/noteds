@extends('layouts.app')

@section('title', __('messages.admin_user_detail'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="text-blue-600 hover:text-blue-800">{{ __('messages.back_to_users') }}</a>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.user_detail', ['name' => $user->name]) }}</h2>
            <a href="{{ route('admin.users.edit', $user) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('messages.edit_user') }}
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
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.user_information') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.name') }}:</strong> {{ $user->name }}</p>
                        <p><strong>{{ __('messages.email') }}:</strong> {{ $user->email }}</p>
                        <p><strong>{{ __('messages.role') }}:</strong> 
                            @if($user->role === 'admin')
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.admin') }}</span>
                            @elseif($user->role === 'seller')
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ __('messages.seller') }}</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">{{ __('messages.buyer') }}</span>
                            @endif
                        </p>
                        <p><strong>{{ __('messages.wallet_balance_label') }}:</strong> {{ currency($user->wallet_balance ?? 0) }}</p>
                        <p><strong>{{ __('messages.joined') }}:</strong> {{ $user->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.statistics') }}</h3>
                    <div class="space-y-2">
                        <p><strong>{{ __('messages.total_notes_label') }}:</strong> {{ $user->notes->count() }}</p>
                        <p><strong>{{ __('messages.public_notes_label') }}:</strong> {{ $user->notes->where('is_public', true)->count() }}</p>
                        <p><strong>{{ __('messages.total_withdraws') }}:</strong> {{ $user->withdraws->count() }}</p>
                        <p><strong>{{ __('messages.pending_withdraws') }}:</strong> {{ $user->withdraws->where('status', 'pending')->count() }}</p>
                        <p><strong>{{ __('messages.transactions_buyer') }}:</strong> {{ $user->transactionsAsBuyer->count() }}</p>
                        <p><strong>{{ __('messages.transactions_seller') }}:</strong> {{ $user->transactionsAsSeller->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Withdraws -->
        @if($user->withdraws->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.recent_withdraws') }}</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->withdraws->take(5) as $withdraw)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $withdraw->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ currency($withdraw->amount) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($withdraw->status === 'approved')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.approved') }}</span>
                                        @elseif($withdraw->status === 'rejected')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.rejected') }}</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
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

