@extends('layouts.app')

@section('title', 'Admin - ' . __('messages.featured_notes'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.featured_notes_management') }}</h2>
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

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                <div class="text-sm text-gray-600">Total</div>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-yellow-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-yellow-400">
                <div class="text-sm text-gray-600">Pending</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
            </div>
            <div class="bg-green-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-green-400">
                <div class="text-sm text-gray-600">Active</div>
                <div class="text-2xl font-bold text-green-600">{{ $stats['active'] }}</div>
            </div>
            <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                <div class="text-sm text-gray-600">Expired</div>
                <div class="text-2xl font-bold text-gray-600">{{ $stats['expired'] }}</div>
            </div>
            <div class="bg-blue-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-blue-400">
                <div class="text-sm text-gray-600">Revenue</div>
                <div class="text-2xl font-bold text-blue-600">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="{{ route('admin.featured-notes.index') }}" class="flex gap-4">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <select name="location" class="rounded-md border-gray-300 shadow-sm">
                    <option value="">All Locations</option>
                    <option value="marketplace_grid" {{ request('location') === 'marketplace_grid' ? 'selected' : '' }}>Marketplace Grid</option>
                    <option value="marketplace_banner" {{ request('location') === 'marketplace_banner' ? 'selected' : '' }}>Marketplace Banner</option>
                    <option value="landing_hero" {{ request('location') === 'landing_hero' ? 'selected' : '' }}>Landing Hero</option>
                    <option value="landing_carousel" {{ request('location') === 'landing_carousel' ? 'selected' : '' }}>Landing Carousel</option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Filter
                </button>
                @if(request()->hasAny(['status', 'location']))
                    <a href="{{ route('admin.featured-notes.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        @if($featuredNotes->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($featuredNotes as $featured)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('marketplace.show', $featured->note) }}" class="text-blue-600 hover:text-blue-800">
                                            {{ Str::limit($featured->note->title, 40) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $featured->user->name }}<br>
                                        <span class="text-xs text-gray-500">{{ $featured->user->email }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $featured->location)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $featured->duration_days }} hari
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        Rp {{ number_format($featured->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($featured->status === 'pending')
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">Pending</span>
                                        @elseif($featured->status === 'active')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">Active</span>
                                        @elseif($featured->status === 'expired')
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Expired</span>
                                        @else
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Cancelled</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($featured->start_date && $featured->end_date)
                                            {{ $featured->start_date->format('d M Y') }}<br>
                                            <span class="text-xs">to {{ $featured->end_date->format('d M Y') }}</span>
                                        @else
                                            <span class="text-gray-400">Not set</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.featured-notes.show', $featured) }}" class="text-blue-600 hover:text-blue-800">
                                            View →
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $featuredNotes->links() }}
            </div>
        @else
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                <p class="text-gray-500">{{ __('messages.no_featured_notes_found') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

