@extends('layouts.app')

@section('title', 'Seller Analytics')

@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Seller Analytics Dashboard</h1>

                <!-- Time Range Selector -->
                <form method="GET" action="{{ route('seller-analytics.index') }}" class="flex items-center gap-4">
                    <select name="time_range" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="7days" {{ $timeRange === '7days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30days" {{ $timeRange === '30days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90days" {{ $timeRange === '90days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="6months" {{ $timeRange === '6months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="1year" {{ $timeRange === '1year' ? 'selected' : '' }}>Last Year</option>
                        <option value="all" {{ $timeRange === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>

                    <select name="group_by" onchange="this.form.submit()"
                        class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="day" {{ request('group_by', 'day') === 'day' ? 'selected' : '' }}>By Day</option>
                        <option value="week" {{ request('group_by') === 'week' ? 'selected' : '' }}>By Week</option>
                        <option value="month" {{ request('group_by') === 'month' ? 'selected' : '' }}>By Month</option>
                    </select>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">{{ currency($stats['total_revenue']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Views</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_views']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Purchases</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_purchases']) }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['conversion_rate'], 2) }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Revenue Chart</h2>
                <canvas id="revenueChart" height="100"></canvas>
            </div>

            <!-- Conversion Rate Tracking -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Conversion Rate Analysis</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-600">Overall Conversion Rate</p>
                            <p class="text-3xl font-bold text-gray-900">
                                {{ number_format($conversionData['overall_conversion_rate'], 2) }}%</p>
                            <p class="text-xs text-gray-500 mt-1">{{ number_format($conversionData['total_views']) }} views
                                → {{ number_format($conversionData['total_purchases']) }} purchases</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Top Performing Notes</h3>
                        <div class="space-y-3">
                            @forelse($conversionData['by_note']->take(5) as $note)
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ \Illuminate\Support\Str::limit($note['note_title'], 40) }}</p>
                                        <p class="text-xs text-gray-600">{{ $note['views'] }} views,
                                            {{ $note['purchases'] }} purchases</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-green-600">
                                            {{ number_format($note['conversion_rate'], 2) }}%</p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-600">No data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traffic Sources -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Traffic Sources</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <canvas id="trafficSourceChart" height="100"></canvas>
                    </div>
                    <div class="space-y-4">
                        @foreach ($trafficSources as $source)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-gray-900 capitalize">
                                        {{ $source['source'] ?: 'Unknown' }}</p>
                                    <p class="text-xs text-gray-600">{{ number_format($source['views']) }} views</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ number_format($source['purchases']) }} purchases</p>
                                    <p class="text-xs text-green-600">{{ number_format($source['conversion_rate'], 2) }}%
                                        conversion</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Geographic Analytics -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Geographic Analytics</h2>
                <div class="space-y-4">
                    @foreach (array_slice($geographicData, 0, 10) as $geo)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-gray-900">{{ $geo['country_name'] }}
                                    ({{ $geo['country_code'] }})</p>
                                <p class="text-xs text-gray-600">{{ number_format($geo['views']) }} views</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900">{{ number_format($geo['purchases']) }}
                                    purchases</p>
                                <p class="text-xs text-green-600">{{ number_format($geo['conversion_rate'], 2) }}%
                                    conversion</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Peak Hours Analysis -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Peak Hours Analysis</h2>
                <canvas id="peakHoursChart" height="80"></canvas>
            </div>

            <!-- A/B Testing -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">A/B Tests</h2>
                    <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Create New Test</a>
                </div>
                @if ($abTests->count() > 0)
                    <div class="space-y-4">
                        @foreach ($abTests as $test)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between mb-2">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $test->note->title ?? 'Unknown Note' }}</h3>
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full {{ $test->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($test->status) }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mb-3">Testing: {{ ucfirst($test->test_type) }}</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-xs text-gray-600">Variant A</p>
                                        <p class="text-sm font-semibold">
                                            {{ number_format($test->variant_a_conversion_rate ?? 0, 2) }}%</p>
                                        <p class="text-xs text-gray-500">{{ $test->variant_a_views }} views,
                                            {{ $test->variant_a_purchases }} purchases</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-600">Variant B</p>
                                        <p class="text-sm font-semibold">
                                            {{ number_format($test->variant_b_conversion_rate ?? 0, 2) }}%</p>
                                        <p class="text-xs text-gray-500">{{ $test->variant_b_views }} views,
                                            {{ $test->variant_b_purchases }} purchases</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-600 py-8">
                        <p class="font-medium">No A/B tests yet</p>
                        <p class="text-sm mt-1">Create your first A/B test to optimize your note titles and descriptions
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: @json($revenueData['labels']),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($revenueData['data']),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    const currency = '{{ auth()->user()->currency ?? 'IDR' }}';
                                    const symbols = {
                                        'USD': '$',
                                        'IDR': 'Rp ',
                                        'AED': 'د.إ ',
                                        'SAR': '﷼ '
                                    };
                                    return (symbols[currency] || 'Rp ') + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // Traffic Source Chart
            const trafficSourceData = @json($trafficSources);
            const trafficSourceCtx = document.getElementById('trafficSourceChart').getContext('2d');
            new Chart(trafficSourceCtx, {
                type: 'doughnut',
                data: {
                    labels: trafficSourceData.map(s => s.source.charAt(0).toUpperCase() + s.source.slice(1)),
                    datasets: [{
                        data: trafficSourceData.map(s => s.views),
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(34, 197, 94)',
                            'rgb(168, 85, 247)',
                            'rgb(251, 146, 60)',
                            'rgb(236, 72, 153)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });

            // Peak Hours Chart
            const peakHoursData = @json($peakHours);
            const peakHoursCtx = document.getElementById('peakHoursChart').getContext('2d');
            new Chart(peakHoursCtx, {
                type: 'bar',
                data: {
                    labels: peakHoursData.map(h => h.hour + ':00'),
                    datasets: [{
                        label: 'Views',
                        data: peakHoursData.map(h => h.views),
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    }, {
                        label: 'Purchases',
                        data: peakHoursData.map(h => h.purchases),
                        backgroundColor: 'rgba(34, 197, 94, 0.5)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
