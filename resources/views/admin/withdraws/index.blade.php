@extends('layouts.app')

@section('title', __('messages.admin_withdraws'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.withdraw_requests') }}</h2>
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_dashboard') }}</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.withdraws.index') }}" class="flex gap-4">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('messages.all_status') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('messages.pending') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('messages.approved') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('messages.rejected') }}</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('messages.filter') }}
                </button>
                @if(request('status'))
                    <a href="{{ route('admin.withdraws.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('messages.clear') }}
                    </a>
                @endif
            </form>
        </div>

        @if($withdraws->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.user') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.amount') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.bank_details') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.processed_by') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('messages.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($withdraws as $withdraw)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $withdraw->created_at->format('d M Y, H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $withdraw->user->name }}<br>
                                        <span class="text-xs text-gray-500">{{ $withdraw->user->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        {{ currency($withdraw->amount) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div>{{ $withdraw->bank_name }}</div>
                                        <div class="text-xs">{{ $withdraw->account_number }}</div>
                                        <div class="text-xs">{{ $withdraw->account_name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($withdraw->status === 'approved')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">{{ __('messages.approved') }}</span>
                                        @elseif($withdraw->status === 'rejected')
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">{{ __('messages.rejected') }}</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">{{ __('messages.pending') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($withdraw->processedBy)
                                            {{ $withdraw->processedBy->name }}<br>
                                            <span class="text-xs">{{ $withdraw->processed_at->format('d M Y, H:i') }}</span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.withdraws.show', $withdraw) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ $withdraw->status === 'pending' ? __('messages.review') : __('messages.view') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    {{ $withdraws->links() }}
                </div>
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600">{{ __('messages.no_withdraw_requests_found') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

