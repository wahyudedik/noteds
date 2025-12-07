<?php $__env->startSection('title', __('Conversation with') . ' ' . $user->name); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('messages.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Messages')); ?>

            </a>
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center">
                    <?php if($user->avatar): ?>
                        <img src="<?php echo e(Storage::url($user->avatar)); ?>" alt="<?php echo e($user->name); ?>" class="w-12 h-12 rounded-full object-cover">
                    <?php else: ?>
                        <span class="text-lg font-semibold text-gray-600"><?php echo e(substr($user->name, 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900"><?php echo e($user->name); ?></h1>
                    <p class="text-sm text-gray-600"><?php echo e(__('Conversation')); ?></p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6 max-h-96 overflow-y-auto">
            <?php if($messages->count() > 0): ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $messages->reverse(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex <?php echo e($message->sender_id === auth()->id() ? 'justify-end' : 'justify-start'); ?>">
                            <div class="max-w-xs lg:max-w-md">
                                <div class="flex items-start gap-2">
                                    <?php if($message->sender_id !== auth()->id()): ?>
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <?php if($message->sender->avatar): ?>
                                                    <img src="<?php echo e(Storage::url($message->sender->avatar)); ?>" alt="<?php echo e($message->sender->name); ?>" class="w-8 h-8 rounded-full object-cover">
                                                <?php else: ?>
                                                    <span class="text-xs font-semibold text-gray-600"><?php echo e(substr($message->sender->name, 0, 1)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <div class="rounded-lg p-3 <?php echo e($message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-900'); ?>">
                                            <p class="text-sm whitespace-pre-wrap"><?php echo e($message->message); ?></p>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 <?php echo e($message->sender_id === auth()->id() ? 'text-right' : ''); ?>">
                                            <?php echo e($message->created_at->format('M d, H:i')); ?>

                                        </p>
                                    </div>
                                    <?php if($message->sender_id === auth()->id()): ?>
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                <?php if(auth()->user()->avatar): ?>
                                                    <img src="<?php echo e(Storage::url(auth()->user()->avatar)); ?>" alt="<?php echo e(auth()->user()->name); ?>" class="w-8 h-8 rounded-full object-cover">
                                                <?php else: ?>
                                                    <span class="text-xs font-semibold text-gray-600"><?php echo e(substr(auth()->user()->name, 0, 1)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8"><?php echo e(__('No messages yet. Start the conversation!')); ?></p>
            <?php endif; ?>
        </div>

        <!-- Message Form -->
        <form action="<?php echo e(route('messages.store', $user)); ?>" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <?php echo csrf_field(); ?>
            <div class="flex gap-4">
                <textarea name="message" rows="3" required
                    placeholder="<?php echo e(__('Type your message...')); ?>"
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 self-end">
                    <?php echo e(__('Send')); ?>

                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\messages\conversation.blade.php ENDPATH**/ ?>