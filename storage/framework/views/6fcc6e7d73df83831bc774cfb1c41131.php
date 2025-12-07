<?php $__env->startSection('title', __('messages.post_details')); ?>

<?php $__env->startSection('content'); ?>
    <?php echo $__env->make('forum.partials.quill-assets', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="<?php echo e(route('forum.index')); ?>"
                    class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <?php echo e(__('messages.back_to_forum')); ?>

                </a>
            </div>

            <!-- Main Post -->
            <?php echo $__env->make('forum.partials.post-card', ['post' => $post], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <?php if(!$post->is_published && $post->scheduled_at): ?>
                <div class="mt-4 bg-amber-50 border border-amber-200 text-amber-700 px-4 py-3 rounded-lg">
                    This post is scheduled to go live on
                    <strong><?php echo e($post->scheduled_at->timezone(config('app.timezone'))->format('d M Y, H:i')); ?></strong>.
                </div>
            <?php endif; ?>

            <!-- Replies (if this is a reply to another post) -->
            <?php if($post->parent): ?>
                <div class="mt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Replying to:</h3>
                    <?php echo $__env->make('forum.partials.post-card', ['post' => $post->parent], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            <?php endif; ?>

            <!-- Replies Section -->
            <?php if($replies->count() > 0): ?>
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e($replies->count()); ?>

                        <?php echo e(Str::plural('Reply', $replies->count())); ?></h3>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="ml-8 border-l-2 border-gray-200 pl-6">
                                <?php echo $__env->make('forum.partials.post-card', ['post' => $reply], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Comments Section -->
            <div class="mt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e($comments->count()); ?>

                    <?php echo e(Str::plural('Comment', $comments->count())); ?></h3>

                <!-- Comment Form -->
                <?php if(auth()->guard()->check()): ?>
                    <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <form id="commentForm" action="<?php echo e(route('forum.comment', $post)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <textarea name="content" id="commentContent" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                                placeholder="Write a comment..." maxlength="2000" required></textarea>
                            <div class="mt-3 flex items-center justify-end">
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    Post Comment
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- Comments List -->
                <?php if($comments->count() > 0): ?>
                    <div class="space-y-4" id="commentsList">
                        <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo $__env->make('forum.partials.comment-card', ['comment' => $comment], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                        <p class="text-gray-500">No comments yet. Be the first to comment!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const showToast = (options = {}) => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                    icon: options.icon || 'info',
                    title: options.title || '',
                    text: options.text || '',
                    ...options,
                });
            };

            const handleFetchError = (error, defaultMessage = 'Something went wrong. Please try again.') => {
                console.error(error);
                showToast({
                    icon: 'error',
                    title: defaultMessage,
                });
            };

            // Comment form submission
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                commentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const content = formData.get('content');

                    if (!content.trim()) {
                        showToast({
                            icon: 'warning',
                            title: 'Komentar tidak boleh kosong.',
                        });
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
                                showToast({
                                    icon: 'error',
                                    title: data.message || 'Gagal mengirim komentar.',
                                });
                            }
                        })
                        .catch(error => handleFetchError(error, 'Gagal mengirim komentar.'));
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
                        handleFetchError(error, 'Gagal menyukai komentar.');
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
                            showToast({
                                icon: 'success',
                                title: 'Komentar diperbarui.',
                            });
                            setTimeout(() => window.location.reload(), 700);
                        } else {
                            showToast({
                                icon: 'error',
                                title: data.message || 'Gagal memperbarui komentar.',
                            });
                        }
                    })
                    .catch(error => {
                        handleFetchError(error, 'Gagal memperbarui komentar.');
                    });

                return false;
            }

            // Delete comment
            function deleteComment(commentId) {
                Swal.fire({
                    title: 'Hapus komentar ini?',
                    text: 'Komentar akan dihapus permanen dan tidak dapat dikembalikan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                }).then(result => {
                    if (!result.isConfirmed) {
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
                                showToast({
                                    icon: 'success',
                                    title: 'Komentar dihapus.',
                                });
                                setTimeout(() => window.location.reload(), 700);
                            } else {
                                showToast({
                                    icon: 'error',
                                    title: data.message || 'Gagal menghapus komentar.',
                                });
                            }
                        })
                        .catch(error => handleFetchError(error, 'Gagal menghapus komentar.'));
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
                            showToast({
                                icon: 'success',
                                title: 'Balasan terkirim.',
                            });
                            setTimeout(() => window.location.reload(), 700);
                        } else {
                            showToast({
                                icon: 'error',
                                title: data.message || 'Gagal mengirim balasan.',
                            });
                        }
                    })
                    .catch(error => handleFetchError(error, 'Gagal mengirim balasan.'));

                return false;
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\forum\show.blade.php ENDPATH**/ ?>