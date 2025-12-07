<?php $__env->startSection('title', __('Gift Note Details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('gifts.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Gifts')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Gift Note Details')); ?></h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <?php if($giftNote->status === 'sent'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                    </svg>
                    <?php echo e(__('Sent')); ?>

                </span>
            <?php elseif($giftNote->status === 'claimed'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <?php echo e(__('Claimed')); ?>

                </span>
            <?php elseif($giftNote->status === 'expired'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                    <?php echo e(__('Expired')); ?>

                </span>
            <?php endif; ?>
        </div>

        <!-- Gift Info Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Gift Information')); ?></h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Note')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('marketplace.show', $giftNote->note)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($giftNote->note->title); ?>

                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Price')); ?></dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900"><?php echo e(currency($giftNote->transaction->amount ?? $giftNote->note->price)); ?></dd>
                </div>
                <?php if($giftNote->gifter_id === auth()->id()): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Recipient')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="<?php echo e(route('public.profile.show', $giftNote->recipient->username)); ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo e($giftNote->recipient->name); ?>

                            </a>
                        </dd>
                    </div>
                <?php else: ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('From')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="<?php echo e(route('public.profile.show', $giftNote->gifter->username)); ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo e($giftNote->gifter->name); ?>

                            </a>
                        </dd>
                    </div>
                <?php endif; ?>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Sent Date')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($giftNote->sent_at->format('M d, Y H:i')); ?></dd>
                </div>
                <?php if($giftNote->expires_at): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Expires')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <?php echo e($giftNote->expires_at->format('M d, Y H:i')); ?>

                            <?php if($giftNote->expires_at->isFuture()): ?>
                                <span class="text-gray-500">(<?php echo e($giftNote->expires_at->diffForHumans()); ?>)</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                <?php endif; ?>
                <?php if($giftNote->claimed_at): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Claimed Date')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($giftNote->claimed_at->format('M d, Y H:i')); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Message -->
        <?php if($giftNote->message): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Gift Message')); ?></h2>
                <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($giftNote->message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Note Preview -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Note Preview')); ?></h2>
            <div class="space-y-3">
                <h3 class="text-xl font-semibold text-gray-900"><?php echo e($giftNote->note->title); ?></h3>
                <?php if($giftNote->note->summary): ?>
                    <p class="text-gray-700"><?php echo e($giftNote->note->summary); ?></p>
                <?php endif; ?>
                <a href="<?php echo e(route('marketplace.show', $giftNote->note)); ?>"
                    class="inline-block text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <?php echo e(__('View Full Note')); ?> →
                </a>
            </div>
        </div>

        <!-- Claim Button (for recipient) -->
        <?php if($giftNote->recipient_id === auth()->id() && $giftNote->canBeClaimed()): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-green-900 mb-2"><?php echo e(__('Claim Your Gift!')); ?></h3>
                <p class="text-sm text-green-800 mb-4">
                    <?php echo e(__('This gift note is ready to be claimed. Once claimed, the note will be added to your library.')); ?>

                </p>
                <form action="<?php echo e(route('gifts.claim', $giftNote)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                        <?php echo e(__('Claim Gift Note')); ?>

                    </button>
                </form>
            </div>
        <?php elseif($giftNote->recipient_id === auth()->id() && $giftNote->isClaimed()): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-blue-400 mb-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <h3 class="text-lg font-semibold text-blue-900 mb-2"><?php echo e(__('Gift Claimed')); ?></h3>
                <p class="text-sm text-blue-800">
                    <?php echo e(__('This gift has been claimed and added to your library.')); ?>

                </p>
                <a href="<?php echo e(route('notes.index')); ?>" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                    <?php echo e(__('View in My Library')); ?> →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\gifts\show.blade.php ENDPATH**/ ?>