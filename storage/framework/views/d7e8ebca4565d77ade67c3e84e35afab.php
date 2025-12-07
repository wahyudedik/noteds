<?php $__env->startSection('title', __('messages.admin_refund_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('admin.refunds.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('messages.back_to_refunds')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.refund_request_details')); ?></h1>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
            <?php if($refund->status === 'pending'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <?php echo e(__('messages.pending_review')); ?>

                </span>
            <?php elseif($refund->status === 'approved'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <?php echo e(__('messages.approved')); ?>

                </span>
            <?php elseif($refund->status === 'rejected'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <?php echo e(__('messages.rejected')); ?>

                </span>
            <?php elseif($refund->status === 'processed'): ?>
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    <?php echo e(__('messages.processed')); ?>

                </span>
            <?php endif; ?>
        </div>

        <!-- Refund Details -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.refund_information')); ?></h2>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.buyer')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('admin.users.show', $refund->buyer)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($refund->buyer->name); ?>

                        </a>
                        <div class="text-xs text-gray-500"><?php echo e($refund->buyer->email); ?></div>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.seller')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('admin.users.show', $refund->seller)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($refund->seller->name); ?>

                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.note')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('marketplace.show', $refund->note)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($refund->note->title); ?>

                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.amount')); ?></dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900"><?php echo e(currency($refund->amount)); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.reason')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e(ucfirst(str_replace('_', ' ', $refund->reason))); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.requested_date')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->created_at->format('M d, Y H:i')); ?></dd>
                </div>
                <?php if($refund->processed_at): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.processed_date')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->processed_at->format('M d, Y H:i')); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if($refund->processedBy): ?>
                    <div>
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.processed_by')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900"><?php echo e($refund->processedBy->name); ?></dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Reason Description -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.reason_description')); ?></h2>
            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($refund->reason_description); ?></p>
        </div>

        <!-- Admin Notes -->
        <?php if($refund->admin_notes): ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-blue-900 mb-4"><?php echo e(__('messages.admin_notes')); ?></h2>
                <p class="text-sm text-blue-800 whitespace-pre-wrap"><?php echo e($refund->admin_notes); ?></p>
            </div>
        <?php endif; ?>

        <!-- Actions (if pending) -->
        <?php if($refund->status === 'pending'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.actions')); ?></h2>
                
                <!-- Approve Form -->
                <form action="<?php echo e(route('admin.refunds.approve', $refund)); ?>" method="POST" class="mb-4">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <label for="admin_notes_approve" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.admin_notes')); ?> (<?php echo e(__('messages.optional')); ?>)
                        </label>
                        <textarea name="admin_notes" id="admin_notes_approve" rows="3"
                            placeholder="<?php echo e(__('messages.add_notes_about_approval')); ?>"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                        <?php echo e(__('messages.approve_refund')); ?>

                    </button>
                </form>

                <!-- Reject Form -->
                <form action="<?php echo e(route('admin.refunds.reject', $refund)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <label for="admin_notes_reject" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.rejection_reason')); ?> <span class="text-red-500">*</span>
                        </label>
                        <textarea name="admin_notes" id="admin_notes_reject" rows="3" required
                            placeholder="<?php echo e(__('messages.explain_why_rejected')); ?>"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                        <p class="mt-1 text-xs text-gray-500"><?php echo e(__('messages.message_will_be_sent_to_buyer')); ?></p>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700">
                        <?php echo e(__('messages.reject_refund')); ?>

                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\refunds\show.blade.php ENDPATH**/ ?>