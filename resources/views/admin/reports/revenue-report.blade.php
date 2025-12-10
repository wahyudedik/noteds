@extends('admin.layouts.app')

@section('title', 'Revenue Report')
@section('header', 'Revenue Report & Analytics')

@section('content')
<div class="space-y-6">
    <!-- Report Period Selector -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" id="start_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" id="end_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                <select id="report_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="revenue">Revenue Report</option>
                    <option value="users">User Report</option>
                    <option value="notes">Note Performance</option>
                    <option value="affiliate">Affiliate Report</option>
                </select>
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Generate Report</button>
            </div>
            <div class="flex items-end">
                <button class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Export PDF</button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-sm font-medium text-blue-900">Total Revenue</p>
            <p class="text-3xl font-bold text-blue-900 mt-2">Rp 125.5M</p>
            <p class="text-xs text-blue-700 mt-2">↑ 12% from last period</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-sm font-medium text-green-900">Commission Revenue</p>
            <p class="text-3xl font-bold text-green-900 mt-2">Rp 25.1M</p>
            <p class="text-xs text-green-700 mt-2">20% of total</p>
        </div>
        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-sm font-medium text-purple-900">Total Transactions</p>
            <p class="text-3xl font-bold text-purple-900 mt-2">2,534</p>
            <p class="text-xs text-purple-700 mt-2">Average: Rp 49.4K</p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-sm font-medium text-orange-900">Avg Order Value</p>
            <p class="text-3xl font-bold text-orange-900 mt-2">Rp 49.4K</p>
            <p class="text-xs text-orange-700 mt-2">↑ 5% from last period</p>
        </div>
    </div>

    <!-- Revenue Breakdown Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue by Day (Line Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend (Last 30 Days)</h2>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Revenue Sources (Pie Chart) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Source</h2>
            <div style="height: 300px;">
                <canvas id="sourceChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Revenue Details Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Daily Revenue Breakdown</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Transactions</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Total Sales</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Commission</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Net Revenue</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Avg Sale</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 0; $i < 30; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::now()->subDays($i)->format('d M Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ rand(50, 150) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Rp {{ number_format(rand(2000000, 8000000), 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">Rp {{ number_format(rand(400000, 1600000), 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600 text-right">Rp {{ number_format(rand(1600000, 6400000), 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">Rp {{ number_format(rand(40000, 60000), 0, ',', '.') }}</td>
                </tr>
                @endfor
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">TOTAL</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">2,534</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Rp 125.5M</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-green-600">Rp 25.1M</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-blue-600">Rp 100.4M</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Rp 49.4K</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Top Performing Notes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Top 20 Performing Notes</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Note Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Seller</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Sales</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Revenue</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase tracking-wider">Avg Rating</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @for($i = 0; $i < 20; $i++)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Note Title {{ $i + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Seller {{ $i + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ rand(10, 200) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">Rp {{ number_format(rand(500000, 5000000), 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 text-right">{{ rand(35, 50) / 10 }}</td>
                </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: ['Day 1', 'Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Day 30'],
        datasets: [{
            label: 'Revenue (Rp)',
            data: [2500000, 3200000, 2800000, 4100000, 5200000, 4800000, 6300000],
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 5,
            pointHoverRadius: 7,
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: true, position: 'top' }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                    }
                }
            }
        }
    }
});

// Source Chart
const sourceCtx = document.getElementById('sourceChart').getContext('2d');
new Chart(sourceCtx, {
    type: 'doughnut',
    data: {
        labels: ['Note Sales', 'Commission', 'Topup', 'Referral'],
        datasets: [{
            data: [60, 20, 12, 8],
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});
</script>
@endpush

@endsection
