@extends('layouts.app')

@section('title', 'Post Details')

@section('content')
@include('forum.partials.quill-assets')
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ route('forum.index') }}" 
               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Forum
            </a>
        </div>

        <!-- Main Post -->
        @include('forum.partials.post-card', ['post' => $post])

        @if(!$post->is_published && $post->scheduled_at)
            <div class="mt-4 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg">
                This post is scheduled to go live on <strong>{{ $post->scheduled_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</strong>.
            </div>
        @endif
 
        <!-- Replies (if this is a reply to another post) -->
        @if($post->parent)
            <div class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Replying to:</h3>
                @include('forum.partials.post-card', ['post' => $post->parent])
            </div>
        @endif

        <!-- Replies Section -->
        @if($replies->count() > 0)
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $replies->count() }} {{ Str::plural('Reply', $replies->count()) }}</h3>
                <div class="space-y-4">
                    @foreach($replies as $reply)
                        <div class="ml-8 border-l-2 border-gray-200 pl-6">
                            @include('forum.partials.post-card', ['post' => $reply])
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Reply Form -->
        @auth
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6" id="reply">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Reply to this post</h3>
                <form action="{{ route('forum.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="parent_id" value="{{ $post->id }}">
                    <input type="hidden" name="content" id="replyContent" required>
                    <div id="replyContentEditor" class="forum-quill-editor border border-gray-300 rounded-lg bg-white"></div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="text-xs text-gray-500" id="replyCharCount">0 / 5000</span>
                    </div>
                    <div class="mt-4 flex items-center justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            Post Reply
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                <p class="text-gray-600 mb-4">Please <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-medium">login</a> to reply to this post.</p>
            </div>
        @endauth

        <!-- Comments Section -->
        <div class="mt-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ $comments->count() }} {{ Str::plural('Comment', $comments->count()) }}</h3>

            <!-- Comment Form -->
            @auth
                <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                    <form id="commentForm" action="{{ route('forum.comment', $post) }}" method="POST">
                        @csrf
                        <textarea name="content" 
                                  id="commentContent"
                                  rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                  placeholder="Write a comment..."
                                  maxlength="2000"
                                  required></textarea>
                        <div class="mt-3 flex items-center justify-end">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                Post Comment
                            </button>
                        </div>
                    </form>
                </div>
            @endauth

            <!-- Comments List -->
            @if($comments->count() > 0)
                <div class="space-y-4" id="commentsList">
                    @foreach($comments as $comment)
                        @include('forum.partials.comment-card', ['comment' => $comment])
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                    <p class="text-gray-500">No comments yet. Be the first to comment!</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const MAX_REPLY_CHARS = window.forumQuillMaxLength || 5000;
        window.forumQuillToolbar = window.forumQuillToolbar || [
            ['bold', 'italic', 'underline', 'strike'],
            [{ header: [1, 2, 3, false] }],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'code-block'],
            ['link'],
            ['clean']
        ];

        const replyInput = document.getElementById('replyContent');
        const replyEditorElement = document.getElementById('replyContentEditor');
        const replyCharCount = document.getElementById('replyCharCount');
        const replyForm = document.querySelector('#reply form');

        if (replyInput && replyEditorElement && window.Quill) {
            const replyQuill = new Quill(replyEditorElement, {
                theme: 'snow',
                modules: {
                    toolbar: window.forumQuillToolbar,
                },
            });

            const updateReplyCount = () => {
                const length = replyQuill.getText().trim().length;
                if (replyCharCount) {
                    replyCharCount.textContent = `${length} / ${MAX_REPLY_CHARS}`;
                    if (length > MAX_REPLY_CHARS) {
                        replyCharCount.classList.add('text-red-600');
                    } else {
                        replyCharCount.classList.remove('text-red-600');
                    }
                }
            };

            updateReplyCount();

            replyQuill.on('text-change', function () {
                replyInput.value = replyQuill.root.innerHTML;
                updateReplyCount();
            });

            if (replyForm) {
                replyForm.addEventListener('submit', function (event) {
                    const textLength = replyQuill.getText().trim().length;

                    if (textLength === 0) {
                        event.preventDefault();
                        alert('Please enter some content before posting your reply.');
                        return false;
                    }

                    if (textLength > MAX_REPLY_CHARS) {
                        event.preventDefault();
                        alert('Reply content may not be greater than 5000 characters.');
                        return false;
                    }

                    replyInput.value = replyQuill.root.innerHTML;
                });
            }

            window.forumQuillEditors = window.forumQuillEditors || {};
            window.forumQuillEditors.reply = replyQuill;
        }
    });

    // Comment form submission
    const commentForm = document.getElementById('commentForm');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const content = formData.get('content');
            
            if (!content.trim()) {
                alert('Please enter a comment.');
                return;
            }

            fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to show new comment
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to post comment.');
                }
            })
            .catch(error => {
                console.error('Error posting comment:', error);
                alert('Failed to post comment. Please try again.');
            });
        });
    }

    // Like comment
    function likeComment(commentId) {
        fetch(`/forum/comment/${commentId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likeBtn = document.getElementById(`likeCommentBtn-${commentId}`);
                const likesCount = document.getElementById(`commentLikesCount-${commentId}`);
                
                if (data.liked) {
                    likeBtn.classList.add('text-red-600');
                    likeBtn.classList.remove('text-gray-600');
                    likeBtn.querySelector('svg').setAttribute('fill', 'currentColor');
                } else {
                    likeBtn.classList.remove('text-red-600');
                    likeBtn.classList.add('text-gray-600');
                    likeBtn.querySelector('svg').setAttribute('fill', 'none');
                }
                
                if (likesCount) {
                    likesCount.textContent = data.likes_count;
                }
            }
        })
        .catch(error => {
            console.error('Error liking comment:', error);
        });
    }

    // Edit comment
    function editComment(commentId) {
        const contentDiv = document.getElementById(`commentContent-${commentId}`);
        const editForm = document.getElementById(`editCommentForm-${commentId}`);
        
        if (contentDiv && editForm) {
            contentDiv.classList.add('hidden');
            editForm.classList.remove('hidden');
        }
    }

    function cancelEditComment(commentId) {
        const contentDiv = document.getElementById(`commentContent-${commentId}`);
        const editForm = document.getElementById(`editCommentForm-${commentId}`);
        
        if (contentDiv && editForm) {
            contentDiv.classList.remove('hidden');
            editForm.classList.add('hidden');
        }
    }

    function submitEditComment(event, commentId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        fetch(`/forum/comment/${commentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': 'PUT',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to update comment. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error updating comment:', error);
            alert('Failed to update comment. Please try again.');
        });
        
        return false;
    }

    // Delete comment
    function deleteComment(commentId) {
        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }

        fetch(`/forum/comment/${commentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': 'DELETE',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete comment. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error deleting comment:', error);
            alert('Failed to delete comment. Please try again.');
        });
    }

    // Reply to comment
    function showReplyForm(commentId) {
        const replyForm = document.getElementById(`replyForm-${commentId}`);
        if (replyForm) {
            replyForm.classList.remove('hidden');
        }
    }

    function hideReplyForm(commentId) {
        const replyForm = document.getElementById(`replyForm-${commentId}`);
        if (replyForm) {
            replyForm.classList.add('hidden');
            replyForm.querySelector('textarea').value = '';
        }
    }

    function submitReplyToComment(event, postId, parentCommentId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        formData.append('parent_id', parentCommentId);
        
        fetch(`/forum/post/${postId}/comment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to post reply. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error posting reply:', error);
            alert('Failed to post reply. Please try again.');
        });
        
        return false;
    }
</script>
@endpush
@endsection

