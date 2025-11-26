@extends('layouts.app')

@section('title', __('messages.create_new_note'))

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
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.create_new_note') }}</h1>
                </div>
                <p class="mt-2 text-base text-gray-600">{{ __('messages.share_knowledge') }}</p>
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

            <!-- Validation Errors with SweetAlert2 -->
            @if ($errors->any())
                @push('scripts')
                <script>
                    (function showValidationErrors() {
                        if (typeof Swal === 'undefined') {
                            setTimeout(showValidationErrors, 100);
                            return;
                        }

                        const errors = @json($errors->all());
                        const errorFields = @json($errors->keys());
                        
                        // Get field labels mapping
                        const fieldLabels = {
                            'title': 'Judul',
                            'content': 'Konten',
                            'price': 'Harga',
                            'discount_price': 'Harga Diskon',
                            'attachments': 'File Lampiran',
                            'attachments.*': 'File Lampiran',
                            'thumbnails': 'Thumbnail',
                            'thumbnails.*': 'Thumbnail',
                            'video_preview': 'Video Preview',
                            'external_links': 'Link Eksternal',
                            'workspace_id': 'Workspace',
                            'folder_id': 'Folder',
                            'tags': 'Tag',
                            'tags.*': 'Tag',
                            'ecosystem_category': 'Kategori Ekosistem',
                            'code_language': 'Bahasa Pemrograman',
                            'code_framework': 'Framework',
                            'code_type': 'Tipe Code',
                            'code_demo_link': 'Link Demo Code',
                            'photo_resolution': 'Resolusi Photo',
                            'photo_type': 'Tipe Photo',
                            'photo_format': 'Format Photo',
                            'photo_gallery_link': 'Link Gallery Photo',
                            'design_type': 'Tipe Design',
                            'design_format': 'Format Design',
                            'design_preview_link': 'Link Preview Design',
                            'audio_duration': 'Durasi Audio',
                            'audio_format': 'Format Audio',
                            'audio_genre': 'Genre Audio',
                            'audio_link': 'Link Audio',
                            'video_duration': 'Durasi Video',
                            'video_resolution': 'Resolusi Video',
                            'video_format': 'Format Video',
                            'video_link': 'Link Video',
                            'theme_platform': 'Platform Theme',
                            'theme_type': 'Tipe Theme',
                            'theme_preview_link': 'Link Preview Theme',
                            'three_d_format': 'Format 3D',
                            'three_d_type': 'Tipe 3D',
                            'three_d_preview_link': 'Link Preview 3D',
                            'language': 'Bahasa',
                            'scheduled_publish_at': 'Jadwal Publikasi',
                            'summary': 'Ringkasan',
                            'preview_content': 'Konten Preview',
                            'preview_percentage': 'Persentase Preview',
                        };

                        // Build error message
                        let errorMessage = '<div class="text-left">';
                        errorMessage += '<p class="font-semibold mb-2">Mohon perbaiki kesalahan berikut:</p>';
                        errorMessage += '<ul class="list-disc list-inside space-y-1 text-sm">';
                        
                        errors.forEach((error, index) => {
                            const field = errorFields[index] || '';
                            const fieldLabel = fieldLabels[field] || field;
                            errorMessage += `<li>${error}</li>`;
                        });
                        
                        errorMessage += '</ul>';
                        errorMessage += '</div>';

                        // Show SweetAlert2 error
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            html: errorMessage,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#dc2626',
                            width: '500px',
                            customClass: {
                                popup: 'text-left'
                            }
                        });

                        // Scroll to first error field
                        const firstErrorField = errorFields[0];
                        if (firstErrorField) {
                            const fieldElement = document.querySelector(`[name="${firstErrorField}"], [name="${firstErrorField}.*"]`);
                            if (fieldElement) {
                                setTimeout(() => {
                                    fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    fieldElement.focus();
                                    if (fieldElement.classList) {
                                        fieldElement.classList.add('border-red-500', 'ring-2', 'ring-red-500');
                                    }
                                }, 500);
                            }
                        }
                    })();
                </script>
                @endpush
            @endif


            <!-- Form Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.note_details') }}</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.title') }} <span class="text-red-500 font-bold" title="Wajib diisi">*</span>
                                    <span class="text-xs text-gray-500 ml-1">(Wajib)</span>
                                </label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                    :placeholder="__('messages.enter_note_title')"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Ecosystem Category -->
                            <div>
                                <label for="ecosystem_category" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.ecosystem_category') }}
                                </label>
                                <select name="ecosystem_category" id="ecosystem_category"
                                    autocomplete="off" data-form-filler="ignore"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                    @foreach($ecosystems as $key => $label)
                                        <option value="{{ $key }}" {{ old('ecosystem_category')===$key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('ecosystem_category')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-600">
                                    Pilih kategori ekosistem untuk menampilkan field yang relevan.
                                </p>
                            </div>

                            <!-- Ecosystem-Specific Fields -->
                            <div id="ecosystem-fields" class="mt-4 space-y-4">
                                <!-- Code Ecosystem Fields -->
                                <div id="code-fields" class="hidden p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-blue-900 mb-3">Detail Code</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label for="code_language" class="block text-sm font-medium text-gray-700 mb-2">
                                                Bahasa Pemrograman
                                            </label>
                                            <input type="text" name="code_language" id="code_language" value="{{ old('code_language') }}"
                                                placeholder="PHP, JavaScript, Python, dll"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="code_framework" class="block text-sm font-medium text-gray-700 mb-2">
                                                Framework
                                            </label>
                                            <input type="text" name="code_framework" id="code_framework" value="{{ old('code_framework') }}"
                                                placeholder="Laravel, React, Vue, dll"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div>
                                            <label for="code_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe Code
                                            </label>
                                            <select name="code_type" id="code_type"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                                <option value="">Pilih Tipe</option>
                                                <option value="plugin" {{ old('code_type') === 'plugin' ? 'selected' : '' }}>Plugin</option>
                                                <option value="script" {{ old('code_type') === 'script' ? 'selected' : '' }}>Script</option>
                                                <option value="library" {{ old('code_type') === 'library' ? 'selected' : '' }}>Library</option>
                                                <option value="component" {{ old('code_type') === 'component' ? 'selected' : '' }}>Component</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="code_demo_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                                Link Demo/Repository (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="code_demo_link" id="code_demo_link" value="{{ old('code_demo_link') }}"
                                            placeholder="https://github.com/... atau https://codepen.io/... atau https://demo.example.com"
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke GitHub, CodePen, atau live demo</p>
                                        @error('code_demo_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Photo Ecosystem Fields -->
                                <div id="photo-fields" class="hidden p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-green-900 mb-3">Detail Photo</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label for="photo_resolution" class="block text-sm font-medium text-gray-700 mb-2">
                                                Resolusi
                                            </label>
                                            <input type="text" name="photo_resolution" id="photo_resolution" value="{{ old('photo_resolution') }}"
                                                placeholder="1920x1080, 4K, dll"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                        </div>
                                        <div>
                                            <label for="photo_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe Photo
                                            </label>
                                            <select name="photo_type" id="photo_type"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                                <option value="">Pilih Tipe</option>
                                                <option value="stock" {{ old('photo_type') === 'stock' ? 'selected' : '' }}>Stock</option>
                                                <option value="portrait" {{ old('photo_type') === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                                <option value="landscape" {{ old('photo_type') === 'landscape' ? 'selected' : '' }}>Landscape</option>
                                                <option value="product" {{ old('photo_type') === 'product' ? 'selected' : '' }}>Product</option>
                                                <option value="event" {{ old('photo_type') === 'event' ? 'selected' : '' }}>Event</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="photo_format" class="block text-sm font-medium text-gray-700 mb-2">
                                                Format
                                            </label>
                                            <select name="photo_format" id="photo_format"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                                <option value="">Pilih Format</option>
                                                <option value="jpeg" {{ old('photo_format') === 'jpeg' ? 'selected' : '' }}>JPEG</option>
                                                <option value="jpg" {{ old('photo_format') === 'jpg' ? 'selected' : '' }}>JPG</option>
                                                <option value="png" {{ old('photo_format') === 'png' ? 'selected' : '' }}>PNG</option>
                                                <option value="raw" {{ old('photo_format') === 'raw' ? 'selected' : '' }}>RAW</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="photo_gallery_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Link Gallery (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="photo_gallery_link" id="photo_gallery_link" value="{{ old('photo_gallery_link') }}"
                                            placeholder="https://flickr.com/... atau https://500px.com/... atau https://unsplash.com/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke Flickr, 500px, Unsplash, atau platform gallery lainnya</p>
                                        @error('photo_gallery_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Design Ecosystem Fields -->
                                <div id="design-fields" class="hidden p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-purple-900 mb-3">Detail Design</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="design_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe Design
                                            </label>
                                            <select name="design_type" id="design_type"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                                <option value="">Pilih Tipe</option>
                                                <option value="logo" {{ old('design_type') === 'logo' ? 'selected' : '' }}>Logo</option>
                                                <option value="flyer" {{ old('design_type') === 'flyer' ? 'selected' : '' }}>Flyer</option>
                                                <option value="icon" {{ old('design_type') === 'icon' ? 'selected' : '' }}>Icon</option>
                                                <option value="illustration" {{ old('design_type') === 'illustration' ? 'selected' : '' }}>Illustration</option>
                                                <option value="print" {{ old('design_type') === 'print' ? 'selected' : '' }}>Print</option>
                                                <option value="branding" {{ old('design_type') === 'branding' ? 'selected' : '' }}>Branding</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="design_format" class="block text-sm font-medium text-gray-700 mb-2">
                                                Format
                                            </label>
                                            <select name="design_format" id="design_format"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                                <option value="">Pilih Format</option>
                                                <option value="ai" {{ old('design_format') === 'ai' ? 'selected' : '' }}>AI (Illustrator)</option>
                                                <option value="psd" {{ old('design_format') === 'psd' ? 'selected' : '' }}>PSD (Photoshop)</option>
                                                <option value="eps" {{ old('design_format') === 'eps' ? 'selected' : '' }}>EPS</option>
                                                <option value="pdf" {{ old('design_format') === 'pdf' ? 'selected' : '' }}>PDF</option>
                                                <option value="svg" {{ old('design_format') === 'svg' ? 'selected' : '' }}>SVG</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="design_preview_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Link Preview (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="design_preview_link" id="design_preview_link" value="{{ old('design_preview_link') }}"
                                            placeholder="https://behance.net/... atau https://dribbble.com/... atau https://figma.com/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke Behance, Dribbble, Figma, atau platform preview lainnya</p>
                                        @error('design_preview_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Audio Ecosystem Fields -->
                                <div id="audio-fields" class="hidden p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-yellow-900 mb-3">Detail Audio</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label for="audio_duration" class="block text-sm font-medium text-gray-700 mb-2">
                                                Durasi (detik)
                                            </label>
                                            <input type="number" name="audio_duration" id="audio_duration" value="{{ old('audio_duration') }}"
                                                min="1" placeholder="120"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                                        </div>
                                        <div>
                                            <label for="audio_format" class="block text-sm font-medium text-gray-700 mb-2">
                                                Format
                                            </label>
                                            <select name="audio_format" id="audio_format"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                                                <option value="">Pilih Format</option>
                                                <option value="mp3" {{ old('audio_format') === 'mp3' ? 'selected' : '' }}>MP3</option>
                                                <option value="wav" {{ old('audio_format') === 'wav' ? 'selected' : '' }}>WAV</option>
                                                <option value="flac" {{ old('audio_format') === 'flac' ? 'selected' : '' }}>FLAC</option>
                                                <option value="aac" {{ old('audio_format') === 'aac' ? 'selected' : '' }}>AAC</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="audio_genre" class="block text-sm font-medium text-gray-700 mb-2">
                                                Genre
                                            </label>
                                            <input type="text" name="audio_genre" id="audio_genre" value="{{ old('audio_genre') }}"
                                                placeholder="Pop, Rock, Electronic, dll"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                                        </div>
                                    </div>
                                    <div>
                                        <label for="audio_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                                </svg>
                                                Link Audio (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="audio_link" id="audio_link" value="{{ old('audio_link') }}"
                                            placeholder="https://soundcloud.com/... atau https://spotify.com/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke SoundCloud, Spotify, atau platform audio lainnya</p>
                                        @error('audio_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Video Ecosystem Fields -->
                                <div id="video-fields" class="hidden p-4 bg-red-50 border border-red-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-red-900 mb-3">Detail Video</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                        <div>
                                            <label for="video_duration" class="block text-sm font-medium text-gray-700 mb-2">
                                                Durasi (detik)
                                            </label>
                                            <input type="number" name="video_duration" id="video_duration" value="{{ old('video_duration') }}"
                                                min="1" placeholder="60"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500">
                                        </div>
                                        <div>
                                            <label for="video_resolution" class="block text-sm font-medium text-gray-700 mb-2">
                                                Resolusi
                                            </label>
                                            <input type="text" name="video_resolution" id="video_resolution" value="{{ old('video_resolution') }}"
                                                placeholder="1920x1080, 4K, dll"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500">
                                        </div>
                                        <div>
                                            <label for="video_format" class="block text-sm font-medium text-gray-700 mb-2">
                                                Format
                                            </label>
                                            <select name="video_format" id="video_format"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500">
                                                <option value="">Pilih Format</option>
                                                <option value="mp4" {{ old('video_format') === 'mp4' ? 'selected' : '' }}>MP4</option>
                                                <option value="mov" {{ old('video_format') === 'mov' ? 'selected' : '' }}>MOV</option>
                                                <option value="avi" {{ old('video_format') === 'avi' ? 'selected' : '' }}>AVI</option>
                                                <option value="webm" {{ old('video_format') === 'webm' ? 'selected' : '' }}>WebM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="video_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                                Link Video (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="video_link" id="video_link" value="{{ old('video_link') }}"
                                            placeholder="https://youtube.com/watch?v=... atau https://vimeo.com/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke YouTube, Vimeo, atau platform video lainnya</p>
                                        @error('video_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Theme Ecosystem Fields -->
                                <div id="theme-fields" class="hidden p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-indigo-900 mb-3">Detail Theme</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="theme_platform" class="block text-sm font-medium text-gray-700 mb-2">
                                                Platform
                                            </label>
                                            <select name="theme_platform" id="theme_platform"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Pilih Platform</option>
                                                <option value="wordpress" {{ old('theme_platform') === 'wordpress' ? 'selected' : '' }}>WordPress</option>
                                                <option value="shopify" {{ old('theme_platform') === 'shopify' ? 'selected' : '' }}>Shopify</option>
                                                <option value="html" {{ old('theme_platform') === 'html' ? 'selected' : '' }}>HTML</option>
                                                <option value="drupal" {{ old('theme_platform') === 'drupal' ? 'selected' : '' }}>Drupal</option>
                                                <option value="magento" {{ old('theme_platform') === 'magento' ? 'selected' : '' }}>Magento</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="theme_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe Theme
                                            </label>
                                            <select name="theme_type" id="theme_type"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                                                <option value="">Pilih Tipe</option>
                                                <option value="business" {{ old('theme_type') === 'business' ? 'selected' : '' }}>Business</option>
                                                <option value="ecommerce" {{ old('theme_type') === 'ecommerce' ? 'selected' : '' }}>E-commerce</option>
                                                <option value="blog" {{ old('theme_type') === 'blog' ? 'selected' : '' }}>Blog</option>
                                                <option value="portfolio" {{ old('theme_type') === 'portfolio' ? 'selected' : '' }}>Portfolio</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="theme_preview_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Link Preview/Live Demo (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="theme_preview_link" id="theme_preview_link" value="{{ old('theme_preview_link') }}"
                                            placeholder="https://demo.example.com atau https://themeforest.net/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke live demo atau preview theme</p>
                                        @error('theme_preview_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <!-- 3D Ecosystem Fields -->
                                <div id="three-d-fields" class="hidden p-4 bg-teal-50 border border-teal-200 rounded-lg">
                                    <h4 class="text-sm font-semibold text-teal-900 mb-3">Detail 3D</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <label for="three_d_format" class="block text-sm font-medium text-gray-700 mb-2">
                                                Format
                                            </label>
                                            <select name="three_d_format" id="three_d_format"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                                                <option value="">Pilih Format</option>
                                                <option value="obj" {{ old('three_d_format') === 'obj' ? 'selected' : '' }}>OBJ</option>
                                                <option value="fbx" {{ old('three_d_format') === 'fbx' ? 'selected' : '' }}>FBX</option>
                                                <option value="blend" {{ old('three_d_format') === 'blend' ? 'selected' : '' }}>Blend</option>
                                                <option value="dae" {{ old('three_d_format') === 'dae' ? 'selected' : '' }}>DAE</option>
                                                <option value="3ds" {{ old('three_d_format') === '3ds' ? 'selected' : '' }}>3DS</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="three_d_type" class="block text-sm font-medium text-gray-700 mb-2">
                                                Tipe 3D
                                            </label>
                                            <select name="three_d_type" id="three_d_type"
                                                autocomplete="off" data-form-filler="ignore"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                                                <option value="">Pilih Tipe</option>
                                                <option value="model" {{ old('three_d_type') === 'model' ? 'selected' : '' }}>Model</option>
                                                <option value="texture" {{ old('three_d_type') === 'texture' ? 'selected' : '' }}>Texture</option>
                                                <option value="rig" {{ old('three_d_type') === 'rig' ? 'selected' : '' }}>Rig</option>
                                                <option value="animation" {{ old('three_d_type') === 'animation' ? 'selected' : '' }}>Animation</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="three_d_preview_link" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Link Preview (Optional)
                                            </span>
                                        </label>
                                        <input type="url" name="three_d_preview_link" id="three_d_preview_link" value="{{ old('three_d_preview_link') }}"
                                            placeholder="https://sketchfab.com/... atau https://poly.google.com/..."
                                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-teal-500 focus:ring-2 focus:ring-teal-500">
                                        <p class="mt-1 text-xs text-gray-500">Link ke Sketchfab, Poly, atau platform preview 3D lainnya</p>
                                        @error('three_d_preview_link')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Language & Scheduling -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="language" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.note_language') }}
                                    </label>
                                    <select name="language" id="language"
                                        autocomplete="off" data-form-filler="ignore"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                        @foreach($languages as $code => $label)
                                            <option value="{{ $code }}" {{ old('language')===$code ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('language')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="scheduled_publish_at" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.scheduled_publish_at') }} ({{ __('messages.optional') }})
                                    </label>
                                    <input type="datetime-local" name="scheduled_publish_at" id="scheduled_publish_at" value="{{ old('scheduled_publish_at') }}"
                                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                    <p class="mt-2 text-xs text-gray-600">{{ __('messages.scheduled_publish_help') }}</p>
                                    @error('scheduled_publish_at')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Content (Rich Text Editor) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.content') }} <span class="text-red-500 font-bold" title="Wajib diisi">*</span>
                                    <span class="text-xs text-gray-500 ml-1">(Wajib)</span>
                                </label>
                                <div class="mt-1" id="editor-wrapper" style="min-height: 300px;">
                                    <div id="content-editor" style="min-height: 300px;" class="@error('content') border-2 border-red-500 rounded-lg @enderror"></div>
                                </div>
                                <textarea name="content" id="content" class="hidden" required>{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="mt-2 text-xs text-gray-500">
                                    <span class="font-semibold text-red-500">*</span> Field ini wajib diisi untuk membuat note.
                                </p>
                            </div>

                            <!-- Summary -->
                            <div>
                                <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.summary') }} <span class="text-xs text-gray-500">(Optional)</span>
                                </label>
                                <textarea name="summary" id="summary" rows="3"
                                    placeholder="Enter a brief summary of your note..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('summary') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.brief_summary_note') }}</p>
                                @error('summary')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preview Content (For paid notes - shown before purchase) -->
                            <div id="preview-content-wrapper"
                                style="display: {{ old('price', 0) > 0 ? 'block' : 'none' }};">
                                <label for="preview_content" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.preview_content') }} <span
                                        class="text-xs text-gray-500">{{ __('messages.preview_content_optional') }}</span>
                                </label>
                                <textarea name="preview_content" id="preview_content" rows="3" maxlength="300"
                                    placeholder="{{ __('messages.enter_preview_for_buyers') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('preview_content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('preview_content') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span id="preview-char-count">0</span>/300 {{ __('messages.characters') }}.
                                    @if (empty(old('preview_content')))
                                        {{ __('messages.auto_generated_from_content') }}
                                    @endif
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
                                            min="0" max="100" step="5"
                                            value="{{ old('preview_percentage', 0) }}"
                                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        <div class="w-20 text-center">
                                            <span id="preview-percentage-value"
                                                class="text-lg font-semibold text-blue-600">{{ old('preview_percentage', 0) }}</span>
                                            <span class="text-sm text-gray-600">%</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="text-xs text-gray-600">
                                            <p><strong>0%</strong> = Konten terkunci sepenuhnya (hanya preview text di atas)
                                            </p>
                                            <p><strong>50%</strong> = Setengah baris konten terlihat (misal: 100 baris → 50
                                                baris terlihat)</p>
                                            <p><strong>100%</strong> = Konten terlihat penuh (tidak ada kunci)</p>
                                            <p class="mt-1 text-gray-500">Preview dihitung berdasarkan
                                                <strong>baris</strong>, bukan karakter. File attachments tetap terkunci
                                                sebelum pembelian.
                                            </p>
                                        </div>
                                    </div>
                                    @error('preview_percentage')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Thumbnail Images -->
                            <div id="thumbnail-wrapper" class="mt-6">
                                <label for="thumbnails" class="block text-sm font-medium text-gray-700 mb-2">
                                    Thumbnail Images <span class="text-xs text-gray-500">(Maksimal 5 gambar,
                                        opsional)</span>
                                </label>
                                <div
                                    class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-4h-12m-6 4h.01M17 8h.01"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center items-center">
                                            <label for="thumbnails"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload gambar</span>
                                                <input type="file" name="thumbnails[]" id="thumbnails" multiple
                                                    accept="image/*" class="sr-only"
                                                    onchange="handleThumbnailUpload(this)">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 10MB per gambar (maks 10 file)</p>
                                    </div>
                                </div>
                                <div id="thumbnail-preview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <!-- Thumbnail previews will be inserted here -->
                                </div>
                                @error('thumbnails')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('thumbnails.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Video Preview -->
                            <div class="mt-6">
                                <label for="video_preview" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        Video Preview <span class="text-xs text-gray-500 font-normal">(Maksimal 2 menit, opsional)</span>
                                    </span>
                                </label>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                                    <p class="text-xs text-blue-800 mb-2">
                                        🎬 <strong>Tips:</strong> Upload video preview untuk menarik perhatian buyer. Video akan otomatis generate thumbnail dan dapat di-play dengan hover.
                                    </p>
                                    <ul class="text-xs text-blue-700 space-y-1 list-disc list-inside">
                                        <li>Format: MP4, WebM, OGG, QuickTime</li>
                                        <li>Maksimal durasi: 2 menit</li>
                                        <li>Maksimal ukuran: 100MB</li>
                                        <li>Thumbnail akan otomatis di-generate</li>
                                    </ul>
                                </div>
                                <div class="mt-1 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-red-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center items-center">
                                            <label for="video_preview"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                                <span>Upload video</span>
                                                <input type="file" name="video_preview" id="video_preview"
                                                    accept="video/mp4,video/webm,video/ogg,video/quicktime" class="sr-only"
                                                    onchange="handleVideoPreviewUpload(this)">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">MP4, WebM, OGG, QuickTime hingga 100MB (maks 2 menit)</p>
                                    </div>
                                </div>
                                <div id="video-preview-container" class="mt-4 hidden">
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">Video Preview</span>
                                            <button type="button" onclick="removeVideoPreview()" class="text-red-600 hover:text-red-800 text-sm">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        <video id="video-preview-player" class="w-full rounded-lg" controls>
                                            <source id="video-preview-source" src="" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <p id="video-preview-info" class="mt-2 text-xs text-gray-600"></p>
                                    </div>
                                </div>
                                @error('video_preview')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- File Attachments -->
                            <div>
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.file_attachments') }}
                                    <span class="text-xs text-gray-500 font-normal">
                                        {{ __('messages.file_attachments_optional') }}
                                    </span>
                                </label>
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48" aria-hidden="true">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="attachments"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>{{ __('messages.upload_files') }}</span>
                                                <input id="attachments" name="attachments[]" type="file" multiple
                                                    accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx"
                                                    class="sr-only">
                                            </label>
                                            <p class="pl-1">{{ __('messages.or_drag_and_drop') }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            Maksimal 10MB per file (maksimal 10 file)
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

                            <!-- Demo Link (for apps/software) - Hidden when ecosystem is selected -->
                            <div id="demo-link-wrapper" class="mt-6">
                                <label for="demo_link" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Link Demo Aplikasi/Software (Optional)
                                    </span>
                                    <span class="text-xs text-gray-500 font-normal ml-7">
                                        (Hanya untuk note tanpa ecosystem. Jika memilih ecosystem, gunakan field demo link di bagian ecosystem)
                                    </span>
                                </label>
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                    <p class="text-xs text-green-800 mb-2">
                                        🚀 <strong>Tips:</strong> Jika produk Anda adalah aplikasi, software, atau website (tanpa ecosystem), masukkan link demo/live preview di sini. 
                                        Contoh: https://demo.example.com, https://app.example.com/demo, atau link ke Vercel/Netlify preview.
                                    </p>
                                </div>
                                <input type="url" 
                                    name="demo_link" 
                                    id="demo_link" 
                                    value="{{ old('demo_link') }}"
                                    placeholder="https://demo.example.com atau https://app.example.com/demo"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200 @error('demo_link') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                >
                                <p class="mt-1 text-xs text-gray-500">
                                    Link demo akan ditampilkan dengan badge khusus di halaman note untuk menarik perhatian buyer.
                                </p>
                                @error('demo_link')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- External Links (for large files) -->
                            <div class="mt-6">
                                <label for="external_links" class="block text-sm font-medium text-gray-700 mb-2">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                        </svg>
                                        External Links (untuk file besar >10MB)
                                    </span>
                                    <span class="text-xs text-gray-500 font-normal ml-7">
                                        (Opsional - gunakan link eksternal untuk menghemat memori server)
                                    </span>
                                </label>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                                    <p class="text-xs text-blue-800 mb-2">
                                        💡 <strong>Tips:</strong> Untuk file besar (>10MB), gunakan link eksternal dari Google Drive, Dropbox, atau hosting lainnya. 
                                        Satu link per baris.
                                    </p>
                                </div>
                                <textarea 
                                    name="external_links" 
                                    id="external_links" 
                                    rows="5"
                                    placeholder="https://drive.google.com/file/d/...&#10;https://dropbox.com/s/...&#10;https://example.com/file.zip"
                                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('external_links') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                                >{{ old('external_links') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    Masukkan link eksternal (satu per baris). Link akan ditampilkan sebagai attachment yang dapat diakses langsung.
                                </p>
                                @error('external_links')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('external_links.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Content Protection -->
                            @include('notes.partials.content-protection')

                            <!-- Price and Public Toggle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.price') }} <span
                                            class="text-xs text-gray-500 font-normal">{{ __('messages.price_rp_0_for_free') }}</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="price" id="price"
                                            value="{{ old('price', 0) }}" min="0" step="0.01" placeholder="0"
                                            class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    </div>
                                    <div class="mt-2 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-xs text-gray-600">
                                            <strong class="text-green-700">{{ __('messages.free') }}</strong>:
                                            {{ __('messages.share_knowledge_freely') }} •
                                            <strong class="text-blue-700">{{ __('messages.paid') }}</strong>:
                                            {{ __('messages.set_your_own_price') }}
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
                                    <div class="mt-4" id="discount-price-wrapper"
                                        style="display: {{ old('discount_price') || old('price', 0) > 0 ? 'block' : 'none' }};">
                                        <label for="discount_price" class="block text-sm font-medium text-gray-700 mb-2">
                                            Harga Diskon <span class="text-xs text-gray-500 font-normal">(Opsional)</span>
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" name="discount_price" id="discount_price"
                                                value="{{ old('discount_price') }}" min="0" step="0.01"
                                                placeholder="0"
                                                class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200 @error('discount_price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                        </div>
                                        <div class="mt-2 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            <p class="text-xs text-gray-600">
                                                Set harga diskon untuk menarik pembeli. Harga diskon harus lebih murah dari
                                                harga normal.
                                            </p>
                                        </div>
                                        <div id="discount-preview" class="mt-2 text-sm text-gray-600 hidden">
                                            <span class="font-medium text-green-600">Diskon: <span
                                                    id="discount-percent">0</span>%</span>
                                        </div>
                                        @error('discount_price')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Sale Mode -->
                                    <div class="mt-6">
                                        <label for="sale_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                            Mode Penjualan <span class="text-xs text-gray-500 font-normal">(Pilih sistem penjualan)</span>
                                        </label>
                                        <div class="space-y-3">
                                            <label id="sale-mode-scarcity-label" class="flex items-start p-4 rounded-lg border-2 {{ old('sale_mode', 'scarcity') === 'scarcity' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }} cursor-pointer transition-all duration-200">
                                                <input type="radio" name="sale_mode" value="scarcity" {{ old('sale_mode', 'scarcity') === 'scarcity' ? 'checked' : '' }}
                                                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                <div class="ml-3 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-gray-900">Scarcity Mode</span>
                                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded">Default</span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 mt-1">
                                                        • Buyer hanya bisa beli 1x per user<br>
                                                        • Buyer bisa resell dengan harga custom<br>
                                                        • Original creator dapat komisi di setiap penjualan<br>
                                                        • Grace period <span id="grace-period-display">{{ old('grace_period_days', 30) }}</span> hari untuk pembelian ulang
                                                    </p>
                                                </div>
                                            </label>
                                            <label id="sale-mode-standard-label" class="flex items-start p-4 rounded-lg border-2 {{ old('sale_mode') === 'standard' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }} cursor-pointer transition-all duration-200">
                                                <input type="radio" name="sale_mode" value="standard" {{ old('sale_mode') === 'standard' ? 'checked' : '' }}
                                                    class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                                <div class="ml-3 flex-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm font-semibold text-gray-900">Standard Mode</span>
                                                    </div>
                                                    <p class="text-xs text-gray-600 mt-1">
                                                        • Multiple sales (bisa dijual ke banyak buyer)<br>
                                                        • Buyer tidak bisa resell<br>
                                                        • Tidak ada komisi untuk original creator<br>
                                                        • Cocok untuk konten yang perlu diakses ulang
                                                    </p>
                                                </div>
                                            </label>
                                        </div>
                                        @error('sale_mode')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Grace Period & Relist Multiplier (only for scarcity mode) -->
                                    <div id="scarcity-settings" class="mt-4 space-y-4" style="display: {{ old('sale_mode', 'scarcity') === 'scarcity' ? 'block' : 'none' }};">
                                        <div>
                                            <label for="grace_period_days" class="block text-sm font-medium text-gray-700 mb-2">
                                                Grace Period (Hari) <span class="text-xs text-gray-500 font-normal">(Default: 30)</span>
                                            </label>
                                            <input type="number" name="grace_period_days" id="grace_period_days"
                                                value="{{ old('grace_period_days', 30) }}" min="0" max="365" step="1"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('grace_period_days') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Periode dimana buyer yang sudah menjual bisa membeli kembali dengan harga original.
                                            </p>
                                            @error('grace_period_days')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="relist_price_multiplier" class="block text-sm font-medium text-gray-700 mb-2">
                                                Relist Price Multiplier <span class="text-xs text-gray-500 font-normal">(Default: 1.5x)</span>
                                            </label>
                                            <input type="number" name="relist_price_multiplier" id="relist_price_multiplier"
                                                value="{{ old('relist_price_multiplier', 1.5) }}" min="1" max="10" step="0.1"
                                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('relist_price_multiplier') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                            <p class="mt-1 text-xs text-gray-500">
                                                Harga pembelian ulang setelah grace period = harga original × multiplier ini.
                                            </p>
                                            @error('relist_price_multiplier')
                                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-end">
                                    <label
                                        class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200 w-full">
                                        <input type="checkbox" name="is_public" value="1"
                                            {{ old('is_public') ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-200">
                                        <div class="ml-3">
                                            <span
                                                class="text-sm font-medium text-gray-700">{{ __('messages.make_public') }}</span>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ __('messages.available_in_marketplace') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Organization -->
                            @if (true)
                                <!-- Workspace & Folder Features -->
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                                    <div>
                                        <label for="folder_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                            {{ __('messages.folder_optional') }}
                                        </label>
                                        <select name="folder_id" id="folder_id"
                                            autocomplete="off" data-form-filler="ignore"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                            <option value="">{{ __('messages.none_root') }}</option>
                                            @foreach ($folders as $folder)
                                                <option value="{{ $folder->id }}"
                                                    data-workspace-id="{{ $folder->workspace_id ?? '' }}"
                                                    {{ old('folder_id', $selectedFolder?->id) == $folder->id ? 'selected' : '' }}>
                                                    {{ $folder->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('messages.organize_notes_in_folders') }}</p>
                                    </div>

                                    <div>
                                        <label for="workspace_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ __('messages.workspace_optional') }}
                                        </label>
                                        <select name="workspace_id" id="workspace_id"
                                            autocomplete="off" data-form-filler="ignore"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                            <option value="">{{ __('messages.personal') }}</option>
                                            @foreach ($workspaces as $workspace)
                                                <option value="{{ $workspace->id }}"
                                                    {{ old('workspace_id', $selectedWorkspace?->id) == $workspace->id ? 'selected' : '' }}>
                                                    {{ $workspace->name }} ({{ $workspace->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.assign_to_workspace') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            <div>
                                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tags
                                </label>
                                <div class="flex flex-wrap gap-2 mb-3 min-h-[2.5rem] p-2 border border-gray-200 rounded-lg bg-gray-50"
                                    id="tags-container">
                                    @if (old('tags'))
                                        @foreach (old('tags') as $tag)
                                            <span
                                                class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <input type="hidden" name="tags[]" value="{{ $tag }}">
                                                {{ $tag }}
                                                <button type="button"
                                                    class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <input type="text" id="tag-input"
                                            placeholder="{{ __('messages.type_tag_and_press_enter') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                    </div>
                                    <div>
                                        <select id="tag-select"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                            <option value="">{{ __('messages.select_existing_tag') }}</option>
                                            @foreach ($tags as $tag)
                                                <option value="{{ $tag->name }}">{{ $tag->name }}
                                                    ({{ $tag->notes_count }} notes)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Add tags to categorize your note. Press Enter to add a new tag or select from existing
                                    tags.
                                </p>
                                @error('tags.*')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Draft & Schedule Options -->
                            <div class="mt-6 pt-6 border-t border-gray-200 bg-gray-50 rounded-lg p-4">
                                <div class="space-y-4">
                                    <!-- Save as Draft -->
                                    <div class="flex items-center">
                                        <input type="checkbox" name="save_as_draft" id="save_as_draft" value="1"
                                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                        <label for="save_as_draft" class="ml-2 text-sm font-medium text-gray-700">
                                            {{ __('Save as Draft') }}
                                        </label>
                                    </div>
                                    
                                    <!-- Schedule Publishing -->
                                    <div id="schedule-section" class="hidden">
                                        <label for="scheduled_at" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ __('Schedule Publishing') }}
                                        </label>
                                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('The note will be published automatically at the scheduled time.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                                <a href="{{ route('notes.index') }}"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    Cancel
                                </a>
                                <button type="submit" id="submit-btn"
                                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span id="submit-text">{{ __('Create Note') }}</span>
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
                    // Import Quill Delta for content insertion
                    const Delta = Quill.import('delta');

                    // Initialize Quill Rich Text Editor
                    const quill = new Quill('#content-editor', {
                        theme: 'snow',
                        placeholder: 'Write your note content here...',
                        modules: {
                            toolbar: [
                                [{
                                    'header': [1, 2, 3, false]
                                }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{
                                    'color': []
                                }, {
                                    'background': []
                                }],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                ['blockquote', 'code-block'],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    const walletTopupUrl = '{{ route('wallet.index') }}';

                    // Get textarea element
                    const contentTextarea = document.getElementById('content');

                    // Sync Quill content to hidden textarea for form submission
                    quill.on('text-change', function() {
                        contentTextarea.value = quill.root.innerHTML;
                    });

                    // Set initial content if exists (from old input)
                    if (contentTextarea.value) {
                        quill.root.innerHTML = contentTextarea.value;
                    }

                    // Ecosystem-specific fields toggle
                    const ecosystemSelect = document.getElementById('ecosystem_category');
                    const ecosystemFields = {
                        'code': document.getElementById('code-fields'),
                        'photo': document.getElementById('photo-fields'),
                        'design': document.getElementById('design-fields'),
                        'audio': document.getElementById('audio-fields'),
                        'video': document.getElementById('video-fields'),
                        'theme': document.getElementById('theme-fields'),
                        '3d': document.getElementById('three-d-fields'),
                        'elements': null, // Elements doesn't have specific fields
                    };
                    
                    // Ecosystems that have their own demo/preview link fields
                    const ecosystemsWithDemoLink = ['code', 'photo', 'design', 'audio', 'video', 'theme', '3d'];
                    const demoLinkWrapper = document.getElementById('demo-link-wrapper');

                    function toggleEcosystemFields() {
                        const selectedEcosystem = ecosystemSelect.value;
                        
                        // Hide all ecosystem fields first
                        Object.values(ecosystemFields).forEach(field => {
                            if (field) {
                                field.classList.add('hidden');
                            }
                        });
                        
                        // Show selected ecosystem fields
                        if (selectedEcosystem && ecosystemFields[selectedEcosystem]) {
                            ecosystemFields[selectedEcosystem].classList.remove('hidden');
                        }
                        
                        // Hide general demo_link if ecosystem with its own demo link is selected
                        if (demoLinkWrapper) {
                            if (selectedEcosystem && ecosystemsWithDemoLink.includes(selectedEcosystem)) {
                                demoLinkWrapper.classList.add('hidden');
                            } else {
                                demoLinkWrapper.classList.remove('hidden');
                            }
                        }
                    }

                    // Initial toggle based on selected value (from old input)
                    toggleEcosystemFields();
                    
                    // Listen for changes
                    ecosystemSelect.addEventListener('change', toggleEcosystemFields);

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
                                updatePriceGuidanceUI();
                            }
                        }
                    });

                    function tagExists(tagName) {
                        return Array.from(tagsContainer.querySelectorAll('input[type="hidden"]'))
                            .some(input => input.value.toLowerCase() === tagName.toLowerCase());
                    }

                    function addTag(tagName) {
                        const tagItem = document.createElement('span');
                        tagItem.className =
                            'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200';
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

                    // Preview content character counter
                    const previewContentTextarea = document.getElementById('preview_content');
                    const previewCharCount = document.getElementById('preview-char-count');
                    if (previewContentTextarea && previewCharCount) {
                        previewCharCount.textContent = (previewContentTextarea.value || '').length;
                        previewContentTextarea.addEventListener('input', function() {
                            previewCharCount.textContent = this.value.length;
                        });
                    }

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
                            updatePriceGuidanceUI();
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

                    // Filter folders based on selected workspace
                    const workspaceSelect = document.getElementById('workspace_id');
                    const folderSelect = document.getElementById('folder_id');

                    if (workspaceSelect && folderSelect) {
                        function filterFoldersByWorkspace() {
                            const selectedWorkspaceId = workspaceSelect.value;
                            const folderOptions = folderSelect.querySelectorAll('option[data-workspace-id]');

                            folderOptions.forEach(option => {
                                const folderWorkspaceId = option.getAttribute('data-workspace-id') || '';

                                // Show folder if:
                                // 1. No workspace selected (personal) AND folder has no workspace_id (personal folder)
                                // 2. Workspace selected AND folder's workspace_id matches selected workspace
                                if (selectedWorkspaceId === '') {
                                    // Personal mode: only show folders without workspace_id
                                    option.style.display = folderWorkspaceId === '' ? '' : 'none';
                                } else {
                                    // Workspace mode: only show folders that belong to selected workspace
                                    option.style.display = folderWorkspaceId === selectedWorkspaceId ? '' : 'none';
                                }
                            });

                            // Reset folder selection if current selection is hidden
                            const selectedOption = folderSelect.options[folderSelect.selectedIndex];
                            if (selectedOption && selectedOption.style.display === 'none' && selectedOption.value !== '') {
                                folderSelect.value = '';
                            }
                        }

                        // Filter on page load
                        filterFoldersByWorkspace();

                        // Filter when workspace changes
                        workspaceSelect.addEventListener('change', filterFoldersByWorkspace);
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

                    // File upload preview with background upload for files > 5MB
                    const fileInput = document.getElementById('attachments');
                    const fileList = document.getElementById('file-list');
                    const uploadProgressContainer = document.getElementById('upload-progress-container');
                    const uploadProgressBar = document.getElementById('upload-progress-bar');
                    const uploadProgressPercent = document.getElementById('upload-progress-percent');
                    const uploadProgressText = document.getElementById('upload-progress-text');
                    const MAX_FILE_SIZE = 10485760; // 10MB in bytes
                    const MAX_FILES = 10; // Maximum 10 files per note
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
                                    // Check response status and content type before parsing
                                    const contentType = xhr.getResponseHeader('content-type') || '';
                                    const isJson = contentType.includes('application/json');
                                    
                                    // Check if response is empty
                                    if (!xhr.responseText || xhr.responseText.trim() === '') {
                                        const error = 'Server tidak mengembalikan response. File akan otomatis diupload saat form disubmit.';
                                        fileUploadStatus.set(fileIndex, { status: 'error', error: error, canRetry: true });
                                        updateFileItemStatus(fileIndex, 'error', 0, error);
                                        reject(new Error(error));
                                        return;
                                    }
                                    
                                    // Handle non-JSON responses (HTML error pages, etc.)
                                    if (!isJson) {
                                        let error = 'Server mengembalikan response yang tidak valid. ';
                                        
                                        // Check for common error status codes
                                        if (xhr.status === 413) {
                                            error = 'File terlalu besar (413 Request Entity Too Large). Pastikan nginx client_max_body_size sudah dikonfigurasi dengan benar. File akan otomatis diupload saat form disubmit.';
                                        } else if (xhr.status === 500) {
                                            error = 'Server error (500). File akan otomatis diupload saat form disubmit. Silakan hubungi administrator jika masalah berlanjut.';
                                        } else if (xhr.status === 504 || xhr.status === 524) {
                                            // 504 = Gateway Timeout, 524 = Cloudflare Timeout
                                            error = `Upload timeout (${xhr.status}). File terlalu besar atau koneksi lambat. Pastikan nginx timeout dan Cloudflare timeout sudah dikonfigurasi dengan benar. File akan otomatis diupload saat form disubmit.`;
                                        } else if (xhr.status >= 400) {
                                            error = `Server error (${xhr.status}). File akan otomatis diupload saat form disubmit.`;
                                        } else {
                                            error += 'File akan otomatis diupload saat form disubmit.';
                                        }
                                        
                                        console.error('Non-JSON response from server:', {
                                            status: xhr.status,
                                            contentType: contentType,
                                            responsePreview: xhr.responseText.substring(0, 200)
                                        });
                                        
                                        fileUploadStatus.set(fileIndex, { status: 'error', error: error, canRetry: true });
                                        updateFileItemStatus(fileIndex, 'error', 0, error);
                                        reject(new Error(error));
                                        return;
                                    }
                                    
                                    try {
                                        const response = JSON.parse(xhr.responseText);
                                        
                                        // Handle successful response
                                        if (xhr.status === 200 && response.success) {
                                            backgroundUploadIds.push(response.upload_id);
                                            fileUploadStatus.set(fileIndex, { status: 'completed', uploadId: response.upload_id });
                                            updateFileItemStatus(fileIndex, 'completed');
                                            resolve(response);
                                            return;
                                        }
                                        
                                        // Handle error response (JSON but not success)
                                        let errorMessage = response.error || response.message || 'Upload failed';
                                        
                                        // Show file size limit info
                                        if (file.size > MAX_FILE_SIZE) {
                                            errorMessage += ' Maksimal 10MB per file.';
                                        }
                                        
                                        // Show server limits if provided
                                        if (response.server_limits) {
                                            errorMessage += ` (Limit server: ${response.server_limits.upload_max_filesize})`;
                                        }
                                        
                                        fileUploadStatus.set(fileIndex, { status: 'error', error: errorMessage, canRetry: response.can_retry !== false });
                                        updateFileItemStatus(fileIndex, 'error', 0, errorMessage);
                                        
                                        // Show SweetAlert for important errors (but don't block - file will upload on form submit)
                                        if (file.size > MAX_FILE_SIZE) {
                                            Swal.fire({
                                                icon: 'warning',
                                                title: 'File Terlalu Besar',
                                                html: errorMessage,
                                                confirmButtonText: 'OK'
                                            });
                                        }
                                        
                                        reject(new Error(errorMessage));
                                        
                                    } catch (e) {
                                        // JSON parse error
                                        console.error('Failed to parse JSON response:', {
                                            error: e.message,
                                            status: xhr.status,
                                            responsePreview: xhr.responseText.substring(0, 500),
                                            contentType: contentType
                                        });
                                        
                                        let error = 'Gagal memparse response server. ';
                                        
                                        // Provide more specific error based on status code
                                        if (xhr.status === 413) {
                                            error = 'File terlalu besar (413). Pastikan nginx client_max_body_size sudah dikonfigurasi. File akan otomatis diupload saat form disubmit.';
                                        } else if (xhr.status === 500) {
                                            error = 'Server error (500). File akan otomatis diupload saat form disubmit.';
                                        } else if (xhr.status === 504 || xhr.status === 524) {
                                            // 504 = Gateway Timeout, 524 = Cloudflare Timeout
                                            error = `Upload timeout (${xhr.status}). File terlalu besar atau koneksi lambat. Pastikan nginx timeout dan Cloudflare timeout sudah dikonfigurasi. File akan otomatis diupload saat form disubmit.`;
                                        } else if (xhr.status === 0) {
                                            error = 'Koneksi terputus atau timeout. File akan otomatis diupload saat form disubmit.';
                                        } else {
                                            error += 'File akan otomatis diupload saat form disubmit.';
                                        }
                                        
                                        fileUploadStatus.set(fileIndex, { status: 'error', error: error, canRetry: true });
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
                                
                                // Set timeout - 15 minutes for very large files (51MB+)
                                xhr.timeout = 900000; // 15 minutes (900 seconds)
                                
                                // Set request headers to ensure JSON response
                                xhr.setRequestHeader('Accept', 'application/json');
                                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                                
                                xhr.open('POST', '{{ route("notes.upload-background") }}');
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
                                const isLargeFile = false; // All files use standard upload (max 10MB)
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
                                    // Keep files that haven't exceeded limits
                                    // Remove large files that were successfully uploaded in background
                                    // Keep all files (standard upload, max 10MB)
                                    if (file.size <= MAX_FILE_SIZE) {
                                        filesToKeep.push(file); // Keep files within limit
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

                    // AI features removed - all AI functionality has been moved to Workspace UI

                    // Toggle scarcity settings and border based on sale_mode
                    const saleModeInputs = document.querySelectorAll('input[name="sale_mode"]');
                    const scarcitySettings = document.getElementById('scarcity-settings');
                    const scarcityLabel = document.getElementById('sale-mode-scarcity-label');
                    const standardLabel = document.getElementById('sale-mode-standard-label');
                    
                    if (saleModeInputs.length > 0) {
                        function updateSaleModeUI() {
                            const selectedMode = document.querySelector('input[name="sale_mode"]:checked')?.value;
                            
                            // Update border and background for scarcity label
                            if (scarcityLabel) {
                                if (selectedMode === 'scarcity') {
                                    scarcityLabel.classList.remove('border-gray-200', 'hover:border-gray-300');
                                    scarcityLabel.classList.add('border-blue-500', 'bg-blue-50');
                                } else {
                                    scarcityLabel.classList.remove('border-blue-500', 'bg-blue-50');
                                    scarcityLabel.classList.add('border-gray-200', 'hover:border-gray-300');
                                }
                            }
                            
                            // Update border and background for standard label
                            if (standardLabel) {
                                if (selectedMode === 'standard') {
                                    standardLabel.classList.remove('border-gray-200', 'hover:border-gray-300');
                                    standardLabel.classList.add('border-blue-500', 'bg-blue-50');
                                } else {
                                    standardLabel.classList.remove('border-blue-500', 'bg-blue-50');
                                    standardLabel.classList.add('border-gray-200', 'hover:border-gray-300');
                                }
                            }
                            
                            // Toggle scarcity settings visibility
                            if (scarcitySettings) {
                                if (selectedMode === 'scarcity') {
                                    scarcitySettings.style.display = 'block';
                                } else {
                                    scarcitySettings.style.display = 'none';
                                }
                            }
                        }
                        
                        saleModeInputs.forEach(input => {
                            input.addEventListener('change', updateSaleModeUI);
                        });
                        
                        // Initialize on page load
                        updateSaleModeUI();
                    }

                    // Draft & Schedule functionality
                    const draftCheckbox = document.getElementById('save_as_draft');
                    const scheduleSection = document.getElementById('schedule-section');
                    const scheduledAtInput = document.getElementById('scheduled_at');
                    const submitBtn = document.getElementById('submit-btn');
                    const submitText = document.getElementById('submit-text');

                    if (draftCheckbox) {
                        draftCheckbox.addEventListener('change', function() {
                            if (this.checked) {
                                scheduleSection.classList.remove('hidden');
                                submitText.textContent = '{{ __('Save as Draft') }}';
                            } else {
                                scheduleSection.classList.add('hidden');
                                scheduledAtInput.value = '';
                                submitText.textContent = '{{ __('Create Note') }}';
                            }
                        });

                        // Check if scheduled_at is set, show schedule section
                        if (scheduledAtInput && scheduledAtInput.value) {
                            scheduleSection.classList.remove('hidden');
                            draftCheckbox.checked = false;
                        }

                        // Update submit text based on schedule
                        scheduledAtInput.addEventListener('change', function() {
                            if (this.value) {
                                submitText.textContent = '{{ __('Schedule Note') }}';
                                draftCheckbox.checked = false;
                            } else if (!draftCheckbox.checked) {
                                submitText.textContent = '{{ __('Create Note') }}';
                            }
                        });
                    }

                    // Video Preview Upload Handler
                    window.handleVideoPreviewUpload = function(input) {
                        const file = input.files[0];
                        if (!file) return;

                        // Validate file type
                        const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];
                        if (!allowedTypes.includes(file.type)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format Video Tidak Valid',
                                text: 'Format video harus MP4, WebM, OGG, atau QuickTime.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            input.value = '';
                            return;
                        }

                        // Validate file size (100MB)
                        const maxSize = 100 * 1024 * 1024; // 100MB
                        if (file.size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: 'Ukuran video maksimal 100MB.',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            input.value = '';
                            return;
                        }

                        // Show video preview
                        const container = document.getElementById('video-preview-container');
                        const player = document.getElementById('video-preview-player');
                        const source = document.getElementById('video-preview-source');
                        const info = document.getElementById('video-preview-info');

                        const url = URL.createObjectURL(file);
                        source.src = url;
                        player.load();

                        // Format file size
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        info.textContent = `File: ${file.name} (${fileSizeMB} MB)`;

                        container.classList.remove('hidden');
                    };

                    // Remove Video Preview
                    window.removeVideoPreview = function() {
                        const input = document.getElementById('video_preview');
                        const container = document.getElementById('video-preview-container');
                        const player = document.getElementById('video-preview-player');
                        const source = document.getElementById('video-preview-source');
                        const info = document.getElementById('video-preview-info');

                        if (input) input.value = '';
                        if (source) {
                            const url = source.src;
                            if (url && url.startsWith('blob:')) {
                                URL.revokeObjectURL(url);
                            }
                            source.src = '';
                        }
                        if (player) {
                            player.pause();
                            player.load();
                        }
                        if (info) info.textContent = '';
                        if (container) container.classList.add('hidden');
                    };
                });
            </script>
        @endpush
    @endsection
