<?php $__env->startSection('title', __('messages.product_conversations')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-10 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.product_conversations')); ?></h1>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo e(__('messages.product_conversations_description')); ?>

            </p>
        </div>

        <?php if($conversations->isEmpty()): ?>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-10 text-center">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                <h2 class="text-lg font-semibold text-gray-900 mb-1"><?php echo e(__('messages.no_conversations_yet')); ?></h2>
                <p class="text-sm text-gray-500">
                    <?php echo e(__('messages.conversations_auto_created')); ?>

                </p>
                <a href="<?php echo e(route('marketplace.index')); ?>"
                    class="inline-flex items-center mt-6 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <?php echo e(__('messages.explore_marketplace')); ?>

                </a>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $lastMessage = $conversation->latestMessage;
                        $otherUser = $conversation->buyer_id === $user->id ? $conversation->seller : $conversation->buyer;
                        $timestamp = $conversation->last_message_at ?? $conversation->updated_at;
                    ?>
                    <a href="<?php echo e(route('note-conversations.show', $conversation)); ?>"
                        class="block bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                        <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-xs uppercase text-gray-400">
                                    <span><?php echo e($conversation->note->title ?? __('messages.product_not_available')); ?></span>
                                    <span>•</span>
                                    <span><?php echo e($conversation->buyer_id === $user->id ? __('messages.you_as_buyer') : __('messages.you_as_seller')); ?></span>
                                </div>
                                <h3 class="mt-1 text-lg font-semibold text-gray-900">
                                    <?php echo e($otherUser->name); ?> (<?php echo e($otherUser->role ?? __('messages.user')); ?>)
                                </h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    <?php if($lastMessage): ?>
                                        <span class="font-medium">
                                            <?php echo e($lastMessage->sender_id === $user->id ? __('messages.you') . ':' : ($lastMessage->sender->name ?? __('messages.user')) . ':'); ?>

                                        </span>
                                        <?php echo e(\Illuminate\Support\Str::limit($lastMessage->message, 120)); ?>

                                    <?php else: ?>
                                        <?php echo e(__('messages.no_messages_yet')); ?>

                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-gray-400 block">
                                    <?php echo e($timestamp?->diffForHumans() ?? ''); ?>

                                </span>
                                <span
                                    class="mt-2 inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-600">
                                    <?php echo e(__('messages.continue_chat')); ?>

                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="mt-6">
                <?php echo e($conversations->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\note-conversations\index.blade.php ENDPATH**/ ?>