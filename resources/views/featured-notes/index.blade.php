@extends('layouts.app')

@section('title', __('messages.my_featured_notes'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.my_featured_notes') }}</h1>
                <p class="mt-2 text-base text-gray-600">{{ __('messages.manage_featured_notes') }}</p>
            </div>
            <a href="{{ route('featured-notes.create') }}" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                + {{ __('messages.request_featured') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Analytics Dashboard -->
        @if(isset($analytics))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Impressions</div>
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($analytics['total_impressions'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Clicks</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($analytics['total_clicks'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Average CTR</div>
                    <div class="text-3xl font-bold text-purple-600">{{ $analytics['avg_ctr'] }}%</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Total Spent</div>
                    <div class="text-3xl font-bold text-red-600">Rp {{ number_format($analytics['total_spent'], 0, ',', '.') }}</div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Active Featured</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $analytics['active_count'] }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">Revenue from Featured</div>
                    <div class="text-2xl font-bold text-green-600">Rp {{ number_format($analytics['revenue_from_featured'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1">ROI</div>
                    <div class="text-2xl font-bold {{ $analytics['roi'] >= 100 ? 'text-green-600' : ($analytics['roi'] > 0 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $analytics['roi'] }}%
                    </div>
                </div>
            </div>
        @endif

        @if($featuredNotes->count() > 0)
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Durasi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Analytics</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($featuredNotes as $featured)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('marketplace.show', $featured->note) }}" class="text-blue-600 hover:text-blue-800">
                                        {{ Str::limit($featured->note->title, 40) }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ ucfirst(str_replace('_', ' ', $featured->location)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $featured->duration_days }} hari
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Rp {{ number_format($featured->price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($featured->status === 'pending')
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                    @elseif($featured->status === 'active')
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Active</span>
                                    @elseif($featured->status === 'expired')
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">Expired</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Cancelled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    @if($featured->status === 'active' || $featured->status === 'expired')
                                        <div class="space-y-1">
                                            <div>👁️ {{ number_format($featured->impressions, 0, ',', '.') }}</div>
                                            <div>👆 {{ number_format($featured->clicks, 0, ',', '.') }}</div>
                                            @if($featured->impressions > 0)
                                                <div class="text-xs text-gray-500">CTR: {{ number_format(($featured->clicks / $featured->impressions) * 100, 2) }}%</div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $featured->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $featuredNotes->links() }}
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
                <p class="text-gray-500 mb-4">Anda belum memiliki featured note request.</p>
                <a href="{{ route('featured-notes.create') }}" class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                    Request Featured Note
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

