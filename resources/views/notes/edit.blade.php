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

        <!-- Form Card -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.note_details') }}</h2>
            </div>
            <div class="p-6">
            <!-- Flash Messages -->
            @if(session('error') || session('upgrade_message'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            @if(session('error'))
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            @endif
                            @if(session('upgrade_message'))
                                <p class="text-sm font-medium text-red-800">{{ session('upgrade_message') }}</p>
                            @endif
                            @if(!auth()->user()->hasPremium() || session('upgrade_message'))
                                <div class="mt-3">
                                    <a href="{{ route('subscription.create') }}" class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        Upgrade to Premium →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

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

                    <!-- Summary -->
                    <div>
                        <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.summary') }} <span class="text-xs text-gray-500">{{ __('messages.summary_optional') }}</span>
                        </label>
                        <textarea name="summary" id="summary" rows="3"
                            placeholder="{{ __('messages.brief_summary_placeholder') }}"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('summary', $note->summary) }}</textarea>
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

                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                            <div class="space-y-1 text-center w-full">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-4h-12m-6 4h.01M17 8h.01" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
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
                                        {{ __('messages.max_50mb_per_file') }}
                                    @else
                                        {{ __('messages.max_5mb_per_file') }}
                                    @endif
                                </p>
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

    // File upload preview
    const fileInput = document.getElementById('attachments');
    const fileList = document.getElementById('file-list');
    if (fileInput && fileList) {
        fileInput.addEventListener('change', function() {
            fileList.innerHTML = '';
            Array.from(this.files).forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200';
                fileItem.innerHTML = `
                    <div class="flex items-center flex-1 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                    </div>
                `;
                fileList.appendChild(fileItem);
            });
        });
    }

    const tagInput = document.getElementById('tag-input');
    const tagSelect = document.getElementById('tag-select');
    const tagsContainer = document.getElementById('tags-container');

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
            }
        }
    });

    function tagExists(tagName) {
        return Array.from(tagsContainer.querySelectorAll('input[type="hidden"]'))
            .some(input => input.value.toLowerCase() === tagName.toLowerCase());
    }

    function addTag(tagName) {
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
    }
});
</script>
@endpush
@endsection
