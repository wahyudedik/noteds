@extends('40-shared.layouts.app')

@section('title', 'Purchase History')

@section('content')
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Purchase History</h1>
                <p class="text-gray-600">List of your note purchases.</p>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-5 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">All Purchases</h2>
                </div>
                <div class="p-5">
                    <p class="text-gray-600">No purchases found.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
