@extends('layouts.app')

@section('title', "Dispute #{$dispute->id}")

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Dispute Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dispute #{{ $dispute->id }}</h1>
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Filed By</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $dispute->initiator->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Filed On</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $dispute->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Other Party</p>
                    <p class="font-semibold text-gray-900 dark:text-white">
                        {{ auth()->user()->id === $dispute->initiator->id 
                            ? $dispute->serviceOrder->vendor->name 
                            : $dispute->initiator->name 
                        }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Dispute Details -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dispute Details</h2>
            <div class="prose dark:prose-invert max-w-none">
                {!! nl2br(e($dispute->reason)) !!}
            </div>
        </div>

        <!-- Evidence Files -->
        @if($dispute->evidence->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Evidence ({{ $dispute->evidence->count() }})</h2>
            <div class="space-y-3">
                @foreach($dispute->evidence as $file)
                <div class="flex items-center justify-between p-4 border border-gray-300 dark:border-gray-600 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $file->original_filename }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Submitted by {{ $file->submittedBy->name }} on {{ $file->created_at->format('M d, Y H:i') }}
                        </p>
                        @if($file->description)
                        <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ $file->description }}</p>
                        @endif
                    </div>
                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                        Download
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Add Evidence Form (if dispute is open) -->
            @if($dispute->isOpen())
            <form action="{{ route('studio.disputes.add-evidence', $dispute) }}" method="POST" enctype="multipart/form-data" class="mt-6 pt-6 border-t border-gray-300 dark:border-gray-600">
                @csrf
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Add More Evidence</h3>
                
                <div class="space-y-4">
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Description (Optional)
                        </label>
                        <textarea 
                            name="description" 
                            id="description"
                            rows="3"
                            placeholder="Describe this evidence..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        ></textarea>
                    </div>

                    <div>
                        <label for="files" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Files
                        </label>
                        <input 
                            type="file" 
                            name="files[]" 
                            id="files"
                            multiple
                            required
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.mp4"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                        >
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Max 10MB per file</p>
                    </div>

                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Add Evidence
                    </button>
                </div>
            </form>
            @endif
        </div>
        @endif

        <!-- Resolution (if resolved) -->
        @if($dispute->isResolved())
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-green-900 dark:text-green-200 mb-4">✓ Dispute Resolved</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Resolution Type</p>
                    <p class="font-semibold text-green-900 dark:text-green-200">{{ $dispute->getResolutionTypeLabel() }}</p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Resolution Details</p>
                    <p class="font-semibold text-green-900 dark:text-green-200">{{ $dispute->resolution }}</p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Resolved By</p>
                    <p class="font-semibold text-green-900 dark:text-green-200">{{ $dispute->resolver->name }}</p>
                </div>
                <div>
                    <p class="text-gray-600 dark:text-gray-400">Resolved On</p>
                    <p class="font-semibold text-green-900 dark:text-green-200">{{ $dispute->resolved_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Back Button -->
        <div>
            <a href="{{ route('studio.orders.show', $dispute->serviceOrder) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                ← Back to Order
            </a>
        </div>
    </div>
</div>
@endsection
