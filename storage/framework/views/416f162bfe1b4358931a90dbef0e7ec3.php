<?php $__env->startSection('title', __('messages.subscription_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.subscription_details')); ?></h1>
            </div>
        </div>

        <!-- Flash Messages -->
        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Subscription Details -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
            <div class="p-6">
                <!-- User Info -->
                <div class="border-b border-gray-200 pb-6 mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.user_information')); ?></h2>
                    <div class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <?php if($subscription->user->avatar): ?>
                                <img src="<?php echo e($subscription->user->avatar); ?>" alt="<?php echo e($subscription->user->name); ?>" class="h-16 w-16 rounded-full object-cover">
                            <?php else: ?>
                                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                    <span class="text-xl font-bold text-white"><?php echo e(strtoupper(substr($subscription->user->name, 0, 1))); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($subscription->user->name); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo e($subscription->user->email); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Subscription Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-sm text-gray-500"><?php echo e(__('messages.status')); ?></p>
                        <div class="mt-1">
                            <?php if($subscription->status === 'active'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <?php echo e(__('messages.active')); ?>

                                </span>
                            <?php elseif($subscription->status === 'pending'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <?php echo e(__('messages.pending')); ?>

                                </span>
                            <?php elseif($subscription->status === 'expired'): ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                    <?php echo e(__('messages.expired')); ?>

                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <?php echo e(__('messages.cancelled')); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500"><?php echo e(__('messages.plan')); ?></p>
                        <p class="mt-1 text-base font-medium text-gray-900 capitalize"><?php echo e($subscription->plan); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500"><?php echo e(__('messages.submitted_at')); ?></p>
                        <p class="mt-1 text-base text-gray-900"><?php echo e($subscription->created_at->format('d M Y, H:i')); ?></p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500"><?php echo e(__('messages.expires_at')); ?></p>
                        <p class="mt-1 text-base text-gray-900"><?php echo e($subscription->expired_at ? $subscription->expired_at->format('d M Y, H:i') : '-'); ?></p>
                    </div>

                    <?php if($subscription->approved_by): ?>
                        <div>
                            <p class="text-sm text-gray-500"><?php echo e(__('messages.approved_by')); ?></p>
                            <p class="mt-1 text-base text-gray-900"><?php echo e($subscription->approvedBy->name ?? __('messages.admin')); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500"><?php echo e(__('messages.approved_at')); ?></p>
                            <p class="mt-1 text-base text-gray-900"><?php echo e($subscription->approved_at ? $subscription->approved_at->format('d M Y, H:i') : '-'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Payment Proof -->
                <?php if($subscription->payment_proof): ?>
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.payment_proof')); ?></h3>
                        <a href="<?php echo e($subscription->payment_proof); ?>" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            <?php echo e(__('messages.view_payment_proof')); ?>

                        </a>
                    </div>
                <?php endif; ?>

                <!-- Admin Notes -->
                <?php if($subscription->admin_notes): ?>
                    <div class="border-t border-gray-200 pt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.admin_notes')); ?></h3>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($subscription->admin_notes); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Approve/Reject Actions -->
        <?php if($subscription->status === 'pending'): ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.actions')); ?></h2>
                    
                    <!-- Approve Form -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                        <h3 class="text-base font-semibold text-green-900 mb-4"><?php echo e(__('messages.approve_subscription')); ?></h3>
                        <form action="<?php echo e(route('admin.subscriptions.approve', $subscription)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="space-y-4">
                                <div>
                                    <label for="expired_at" class="block text-sm font-medium text-green-900 mb-2">
                                        <?php echo e(__('messages.expiration_date')); ?> <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="expired_at" id="expired_at" required
                                        min="<?php echo e(date('Y-m-d', strtotime('+1 day'))); ?>"
                                        class="block w-full rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200">
                                </div>
                                <div>
                                    <label for="approve_notes" class="block text-sm font-medium text-green-900 mb-2">
                                        <?php echo e(__('messages.notes_optional')); ?>

                                    </label>
                                    <textarea name="admin_notes" id="approve_notes" rows="3"
                                        :placeholder="__('messages.add_notes_approval')"
                                        class="block w-full rounded-lg border-green-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200"></textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('messages.approve_subscription')); ?>

                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                        <h3 class="text-base font-semibold text-red-900 mb-4"><?php echo e(__('messages.reject_subscription')); ?></h3>
                        <form action="<?php echo e(route('admin.subscriptions.reject', $subscription)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="space-y-4">
                                <div>
                                    <label for="reject_notes" class="block text-sm font-medium text-red-900 mb-2">
                                        <?php echo e(__('messages.reason_for_rejection')); ?> <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="admin_notes" id="reject_notes" rows="3" required
                                        :placeholder="__('messages.explain_rejection')"
                                        class="block w-full rounded-lg border-red-300 shadow-sm focus:border-red-500 focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition-all duration-200"></textarea>
                                </div>
                                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                    <?php echo e(__('messages.reject_subscription')); ?>

                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\subscriptions\show.blade.php ENDPATH**/ ?>