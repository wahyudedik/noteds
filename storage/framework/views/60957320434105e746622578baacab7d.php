<?php $__env->startSection('title', 'Following Activity Feed'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-3xl font-bold text-gray-900">Following Activity</h1>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('activity.index')); ?>" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-md">
                        All Activities
                    </a>
                </div>
            </div>
            <p class="text-base text-gray-600">See what people you follow are doing</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <form method="GET" action="<?php echo e(route('activity.following')); ?>" class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter by Type</label>
                    <select name="type" class="w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">All Activities</option>
                        <?php $__currentLoopData = $activityTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($type); ?>" <?php echo e(request('type') === $type ? 'selected' : ''); ?>>
                                <?php echo e(ucfirst(str_replace('.', ' ', $type))); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-md">
                        Filter
                    </button>
                    <?php if(request()->has('type')): ?>
                        <a href="<?php echo e(route('activity.following')); ?>" class="ml-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-md">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Activity Feed -->
        <div id="activity-feed" class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php echo $__env->make('activity.partials.activity-item', ['activity' => $activity], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white rounded-lg shadow-sm p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No activities from people you follow</h3>
                    <p class="mt-1 text-sm text-gray-500">Start following users to see their activities here.</p>
                    <div class="mt-6">
                        <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                            Discover Users
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($activities->links()); ?>

        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Real-time updates via Laravel Echo
        // Note: For development, broadcasting uses 'log' driver by default
        // For production, configure Pusher/Redis and install Laravel Echo
        <?php if(config('broadcasting.default') !== 'log' && config('broadcasting.default') !== null): ?>
        if (typeof Echo !== 'undefined') {
            // Listen for new activities from followed users
            Echo.channel('activity-feed')
                .listen('.activity.created', (e) => {
                    // Check if activity is from a followed user
                    const followingIds = <?php echo json_encode(auth()->user()->following()->pluck('following_id')->toArray(), 15, 512) ?>;
                    if (followingIds.includes(e.activity.user_id)) {
                        location.reload();
                    }
                });

            // Listen for activity updates (likes, comments)
            <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            Echo.channel('activity.<?php echo e($activity->id); ?>')
                .listen('.activity.liked', (e) => {
                    updateActivityLikes('<?php echo e($activity->id); ?>', e.likes_count, e.liked);
                })
                .listen('.activity.commented', (e) => {
                    updateActivityComments('<?php echo e($activity->id); ?>', e.comments_count);
                })
                .listen('.activity.shared', (e) => {
                    updateActivityShares('<?php echo e($activity->id); ?>', e.shares_count);
                });
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        }
        <?php endif; ?>

        // Like functionality
        window.likeActivity = function(activityId) {
            fetch(`/activity/${activityId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                updateActivityLikes(activityId, data.likes_count, data.liked);
            })
            .catch(error => console.error('Error:', error));
        };

        // Comment functionality
        window.commentActivity = function(activityId, parentId = null) {
            const content = parentId 
                ? document.querySelector(`#reply-content-${parentId}`).value
                : document.querySelector(`#comment-content-${activityId}`).value;
            
            if (!content.trim()) {
                alert('Please enter a comment');
                return;
            }

            fetch(`/activity/${activityId}/comment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    content: content,
                    parent_id: parentId
                })
            })
            .then(response => response.json())
            .then(data => {
                location.reload(); // Reload to show new comment
            })
            .catch(error => console.error('Error:', error));
        };

        // Share functionality
        window.shareActivity = function(activityId, platform = 'copy_link') {
            fetch(`/activity/${activityId}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    platform: platform
                })
            })
            .then(response => response.json())
            .then(data => {
                if (platform === 'copy_link') {
                    navigator.clipboard.writeText(data.share_url).then(() => {
                        alert('Link copied to clipboard!');
                    });
                } else {
                    // Open share dialog for social media
                    const shareUrl = encodeURIComponent(data.share_url);
                    const text = encodeURIComponent('Check out this activity on Noteds!');
                    const urls = {
                        facebook: `https://www.facebook.com/sharer/sharer.php?u=${shareUrl}`,
                        twitter: `https://twitter.com/intent/tweet?url=${shareUrl}&text=${text}`,
                        linkedin: `https://www.linkedin.com/sharing/share-offsite/?url=${shareUrl}`
                    };
                    if (urls[platform]) {
                        window.open(urls[platform], '_blank', 'width=600,height=400');
                    }
                }
                updateActivityShares(activityId, data.shares_count);
            })
            .catch(error => console.error('Error:', error));
        };

        function updateActivityLikes(activityId, count, liked) {
            const likeBtn = document.querySelector(`#like-btn-${activityId}`);
            const likeCount = document.querySelector(`#like-count-${activityId}`);
            
            if (likeBtn) {
                likeBtn.classList.toggle('text-red-600', liked);
                likeBtn.classList.toggle('text-gray-400', !liked);
            }
            if (likeCount) {
                likeCount.textContent = count;
            }
        }

        function updateActivityComments(activityId, count) {
            const commentCount = document.querySelector(`#comment-count-${activityId}`);
            if (commentCount) {
                commentCount.textContent = count;
            }
        }

        function updateActivityShares(activityId, count) {
            const shareCount = document.querySelector(`#share-count-${activityId}`);
            if (shareCount) {
                shareCount.textContent = count;
            }
        }

        function toggleComments(activityId) {
            const section = document.getElementById(`comments-section-${activityId}`);
            section.classList.toggle('hidden');
        }

        function toggleShareMenu(activityId) {
            const menu = document.getElementById(`share-menu-${activityId}`);
            menu.classList.toggle('hidden');
            
            // Close other menus
            document.querySelectorAll('[id^="share-menu-"]').forEach(m => {
                if (m.id !== `share-menu-${activityId}`) {
                    m.classList.add('hidden');
                }
            });
        }

        // Close share menu when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[id^="share-menu-"]') && !event.target.closest('button[onclick*="toggleShareMenu"]')) {
                document.querySelectorAll('[id^="share-menu-"]').forEach(menu => {
                    menu.classList.add('hidden');
                });
            }
        });
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\activity\following.blade.php ENDPATH**/ ?>