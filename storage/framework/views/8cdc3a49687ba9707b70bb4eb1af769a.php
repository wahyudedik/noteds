<?php $__env->startSection('title', __('messages.subscription_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.subscription_details')); ?></h1>
                <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.view_subscription_information')); ?></p>
            </div>
            <a href="<?php echo e(route('subscription.index')); ?>" class="text-gray-600 hover:text-gray-800">
                <?php echo e(__('messages.back_to_subscriptions')); ?>

            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.subscription_information')); ?></h2>
            </div>
            <div class="p-6">
                <!-- Plan -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.plan')); ?></label>
                    <?php if($subscription->plan === 'premium'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                            <?php echo e(__('messages.premium_plan')); ?>

                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                            <?php echo e(__('messages.basic')); ?>

                        </span>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.status')); ?></label>
                    <?php if($subscription->status === 'active'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                            <?php echo e(__('messages.active')); ?>

                        </span>
                    <?php elseif($subscription->status === 'pending'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                            <?php echo e(__('messages.pending_approval')); ?>

                        </span>
                    <?php elseif($subscription->status === 'expired'): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-orange-100 text-orange-800">
                            <?php echo e(__('messages.expired')); ?>

                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                            <?php echo e(__('messages.cancelled')); ?>

                        </span>
                    <?php endif; ?>
                </div>

                <!-- Expiration -->
                <?php if($subscription->expired_at): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.expires_on')); ?></label>
                        <p class="text-sm text-gray-900"><?php echo e($subscription->expired_at->format('F d, Y')); ?></p>
                    </div>
                <?php endif; ?>

                <!-- Payment Proof -->
                <?php if($subscription->payment_proof): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.payment_proof')); ?></label>
                        <a href="<?php echo e($subscription->payment_proof); ?>" target="_blank" class="text-blue-600 hover:text-blue-700 text-sm">
                            <?php echo e(__('messages.view_payment_proof')); ?>

                        </a>
                    </div>
                <?php endif; ?>

                <!-- Admin Notes -->
                <?php if($subscription->admin_notes): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.admin_notes')); ?></label>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                            <p class="text-sm text-gray-700"><?php echo e($subscription->admin_notes); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Approved By -->
                <?php if($subscription->approvedBy): ?>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.approved_by')); ?></label>
                        <p class="text-sm text-gray-900"><?php echo e($subscription->approvedBy->name); ?></p>
                        <?php if($subscription->approved_at): ?>
                            <p class="text-xs text-gray-500"><?php echo e($subscription->approved_at->format('F d, Y H:i')); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Dates -->
                <div class="border-t border-gray-200 pt-6 mt-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.submitted')); ?></label>
                            <p class="text-sm text-gray-600"><?php echo e($subscription->created_at->format('F d, Y')); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.last_updated')); ?></label>
                            <p class="text-sm text-gray-600"><?php echo e($subscription->updated_at->format('F d, Y')); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\subscription\show.blade.php ENDPATH**/ ?>