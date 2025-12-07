<?php $__env->startSection('title', __('buyer.purchase_history.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('buyer.purchase_history.title')); ?></h1>
                <p class="mt-2 text-sm text-gray-600"><?php echo e(__('buyer.purchase_history.subtitle')); ?></p>
            </div>
            <a href="<?php echo e(route('buyer-analytics.index')); ?>" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                <?php echo e(__('buyer.purchase_history.back_to_analytics')); ?>

            </a>
        </div>

        <?php if($purchases->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('buyer.purchase_history.headers.note')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('buyer.purchase_history.headers.seller')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('buyer.purchase_history.headers.price')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('buyer.purchase_history.headers.date')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('buyer.purchase_history.headers.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $purchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($purchase->note->title); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e(Str::limit($purchase->note->summary ?? '', 50)); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo e($purchase->note->user->name); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900"><?php echo e(currency($purchase->purchase_price)); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo e($purchase->purchased_at->format('d M Y')); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($purchase->purchased_at->format('H:i')); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?php echo e(route('marketplace.show', $purchase->note)); ?>" class="text-blue-600 hover:text-blue-700">
                                            <?php echo e(__('buyer.purchase_history.view')); ?>

                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <?php echo e($purchases->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900"><?php echo e(__('buyer.purchase_history.empty_title')); ?></h3>
                <p class="mt-1 text-sm text-gray-500"><?php echo e(__('buyer.purchase_history.empty_message')); ?></p>
                <div class="mt-6">
                    <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <?php echo e(__('buyer.purchase_history.browse_marketplace')); ?>

                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\buyer\analytics\purchase-history.blade.php ENDPATH**/ ?>