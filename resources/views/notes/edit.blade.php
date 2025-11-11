@extends('layouts.app')

@section('title', __('messages.edit_note'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('notes.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.edit_note') }}</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.update_note_information') }}</p>
        </div>

        <!-- Flash Messages -->
        @if (session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                        @if (!auth()->user()->hasPremium() || session('upgrade_message'))
                            <div class="mt-3">
                                <a href="{{ route('subscription.create') }}"
                                    class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                    </svg>
                                    {{ __('messages.upgrade_to_premium') }} →
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.note_details') }}</h2>
            </div>
            <div class="p-6">
                <form action="{{ route('notes.update', $note) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.title') }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $note->title) }}" required
                            :placeholder="__('messages.enter_note_title')"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        @error('title')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Content (Rich Text Editor) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.content') }} <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1" id="editor-wrapper" style="min-height: 300px;">
                            <div id="content-editor" style="min-height: 300px;"></div>
                        </div>
                        <textarea name="content" id="content" class="hidden" required>{{ old('content', $note->content) }}</textarea>
                        @error('content')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- AI Assistant (for summary and tags) -->
                    <div
                        class="flex items-start gap-3 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1">AI Assistant</h4>
                            <p class="text-xs text-gray-600 mb-3">Let AI help you generate a summary and suggest
                                tags</p>
                            <button type="button" id="ai-analyze-btn"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span id="ai-btn-text">Generate Summary & Tags</span>
                                <svg id="ai-loading" class="hidden w-4 h-4 ml-2 animate-spin" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Summary (Auto-generated by AI) -->
                    <div>
                        <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.summary') }} <span
                                class="text-xs text-gray-500">{{ __('messages.summary_optional_ai') }}</span>
                        </label>
                        <textarea name="summary" id="summary" rows="3"
                            placeholder="{{ __('messages.auto_generated_summary_placeholder') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('summary', $note->summary) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.brief_summary_note') }}</p>
                        @error('summary')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Preview Content (For paid notes - shown before purchase) -->
                    @if(old('price', $note->price) > 0)
                    <div id="preview-content-wrapper">
                        <label for="preview_content" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.preview_content') }} <span class="text-xs text-gray-500">{{ __('messages.preview_content_optional') }}</span>
                        </label>
                        <textarea name="preview_content" id="preview_content" rows="3" maxlength="300"
                            placeholder="{{ __('messages.enter_preview_for_buyers') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('preview_content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('preview_content', $note->preview_content) }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span id="preview-char-count">{{ strlen(old('preview_content', $note->preview_content)) }}</span>/300 {{ __('messages.characters') }}. 
                            @if(empty(old('preview_content', $note->preview_content))) {{ __('messages.auto_generated_from_content') }} @endif
                        </p>
                        @error('preview_content')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        <!-- Preview Percentage -->
                        <div class="mt-4">
                            <label for="preview_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                Persentase Preview Konten <span class="text-xs text-gray-500">(Opsional)</span>
                            </label>
                            <div class="flex items-center gap-4">
                                <input type="range" name="preview_percentage" id="preview_percentage" 
                                    min="0" max="100" step="5" value="{{ old('preview_percentage', $note->preview_percentage ?? 0) }}"
                                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                <div class="w-20 text-center">
                                    <span id="preview-percentage-value" class="text-lg font-semibold text-blue-600">{{ old('preview_percentage', $note->preview_percentage ?? 0) }}</span>
                                    <span class="text-sm text-gray-600">%</span>
                                </div>
                            </div>
                            <div class="mt-2 flex items-start gap-2">
                                <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="text-xs text-gray-600">
                                    <p><strong>0%</strong> = Konten terkunci sepenuhnya (hanya preview text di atas)</p>
                                    <p><strong>50%</strong> = Setengah baris konten terlihat (misal: 100 baris → 50 baris terlihat)</p>
                                    <p><strong>100%</strong> = Konten terlihat penuh (tidak ada kunci)</p>
                                    <p class="mt-1 text-gray-500">Preview dihitung berdasarkan <strong>baris</strong>, bukan karakter. File attachments tetap terkunci sebelum pembelian.</p>
                                </div>
                            </div>
                            @error('preview_percentage')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Thumbnail Images -->
                    <div id="thumbnail-wrapper" class="mt-6">
                        <label for="thumbnails" class="block text-sm font-medium text-gray-700 mb-2">
                            Thumbnail Images <span class="text-xs text-gray-500">(Maksimal 5 gambar, opsional)</span>
                        </label>
                        
                        <!-- Existing Thumbnails -->
                        @if($note->hasThumbnails())
                            <div class="mb-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                                @foreach($note->thumbnails as $index => $thumbnail)
                                    <div class="relative group">
                                        <img src="{{ Storage::url($thumbnail) }}" alt="Thumbnail {{ $index + 1 }}" 
                                            class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                                        <button type="button" onclick="removeExistingThumbnail('{{ $thumbnail }}')" 
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                        <input type="hidden" name="removed_thumbnails[]" id="removed_thumbnail_{{ $index }}" value="">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-4h-12m-6 4h.01M17 8h.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 justify-center items-center">
                                    <label for="thumbnails" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>Upload gambar</span>
                                        <input type="file" name="thumbnails[]" id="thumbnails" multiple accept="image/*" class="sr-only" onchange="handleThumbnailUpload(this)">
                                    </label>
                                    <p class="pl-1">atau drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 5MB per gambar (maks 5 gambar)</p>
                            </div>
                        </div>
                        <div id="thumbnail-preview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                            <!-- New thumbnail previews will be inserted here -->
                        </div>
                        @error('thumbnails')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('thumbnails.*')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Existing Attachments -->
                    @if($note->hasAttachments())
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Current Attachments ({{ $note->file_count }})
                            </label>
                            <div class="space-y-2 mb-3">
                                @foreach($note->attachments as $index => $attachment)
                                    @php
                                        $filename = is_array($attachment) ? ($attachment['filename'] ?? 'Unknown') : basename($attachment);
                                    @endphp
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">{{ $filename }}</p>
                                                @if(is_array($attachment) && isset($attachment['size']))
                                                    <p class="text-xs text-gray-500">{{ number_format($attachment['size'] / 1024, 2) }} KB</p>
                                                @endif
                                            </div>
                                        </div>
                                        <label class="ml-3 flex items-center cursor-pointer">
                                            <input type="checkbox" name="removed_attachments[]" value="{{ $filename }}" class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                            <span class="ml-2 text-xs text-red-600">{{ __('messages.remove') }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- New File Attachments -->
                    <div>
                        <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.add_more_files') }}
                            <span class="text-xs text-gray-500 font-normal">
                                {{ __('messages.file_attachments_optional') }}
                            </span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                        <span>{{ __('messages.upload_files') }}</span>
                                        <input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx" class="sr-only">
                                    </label>
                                    <p class="pl-1">{{ __('messages.or_drag_and_drop') }}</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    @if(auth()->user()->hasPremium())
                                        Maksimal 100MB per file
                                    @else
                                        {{ __('messages.max_5mb_per_file') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <!-- Large Files Warning -->
                        @if(session('large_files_warning'))
                            <div class="mt-3 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <p class="text-sm font-medium text-yellow-800">
                                            File Besar Terdeteksi (40MB+)
                                        </p>
                                        <div class="mt-2 text-sm text-yellow-700">
                                            <p>File berikut berukuran besar dan mungkin memerlukan waktu lebih lama untuk diupload:</p>
                                            <ul class="mt-1 list-disc list-inside">
                                                @foreach(session('large_files_warning') as $file)
                                                    <li>{{ $file }}</li>
                                                @endforeach
                                            </ul>
                                            <p class="mt-2 font-semibold">⚠️ Harap jangan menutup halaman ini selama proses upload berlangsung.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Upload Progress Bar (for large files) -->
                        <div id="upload-progress-container" class="mt-3 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-blue-800">Uploading large file...</span>
                                    <span id="upload-progress-percent" class="text-sm font-semibold text-blue-600">0%</span>
                                </div>
                                <div class="w-full bg-blue-200 rounded-full h-2.5">
                                    <div id="upload-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                                <div class="mt-2 text-xs text-blue-600">
                                    <span id="upload-progress-text">Preparing upload...</span>
                                </div>
                            </div>
                        </div>

                        <div id="file-list" class="mt-3 space-y-2"></div>
                        
                        @error('attachments')
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800">{!! $message !!}</p>
                                @if(!auth()->user()->hasPremium())
                                    <a href="{{ route('subscription.create') }}" class="mt-2 inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        {{ __('messages.upgrade_to_premium_arrow') }}
                                    </a>
                                @endif
                            </div>
                        @enderror
                        @error('attachments.*')
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm text-red-800">{!! $message !!}</p>
                            </div>
                        @enderror
                        
                        @if(session('upload_errors'))
                            <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                <p class="text-sm font-medium text-red-800 mb-2">Upload errors:</p>
                                <ul class="list-disc list-inside text-sm text-red-700">
                                    @foreach(session('upload_errors') as $filename => $error)
                                        <li><strong>{{ $filename }}:</strong> {{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Price and Status -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.price') }} <span class="text-xs text-gray-500 font-normal">{{ __('messages.price_rp_0_for_free') }}</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="price" id="price" value="{{ old('price', $note->price) }}" min="0" step="0.01"
                                    placeholder="0"
                                    class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                            </div>
                            <div class="mt-2 flex items-start gap-2">
                                <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-xs text-gray-600">
                                    <strong class="text-green-700">{{ __('messages.free') }}</strong>: {{ __('messages.share_knowledge_freely') }} • 
                                    <strong class="text-blue-700">{{ __('messages.paid') }}</strong>: {{ __('messages.set_your_own_price') }}
                                </p>
                            </div>
                            @error('price')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if (!empty($priceGuidance))
                                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-xs text-blue-800 space-y-2">
                                    @if (!empty($priceGuidance['min_default']))
                                        <p class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5a1 1 0 112 0v1.528a6 6 0 014.472 9.472l.764.764a1 1 0 01-1.414 1.414l-.764-.764A6 6 0 1111 6.528V5z" />
                                            </svg>
                                            <span>{!! __('messages.price_guidance_minimum_default', ['amount' => '<strong id="price-guidance-min-default">' . currency($priceGuidance['min_default']) . '</strong>']) !!}</span>
                                        </p>
                                    @endif
                                    <p class="flex items-center gap-2 {{ empty($priceGuidance['recommended_price']) ? 'hidden' : '' }}" id="price-guidance-recommended-row">
                                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{!! __('messages.price_guidance_recommended', [
                                            'amount' => '<strong id="price-guidance-recommended">' . ($priceGuidance['recommended_price'] ? currency($priceGuidance['recommended_price']) : '—') . '</strong>',
                                            'multiplier' => $priceGuidance['recommended_multiplier'],
                                        ]) !!}</span>
                                    </p>
                                    <p class="flex items-center gap-2 hidden" id="price-guidance-selected-row">
                                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        <span id="price-guidance-selected" data-template="{{ __('messages.price_guidance_selected_min', ['amount' => ':amount']) }}"></span>
                                    </p>
                                    <p class="text-xs font-semibold text-red-600 hidden" id="price-guidance-warning" data-template="{{ __('messages.price_guidance_warning_below', ['amount' => ':amount']) }}"></p>
                                    @if (!empty($priceGuidance['category_rules']))
                                        <div>
                                            <p class="font-semibold text-blue-900">{{ __('messages.price_guidance_category_heading') }}</p>
                                            <ul class="mt-1 space-y-1 text-blue-700">
                                                @foreach ($priceGuidance['category_rules'] as $rule)
                                                    <li>• {!! __('messages.price_guidance_category_item', [
                                                        'category' => '<strong>' . e($rule['tag_name'] ?? $rule['tag_slug']) . '</strong>',
                                                        'amount' => '<strong>' . currency($rule['min_price']) . '</strong>',
                                                    ]) !!}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Discount Price -->
                            <div class="mt-4" id="discount-price-wrapper" style="display: {{ old('discount_price', $note->discount_price) || old('price', $note->price) > 0 ? 'block' : 'none' }};">
                                <label for="discount_price" class="block text-sm font-medium text-gray-700 mb-2">
                                    Harga Diskon <span class="text-xs text-gray-500 font-normal">(Opsional)</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" name="discount_price" id="discount_price"
                                        value="{{ old('discount_price', $note->discount_price) }}" min="0" step="0.01" placeholder="0"
                                        class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200 @error('discount_price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                </div>
                                <div class="mt-2 flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                    <p class="text-xs text-gray-600">
                                        Set harga diskon untuk menarik pembeli. Harga diskon harus lebih murah dari harga normal.
                                    </p>
                                </div>
                                <div id="discount-preview" class="mt-2 text-sm text-gray-600 {{ $note->hasDiscount() ? '' : 'hidden' }}">
                                    <span class="font-medium text-green-600">Diskon: <span id="discount-percent">{{ $note->discount_percent ?? 0 }}</span>%</span>
                                </div>
                                @error('discount_price')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.status') }}
                            </label>
                            <select name="status" id="status"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('status') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                <option value="active" {{ old('status', $note->status) === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                                <option value="sold" {{ old('status', $note->status) === 'sold' ? 'selected' : '' }}>{{ __('messages.sold') }}</option>
                                <option value="inactive" {{ old('status', $note->status) === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Public Toggle -->
                    <div>
                        <label class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200">
                            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $note->is_public) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-200">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700">{{ __('messages.make_public') }}</span>
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('messages.available_in_marketplace') }}</p>
                            </div>
                        </label>
                    </div>

                    @if(auth()->user()->hasPremium())
                    <!-- Organization (Premium Features) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                        <div>
                            <label for="folder_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                {{ __('messages.folder_optional') }}
                            </label>
                            <select name="folder_id" id="folder_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                <option value="">{{ __('messages.none_root') }}</option>
                                @foreach($folders as $folder)
                                    <option value="{{ $folder->id }}" {{ old('folder_id', $note->folder_id) == $folder->id ? 'selected' : '' }}>
                                        {{ $folder->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.organize_notes_in_folders') }}</p>
                        </div>

                        <div>
                            <label for="workspace_id" class="block text-sm font-medium text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ __('messages.workspace_optional') }}
                            </label>
                            <select name="workspace_id" id="workspace_id"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                <option value="">{{ __('messages.personal') }}</option>
                                @foreach($workspaces as $workspace)
                                    <option value="{{ $workspace->id }}" {{ old('workspace_id', $note->workspace_id) == $workspace->id ? 'selected' : '' }}>
                                        {{ $workspace->name }} ({{ $workspace->type }})
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">{{ __('messages.assign_to_workspace') }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Tags -->
                    <div>
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                            Tags
                        </label>
                        <div class="flex flex-wrap gap-2 mb-3 min-h-[2.5rem] p-2 border border-gray-200 rounded-lg bg-gray-50" id="tags-container">
                            @if(old('tags'))
                                @foreach(old('tags') as $tag)
                                    <span class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <input type="hidden" name="tags[]" value="{{ $tag }}">
                                        {{ $tag }}
                                        <button type="button" class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            @else
                                @foreach($note->tags as $tag)
                                    <span class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <input type="hidden" name="tags[]" value="{{ $tag->name }}">
                                        {{ $tag->name }}
                                        <button type="button" class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </span>
                                @endforeach
                            @endif
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <input type="text" id="tag-input" placeholder="{{ __('messages.type_tag_and_press_enter') }}" 
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                            </div>
                            <div>
                                <select id="tag-select" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                    <option value="">{{ __('messages.select_existing_tag') }}</option>
                                    @foreach($tags as $tag)
                                        <option value="{{ $tag->name }}">{{ $tag->name }} ({{ $tag->notes_count }} notes)</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Add tags to categorize your note. Press Enter to add a new tag or select from existing tags.
                        </p>
                        @error('tags.*')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                        <a href="{{ route('notes.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Update Note
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill Rich Text Editor
    const quill = new Quill('#content-editor', {
        theme: 'snow',
        placeholder: 'Write your note content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ]
        }
    });

    // Get textarea element
    const contentTextarea = document.getElementById('content');

    // Sync Quill content to hidden textarea for form submission
    quill.on('text-change', function() {
        contentTextarea.value = quill.root.innerHTML;
    });

    // Set initial content if exists
    if (contentTextarea.value) {
        quill.root.innerHTML = contentTextarea.value;
    }

    // Preview content character counter
    const previewContentTextarea = document.getElementById('preview_content');
    const previewCharCount = document.getElementById('preview-char-count');
    if (previewContentTextarea && previewCharCount) {
        previewCharCount.textContent = (previewContentTextarea.value || '').length;
        previewContentTextarea.addEventListener('input', function() {
            previewCharCount.textContent = this.value.length;
        });
    }

    // Preview Percentage Slider
    const previewPercentageInput = document.getElementById('preview_percentage');
    const previewPercentageValue = document.getElementById('preview-percentage-value');
    if (previewPercentageInput && previewPercentageValue) {
        previewPercentageInput.addEventListener('input', function() {
            previewPercentageValue.textContent = this.value;
        });
        // Initial value
        previewPercentageValue.textContent = previewPercentageInput.value;
    }

    // Thumbnail Upload Handler
    window.handleThumbnailUpload = function(input) {
        const files = Array.from(input.files);
        const previewContainer = document.getElementById('thumbnail-preview');
        const maxFiles = 5;
        
        // Limit to max 5 files
        if (files.length > maxFiles) {
            Swal.fire({
                icon: 'warning',
                title: 'Terlalu Banyak Gambar',
                text: `Maksimal ${maxFiles} gambar. Hanya ${maxFiles} gambar pertama yang akan diupload.`,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            // Keep only first 5 files
            const dt = new DataTransfer();
            files.slice(0, maxFiles).forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        previewContainer.innerHTML = '';
        const filesToShow = Array.from(input.files).slice(0, maxFiles);
        
        filesToShow.forEach((file, index) => {
            if (!file.type.startsWith('image/')) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Tidak Valid',
                    text: `${file.name} bukan file gambar.`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'relative group';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Thumbnail ${index + 1}" 
                        class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                    <button type="button" onclick="removeThumbnail(${index})" 
                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;
                previewContainer.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    };

    window.removeThumbnail = function(index) {
        const input = document.getElementById('thumbnails');
        const dt = new DataTransfer();
        const files = Array.from(input.files);
        files.forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        input.files = dt.files;
        handleThumbnailUpload(input);
    };

    window.removeExistingThumbnail = function(thumbnail) {
        // Find the hidden input for this thumbnail
        const existingThumbnails = @json($note->thumbnails ?? []);
        const index = existingThumbnails.indexOf(thumbnail);
        if (index !== -1) {
            const hiddenInput = document.getElementById('removed_thumbnail_' + index);
            if (hiddenInput) {
                hiddenInput.value = thumbnail;
            }
            // Hide the thumbnail visually
            event.target.closest('.relative').style.display = 'none';
        }
    };

    // Show preview content field when price > 0
    const priceInput = document.getElementById('price');
    const previewContentWrapper = document.getElementById('preview-content-wrapper');
    const discountPriceWrapper = document.getElementById('discount-price-wrapper');
    const discountPriceInput = document.getElementById('discount_price');
    const discountPreview = document.getElementById('discount-preview');
    const discountPercent = document.getElementById('discount-percent');
const priceGuidanceData = @json($priceGuidance);
const priceGuidanceMinDefaultEl = document.getElementById('price-guidance-min-default');
const priceGuidanceRecommendedRow = document.getElementById('price-guidance-recommended-row');
const priceGuidanceRecommendedEl = document.getElementById('price-guidance-recommended');
const priceGuidanceSelectedRow = document.getElementById('price-guidance-selected-row');
const priceGuidanceSelectedEl = document.getElementById('price-guidance-selected');
const priceGuidanceWarningEl = document.getElementById('price-guidance-warning');
const baseCurrency = 'IDR';
const locale = document.documentElement.lang === 'en' ? 'en-ID' : 'id-ID';
const currencyFormatter = typeof Intl !== 'undefined'
    ? new Intl.NumberFormat(locale, { style: 'currency', currency: baseCurrency, maximumFractionDigits: 0 })
    : null;
const minDefaultPrice = parseFloat(priceGuidanceData?.min_default ?? 0) || 0;
const recommendedMultiplier = parseFloat(priceGuidanceData?.recommended_multiplier ?? 0) || 0;
const categoryMinRules = Array.isArray(priceGuidanceData?.category_rules) ? priceGuidanceData.category_rules : [];

// Declare tagsContainer early so it can be used in functions below
const tagsContainer = document.getElementById('tags-container');

function formatCurrency(amount) {
    if (!currencyFormatter) {
        return 'Rp ' + Number(amount || 0).toLocaleString('id-ID');
    }
    return currencyFormatter.format(Math.max(0, amount || 0));
}

function slugify(text) {
    return (text || '')
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function getSelectedTagValues() {
    if (!tagsContainer) {
        return [];
    }
    return Array.from(tagsContainer.querySelectorAll('input[type="hidden"]'))
        .map(input => input.value || '')
        .filter(Boolean);
}

function getEffectiveMinimum(tags) {
    let minPrice = minDefaultPrice;
    if (!tags.length || !categoryMinRules.length) {
        return minPrice;
    }

    const ruleMap = categoryMinRules.reduce((acc, rule) => {
        if (rule?.tag_slug) {
            acc[rule.tag_slug] = parseFloat(rule.min_price ?? 0) || 0;
        }
        return acc;
    }, {});

    tags.forEach(tagName => {
        const slug = slugify(tagName);
        if (slug && ruleMap[slug]) {
            minPrice = Math.max(minPrice, ruleMap[slug]);
        }
    });

    return minPrice;
}

function updatePriceGuidanceUI() {
    if (!priceGuidanceData) {
        return;
    }

    const tags = getSelectedTagValues();
    const effectiveMin = getEffectiveMinimum(tags);
    const recommendedPrice = recommendedMultiplier > 0 ? Math.round(effectiveMin * recommendedMultiplier) : null;
    const currentPrice = parseFloat(priceInput?.value || 0) || 0;

    if (priceGuidanceMinDefaultEl) {
        priceGuidanceMinDefaultEl.textContent = formatCurrency(minDefaultPrice);
    }

    if (priceGuidanceRecommendedRow && priceGuidanceRecommendedEl) {
        if (recommendedPrice && recommendedPrice > 0) {
            priceGuidanceRecommendedRow.classList.remove('hidden');
            priceGuidanceRecommendedEl.textContent = formatCurrency(recommendedPrice);
        } else {
            priceGuidanceRecommendedRow.classList.add('hidden');
        }
    }

    if (priceGuidanceSelectedRow && priceGuidanceSelectedEl) {
        const template = priceGuidanceSelectedEl.getAttribute('data-template') || '';
        if (effectiveMin > minDefaultPrice) {
            priceGuidanceSelectedRow.classList.remove('hidden');
            priceGuidanceSelectedEl.innerHTML = template.replace(':amount', '<strong>' + formatCurrency(effectiveMin) + '</strong>');
        } else {
            priceGuidanceSelectedRow.classList.add('hidden');
            priceGuidanceSelectedEl.innerHTML = '';
        }
    }

    if (priceGuidanceWarningEl) {
        const template = priceGuidanceWarningEl.getAttribute('data-template') || '';
        if (currentPrice > 0 && currentPrice < effectiveMin) {
            priceGuidanceWarningEl.classList.remove('hidden');
            priceGuidanceWarningEl.innerHTML = template.replace(':amount', '<strong>' + formatCurrency(effectiveMin) + '</strong>');
        } else {
            priceGuidanceWarningEl.classList.add('hidden');
            priceGuidanceWarningEl.innerHTML = '';
        }
    }
}

    function updateDiscountPreview() {
        const price = parseFloat(priceInput.value) || 0;
        const discountPrice = parseFloat(discountPriceInput.value) || 0;

        if (price > 0) {
            discountPriceWrapper.style.display = 'block';
        } else {
            discountPriceWrapper.style.display = 'none';
            discountPriceInput.value = '';
        }

        if (discountPrice > 0 && discountPrice < price) {
            const percent = Math.round(((price - discountPrice) / price) * 100);
            discountPercent.textContent = percent;
            discountPreview.classList.remove('hidden');
        } else {
            discountPreview.classList.add('hidden');
        }

    updatePriceGuidanceUI();
    }

    if (priceInput && previewContentWrapper) {
        priceInput.addEventListener('input', function() {
            if (parseFloat(this.value) > 0) {
                previewContentWrapper.style.display = 'block';
            } else {
                previewContentWrapper.style.display = 'none';
            }
            updateDiscountPreview();
        });
        // Initial check
        if (parseFloat(priceInput.value) > 0) {
            previewContentWrapper.style.display = 'block';
        } else {
            previewContentWrapper.style.display = 'none';
        }
    }

    if (discountPriceInput) {
        discountPriceInput.addEventListener('input', updateDiscountPreview);
    }

    // Initial check
    updateDiscountPreview();
updatePriceGuidanceUI();

    // File upload preview with background upload for files > 5MB
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('file-list');
    const uploadProgressContainer = document.getElementById('upload-progress-container');
    const uploadProgressBar = document.getElementById('upload-progress-bar');
    const uploadProgressPercent = document.getElementById('upload-progress-percent');
    const uploadProgressText = document.getElementById('upload-progress-text');
    const BACKGROUND_UPLOAD_THRESHOLD = 5242880; // 5MB in bytes
    const backgroundUploadIds = []; // Store upload IDs for form submission
    const fileUploadStatus = new Map(); // Track upload status per file
    const selectedFiles = new Map(); // Store selected files by index
    
    function formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        }
        return bytes + ' bytes';
    }
    
    async function uploadFileInBackground(file, fileIndex) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        
        // Update UI to show uploading
        fileUploadStatus.set(fileIndex, { status: 'uploading', progress: 0 });
        updateFileItemStatus(fileIndex, 'uploading', 0);
        
        try {
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    fileUploadStatus.set(fileIndex, { status: 'uploading', progress: percentComplete });
                    updateFileItemStatus(fileIndex, 'uploading', percentComplete);
                }
            });
            
                            return new Promise((resolve, reject) => {
                                xhr.onload = function() {
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        if (xhr.status === 200 && response.success) {
                                            backgroundUploadIds.push(response.upload_id);
                                            fileUploadStatus.set(fileIndex, { status: 'completed', uploadId: response.upload_id });
                                            updateFileItemStatus(fileIndex, 'completed');
                                            resolve(response);
                                        } else {
                                            // Handle error response
                                            let errorMessage = response.error || 'Upload failed';
                                            
                                            // Check if premium is required
                                            if (response.requires_premium) {
                                                errorMessage += ' <a href="{{ route("subscription.create") }}" class="underline font-semibold">Upgrade ke Premium</a>';
                                            }
                                            
                                            // Show server limits if provided
                                            if (response.server_limits) {
                                                errorMessage += ` (Limit server: ${response.server_limits.upload_max_filesize})`;
                                            }
                                            
                                            fileUploadStatus.set(fileIndex, { status: 'error', error: errorMessage, canRetry: response.can_retry !== false });
                                            updateFileItemStatus(fileIndex, 'error', 0, errorMessage);
                                            
                                            // Show SweetAlert for important errors (but don't block - file will upload on form submit)
                                            if (response.requires_premium) {
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Premium Diperlukan',
                                                    html: errorMessage,
                                                    confirmButtonText: 'OK'
                                                });
                                            }
                                            
                                            reject(new Error(errorMessage));
                                        }
                                    } catch (e) {
                                        const error = 'Failed to parse server response';
                                        fileUploadStatus.set(fileIndex, { status: 'error', error: error });
                                        updateFileItemStatus(fileIndex, 'error', 0, error);
                                        reject(new Error(error));
                                    }
                                };
                                
                                xhr.onerror = function() {
                                    const error = 'Network error. Pastikan koneksi internet stabil. File akan otomatis diupload saat form disubmit.';
                                    fileUploadStatus.set(fileIndex, { status: 'error', error: error, canRetry: true });
                                    updateFileItemStatus(fileIndex, 'error', 0, error);
                                    reject(new Error(error));
                                };
                                
                                xhr.ontimeout = function() {
                                    const error = 'Upload timeout. File akan otomatis diupload saat form disubmit.';
                                    fileUploadStatus.set(fileIndex, { status: 'error', error: error, canRetry: true });
                                    updateFileItemStatus(fileIndex, 'error', 0, error);
                                    reject(new Error(error));
                                };
                                
                                xhr.open('POST', '{{ route("notes.upload-background") }}');
                                xhr.timeout = 600000; // 10 minutes timeout for large files
                                xhr.send(formData);
                            });
        } catch (error) {
            fileUploadStatus.set(fileIndex, { status: 'error', error: error.message });
            updateFileItemStatus(fileIndex, 'error', 0, error.message);
            throw error;
        }
    }
    
    function updateFileItemStatus(fileIndex, status, progress = 0, error = null) {
        const fileItem = document.querySelector(`[data-file-index="${fileIndex}"]`);
        if (!fileItem) return;
        
        const statusElement = fileItem.querySelector('.file-upload-status');
        const progressBar = fileItem.querySelector('.file-progress-bar');
        
        if (status === 'uploading') {
            statusElement.innerHTML = `
                <span class="text-xs text-blue-600">Uploading... ${Math.round(progress)}%</span>
            `;
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
        } else if (status === 'completed') {
            statusElement.innerHTML = `
                <span class="text-xs text-green-600 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    Uploaded
                </span>
            `;
            fileItem.classList.remove('bg-yellow-50', 'border-yellow-300');
            fileItem.classList.add('bg-green-50', 'border-green-300');
        } else if (status === 'error') {
            const errorMsg = error || 'Upload failed';
            statusElement.innerHTML = `
                <div class="text-xs text-red-600 space-y-1">
                    <p class="font-semibold">⚠️ ${errorMsg}</p>
                    <p class="text-red-500 text-xs italic">💡 Jangan khawatir, file akan otomatis diupload saat form disubmit.</p>
                </div>
            `;
            fileItem.classList.remove('bg-yellow-50', 'border-yellow-300');
            fileItem.classList.add('bg-red-50', 'border-red-300');
        }
    }
    
    if (fileInput && fileList) {
        fileInput.addEventListener('change', async function() {
            fileList.innerHTML = '';
            selectedFiles.clear(); // Clear previous files
            fileUploadStatus.clear(); // Clear previous status
            backgroundUploadIds.length = 0; // Clear previous upload IDs
            const files = Array.from(this.files);
            let hasLargeFile = false;
            const uploadPromises = [];
            
            files.forEach((file, index) => {
                selectedFiles.set(index, file); // Store file reference
                const isLargeFile = file.size >= BACKGROUND_UPLOAD_THRESHOLD;
                if (isLargeFile) {
                    hasLargeFile = true;
                }
                
                const fileItem = document.createElement('div');
                fileItem.setAttribute('data-file-index', index);
                fileItem.className = `flex items-center justify-between p-3 rounded-lg border ${isLargeFile ? 'bg-yellow-50 border-yellow-300' : 'bg-gray-50 border-gray-200'}`;
                
                let warningIcon = '';
                if (isLargeFile) {
                    warningIcon = `
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    `;
                }
                
                fileItem.innerHTML = `
                    <div class="flex items-center flex-1 min-w-0">
                        ${warningIcon}
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium ${isLargeFile ? 'text-yellow-900' : 'text-gray-900'} truncate">${file.name}</p>
                            <p class="text-xs ${isLargeFile ? 'text-yellow-700' : 'text-gray-500'}">
                                ${formatFileSize(file.size)}
                                ${isLargeFile ? '<span class="ml-2 font-semibold">(Large file - uploading in background...)</span>' : ''}
                            </p>
                            ${isLargeFile ? `
                                <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="file-progress-bar bg-blue-600 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                                </div>
                            ` : ''}
                            <div class="file-upload-status mt-1"></div>
                        </div>
                    </div>
                `;
                fileList.appendChild(fileItem);
                
                // Start background upload for large files
                if (isLargeFile) {
                    uploadPromises.push(uploadFileInBackground(file, index));
                }
            });
            
            // Show progress container if there are large files
            if (hasLargeFile && uploadProgressContainer) {
                uploadProgressContainer.classList.remove('hidden');
                uploadProgressText.textContent = 'File besar terdeteksi. Upload sedang berjalan di background...';
            } else if (uploadProgressContainer) {
                uploadProgressContainer.classList.add('hidden');
            }
            
            // Wait for all background uploads to complete (but don't block)
            if (uploadPromises.length > 0) {
                Promise.allSettled(uploadPromises).then(results => {
                    const failed = results.filter(r => r.status === 'rejected').length;
                    if (failed === 0) {
                        uploadProgressText.textContent = 'Semua file berhasil diupload!';
                        uploadProgressContainer.classList.add('hidden');
                    } else {
                        uploadProgressText.textContent = `${failed} file gagal diupload. Silakan coba lagi.`;
                    }
                });
            }
        });
    }
    
    // Add hidden input for background upload IDs before form submission
    const form = document.querySelector('form[action*="notes"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if there are any files still uploading
            const uploadingFiles = [];
            fileUploadStatus.forEach((status, index) => {
                if (status.status === 'uploading') {
                    const file = selectedFiles.get(index);
                    if (file) {
                        uploadingFiles.push(file.name);
                    }
                }
            });
            
            if (uploadingFiles.length > 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Upload Sedang Berlangsung',
                    html: `File berikut masih dalam proses upload:<br><strong>${uploadingFiles.join(', ')}</strong><br><br>Harap tunggu hingga upload selesai sebelum submit form.`,
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Check for failed uploads
            const failedFiles = [];
            fileUploadStatus.forEach((status, index) => {
                if (status.status === 'error') {
                    const file = selectedFiles.get(index);
                    if (file) {
                        failedFiles.push(file.name);
                    }
                }
            });
            
            // Add background upload IDs to form
            backgroundUploadIds.forEach(uploadId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'background_upload_ids[]';
                input.value = uploadId;
                form.appendChild(input);
            });
            
            // Remove large files from file input to avoid duplicate upload (only if they're successfully uploaded)
            if (fileInput && fileInput.files.length > 0) {
                const files = Array.from(fileInput.files);
                const filesToKeep = [];
                files.forEach((file, index) => {
                    const status = fileUploadStatus.get(index);
                    // Keep small files (< 5MB) or files that failed to upload in background
                    // Remove large files that were successfully uploaded in background
                    if (file.size < BACKGROUND_UPLOAD_THRESHOLD) {
                        filesToKeep.push(file); // Keep all small files
                    } else if (status && status.status === 'error') {
                        filesToKeep.push(file); // Keep files that failed to upload - they will be uploaded on form submit
                    } else if (status && status.status !== 'completed') {
                        filesToKeep.push(file); // Keep files that didn't complete (shouldn't happen, but safety check)
                    }
                    // Files with status === 'completed' are removed (they're already uploaded)
                });
                const dt = new DataTransfer();
                filesToKeep.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
            }
            
            // Show info message if there are failed files (but don't block submission)
            if (failedFiles.length > 0) {
                // Just log to console, don't block - files will be uploaded on form submit
                console.log('Some files failed to upload in background, will be uploaded on form submit:', failedFiles);
            }
        });
    }

    // AI Analyze Button (for summary and tags)
    const aiAnalyzeBtn = document.getElementById('ai-analyze-btn');
    const aiBtnText = document.getElementById('ai-btn-text');
    const aiLoading = document.getElementById('ai-loading');
    const summaryTextarea = document.getElementById('summary');

    if (aiAnalyzeBtn) {
        aiAnalyzeBtn.addEventListener('click', async function() {
            // Get plain text content from Quill for AI analysis
            const content = quill.getText().trim();

            if (!content) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Content',
                    text: 'Please enter some content first.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            // Show loading state
            aiAnalyzeBtn.disabled = true;
            aiBtnText.textContent = 'Analyzing...';
            aiLoading.classList.remove('hidden');

            try {
                const response = await fetch('{{ route('ai.analyze') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        content: content
                    })
                });

                const data = await response.json();

                // Check premium requirement
                if (data.error && data.error === 'premium_required') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Premium Required',
                        text: 'AI features require a premium subscription.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    aiAnalyzeBtn.disabled = false;
                    aiLoading.classList.add('hidden');
                    aiBtnText.textContent = 'Generate Summary & Tags';
                    return;
                }

                if (data.success) {
                    // Fill summary
                    if (data.data.summary && summaryTextarea) {
                        summaryTextarea.value = data.data.summary;
                    }

                    // Add suggested tags
                    if (data.data.tags && data.data.tags.length > 0) {
                        data.data.tags.forEach(tag => {
                            if (!tagExists(tag)) {
                                addTag(tag);
                            }
                        });
                    }

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Generated!',
                        text: 'AI has generated summary and suggested tags.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    throw new Error(data.message || 'Failed to analyze content');
                }
            } catch (error) {
                console.error('AI analysis error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Failed to analyze content. Please try again.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            } finally {
                aiAnalyzeBtn.disabled = false;
                aiLoading.classList.add('hidden');
                aiBtnText.textContent = 'Generate Summary & Tags';
            }
        });
    }

    // Tag management (tagsContainer already declared above)
    const tagInput = document.getElementById('tag-input');
    const tagSelect = document.getElementById('tag-select');

    // Tag management helper functions
    function tagExists(tagName) {
        if (!tagsContainer) {
            return false;
        }
        return Array.from(tagsContainer.querySelectorAll('input[type="hidden"]'))
            .some(input => input.value.toLowerCase() === tagName.toLowerCase());
    }

    function addTag(tagName) {
        if (!tagsContainer) {
            return;
        }
        const tagItem = document.createElement('span');
        tagItem.className = 'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200';
        tagItem.innerHTML = `
            <input type="hidden" name="tags[]" value="${tagName}">
            ${tagName}
            <button type="button" class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        `;
        tagsContainer.appendChild(tagItem);
        updatePriceGuidanceUI();
    }

    // Setup tag management event listeners (only if elements exist)
    if (tagInput && tagSelect && tagsContainer) {
        // Add tag from input
        tagInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const tagValue = this.value.trim();
                if (tagValue && !tagExists(tagValue)) {
                    addTag(tagValue);
                    this.value = '';
                }
            }
        });

        // Add tag from select
        tagSelect.addEventListener('change', function() {
            const tagValue = this.value.trim();
            if (tagValue && !tagExists(tagValue)) {
                addTag(tagValue);
                this.value = '';
            }
        });

        // Remove tag
        tagsContainer.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-tag') || e.target.closest('.remove-tag')) {
                const tagItem = e.target.closest('.tag-item');
                if (tagItem) {
                    tagItem.remove();
                    updatePriceGuidanceUI();
                }
            }
        });
    }
});
</script>
@endpush
@endsection
