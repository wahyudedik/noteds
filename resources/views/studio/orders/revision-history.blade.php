@extends('layouts.app')

@section('title', "Revision History - Order #{$order->id}")

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Revision History</h1>
                    <a href="{{ route('studio.orders.work-detail', $order) }}" class="text-blue-600 hover:text-blue-700">
                        ← Back to Order
                    </a>
                </div>

                @if ($revisions->isEmpty())
                    <div class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">No revisions yet</p>
                    </div>
                @else
                    <div class="space-y-6">
                        @foreach ($revisions as $revision)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            Revision #{{ $revision->revision_number }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            Requested by: <strong>{{ $revision->requester->name }}</strong>
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium {{ $revision->status === 'accepted' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : ($revision->status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200') }}">
                                        {{ $revision->getStatusLabel() }}
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Request Reason:</p>
                                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $revision->request_reason }}</p>
                                    </div>

                                    @if ($revision->submitted_at)
                                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                Submitted by: <strong>{{ $revision->submitter->name ?? 'N/A' }}</strong> on
                                                {{ $revision->submitted_at->format('M d, Y H:i') }}
                                            </p>
                                            @if ($revision->submission_notes)
                                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mt-2">
                                                    Submission Notes:</p>
                                                <p class="text-gray-600 dark:text-gray-400">
                                                    {{ $revision->submission_notes }}</p>
                                            @endif
                                        </div>
                                    @endif

                                    @if ($revision->rejection_reason)
                                        <div
                                            class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 bg-red-50 dark:bg-red-900/20 p-3 rounded">
                                            <p class="text-sm font-medium text-red-800 dark:text-red-200">Rejection Reason:
                                            </p>
                                            <p class="text-red-700 dark:text-red-300 text-sm mt-1">
                                                {{ $revision->rejection_reason }}</p>
                                        </div>
                                    @endif
                                </div>

                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-4">
                                    Requested: {{ $revision->created_at->format('M d, Y H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
