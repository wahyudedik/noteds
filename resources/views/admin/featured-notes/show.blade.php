@extends('layouts.app')

@section('title', 'Admin - Featured Note Detail')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="{{ route('admin.featured-notes.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Featured Notes</a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Note Detail</h2>

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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Note Information</h3>
                    <div class="space-y-2">
                        <p><strong>Title:</strong> 
                            <a href="{{ route('marketplace.show', $featuredNote->note) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $featuredNote->note->title }}
                            </a>
                        </p>
                        <p><strong>Summary:</strong> {{ Str::limit($featuredNote->note->summary ?? 'No summary', 100) }}</p>
                        <p><strong>Price:</strong> Rp {{ number_format($featuredNote->note->price, 0, ',', '.') }}</p>
                        <p><strong>Status:</strong> 
                            @if($featuredNote->note->is_public)
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Public</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Private</span>
                            @endif
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Seller Information</h3>
                    <div class="space-y-2">
                        <p><strong>Name:</strong> {{ $featuredNote->user->name }}</p>
                        <p><strong>Email:</strong> {{ $featuredNote->user->email }}</p>
                        <p><strong>Wallet Balance:</strong> Rp {{ number_format($featuredNote->user->wallet_balance ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Featured Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong>Location:</strong> {{ ucfirst(str_replace('_', ' ', $featuredNote->location)) }}</p>
                        <p><strong>Duration:</strong> {{ $featuredNote->duration_days }} hari</p>
                        <p><strong>Price:</strong> <span class="text-lg font-bold text-green-600">Rp {{ number_format($featuredNote->price, 0, ',', '.') }}</span></p>
                    </div>
                    <div>
                        <p><strong>Status:</strong> 
                            @if($featuredNote->status === 'pending')
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                            @elseif($featuredNote->status === 'active')
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>
                            @elseif($featuredNote->status === 'expired')
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Expired</span>
                            @else
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Cancelled</span>
                            @endif
                        </p>
                        @if($featuredNote->start_date && $featuredNote->end_date)
                            <p><strong>Start Date:</strong> {{ $featuredNote->start_date->format('d M Y, H:i') }}</p>
                            <p><strong>End Date:</strong> {{ $featuredNote->end_date->format('d M Y, H:i') }}</p>
                            @if($featuredNote->isActive())
                                <p class="text-sm text-green-600 mt-2">✓ Currently Active</p>
                            @elseif($featuredNote->end_date < now() && $featuredNote->status === 'active')
                                <p class="text-sm text-gray-600 mt-2">⚠ Expired (will be updated by cron)</p>
                            @endif
                        @else
                            <p class="text-gray-400">Dates not set yet</p>
                        @endif
                        <p><strong>Requested:</strong> {{ $featuredNote->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Analytics</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><strong>Impressions:</strong> {{ number_format($featuredNote->impressions, 0, ',', '.') }}</p>
                        <p><strong>Clicks:</strong> {{ number_format($featuredNote->clicks, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        @if($featuredNote->impressions > 0)
                            <p><strong>CTR:</strong> {{ number_format(($featuredNote->clicks / $featuredNote->impressions) * 100, 2) }}%</p>
                        @else
                            <p><strong>CTR:</strong> 0%</p>
                        @endif
                    </div>
                </div>
            </div>

            @if($featuredNote->admin_notes)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2">Admin Notes</h4>
                    <p class="text-gray-700">{{ $featuredNote->admin_notes }}</p>
                </div>
            @endif

            @if($featuredNote->status === 'pending')
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4">Process Request</h4>
                    
                    <!-- Approve Form -->
                    <form action="{{ route('admin.featured-notes.approve', $featuredNote) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-4">
                            <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                            <textarea name="admin_notes" id="approve_notes" rows="3" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Optional notes for approval..."></textarea>
                        </div>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                            Approve Featured Note
                        </button>
                    </form>

                    <!-- Reject Form -->
                    <form action="{{ route('admin.featured-notes.reject', $featuredNote) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason <span class="text-red-500">*</span></label>
                            <textarea name="admin_notes" id="reject_notes" rows="3" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="Please provide a reason for rejection..."></textarea>
                            <p class="mt-1 text-sm text-gray-500">Funds will be refunded to seller's wallet.</p>
                        </div>
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                            Reject & Refund
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

