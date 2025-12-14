@extends('40-shared.layouts.app')

@section('title', 'Buyer Analytics')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Buyer Analytics</h1>
                <p class="text-gray-600">Overview of your purchases and activity.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-700">Total Purchases</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-700">Total Spent</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">—</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <h3 class="text-sm font-semibold text-gray-700">Average Rating Given</h3>
                    <p class="mt-2 text-3xl font-bold text-gray-900">—</p>
                </div>
            </div>

            <div class="mt-8 bg-white rounded-lg shadow p-5">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Purchases</h2>
                    <a href="{{ route('buyer-analytics.purchase-history') }}" class="text-blue-600 hover:text-blue-800">View
                        All</a>
                </div>
                <p class="mt-4 text-gray-600">No data available yet.</p>
            </div>
        </div>
    </div>
@endsection
