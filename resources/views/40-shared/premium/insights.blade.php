@extends('40-shared/layouts/app')

@section('title', 'Premium Insights - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gradient-to-b from-purple-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Header -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Premium Insights</h1>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Get advanced analytics, detailed reports, and insights to
                    grow your business faster.</p>
            </div>

            <!-- Premium Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                <!-- Advanced Analytics -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Advanced Analytics</h3>
                    <p class="text-gray-600 mb-4">Deep dive into your sales, traffic, and user behavior with comprehensive
                        reports.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>✓ Real-time performance metrics</li>
                        <li>✓ Custom report generation</li>
                        <li>✓ Data export capabilities</li>
                    </ul>
                </div>

                <!-- Revenue Insights -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Revenue Intelligence</h3>
                    <p class="text-gray-600 mb-4">Maximize earnings with predictive analytics and optimization
                        recommendations.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>✓ Revenue forecasting</li>
                        <li>✓ Pricing optimization</li>
                        <li>✓ Conversion analysis</li>
                    </ul>
                </div>

                <!-- Competitor Intelligence -->
                <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Market Intelligence</h3>
                    <p class="text-gray-600 mb-4">Stay ahead of the competition with market trends and competitor
                        benchmarking.</p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li>✓ Competitor monitoring</li>
                        <li>✓ Trend analysis</li>
                        <li>✓ Market benchmarking</li>
                    </ul>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg shadow-lg p-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to unlock insights?</h2>
                <p class="text-purple-100 text-lg mb-6">Subscribe to Premium and get instant access to all advanced
                    features.</p>
                @auth
                    <a href="{{ route('subscriptions.plans') }}"
                        class="inline-block bg-white text-purple-600 font-semibold py-3 px-8 rounded-lg hover:shadow-lg transition-shadow">
                        View Plans
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-block bg-white text-purple-600 font-semibold py-3 px-8 rounded-lg hover:shadow-lg transition-shadow">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </div>
@endsection
