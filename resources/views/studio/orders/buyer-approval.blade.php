@extends('layouts.app')

@section('title', 'Approve Work - ' . $order->title)

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-5xl">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="text-sm">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('studio.orders.index') }}" class="text-blue-600 hover:underline">My Orders</a></li>
                    <li class="text-gray-500">/</li>
                    <li><a href="{{ route('studio.orders.show', $order) }}"
                            class="text-blue-600 hover:underline">{{ $order->title }}</a></li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-700">Review & Approve Work</li>
                </ol>
            </nav>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2 space-y-6">
                <!-- Order Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    @php
                        $currencyService = app(\App\Services\CurrencyService::class);
                        $userCurrency = $currencyService->getUserCurrency(auth()->user());
                        $budgetDisplay = currency($order->budget, $userCurrency, 'IDR');
                    @endphp
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $order->title }}</h1>
                    <div class="flex items-center gap-4 text-sm text-gray-600">
                        <span>Vendor: <strong>{{ $order->assignedVendor->name }}</strong></span>
                        <span>Budget: <strong>{{ $budgetDisplay }}</strong></span>
                    </div>
                </div>

                <!-- Vendor Submission -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">Vendor's Submission</h2>
                    <p class="text-sm text-gray-600 mb-4">Submitted on
                        {{ $submission->submitted_at->format('d M Y \a\t H:i') }} by {{ $submission->vendor->name }}</p>

                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="font-semibold text-gray-900 mb-3">Description</h3>
                        <div class="prose prose-sm max-w-none mb-6">
                            <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $submission->description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Submitted Files -->
                @if ($submission->getFileCount() > 0)
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            📎 Submitted Files ({{ $submission->getFileCount() }})
                        </h3>

                        <div class="space-y-2">
                            @foreach ($submission->files ?? [] as $index => $file)
                                <div
                                    class="flex items-center justify-between p-3 border border-gray-200 rounded hover:bg-gray-50 transition">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8 16a2 2 0 002-2V7.414L10.586 9a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L8 7.414V14a2 2 0 002 2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span class="text-sm font-medium text-gray-900">{{ basename($file) }}</span>
                                    </div>
                                    <a href="{{ $file }}" target="_blank"
                                        class="px-3 py-1 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded text-xs font-medium transition">
                                        View
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Approval/Rejection Form -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Your Decision</h3>

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="font-semibold text-red-800 mb-2">Errors:</h4>
                            <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Approve Form -->
                        <form action="{{ route('studio.orders.approve-work', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="border-l-4 border-green-400 bg-green-50 p-4 rounded">
                                <h4 class="font-semibold text-green-900 mb-2">✓ Approve Work</h4>
                                <p class="text-sm text-green-800 mb-4">The work meets the requirements and you're satisfied
                                    with it.</p>

                                <textarea name="notes" placeholder="Optional: Leave feedback or notes for the vendor..." rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm mb-3">{{ old('notes') }}</textarea>

                                <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                    ✓ Approve This Work
                                </button>
                            </div>
                        </form>

                        <div class="border-t border-gray-200 py-4"></div>

                        <!-- Reject Form -->
                        <form action="{{ route('studio.orders.reject-work', $order) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="border-l-4 border-red-400 bg-red-50 p-4 rounded">
                                <h4 class="font-semibold text-red-900 mb-2">✗ Request Revision</h4>
                                <p class="text-sm text-red-800 mb-4">The work needs revisions or doesn't meet the
                                    requirements.</p>

                                <textarea name="notes" placeholder="Please explain what needs to be changed or improved..." rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm mb-3 @error('notes') border-red-500 @enderror"
                                    required>{{ old('notes') }}</textarea>

                                @error('notes')
                                    <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                                @enderror

                                <p class="text-xs text-red-700 mb-3">✓ Be specific about what needs to be changed</p>

                                <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                                    ✗ Request Revision
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-gray-600">Budget</p>
                            <p class="font-semibold text-gray-900">{{ $budgetDisplay }}</p>
                        </div>

                        <div class="border-t border-gray-200 pt-3">
                            <p class="text-gray-600">Escrow Amount</p>
                            <p class="font-semibold text-gray-900">
                                {{ currency($order->escrow_amount, $userCurrency, 'IDR') }}</p>
                        </div>

                        <div class="border-t border-gray-200 pt-3">
                            <p class="text-gray-600">Status</p>
                            <p class="font-semibold text-blue-600">Awaiting Your Approval</p>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <h4 class="font-semibold text-blue-900 text-sm mb-2">📋 What happens next?</h4>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li>✓ After you approve, admin verifies the work</li>
                            <li>✓ Payment gets released to vendor</li>
                            <li>✓ Order marked as complete</li>
                        </ul>
                    </div>
                </div>

                <!-- Vendor Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Vendor Information</h3>

                    <div class="flex items-center gap-3 mb-4">
                        <div
                            class="w-12 h-12 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">
                            {{ substr($order->assignedVendor->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $order->assignedVendor->name }}</p>
                            <p class="text-xs text-gray-500">{{ $order->assignedVendor->email }}</p>
                        </div>
                    </div>

                    <a href="{{ route('public.profile.show', $order->assignedVendor->username) }}" target="_blank"
                        class="block text-center px-4 py-2 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded font-medium text-sm transition">
                        View Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Submission Checklist Info -->
    <div class="mt-10 bg-yellow-50 border border-yellow-200 rounded-lg p-6 max-w-5xl mx-auto">
        <h3 class="font-semibold text-yellow-900 mb-3">📝 Review Checklist Before Approving:</h3>
        <ul class="grid grid-cols-2 gap-3 text-sm text-yellow-800">
            <li class="flex items-start">
                <input type="checkbox" class="mt-1 mr-2" disabled> Does it meet your requirements?
            </li>
            <li class="flex items-start">
                <input type="checkbox" class="mt-1 mr-2" disabled> Is the quality acceptable?
            </li>
            <li class="flex items-start">
                <input type="checkbox" class="mt-1 mr-2" disabled> Did you review all files?
            </li>
            <li class="flex items-start">
                <input type="checkbox" class="mt-1 mr-2" disabled> No issues or concerns?
            </li>
        </ul>
    </div>
@endsection
