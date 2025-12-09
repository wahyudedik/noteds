@extends('layouts.app')

@section('title', "Dispute Resolution - ##{$dispute->id}")

@section('content')
<div class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">Dispute Resolution</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Dispute Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Dispute Header -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Dispute #{{ $dispute->id }}</h2>
                            <p class="text-gray-600 dark:text-gray-400">Order #{{ $dispute->service_order_id }} - {{ $dispute->serviceOrder->title }}</p>
                        </div>
                        <span class="px-4 py-2 rounded-lg font-semibold text-white
                            @if($dispute->isOpen()) bg-yellow-500
                            @elseif($dispute->isUnderReview()) bg-blue-500
                            @else bg-green-500
                            @endif
                        ">
                            {{ $dispute->getStatusLabel() }}
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Filed By</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $dispute->initiator->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $dispute->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Against</p>
                            @php
                            $otherParty = $dispute->initiator->id === $dispute->serviceOrder->buyer_id 
                                ? $dispute->serviceOrder->vendor 
                                : $dispute->serviceOrder->buyer;
                            @endphp
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $otherParty->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $otherParty->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Order Amount</p>
                            <p class="font-semibold text-gray-900 dark:text-white">${{ number_format($dispute->serviceOrder->total_amount, 2) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">{{ $dispute->serviceOrder->currency }}</p>
                        </div>
                    </div>
                </div>

                <!-- Dispute Reason -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Reason</h3>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($dispute->reason)) !!}
                    </div>
                </div>

                <!-- Evidence Files -->
                @if($dispute->evidence->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Evidence ({{ $dispute->evidence->count() }})</h3>
                    <div class="space-y-3">
                        @foreach($dispute->evidence as $file)
                        <div class="flex items-center justify-between p-4 border border-gray-300 dark:border-gray-600 rounded-lg">
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $file->original_filename }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    by {{ $file->submittedBy->name }} - {{ $file->created_at->format('M d, Y H:i') }}
                                </p>
                                @if($file->description)
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 italic">{{ $file->description }}</p>
                                @endif
                            </div>
                            <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                                Download
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Resolution (if already resolved) -->
                @if($dispute->isResolved())
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6">
                    <h3 class="font-semibold text-green-900 dark:text-green-200 mb-3">✓ Already Resolved</h3>
                    <div class="space-y-2 text-sm text-green-900 dark:text-green-200">
                        <p><strong>Type:</strong> {{ $dispute->getResolutionTypeLabel() }}</p>
                        <p><strong>Details:</strong> {{ $dispute->resolution }}</p>
                        <p><strong>By:</strong> {{ $dispute->resolver->name }} on {{ $dispute->resolved_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Resolution Panel -->
            @if(!$dispute->isResolved())
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Resolve Dispute</h3>
                    
                    <form action="{{ route('admin.disputes.resolve', $dispute) }}" method="POST" class="space-y-4">
                        @csrf

                        <div>
                            <label for="resolution_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Resolution Type
                            </label>
                            <select 
                                name="resolution_type" 
                                id="resolution_type"
                                required
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                onchange="updateResolutionInfo()"
                            >
                                <option value="">Select an option...</option>
                                <option value="refund_buyer">Refund Buyer (Full)</option>
                                <option value="payment_vendor">Pay Vendor (Full)</option>
                                <option value="partial">Partial Refund/Payment</option>
                                <option value="custom">Custom Amount</option>
                            </select>
                        </div>

                        <!-- Partial/Custom Amount -->
                        <div id="amount_section" class="hidden">
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Amount (${number_format($dispute->serviceOrder->total_amount, 2)})
                            </label>
                            <input 
                                type="number" 
                                name="amount" 
                                id="amount"
                                step="0.01"
                                min="0"
                                max="{{ $dispute->serviceOrder->total_amount }}"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Notes (Optional)
                            </label>
                            <textarea 
                                name="notes" 
                                id="notes"
                                rows="4"
                                placeholder="Document your decision reasoning..."
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            ></textarea>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                            Resolve Dispute
                        </button>
                    </form>

                    <!-- Info Box -->
                    <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg">
                        <p class="text-xs text-blue-900 dark:text-blue-200">
                            <strong>Note:</strong> Refunding buyer or paying vendor will trigger automatic wallet updates with notifications to both parties.
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function updateResolutionInfo() {
    const type = document.getElementById('resolution_type').value;
    const amountSection = document.getElementById('amount_section');
    const amountInput = document.getElementById('amount');
    
    if (type === 'partial' || type === 'custom') {
        amountSection.classList.remove('hidden');
        amountInput.required = true;
    } else {
        amountSection.classList.add('hidden');
        amountInput.required = false;
    }
}
</script>
@endsection
