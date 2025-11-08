@extends('layouts.app')

@section('title', __('featured.ab_testing_title'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('featured.ab_testing_title') }}</h2>
            <div class="flex gap-4">
                <a href="{{ route('admin.featured-notes.index') }}" class="text-blue-600 hover:text-blue-800">← {{ __('messages.back_to_featured_notes') }}</a>
                <a href="{{ route('admin.dashboard') }}" class="text-gray-600 hover:text-gray-800">{{ __('messages.admin_dashboard') }}</a>
            </div>
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

        @if(count($analytics) > 0)
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('featured.ab_testing_table_title') }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ __('featured.ab_testing_description') }}</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.ab_testing_variant') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.ab_testing_location') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.ab_testing_requests') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.impressions_label') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.clicks_label') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.ctr_label') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('featured.ab_testing_total_spent') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($analytics as $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                            {{ $data['variant'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ __('featured.locations.' . $data['location']) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $data['count'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($data['impressions'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($data['clicks'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($data['ctr'] > 5)
                                            <span class="text-green-600 font-semibold">{{ number_format($data['ctr'], 2) }}%</span>
                                        @elseif($data['ctr'] > 2)
                                            <span class="text-yellow-600 font-semibold">{{ number_format($data['ctr'], 2) }}%</span>
                                        @else
                                            <span class="text-red-600 font-semibold">{{ number_format($data['ctr'], 2) }}%</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ currency($data['total_spent']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">{{ __('featured.ab_testing_total_variants') }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ count(array_unique(array_column($analytics, 'variant'))) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">{{ __('featured.ab_testing_total_impressions') }}</div>
                    <div class="text-2xl font-bold text-blue-600">{{ number_format(array_sum(array_column($analytics, 'impressions')), 0, ',', '.') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">{{ __('featured.ab_testing_total_clicks') }}</div>
                    <div class="text-2xl font-bold text-green-600">{{ number_format(array_sum(array_column($analytics, 'clicks')), 0, ',', '.') }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600">{{ __('featured.ab_testing_average_ctr') }}</div>
                    @php
                        $totalImpressions = array_sum(array_column($analytics, 'impressions'));
                        $totalClicks = array_sum(array_column($analytics, 'clicks'));
                        $avgCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions * 100) : 0;
                    @endphp
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($avgCTR, 2) }}%</div>
                </div>
            </div>
        @else
            <div class="bg-white shadow-sm rounded-lg p-8 text-center">
                <div class="text-gray-400 mb-4">
                    <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('featured.ab_testing_no_data_title') }}</h3>
                <p class="text-gray-600 mb-4">{{ __('featured.ab_testing_no_data_message') }}</p>
                <a href="{{ route('admin.featured-notes.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('featured.ab_testing_view_all') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

