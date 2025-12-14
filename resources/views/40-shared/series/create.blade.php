@extends('40-shared/layouts/app')

@section('title', 'Create Note Series - ' . config('app.name'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Create Note Series</h1>
                <p class="text-gray-600 mt-2">Organize your notes into a series for better structure and navigation.</p>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-lg shadow p-6 md:p-8">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-medium text-red-900 mb-2">Please fix the errors below:</p>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-sm text-red-700">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('series.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-900 mb-2">Series Title *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., Advanced JavaScript Concepts" required>
                        <p class="mt-1 text-xs text-gray-500">Maximum 255 characters</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                        <textarea id="description" name="description" rows="5"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                            placeholder="Describe what this series covers...">{{ old('description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">Maximum 2000 characters</p>
                    </div>

                    <!-- Cover Image -->
                    <div>
                        <label for="cover_image" class="block text-sm font-medium text-gray-900 mb-2">Cover Image</label>
                        <div class="relative">
                            <input type="file" id="cover_image" name="cover_image" accept="image/*" class="hidden"
                                onchange="updateImagePreview(this)">
                            <button type="button" onclick="document.getElementById('cover_image').click()"
                                class="w-full border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition-colors cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-8-8l-6-6m0 0l-6 6m6-6v24"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">Click to upload or drag and drop</p>
                                <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                            </button>
                            <div id="image-preview" class="mt-4 hidden">
                                <img id="preview-img" src="" alt="Preview" class="max-h-64 mx-auto rounded-lg">
                                <button type="button" onclick="removeImagePreview()"
                                    class="mt-2 text-sm text-red-600 hover:text-red-700">Remove image</button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center">
                        <input type="checkbox" id="is_active" name="is_active" value="1" checked
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        <label for="is_active" class="ml-2 text-sm text-gray-700 cursor-pointer">
                            Make this series active and visible
                        </label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex gap-4 pt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition-colors duration-200">
                            Create Series
                        </button>
                        <a href="{{ route('series.index') }}"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-medium py-2 rounded-lg text-center transition-colors duration-200">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateImagePreview(input) {
                const preview = document.getElementById('image-preview');
                const previewImg = document.getElementById('preview-img');

                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function removeImagePreview() {
                document.getElementById('cover_image').value = '';
                document.getElementById('image-preview').classList.add('hidden');
            }
        </script>
    @endpush
@endsection
