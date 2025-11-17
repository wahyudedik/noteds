@extends('layouts.app')

@section('title', __('messages.batch_download'))

@section('content')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Batch Download</h1>
                    <p class="mt-2 text-sm text-gray-600">Download all attachments from multiple notes in one ZIP file</p>
                </div>
                <a href="{{ route('collections.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                    </svg>
                    My Collections
                </a>
            </div>
        </div>

        @if($purchasedNotes->count() > 0)
            <form id="batchDownloadForm" action="{{ route('batch-download.download') }}" method="POST">
                @csrf
                
                <!-- Selection Controls -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Select Notes</h2>
                            <p class="text-sm text-gray-600 mt-1">Select up to 20 notes to download all attachments</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="selectAll()" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Select All
                            </button>
                            <button type="button" onclick="deselectAll()" 
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                Deselect All
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <span id="selectedCount" class="text-sm font-medium text-gray-700">0 notes selected</span>
                    </div>
                </div>

                <!-- Notes List -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach($purchasedNotes as $note)
                            <div class="p-4 hover:bg-gray-50 transition-colors duration-200">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" 
                                           name="note_ids[]" 
                                           value="{{ $note->id }}"
                                           class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded note-checkbox"
                                           onchange="updateSelectedCount()">
                                    <div class="ml-3 flex-1">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h3 class="text-sm font-semibold text-gray-900">{{ $note->title }}</h3>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    by {{ $note->user->name }}
                                                </p>
                                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-600">
                                                    <span class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                                        </svg>
                                                        {{ count($note->attachments ?? []) }} file(s)
                                                    </span>
                                                    @if($note->price > 0)
                                                        <span class="flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                            </svg>
                                                            {{ currency($note->price) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                
                                                <!-- File List Preview -->
                                                @if(!empty($note->attachments))
                                                    <div class="mt-2 space-y-1">
                                                        @foreach(array_slice($note->attachments, 0, 3) as $attachment)
                                                            @php
                                                                $filename = is_array($attachment) ? ($attachment['filename'] ?? 'Unknown') : basename($attachment);
                                                            @endphp
                                                            <p class="text-xs text-gray-500 flex items-center">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                                </svg>
                                                                {{ Str::limit($filename, 40) }}
                                                            </p>
                                                        @endforeach
                                                        @if(count($note->attachments) > 3)
                                                            <p class="text-xs text-gray-400 italic">
                                                                +{{ count($note->attachments) - 3 }} more file(s)
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <a href="{{ route('marketplace.show', $note) }}" 
                                               class="ml-4 text-xs text-blue-600 hover:text-blue-700"
                                               target="_blank">
                                                View →
                                            </a>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Download Button -->
                <div class="mt-6 flex items-center justify-end">
                    <button type="submit" 
                            id="downloadButton"
                            disabled
                            class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download Selected (<span id="downloadCount">0</span>)
                    </button>
                </div>
            </form>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No notes with attachments</h3>
                <p class="mt-1 text-sm text-gray-500 mb-4">
                    You don't have any purchased notes with attachments yet.
                </p>
                <a href="{{ route('marketplace.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Browse Marketplace
                </a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.note-checkbox:checked');
        const count = checkboxes.length;
        const selectedCountEl = document.getElementById('selectedCount');
        const downloadCountEl = document.getElementById('downloadCount');
        const downloadButton = document.getElementById('downloadButton');
        
        // Check if elements exist before accessing them
        if (selectedCountEl) {
            selectedCountEl.textContent = count + ' note' + (count !== 1 ? 's' : '') + ' selected';
        }
        if (downloadCountEl) {
            downloadCountEl.textContent = count;
        }
        
        // Enable/disable download button
        if (downloadButton) {
            if (count > 0 && count <= 20) {
                downloadButton.disabled = false;
            } else {
                downloadButton.disabled = true;
            }
        }
        
        // Show warning if more than 20 selected
        if (count > 20) {
            alert('You can only select up to 20 notes at a time.');
            // Uncheck the last one
            checkboxes[checkboxes.length - 1].checked = false;
            updateSelectedCount();
        }
    }
    
    function selectAll() {
        document.querySelectorAll('.note-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
        updateSelectedCount();
    }
    
    function deselectAll() {
        document.querySelectorAll('.note-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        updateSelectedCount();
    }
    
    // Form submission handler - only if form exists
    const batchDownloadForm = document.getElementById('batchDownloadForm');
    if (batchDownloadForm) {
        batchDownloadForm.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('.note-checkbox:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one note to download.');
                return false;
            }
            if (checkboxes.length > 20) {
                e.preventDefault();
                alert('You can only select up to 20 notes at a time.');
                return false;
            }
            
            // Show loading state
            const button = document.getElementById('downloadButton');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Preparing download...';
            }
        });
    }
</script>
@endpush
@endsection

