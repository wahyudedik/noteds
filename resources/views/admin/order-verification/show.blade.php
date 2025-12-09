@extends('layouts.app')

@section('title', 'Verify Order - ' . $order->title)

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="text-sm">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:underline">Dashboard</a></li>
                    <li class="text-gray-500">/</li>
                    <li><a href="{{ route('admin.order-verification.index') }}" class="text-blue-600 hover:underline">Order
                            Verification</a></li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-700">{{ $order->title }}</li>
                </ol>
            </nav>
        </div>

        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-8 text-white mb-8">
            <h1 class="text-3xl font-bold mb-2">{{ $order->title }}</h1>
            <p class="text-blue-100">Order ID: {{ $order->id }}</p>
        </div>

        <div class="grid grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="col-span-2 space-y-6">
                <!-- Order Details -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Details</h2>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-600 mb-1">Buyer</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $order->user->name }}</p>
                            <p class="text-sm text-gray-500">{{ $order->user->email }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Vendor</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $order->assignedVendor->name }}</p>
                            <p class="text-sm text-gray-500">{{ $order->assignedVendor->email }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Budget</p>
                            <p class="text-lg font-semibold text-gray-900">Rp
                                {{ number_format($order->budget, 0, ',', '.') }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600 mb-1">Status</p>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Work Submission -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Work Submission</h2>

                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Submitted by:
                            <strong>{{ $submission->vendor->name }}</strong></p>
                        <p class="text-sm text-gray-600 mb-2">Submitted on:
                            <strong>{{ $submission->submitted_at->format('d M Y H:i') }}</strong></p>
                        <p class="text-sm text-gray-600 mb-2">Status: <strong>{{ $submission->getStatusLabel() }}</strong>
                        </p>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Description</h3>
                        <div class="prose prose-sm max-w-none">
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
                            @foreach ($submission->files ?? [] as $file)
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
                                        Download
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Approval Timeline -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Timeline</h2>

                    <div class="space-y-6">
                        @foreach ($approvalLogs as $log)
                            <div class="flex gap-4">
                                <div class="flex flex-col items-center">
                                    <div
                                        class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6.707 6.707a1 1 0 010 1.414L5.414 9.414a1 1 0 01-1.414-1.414l1.293-1.293a1 1 0 011.414 0zm2.828 0a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zm2.828 0a1 1 0 010 1.414l-2.121 2.121a1 1 0 01-1.414-1.414l2.121-2.121a1 1 0 011.414 0zM9 11a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2zM9 15a1 1 0 100-2 1 1 0 000 2zm7 0a1 1 0 100-2 1 1 0 000 2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $log->getActionLabel() }}</h3>
                                    <p class="text-sm text-gray-600">{{ $log->action_at->format('d M Y H:i') }} by
                                        {{ $log->approver->name }} ({{ $log->getApproverTypeLabel() }})</p>
                                    @if ($log->notes)
                                        <p class="text-sm text-gray-500 mt-2">{{ $log->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Verify or Reject -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Admin Decision</h2>

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                            <h3 class="font-semibold text-red-800 mb-2">Errors:</h3>
                            <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-4">
                        <!-- Verify Form -->
                        <form action="{{ route('admin.order-verification.verify', $order) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <div class="border-l-4 border-green-400 bg-green-50 p-4 rounded">
                                <h4 class="font-semibold text-green-900 mb-2">✓ Verify & Release Payment</h4>
                                <p class="text-sm text-green-800 mb-4">Work looks good. Release escrow to vendor and take
                                    platform fee.</p>

                                <textarea name="notes" placeholder="Add verification notes (required)..." rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm mb-3"
                                    required>{{ old('notes') }}</textarea>

                                <button type="submit"
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                    ✓ Verify & Release Payment
                                </button>
                            </div>
                        </form>

                        <div class="border-t border-gray-200 py-4"></div>

                        <!-- Reject Form -->
                        <form action="{{ route('admin.order-verification.reject', $order) }}" method="POST"
                            class="space-y-4">
                            @csrf
                            <div class="border-l-4 border-red-400 bg-red-50 p-4 rounded">
                                <h4 class="font-semibold text-red-900 mb-2">✗ Reject & Refund Escrow</h4>
                                <p class="text-sm text-red-800 mb-4">Work does not meet standards. Refund escrow to buyer.
                                </p>

                                <textarea name="notes" placeholder="Explain why you're rejecting this work (required)..." rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm mb-3"
                                    required>{{ old('notes') }}</textarea>

                                <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                                    ✗ Reject & Refund Escrow
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Payment Breakdown -->
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Breakdown</h3>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <p class="text-gray-600">Escrow Amount</p>
                            <p class="font-semibold text-gray-900">Rp
                                {{ number_format($breakdown['escrow_amount'], 0, ',', '.') }}</p>
                        </div>

                        <div class="flex justify-between">
                            <p class="text-gray-600">Platform Fee ({{ $breakdown['platform_fee_percent'] }}%)</p>
                            <p class="font-semibold text-red-600">-Rp
                                {{ number_format($breakdown['platform_fee'], 0, ',', '.') }}</p>
                        </div>

                        <div class="border-t border-gray-200 pt-3 flex justify-between">
                            <p class="font-semibold text-gray-900">Vendor Receives</p>
                            <p class="text-lg font-bold text-green-600">Rp
                                {{ number_format($breakdown['vendor_net'], 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <!-- Admin Receives -->
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded">
                        <p class="text-sm text-gray-600 mb-1">Admin Receives (Fee)</p>
                        <p class="text-xl font-bold text-blue-600">Rp
                            {{ number_format($breakdown['platform_fee'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Work Quality Checklist -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quality Checklist</h3>

                    <div class="space-y-2 text-sm">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300">
                            <span class="text-gray-700">Meets order requirements</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300">
                            <span class="text-gray-700">Good quality & professional</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300">
                            <span class="text-gray-700">No plagiarism/copyright issues</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300">
                            <span class="text-gray-700">Files are working properly</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" class="w-4 h-4 rounded border-gray-300">
                            <span class="text-gray-700">No malicious content</span>
                        </label>
                    </div>
                </div>

                <!-- Approval Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-semibold text-blue-900 mb-2">ℹ️ Approval Details</h4>
                    <div class="space-y-2 text-sm text-blue-800">
                        <p><strong>Buyer Approved:</strong> {{ $order->buyer_approved_at->format('d M Y H:i') }}</p>
                        @if ($order->buyer_approval_notes)
                            <p><strong>Buyer Notes:</strong> {{ $order->buyer_approval_notes }}</p>
                        @endif
                    </div>
                </div>

                <!-- Back Button -->
                <a href="{{ route('admin.order-verification.index') }}"
                    class="block w-full text-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>
@endsection
