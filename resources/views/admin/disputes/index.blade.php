@extends('layouts.app')

@section('title', 'Disputes Management')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Disputes Management</h1>

        <!-- Filter Tabs -->
        <div class="flex gap-2 mb-6 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.disputes.index', ['status' => 'all']) }}" class="px-4 py-2 font-medium text-gray-900 dark:text-white border-b-2 @if(request('status') === 'all' || !request('status')) border-blue-500 @else border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 @endif">
                All ({{ $disputes->total() }})
            </a>
            <a href="{{ route('admin.disputes.index', ['status' => 'open']) }}" class="px-4 py-2 font-medium @if(request('status') === 'open') border-b-2 border-yellow-500 text-gray-900 dark:text-white @else border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 @endif">
                Open
            </a>
            <a href="{{ route('admin.disputes.index', ['status' => 'under_review']) }}" class="px-4 py-2 font-medium @if(request('status') === 'under_review') border-b-2 border-blue-500 text-gray-900 dark:text-white @else border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 @endif">
                Under Review
            </a>
            <a href="{{ route('admin.disputes.index', ['status' => 'resolved']) }}" class="px-4 py-2 font-medium @if(request('status') === 'resolved') border-b-2 border-green-500 text-gray-900 dark:text-white @else border-b-2 border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 @endif">
                Resolved
            </a>
        </div>

        @if($disputes->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Dispute #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Filed By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Against</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Filed On</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach($disputes as $dispute)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                            #{{ $dispute->id }}
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">#{{ $dispute->service_order_id }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ Str::limit($dispute->serviceOrder->title, 30) }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <img src="{{ $dispute->initiator->profile_photo_url }}" alt="{{ $dispute->initiator->name }}" class="w-8 h-8 rounded-full">
                                <span class="text-gray-900 dark:text-white">{{ $dispute->initiator->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                @php
                                $otherParty = $dispute->initiator->id === $dispute->serviceOrder->buyer_id 
                                    ? $dispute->serviceOrder->vendor 
                                    : $dispute->serviceOrder->buyer;
                                @endphp
                                <img src="{{ $otherParty->profile_photo_url }}" alt="{{ $otherParty->name }}" class="w-8 h-8 rounded-full">
                                <span class="text-gray-900 dark:text-white">{{ $otherParty->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                            {{ $dispute->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-sm font-semibold text-white
                                @if($dispute->isOpen()) bg-yellow-500
                                @elseif($dispute->isUnderReview()) bg-blue-500
                                @else bg-green-500
                                @endif
                            ">
                                {{ $dispute->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                Review →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-600">
                {{ $disputes->links() }}
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
            <p class="text-gray-600 dark:text-gray-400">No disputes found.</p>
        </div>
        @endif
    </div>
</div>
@endsection
