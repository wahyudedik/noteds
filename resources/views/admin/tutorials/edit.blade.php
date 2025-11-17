@extends('layouts.app')

@section('title', 'Edit Tutorial')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Edit Tutorial</h2>
            <a href="{{ route('admin.tutorials.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Tutorials</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.tutorials.update', $tutorial) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="{{ old('title', $tutorial->title) }}"
                        required
                        placeholder="Enter tutorial title"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="mb-6">
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                        Slug (URL) <span class="text-gray-500">(optional, auto-generated from title)</span>
                    </label>
                    <input type="text" 
                        id="slug"
                        name="slug"
                        value="{{ old('slug', $tutorial->slug) }}"
                        placeholder="tutorial-url-slug"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('slug') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Auto-generated from title if left empty</p>
                    @error('slug')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea 
                        id="description"
                        name="description"
                        rows="3"
                        placeholder="Brief description of the tutorial"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description', $tutorial->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content -->
                <div class="mb-6">
                    <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                        Content <span class="text-red-600">*</span>
                    </label>
                    <div id="editor-container" style="min-height: 400px;"></div>
                    <input type="hidden" id="content" name="content" value="{{ old('content', $tutorial->content) }}" required>
                    @error('content')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category -->
                <div class="mb-6">
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                        Category <span class="text-red-600">*</span>
                    </label>
                    <select name="category" id="category" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('category') border-red-500 @enderror">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ old('category', $tutorial->category) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Thumbnail -->
                <div class="mb-6">
                    <label for="thumbnail" class="block text-sm font-medium text-gray-700 mb-2">
                        Thumbnail Image
                    </label>
                    @if($tutorial->thumbnail)
                        <div class="mb-2">
                            <img src="{{ Storage::url($tutorial->thumbnail) }}" alt="Current thumbnail" class="h-32 w-auto rounded border border-gray-300">
                        </div>
                    @endif
                    <input type="file" 
                        id="thumbnail"
                        name="thumbnail"
                        accept="image/*"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('thumbnail') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Max 2MB. Recommended: 1200x675px. Leave empty to keep current thumbnail.</p>
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Video URL -->
                <div class="mb-6">
                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Video URL (optional)
                    </label>
                    <input type="url" 
                        id="video_url"
                        name="video_url"
                        value="{{ old('video_url', $tutorial->video_url) }}"
                        placeholder="https://www.youtube.com/watch?v=..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('video_url') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">YouTube, Vimeo, or other video platform URL</p>
                    @error('video_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-600">*</span>
                    </label>
                    <select name="status" id="status" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') border-red-500 @enderror">
                        <option value="draft" {{ old('status', $tutorial->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status', $tutorial->status) === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Featured -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                            name="featured" 
                            value="1"
                            {{ old('featured', $tutorial->featured) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Featured Tutorial</span>
                    </label>
                    <p class="mt-1 text-sm text-gray-500">Featured tutorials appear at the top of the list</p>
                </div>

                <!-- Order -->
                <div class="mb-6">
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        Display Order
                    </label>
                    <input type="number" 
                        id="order"
                        name="order"
                        value="{{ old('order', $tutorial->order) }}"
                        min="0"
                        placeholder="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">Lower numbers appear first (default: 0)</p>
                </div>

                <!-- Submit -->
                <div class="flex gap-4">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Update Tutorial
                    </button>
                    <a href="{{ route('admin.tutorials.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script>
    // Initialize Quill editor
    const quill = new Quill('#editor-container', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image', 'code-block'],
                ['blockquote'],
                ['clean']
            ]
        },
        placeholder: 'Write your tutorial content here...'
    });

    // Set initial content
    const content = document.getElementById('content').value;
    if (content) {
        quill.root.innerHTML = content;
    }

    // Update hidden input on content change
    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
    });

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function(e) {
        const title = e.target.value;
        const slugInput = document.getElementById('slug');
        if (!slugInput.value || slugInput.dataset.auto === 'true') {
            slugInput.value = title.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            slugInput.dataset.auto = 'true';
        }
    });

    // Allow manual slug editing
    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.auto = 'false';
    });
</script>
@endpush
@endsection

