<?php $__env->startSection('title', __('Refund Request Details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('refunds.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Refunds')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Refund Request Details')); ?></h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <?php if($refund->status === 'pending'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                    <?php echo e(__('Pending Review')); ?>

                </span>
            <?php elseif($refund->status === 'approved'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <?php echo e(__('Approved')); ?>

                </span>
            <?php elseif($refund->status === 'rejected'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    <?php echo e(__('Rejected')); ?>

                </span>
            <?php elseif($refund->status === 'processed'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <?php echo e(__('Processed')); ?>

                </span>
            <?php endif; ?>
        </div>

        <!-- Details Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Refund Information')); ?></h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Note')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('marketplace.show', $refund->note)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($refund->note->title); ?>

                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Amount')); ?></dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900"><?php echo e(currency($refund->amount)); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Reason')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $refund->reason))); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Requested Date')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->created_at->format('M d, Y H:i')); ?></dd>
                </div>
                <?php if($refund->processed_at): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Processed Date')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->processed_at->format('M d, Y H:i')); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if($refund->processedBy): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Processed By')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->processedBy->name); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Reason Description -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Reason Description')); ?></h2>
            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($refund->reason_description); ?></p>
        </div>

        <!-- Admin Notes -->
        <?php if($refund->admin_notes): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-blue-900 mb-4"><?php echo e(__('Admin Response')); ?></h2>
                <p class="text-sm text-blue-800 whitespace-pre-wrap"><?php echo e($refund->admin_notes); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\refunds\show.blade.php ENDPATH**/ ?>