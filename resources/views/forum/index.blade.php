@extends('layouts.app')

@section('title', __('messages.forum_index'))

@section('content')
    @include('forum.partials.quill-assets')
    <div class="py-10 sm:py-12 bg-slate-50 min-h-screen">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-10">
                <div
                    class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-600 via-indigo-500 to-purple-500 text-white shadow-lg">
                    <div class="absolute inset-0 opacity-30 bg-[radial-gradient(circle_at_top,_#ffffff33,_transparent_55%)]">
                    </div>
                    <div
                        class="relative z-10 px-6 py-8 sm:px-8 sm:py-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p
                                class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs font-medium uppercase tracking-wide backdrop-blur">
                                Community Hub
                            </p>
                            <h1 class="mt-4 text-3xl sm:text-4xl font-semibold tracking-tight">Forum Noteds</h1>
                            <p class="mt-2 text-sm sm:text-base text-white/80 max-w-xl">
                                Ngobrol santai, berbagi insight, dan temukan catatan terbaik dari para kreator lain. Semua
                                dalam satu tempat.
                            </p>
                        </div>
                        <a href="{{ route('forum.preferences.edit') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-2 text-sm font-medium text-white transition hover:bg-white/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                            </svg>
                            Email Preferences
                        </a>
                    </div>
                </div>

                <!-- Search Form -->
                <div class="relative z-10 -mt-6 sm:-mt-8">
                    <div class="rounded-2xl bg-white px-4 py-4 shadow-lg ring-1 ring-slate-100 sm:px-5">
                        <form action="{{ route('forum.index') }}" method="GET"
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
                            <div class="relative flex-1">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" name="search" value="{{ $search ?? '' }}"
                                    placeholder="Cari diskusi, topik, atau kreator..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-10 pr-4 text-sm text-slate-700 transition focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-100">
                            </div>
                            @if (request('filter'))
                                <input type="hidden" name="filter" value="{{ request('filter') }}">
                            @endif
                            <div class="flex items-center gap-2">
                                @if ($search ?? false)
                                    <a href="{{ route('forum.index', ['filter' => $filter]) }}"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-500 transition hover:border-slate-300 hover:text-slate-700">
                                        Reset
                                    </a>
                                @endif
                                <button type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                    Cari
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 7l5 5-5 5M6 7h11" />
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Filter Tabs -->
                <div class="mt-6">
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        @php
                            $filters = [
                                'timeline' => 'Timeline',
                                'following' => 'Mengikuti',
                                'all' => 'Semua Post',
                                'trending' => 'Trending',
                            ];
                        @endphp
                        @foreach ($filters as $value => $label)
                            <a href="{{ route('forum.index', ['filter' => $value]) }}"
                                class="whitespace-nowrap rounded-full px-4 py-2 text-sm font-medium transition {{ $filter === $value ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-600 shadow ring-1 ring-slate-200 hover:text-slate-900' }}">
                                {{ $label }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Create Post Form -->
            <div class="mb-10 rounded-3xl border border-white/60 bg-white/90 p-6 shadow-xl shadow-blue-50/40 backdrop-blur">
                <form action="{{ route('forum.store') }}" method="POST" id="createPostForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <input type="hidden" name="content" id="postContent" required>
                        <div id="postContentEditor"
                            class="forum-quill-editor rounded-2xl border border-slate-200 bg-white/70"></div>
                        <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="text-xs font-medium uppercase tracking-wide text-slate-400" id="charCount">0 /
                                5000</span>
                            <div class="flex flex-wrap items-center gap-2 text-sm font-medium text-blue-600">
                                <label for="media-upload"
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-blue-700 transition hover:border-blue-200 hover:bg-blue-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Upload Media
                                </label>
                                <input type="file" id="media-upload" name="media[]" multiple accept="image/*"
                                    class="hidden" onchange="previewMedia(this)">
                                @auth
                                    @if (auth()->user()->hasRole('seller'))
                                        <button type="button" id="shareNoteBtn"
                                            class="inline-flex items-center gap-2 rounded-full border border-blue-100 px-3 py-1.5 text-blue-600 transition hover:border-blue-200 hover:bg-blue-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 4h16v16H4V4zM8 4v16M4 8h16" />
                                            </svg>
                                            Bagikan Catatan
                                        </button>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Media Preview -->
                    <div id="mediaPreview" class="mb-5 hidden">
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4" id="mediaPreviewGrid"></div>
                    </div>

                    <!-- Note Selection (Hidden by default, only for sellers) -->
                    @auth
                        @if (auth()->user()->hasRole('seller'))
                            <div id="noteSelection" class="mb-5 hidden">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih catatan yang ingin
                                    dibagikan</label>
                                <select name="note_id" id="noteSelect"
                                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                    <option value="">-- No note --</option>
                                    @foreach (auth()->user()->notes()->where('status', 'active')->get() as $note)
                                        <option value="{{ $note->id }}">{{ $note->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endauth

                    <div class="mb-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Visibilitas Post</label>
                            <select name="visibility"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                                <option value="public">Publik — semua orang bisa lihat</option>
                                <option value="followers">Followers saja</option>
                                <option value="private">Pribadi — hanya kamu</option>
                            </select>
                            <p class="mt-2 text-xs text-slate-400">Balasan akan otomatis mengikuti visibilitas post utama.
                            </p>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-semibold text-slate-700">Jadwalkan Post
                                (opsional)</label>
                            <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for') }}"
                                min="{{ now()->format('Y-m-d\TH:i') }}"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                            <p class="mt-2 text-xs text-slate-400">Kosongkan jika ingin langsung tayang. Pilih waktu untuk
                                menjadwalkan publikasi otomatis.</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 font-medium text-slate-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6l3 3" />
                                    <circle cx="12" cy="12" r="9" stroke-width="2"></circle>
                                </svg>
                                Auto-save aktif
                            </span>
                            <span>Draft akan tersimpan saat kamu mengetik.</span>
                        </div>
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            Posting sekarang
                        </button>
                    </div>
                </form>
            </div>

            <!-- Posts Feed -->
            @if ($posts->count() > 0)
                <div class="space-y-6">
                    @foreach ($posts as $post)
                        @include('forum.partials.post-card', ['post' => $post])
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="rounded-3xl border border-dashed border-slate-200 bg-white p-12 text-center shadow-sm">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-50 text-blue-500">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">Belum ada diskusi</h3>
                    <p class="mt-2 text-sm text-slate-500">
                        @if ($filter === 'following')
                            Kamu belum mengikuti siapa pun. Mulai follow kreator favoritmu untuk melihat update mereka di
                            sini.
                        @else
                            Yuk mulai percakapan pertama kamu di komunitas Noteds!
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
                [{
                    header: [1, 2, 3, false]
                }],
                [{
                    list: 'ordered'
                }, {
                    list: 'bullet'
                }],
                ['blockquote', 'code-block'],
                ['link'],
                ['clean']
            ];

            window.forumQuillMaxLength = 5000;

            document.addEventListener('DOMContentLoaded', function() {
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

                    quill.on('text-change', function() {
                        postContentInput.value = quill.root.innerHTML;
                        updateCharCount();
                    });

                    if (createPostForm) {
                        createPostForm.addEventListener('submit', function(event) {
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
                    shareNoteBtn.addEventListener('click', function() {
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
                            reader.onload = function(e) {
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
