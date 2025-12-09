@extends('layouts.app')

@section('title', "Request Revision - Order #{$order->id}")

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Request Revision</h1>

                @if ($order->getRemainingRevisions() <= 0)
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-red-800 font-semibold">No revisions remaining</p>
                        <p class="text-red-700 text-sm mt-1">You have reached the maximum number of revisions for this order.
                            Contact admin for dispute resolution.</p>
                    </div>
                @else
                    <form action="{{ route('studio.orders.request-revision', $order) }}" method="POST" class="space-y-6">
                        @csrf

                        <div>
                            <label for="request_reason"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Revision Reason
                            </label>
                            <textarea name="request_reason" id="request_reason" rows="6" required
                                placeholder="Explain what needs to be revised..."
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('request_reason') }}</textarea>
                            @error('request_reason')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
                            <p class="text-blue-900 dark:text-blue-100">
                                <strong>Revisions Remaining:</strong> {{ $order->getRemainingRevisions() }} of
                                {{ $order->max_revisions }}
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Request Revision
                            </button>
                            <a href="{{ route('studio.orders.work-detail', $order) }}"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endsection
