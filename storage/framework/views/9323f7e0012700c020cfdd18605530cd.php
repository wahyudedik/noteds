<?php $__env->startSection('title', __('messages.admin_refund_management')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.refund_management')); ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?php echo e(__('messages.review_and_process_refund_requests')); ?></p>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600"><?php echo e(__('messages.pending')); ?></div>
                <div class="text-2xl font-bold text-yellow-600"><?php echo e($stats['pending']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600"><?php echo e(__('messages.approved')); ?></div>
                <div class="text-2xl font-bold text-blue-600"><?php echo e($stats['approved']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600"><?php echo e(__('messages.rejected')); ?></div>
                <div class="text-2xl font-bold text-red-600"><?php echo e($stats['rejected']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600"><?php echo e(__('messages.processed')); ?></div>
                <div class="text-2xl font-bold text-green-600"><?php echo e($stats['processed']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="text-sm text-gray-600"><?php echo e(__('messages.pending_amount')); ?></div>
                <div class="text-2xl font-bold text-gray-900"><?php echo e(currency($stats['total_amount_pending'])); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="<?php echo e(route('admin.refunds.index')); ?>" class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="<?php echo e(__('messages.search_by_buyer_name_or_email')); ?>"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <select name="status" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value=""><?php echo e(__('messages.all_status')); ?></option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                        <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>><?php echo e(__('messages.approved')); ?></option>
                        <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>><?php echo e(__('messages.rejected')); ?></option>
                        <option value="processed" <?php echo e(request('status') === 'processed' ? 'selected' : ''); ?>><?php echo e(__('messages.processed')); ?></option>
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    <?php echo e(__('messages.filter')); ?>

                </button>
            </div>
        </form>

        <!-- Refunds Table -->
        <?php if($refunds->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.buyer')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.note')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.amount')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.reason')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.status')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.date')); ?>

                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <?php echo e(__('messages.actions')); ?>

                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $refunds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $refund): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($refund->buyer->name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($refund->buyer->email); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <a href="<?php echo e(route('marketplace.show', $refund->note)); ?>" class="text-blue-600 hover:text-blue-800">
                                            <?php echo e(Str::limit($refund->note->title, 40)); ?>

                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900"><?php echo e(currency($refund->amount)); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $refund->reason))); ?>

                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($refund->status === 'pending'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <?php echo e(__('messages.pending')); ?>

                                        </span>
                                    <?php elseif($refund->status === 'approved'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo e(__('messages.approved')); ?>

                                        </span>
                                    <?php elseif($refund->status === 'rejected'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <?php echo e(__('messages.rejected')); ?>

                                        </span>
                                    <?php elseif($refund->status === 'processed'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <?php echo e(__('messages.processed')); ?>

                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($refund->created_at->format('M d, Y')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo e(route('admin.refunds.show', $refund)); ?>"
                                        class="text-blue-600 hover:text-blue-900">
                                        <?php echo e(__('messages.review')); ?>

                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php echo e($refunds->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('messages.no_refund_requests')); ?></h3>
                <p class="mt-2 text-sm text-gray-500"><?php echo e(__('messages.no_refund_requests_match_filters')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\refunds\index.blade.php ENDPATH**/ ?>