@extends('layouts.app')

@section('title', 'Forum')

@section('content')
@include('forum.partials.quill-assets')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Forum</h1>
                    <p class="mt-2 text-sm text-gray-600">Share your thoughts and discover notes from the community</p>
                </div>
                <a href="{{ route('forum.preferences.edit') }}"
                   class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700">
                    Manage Email Preferences
                </a>
            </div>

            <!-- Search Form -->
            <div class="mt-4">
                <form action="{{ route('forum.index') }}" method="GET" class="flex items-center space-x-2">
                    <input type="text" 
                           name="search" 
                           value="{{ $search ?? '' }}"
                           placeholder="Search posts, users, or notes..."
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @if(request('filter'))
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                    @endif
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                    @if($search ?? false)
                        <a href="{{ route('forum.index', ['filter' => $filter]) }}" 
                           class="px-4 py-2 text-gray-600 hover:text-gray-900">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Filter Tabs -->
            <div class="mt-6 flex items-center space-x-4 border-b border-gray-200">
                <a href="{{ route('forum.index', ['filter' => 'timeline']) }}" 
                   class="px-4 py-2 text-sm font-medium {{ $filter === 'timeline' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Timeline
                </a>
                <a href="{{ route('forum.index', ['filter' => 'following']) }}" 
                   class="px-4 py-2 text-sm font-medium {{ $filter === 'following' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Following
                </a>
                <a href="{{ route('forum.index', ['filter' => 'all']) }}" 
                   class="px-4 py-2 text-sm font-medium {{ $filter === 'all' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    All Posts
                </a>
                <a href="{{ route('forum.index', ['filter' => 'trending']) }}" 
                   class="px-4 py-2 text-sm font-medium {{ $filter === 'trending' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Trending
                </a>
            </div>
        </div>

        <!-- Create Post Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form action="{{ route('forum.store') }}" method="POST" id="createPostForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <input type="hidden" name="content" id="postContent" required>
                    <div id="postContentEditor" class="forum-quill-editor border border-gray-300 rounded-lg"></div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500" id="charCount">0 / 5000</span>
                        <div class="flex items-center space-x-2">
                            <label for="media-upload" class="cursor-pointer text-sm text-blue-600 hover:text-blue-700 font-medium">
                                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Upload Images
                            </label>
                            <input type="file" id="media-upload" name="media[]" multiple accept="image/*" class="hidden" onchange="previewMedia(this)">
                            <button type="button" 
                                    id="shareNoteBtn"
                                    class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                            📄 Share a Note
                        </button>
                    </div>
                </div>

                <!-- Media Preview -->
                <div id="mediaPreview" class="mb-4 hidden">
                    <div class="grid grid-cols-4 gap-2" id="mediaPreviewGrid"></div>
                </div>

                <!-- Note Selection (Hidden by default) -->
                <div id="noteSelection" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Note to Share</label>
                    <select name="note_id" 
                            id="noteSelect"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- No note --</option>
                        @auth
                            @foreach(auth()->user()->notes()->where('status', 'active')->get() as $note)
                                <option value="{{ $note->id }}">{{ $note->title }}</option>
                            @endforeach
                        @endauth
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Post Visibility</label>
                    <select name="visibility" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="public">Public — everyone can see</option>
                        <option value="followers">Followers Only</option>
                        <option value="private">Private — only you</option>
                    </select>
                    <p class="mt-2 text-xs text-gray-500">Choose who can view this post. Replies inherit the original post visibility.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Post (optional)</label>
                    <input type="datetime-local"
                           name="scheduled_for"
                           value="{{ old('scheduled_for') }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-2 text-xs text-gray-500">Leave empty to publish immediately. If you set a time in the future, the post will go live automatically.</p>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Posts Feed -->
        @if($posts->count() > 0)
            <div class="space-y-4">
                @foreach($posts as $post)
                    @include('forum.partials.post-card', ['post' => $post])
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No posts yet</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($filter === 'following')
                        You're not following anyone yet. Start following users to see their posts here.
                    @else
                        Be the first to share something!
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
    <script>
        window.forumQuillToolbar = window.forumQuillToolbar || [
            ['bold', 'italic', 'underline', 'strike'],
            [{ header: [1, 2, 3, false] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block'],
            ['link'],
            ['clean']
        ];

        window.forumQuillMaxLength = 5000;

        document.addEventListener('DOMContentLoaded', function () {
            const postContentInput = document.getElementById('postContent');
            const postContentEditor = document.getElementById('postContentEditor');
            const charCount = document.getElementById('charCount');
            const createPostForm = document.getElementById('createPostForm');

            if (postContentInput && postContentEditor && window.Quill) {
                const quill = new Quill(postContentEditor, {
                    theme: 'snow',
                    modules: {
                        toolbar: window.forumQuillToolbar,
                    },
                });

                if (!postContentInput.value) {
                    postContentInput.value = '<p><br></p>';
                }

                quill.root.innerHTML = postContentInput.value;

                const updateCharCount = () => {
                    const length = quill.getText().trim().length;
                    if (charCount) {
                        charCount.textContent = `${length} / ${window.forumQuillMaxLength}`;
                        if (length > window.forumQuillMaxLength) {
                            charCount.classList.add('text-red-600');
                        } else {
                            charCount.classList.remove('text-red-600');
                        }
                    }
                };

                updateCharCount();

                quill.on('text-change', function () {
                    postContentInput.value = quill.root.innerHTML;
                    updateCharCount();
                });

                if (createPostForm) {
                    createPostForm.addEventListener('submit', function (event) {
                        const textLength = quill.getText().trim().length;

                        if (textLength === 0) {
                            event.preventDefault();
                            alert('Please enter some content before posting.');
                            return false;
                        }

                        if (textLength > window.forumQuillMaxLength) {
                            event.preventDefault();
                            alert('Post content may not be greater than 5000 characters.');
                            return false;
                        }

                        postContentInput.value = quill.root.innerHTML;
                    });
                }

                window.forumQuillEditors = window.forumQuillEditors || {};
                window.forumQuillEditors.create = quill;
            }

            const shareNoteBtn = document.getElementById('shareNoteBtn');
            const noteSelection = document.getElementById('noteSelection');

            if (shareNoteBtn && noteSelection) {
                shareNoteBtn.addEventListener('click', function () {
                    noteSelection.classList.toggle('hidden');
                });
            }
        });

        function previewMedia(input) {
            const preview = document.getElementById('mediaPreview');
            const previewGrid = document.getElementById('mediaPreviewGrid');

            if (input.files && input.files.length > 0) {
                preview.classList.remove('hidden');
                previewGrid.innerHTML = '';

                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const div = document.createElement('div');
                            div.className = 'relative';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}" class="w-full h-24 object-cover rounded-lg">
                                <button type="button" onclick="removeMedia(${index})" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                    ×
                                </button>
                            `;
                            previewGrid.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                preview.classList.add('hidden');
            }
        }

        function removeMedia(index) {
            const input = document.getElementById('media-upload');
            const dt = new DataTransfer();
            const files = Array.from(input.files);

            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });

            input.files = dt.files;
            previewMedia(input);
        }

        window.previewMedia = previewMedia;
        window.removeMedia = removeMedia;
    </script>
@endpush
@endsection

