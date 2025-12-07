<?php $__env->startSection('title', __('messages.forum_email_preferences')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-5 border-b border-gray-200">
                <h1 class="text-2xl font-semibold text-gray-900"><?php echo e(__('messages.forum_email_notifications')); ?></h1>
                <p class="mt-2 text-sm text-gray-600">
                    Pilih email notifikasi yang ingin kamu terima untuk aktivitas forum. Kamu masih akan menerima notifikasi in-app meskipun email dimatikan.
                </p>
            </div>

            <form action="<?php echo e(route('forum.preferences.update')); ?>" method="POST" class="px-6 py-6 space-y-6">
                <?php if(session('success')): ?>
                    <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <?php
                    $options = [
                        'post_liked' => [
                            'title' => 'Post Likes',
                            'description' => 'Receive an email when someone likes your post.',
                        ],
                        'post_commented' => [
                            'title' => 'Post Comments',
                            'description' => 'Receive an email when someone comments on your post.',
                        ],
                        'comment_replied' => [
                            'title' => 'Comment Replies',
                            'description' => 'Receive an email when someone replies to your comment.',
                        ],
                        'comment_liked' => [
                            'title' => 'Comment Likes',
                            'description' => 'Receive an email when someone likes your comment.',
                        ],
                        'new_follower' => [
                            'title' => 'New Followers',
                            'description' => 'Receive an email when someone follows you.',
                        ],
                    ];
                ?>

                <div class="space-y-4">
                    <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start justify-between gap-4 p-4 border border-gray-200 rounded-lg">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900"><?php echo e($option['title']); ?></h3>
                                <p class="mt-1 text-sm text-gray-500"><?php echo e($option['description']); ?></p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="<?php echo e($key); ?>" value="0">
                                <input type="checkbox"
                                       name="<?php echo e($key); ?>"
                                       value="1"
                                       class="sr-only peer"
                                       <?php echo e(($preferences[$key] ?? false) ? 'checked' : ''); ?>>
                                <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-blue-500 peer-checked:bg-blue-600 transition-colors duration-200"></div>
                                <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 peer-checked:translate-x-5"></div>
                            </label>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\forum\preferences.blade.php ENDPATH**/ ?>