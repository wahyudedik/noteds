@extends('layouts.app')

@section('title', 'Submit Work - ' . $order->title)

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
                    <li class="text-gray-700">Submit Work</li>
                </ol>
            </nav>
        </div>

        <!-- Order Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            @php
                $currencyService = app(\App\Services\CurrencyService::class);
                $userCurrency = $currencyService->getUserCurrency(auth()->user());
                $budgetDisplay = currency($order->budget, $userCurrency, 'IDR');
                $escrowDisplay = currency($order->escrow_amount, $userCurrency, 'IDR');
            @endphp
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $order->title }}</h1>
            <div class="flex items-center justify-between text-gray-600 text-sm">
                <span>Budget: <strong>{{ $budgetDisplay }}</strong></span>
                <span>Escrow Funded: <strong>{{ $escrowDisplay }}</strong></span>
                <span
                    class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>
        </div>

        <!-- Order Description -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-3">Order Details</h2>
            <p class="text-gray-700 whitespace-pre-line">{{ $order->description }}</p>
        </div>

        <!-- Work Submission Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Submit Your Work</h2>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <h3 class="font-semibold text-red-800 mb-2">Please fix the errors below:</h3>
                    <ul class="list-disc list-inside space-y-1 text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('studio.orders.submit-work.store', $order) }}" method="POST"
                enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Work Description
                        <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3">Describe your completed work, what you did, and any relevant
                        details.</p>
                    <textarea id="description" name="description" rows="8"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('description') border-red-500 @enderror"
                        placeholder="Describe your completed work..." required>{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters</p>
                </div>

                <!-- File Upload -->
                <div>
                    <label for="files" class="block text-sm font-medium text-gray-700 mb-2">
                        Upload Files
                        <span class="text-red-500">*</span> <span class="text-gray-500 font-normal">(Optional but
                            recommended)</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3">
                        Upload up to 10 files (images, documents, etc.). Each file max 10MB, total max 50MB.
                        <br>Supported: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX, ZIP
                    </p>

                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition"
                        onclick="document.getElementById('files').click()">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" stroke="currentColor" fill="none"
                            viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-10-8v12m0 0l-4-4m4 4l4-4"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <p class="text-gray-600 font-medium">Click to upload or drag and drop</p>
                        <p class="text-sm text-gray-500">or select files from your computer</p>
                    </div>

                    <input type="file" id="files" name="files[]" multiple
                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.xls,.xlsx,.zip" class="hidden"
                        onchange="updateFileList()" />

                    @error('files')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <!-- File List -->
                    <div id="fileList" class="mt-4 space-y-2 hidden">
                        <p class="text-sm font-medium text-gray-700">Selected Files:</p>
                        <ul id="selectedFiles" class="space-y-1"></ul>
                        <p id="fileCount" class="text-xs text-gray-500 mt-2"></p>
                        <p id="totalSize" class="text-xs text-gray-500"></p>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">📋 Before You Submit:</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✓ Make sure your work meets the order requirements</li>
                        <li>✓ Attach all relevant files as proof of work</li>
                        <li>✓ Provide clear description of what you've done</li>
                        <li>✓ Double-check everything before submitting</li>
                    </ul>
                    <p class="text-xs text-blue-700 mt-3">Once submitted, the buyer will review your work. They can approve,
                        reject, or request revisions.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Submit Work
                    </button>
                    <a href="{{ route('studio.orders.show', $order) }}"
                        class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateFileList() {
            const fileInput = document.getElementById('files');
            const files = fileInput.files;
            const fileList = document.getElementById('fileList');
            const selectedFiles = document.getElementById('selectedFiles');
            const fileCount = document.getElementById('fileCount');
            const totalSize = document.getElementById('totalSize');

            selectedFiles.innerHTML = '';

            if (files.length === 0) {
                fileList.classList.add('hidden');
                return;
            }

            fileList.classList.remove('hidden');

            let totalBytes = 0;
            Array.from(files).forEach((file, index) => {
                const li = document.createElement('li');
                li.className = 'text-sm text-gray-700 flex items-center justify-between';

                const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                totalBytes += file.size;

                li.innerHTML = `
            <span>${index + 1}. ${file.name} (${sizeInMB} MB)</span>
            <button type="button" onclick="removeFile(${index})" class="text-red-600 hover:text-red-800 text-xs">Remove</button>
        `;
                selectedFiles.appendChild(li);
            });

            const totalMB = (totalBytes / (1024 * 1024)).toFixed(2);
            fileCount.textContent = `Total files: ${files.length}`;
            totalSize.textContent = `Total size: ${totalMB} MB`;

            if (totalMB > 50) {
                totalSize.classList.add('text-red-600');
                totalSize.textContent += ' ⚠️ Exceeds 50MB limit!';
            }
        }

        function removeFile(index) {
            const fileInput = document.getElementById('files');
            const dataTransfer = new DataTransfer();
            const files = fileInput.files;

            Array.from(files).forEach((file, i) => {
                if (i !== index) {
                    dataTransfer.items.add(file);
                }
            });

            fileInput.files = dataTransfer.files;
            updateFileList();
        }
    </script>
@endsection
