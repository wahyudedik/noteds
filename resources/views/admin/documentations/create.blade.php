@extends('layouts.app')

@section('title', __('messages.admin_create_documentation'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.admin_create_documentation') }}</h1>
                    <p class="mt-2 text-base text-gray-600">{{ __('messages.create_new_documentation') }}</p>
                </div>
                <a href="{{ route('admin.documentations.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    ← {{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>

        <form action="{{ route('admin.documentations.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.title') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        required
                        placeholder="{{ __('messages.example_how_to_create_first_note') }}"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug (Auto-generated, but can be overridden) -->
                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.slug_auto_generated') }} <span class="text-xs text-gray-500">{{ __('messages.auto_generated_from_title') }}</span>
                    </label>
                    <input type="text" 
                        id="slug"
                        name="slug"
                        value="{{ old('slug') }}"
                        placeholder="e.g., how-to-create-first-note"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('slug') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.url_friendly_will_auto_generate') }}</p>
                    @error('slug')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.category') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="category" id="category" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('category') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">{{ __('messages.select_category') }}</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Summary -->
                <div>
                    <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.summary_brief_description') }} <span class="text-xs text-gray-500">{{ __('messages.brief_description_max_chars') }}</span>
                    </label>
                    <textarea name="summary" id="summary" rows="3" maxlength="500"
                        :placeholder="__('messages.brief_summary_placeholder')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('summary') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500"><span id="summary-char-count">{{ strlen(old('summary', '')) }}</span>/500 {{ __('messages.characters') }}</p>
                    @error('summary')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content (Rich Text Editor) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.content') }} <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1" id="editor-wrapper" style="min-height: 400px;">
                        <div id="content-editor" style="min-height: 400px;"></div>
                    </div>
                    <textarea name="content" id="content" class="hidden" required>{{ old('content') }}</textarea>
                    @error('content')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Additional Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.display_order') }}
                        </label>
                        <input type="number" 
                            id="order"
                            name="order"
                            value="{{ old('order', 0) }}"
                            min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.lower_numbers_appear_first') }}</p>
                    </div>

                    <!-- Icon -->
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.icon_emoji_or_code') }} <span class="text-xs text-gray-500">{{ __('messages.emoji_or_icon_code') }}</span>
                        </label>
                        <input type="text" 
                            id="icon"
                            name="icon"
                            value="{{ old('icon') }}"
                            placeholder="📚 or 🔧 or fa-icon"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Links (Dynamic) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.related_links') }}
                    </label>
                    <div id="links-container" class="space-y-2">
                        <!-- Links will be added here dynamically -->
                    </div>
                    <button type="button" id="add-link-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        {{ __('messages.add_link') }}
                    </button>
                </div>

                <!-- Video URLs (Dynamic) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Video URLs <span class="text-xs text-gray-500">(YouTube, Vimeo, etc.)</span>
                    </label>
                    <div id="videos-container" class="space-y-2">
                        <!-- Video URLs will be added here dynamically -->
                    </div>
                    <button type="button" id="add-video-btn" class="mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Video URL
                    </button>
                </div>

                <!-- Tags -->
                <div>
                    <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.tags') }} <span class="text-xs text-gray-500">({{ __('messages.comma_separated') }})</span>
                    </label>
                    <input type="text" 
                        id="tags"
                        name="tags_input"
                        value="{{ old('tags_input') }}"
                        placeholder="e.g., getting-started, tutorial, guide"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.separate_tags_with_commas') }}</p>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" 
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        {{ __('messages.active_visible_public') }}
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.documentations.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        {{ __('messages.create_documentation') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('styles')
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill Editor
    const quill = new Quill('#content-editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['blockquote', 'code-block'],
                ['clean']
            ]
        },
        placeholder: 'Write your documentation content here...',
    });

    // Set initial content if exists
    const contentTextarea = document.getElementById('content');
    if (contentTextarea.value) {
        quill.root.innerHTML = contentTextarea.value;
    }

    // Update textarea on content change
    quill.on('text-change', function() {
        contentTextarea.value = quill.root.innerHTML;
    });

    // Summary character counter
    const summaryTextarea = document.getElementById('summary');
    const summaryCharCount = document.getElementById('summary-char-count');
    if (summaryTextarea && summaryCharCount) {
        summaryCharCount.textContent = summaryTextarea.value.length;
        summaryTextarea.addEventListener('input', function() {
            summaryCharCount.textContent = this.value.length;
        });
    }

    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                const slug = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        slugInput.addEventListener('input', function() {
            if (this.value) {
                this.dataset.autoGenerated = 'false';
            }
        });
    }

    // Dynamic Links
    let linkIndex = 0;
    const linksContainer = document.getElementById('links-container');
    const addLinkBtn = document.getElementById('add-link-btn');

    function addLinkField(title = '', url = '') {
        const linkDiv = document.createElement('div');
        linkDiv.className = 'flex gap-2 items-start';
        linkDiv.innerHTML = `
            <input type="text" name="links[${linkIndex}][title]" value="${title}" placeholder="{{ __('messages.link_title') }}" required
                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
            <input type="url" name="links[${linkIndex}][url]" value="${url}" placeholder="https://example.com" required
                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
            <button type="button" class="remove-link text-red-600 hover:text-red-700" title="{{ __('messages.remove') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        linksContainer.appendChild(linkDiv);
        linkIndex++;

        // Add remove handler
        linkDiv.querySelector('.remove-link').addEventListener('click', function() {
            linkDiv.remove();
        });
    }

    addLinkBtn.addEventListener('click', function() {
        addLinkField();
    });

    // Dynamic Video URLs
    let videoIndex = 0;
    const videosContainer = document.getElementById('videos-container');
    const addVideoBtn = document.getElementById('add-video-btn');

    function addVideoField(url = '') {
        const videoDiv = document.createElement('div');
        videoDiv.className = 'flex gap-2 items-start';
        videoDiv.innerHTML = `
            <input type="url" name="video_urls[]" value="${url}" placeholder="https://youtube.com/watch?v=..." required
                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
            <button type="button" class="remove-video text-red-600 hover:text-red-700" title="Remove">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        `;
        videosContainer.appendChild(videoDiv);
        videoIndex++;

        // Add remove handler
        videoDiv.querySelector('.remove-video').addEventListener('click', function() {
            videoDiv.remove();
        });
    }

    addVideoBtn.addEventListener('click', function() {
        addVideoField();
    });

    // Handle tags input
    const tagsInput = document.getElementById('tags');
    tagsInput.addEventListener('blur', function() {
        // Tags will be processed in controller
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Ensure content is set
        contentTextarea.value = quill.root.innerHTML;
        
        if (!contentTextarea.value.trim()) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: @json(__('messages.content_required')),
                text: @json(__('messages.please_provide_documentation_content')),
            });
            return false;
        }
    });
});
</script>
@endpush
@endsection

