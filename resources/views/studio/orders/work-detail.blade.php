@extends('layouts.app')

@section('title', 'Work Detail - ' . $order->title)

@section('content')
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <nav class="text-sm">
                <ol class="flex items-center space-x-2">
                    <li><a href="{{ route('studio.orders.index') }}" class="text-blue-600 hover:underline">My Orders</a></li>
                    <li class="text-gray-500">/</li>
                    <li><a href="{{ route('studio.orders.show', $order) }}"
                            class="text-blue-600 hover:underline">{{ $order->title }}</a></li>
                    <li class="text-gray-500">/</li>
                    <li class="text-gray-700">Work Detail</li>
                </ol>
            </nav>
        </div>

        <!-- Work Submission Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Work Submission</h1>
                    <p class="text-gray-600">Order: {{ $order->title }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-1">Submitted:</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $submission->submitted_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            <!-- Status Badge -->
            <div class="flex items-center gap-2">
                @if ($submission->status === 'submitted')
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        ⏳ Awaiting Buyer Review
                    </span>
                @elseif($submission->status === 'approved')
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        ✓ Approved by Buyer
                    </span>
                @elseif($submission->status === 'rejected')
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        ✗ Rejected - Revision Needed
                    </span>
                @endif
            </div>
        </div>

        <!-- Submission Description -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Submission Description</h2>
            <div class="prose prose-sm max-w-none">
                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $submission->description }}</p>
            </div>
        </div>

        <!-- Submitted Files -->
        @if ($submission->getFileCount() > 0)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">
                    📎 Submitted Files ({{ $submission->getFileCount() }})
                </h2>

                <div class="space-y-3">
                    @foreach ($submission->files ?? [] as $index => $file)
                        <div
                            class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8 16a2 2 0 002-2V7.414L10.586 9a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L8 7.414V14a2 2 0 002 2z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium text-gray-900">{{ basename($file) }}</span>
                            </div>
                            <a href="{{ $file }}" target="_blank"
                                class="px-3 py-1 bg-blue-100 text-blue-600 hover:bg-blue-200 rounded text-sm font-medium transition">
                                Download
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Approval Timeline</h2>

            <div class="space-y-6">
                <!-- Step 1: Submitted -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold">
                            1
                        </div>
                        <div class="w-1 h-12 bg-gray-200 mt-2"></div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Work Submitted</h3>
                        <p class="text-sm text-gray-600">{{ $submission->submitted_at->format('d M Y \a\t H:i') }}</p>
                        <p class="text-sm text-gray-500 mt-1">Submitted by: {{ auth()->user()->name }}</p>
                    </div>
                </div>

                <!-- Step 2: Buyer Approval -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-10 h-10 rounded-full {{ $submission->status !== 'submitted' ? 'bg-green-600' : 'bg-gray-300' }} text-white flex items-center justify-center font-bold">
                            2
                        </div>
                        <div class="w-1 h-12 bg-gray-200 mt-2"></div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Buyer Approval</h3>
                        @if ($submission->status === 'submitted')
                            <p class="text-sm text-gray-600">⏳ Awaiting buyer review...</p>
                        @elseif($submission->status === 'approved')
                            <p class="text-sm text-gray-600">✓ Approved on
                                {{ $order->buyer_approved_at->format('d M Y \a\t H:i') }}</p>
                            @if ($order->buyer_approval_notes)
                                <p class="text-sm text-gray-500 mt-2"><strong>Buyer's Note:</strong>
                                    {{ $order->buyer_approval_notes }}</p>
                            @endif
                        @elseif($submission->status === 'rejected')
                            <p class="text-sm text-red-600">✗ Rejected - Revision needed</p>
                            @if ($order->buyer_approval_notes)
                                <p class="text-sm text-gray-500 mt-2"><strong>Feedback:</strong>
                                    {{ $order->buyer_approval_notes }}</p>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Step 3: Admin Verification -->
                <div class="flex gap-4">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-10 h-10 rounded-full {{ $order->admin_verified_at ? 'bg-green-600' : 'bg-gray-300' }} text-white flex items-center justify-center font-bold">
                            3
                        </div>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Admin Verification & Payment</h3>
                        @if (!$order->admin_verified_at && $submission->status === 'approved')
                            <p class="text-sm text-gray-600">⏳ Awaiting admin verification...</p>
                        @elseif($order->admin_verified_at)
                            <p class="text-sm text-gray-600">✓ Verified on
                                {{ $order->admin_verified_at->format('d M Y \a\t H:i') }}</p>
                            @if ($order->admin_verification_notes)
                                <p class="text-sm text-gray-500 mt-2"><strong>Admin's Note:</strong>
                                    {{ $order->admin_verification_notes }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-500">Pending completion of buyer approval</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Box -->
        @if ($submission->status === 'submitted')
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-900 mb-2">⏳ What Happens Next?</h3>
                <ol class="text-sm text-blue-800 space-y-1">
                    <li>1. The buyer will review your submitted work</li>
                    <li>2. They can approve or request revisions</li>
                    <li>3. If approved, an admin will verify the work</li>
                    <li>4. After verification, payment will be released to your wallet</li>
                </ol>
            </div>
        @elseif($submission->status === 'rejected')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-red-900 mb-2">📝 Revision Needed</h3>
                <p class="text-sm text-red-800 mb-3">The buyer has requested changes to your work. Please review their
                    feedback above and resubmit.</p>
                <a href="{{ route('studio.orders.submit-work.create', $order) }}"
                    class="inline-block px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded font-medium text-sm transition">
                    Resubmit Work
                </a>
            </div>
        @elseif($submission->status === 'approved' && !$order->admin_verified_at)
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-900 mb-2">⏳ Awaiting Admin Verification</h3>
                <p class="text-sm text-yellow-800">Your work has been approved by the buyer. Admin will now verify and
                    release payment soon.</p>
            </div>
        @elseif($order->admin_verified_at)
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-900 mb-2">✓ Payment Released!</h3>
                <p class="text-sm text-green-800">Your work has been verified and payment has been released to your wallet.
                </p>
            </div>
        @endif

        <!-- Back Button -->
        <div class="flex gap-4">
            <a href="{{ route('studio.orders.show', $order) }}"
                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                ← Back to Order
            </a>
        </div>
    </div>
@endsection
