@extends('40-shared/layouts/app')

@section('title', "File Dispute - Order #{$order->id}")

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">File a Dispute</h1>
                <p class="text-gray-600 dark:text-gray-400 mb-6">Order #{{ $order->id }} - {{ $order->title }}</p>

                <div
                    class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-6">
                    <p class="text-yellow-800 dark:text-yellow-200 font-semibold">⚠️ Important</p>
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm mt-1">Filing a dispute will pause this order
                        pending admin review. Please provide detailed information and evidence.</p>
                </div>

                <form action="{{ route('studio.orders.dispute.store', $order) }}" method="POST" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Reason for Dispute
                        </label>
                        <textarea name="reason" id="reason" rows="6" required
                            placeholder="Describe the issue and why you're filing a dispute..."
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="evidence_files" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Evidence Files (Optional)
                        </label>
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6">
                            <input type="file" name="evidence_files[]" id="evidence_files" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.mp4" class="w-full">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                Max 10MB per file. Accepted: PDF, DOC, DOCX, JPG, PNG, ZIP, MP4
                            </p>
                        </div>
                        @error('evidence_files.*')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex gap-4">
                        <button type="submit"
                            class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            File Dispute
                        </button>
                        <a href="{{ route('studio.orders.show', $order) }}"
                            class="px-6 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

