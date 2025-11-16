@extends('layouts.app')

@section('title', 'View History')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">View History & Revenue</h2>
            <div class="flex gap-2">
                <a href="{{ route('admin.view-history.export', request()->all()) }}" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Export CSV
                </a>
                <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to Dashboard</a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">Total Views</div>
                <div class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_views']) }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">Valid Views</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['valid_views']) }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">Total Revenue</div>
                <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['total_revenue'], 2, ',', '.') }}</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500">Today Revenue</div>
                <div class="text-2xl font-bold text-purple-600">Rp {{ number_format($stats['today_revenue'], 2, ',', '.') }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ number_format($stats['today_views']) }} views today</div>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-sm font-medium text-yellow-800">Pending Validation</div>
                <div class="text-xl font-bold text-yellow-900">{{ number_format($stats['pending_views']) }}</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-sm font-medium text-red-800">Rejected Views</div>
                <div class="text-xl font-bold text-red-900">{{ number_format($stats['rejected_views']) }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.view-history.index') }}" class="flex gap-4 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by note title, user, or IP..."
                    class="flex-1 min-w-[200px] rounded-md border-gray-300 shadow-sm">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                <select name="valid" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('valid') === '1' ? 'selected' : '' }}>Valid Only</option>
                    <option value="0" {{ request('valid') === '0' ? 'selected' : '' }}>Invalid Only</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 shadow-sm">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border-gray-300 shadow-sm">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'valid', 'date_from', 'date_to']))
                    <a href="{{ route('admin.view-history.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        @if($viewRevenues->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viewed At</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($viewRevenues as $viewRevenue)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $viewRevenue->note->title ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">Owner: {{ $viewRevenue->note->user->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($viewRevenue->user)
                                            <div class="text-sm text-gray-900">{{ $viewRevenue->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $viewRevenue->user->email }}</div>
                                        @else
                                            <span class="text-sm text-gray-500">Guest</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp {{ number_format($viewRevenue->amount, 2, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $viewRevenue->ip_address }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                {{ $viewRevenue->validation_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($viewRevenue->validation_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($viewRevenue->validation_status) }}
                                            </span>
                                            @if(!$viewRevenue->is_valid)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Invalid
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $viewRevenue->viewed_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.view-history.show', $viewRevenue) }}" class="text-blue-600 hover:text-blue-900">View Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $viewRevenues->links() }}
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg p-8 text-center">
                <p class="text-gray-500">No view history found.</p>
            </div>
        @endif
    </div>
</div>
@endsection

