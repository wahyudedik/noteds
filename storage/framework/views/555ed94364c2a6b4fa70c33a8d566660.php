<?php
    $currentUser = auth()->user();
    $canReply = $currentUser && ($currentUser->id === $review->user_id || $currentUser->id === $review->note->user_id || $currentUser->hasRole('admin'));
    $canDelete = $currentUser && ($currentUser->id === $reply->user_id || $currentUser->hasRole('admin'));
?>

<div class="pl-10 mt-4">
    <div class="flex gap-3" x-data="{ replyOpen: false }">
        <div class="flex-shrink-0">
            <div class="w-9 h-9 rounded-full bg-gray-200 flex items-center justify-center">
                <?php if($reply->user?->avatar): ?>
                    <?php if(str_starts_with($reply->user->avatar, 'http')): ?>
                        <img src="<?php echo e($reply->user->avatar); ?>" alt="<?php echo e($reply->user->name); ?>"
                            class="w-9 h-9 rounded-full object-cover">
                    <?php else: ?>
                        <img src="<?php echo e(Storage::url($reply->user->avatar)); ?>" alt="<?php echo e($reply->user->name); ?>"
                            class="w-9 h-9 rounded-full object-cover">
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-xs font-semibold text-gray-600"><?php echo e(substr($reply->user?->name ?? 'U', 0, 1)); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex-1 bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900"><?php echo e($reply->user?->name ?? __('messages.user_placeholder_name')); ?></p>
                    <p class="text-xs text-gray-500"><?php echo e(localized_diff_for_humans($reply->created_at)); ?></p>
                </div>
                <div class="flex items-center gap-3">
                    <?php if($canReply): ?>
                        <button type="button" @click="replyOpen = !replyOpen"
                            class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                            <?php echo e(__('messages.reply')); ?>

                        </button>
                    <?php endif; ?>
                    <?php if($canDelete): ?>
                        <form action="<?php echo e(route('reviews.replies.destroy', $reply)); ?>" method="POST"
                            onsubmit="return confirm('<?php echo e(__('messages.review_reply_delete_confirmation')); ?>');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                class="text-xs text-red-600 hover:text-red-700 transition-colors"><?php echo e(__('messages.delete')); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <p class="text-sm text-gray-700 whitespace-pre-wrap mt-2"><?php echo e($reply->message); ?></p>

            <?php if($canReply): ?>
                <form x-show="replyOpen" x-cloak action="<?php echo e(route('reviews.replies.store', $review)); ?>" method="POST"
                    class="mt-3 space-y-2">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="parent_id" value="<?php echo e($reply->id); ?>">
                    <textarea name="message" rows="2" required maxlength="2000"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm"
                        placeholder="<?php echo e(__('messages.review_reply_placeholder_short')); ?>"></textarea>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                            <?php echo e(__('messages.review_reply_send')); ?>

                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if($reply->children && $reply->children->count() > 0): ?>
        <div class="space-y-4 mt-4">
            <?php $__currentLoopData = $reply->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $childReply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('marketplace.partials.review-reply', ['reply' => $childReply, 'review' => $review], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\marketplace\partials\review-reply.blade.php ENDPATH**/ ?>