<?php $__env->startSection('title', __('affiliate.admin_title') . ' - ' . __('affiliate.recent_payouts')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('affiliate.admin_title')); ?></h2>
                <p class="text-gray-600 mt-1"><?php echo e(__('affiliate.recent_payouts')); ?></p>
            </div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">
                ← <?php echo e(__('messages.back_to_dashboard')); ?>

            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.total_payouts')); ?></p>
                <p class="text-2xl font-bold text-gray-900 mt-2"><?php echo e(currency($totalPayouts)); ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.pending_payouts')); ?></p>
                <p class="text-2xl font-bold text-yellow-600 mt-2"><?php echo e(currency($pendingPayouts)); ?></p>
                <p class="text-xs text-gray-500 mt-1"><?php echo e($pendingCount); ?> <?php echo e(__('affiliate.payout_status.pending')); ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <a href="<?php echo e(route('admin.affiliate.index')); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <?php echo e(__('affiliate.affiliate_stats')); ?> →
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.affiliate.payouts')); ?>" class="flex gap-4 flex-wrap">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_status')); ?></option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.pending')); ?></option>
                    <option value="processing" <?php echo e(request('status') === 'processing' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.processing')); ?></option>
                    <option value="completed" <?php echo e(request('status') === 'completed' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.completed')); ?></option>
                    <option value="failed" <?php echo e(request('status') === 'failed' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.failed')); ?></option>
                </select>
                <?php if($affiliates->count() > 0): ?>
                    <select name="affiliate_id" class="rounded-md border-gray-300 shadow-sm">
                        <option value=""><?php echo e(__('messages.all_affiliates') ?: 'All Affiliates'); ?></option>
                        <?php $__currentLoopData = $affiliates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $affiliate): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($affiliate->id); ?>" <?php echo e(request('affiliate_id') == $affiliate->id ? 'selected' : ''); ?>>
                                <?php echo e($affiliate->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                <?php endif; ?>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.filter')); ?>

                </button>
                <?php if(request('status') || request('affiliate_id')): ?>
                    <a href="<?php echo e(route('admin.affiliate.payouts')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Payouts Table -->
        <?php if($payouts->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.date')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.user')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.amount')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.method')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.commissions')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payout): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($payout->created_at->format('d M Y, H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($payout->affiliate->name); ?><br>
                                        <span class="text-xs text-gray-500"><?php echo e($payout->affiliate->email); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e(currency($payout->amount)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e(__('affiliate.payout_methods.' . $payout->payout_method)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($payout->commission_count); ?> <?php echo e(__('affiliate.commissions')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            <?php echo e($payout->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                               ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                               ($payout->status === 'failed' ? 'bg-red-100 text-red-800' : 
                                               ($payout->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')))); ?>">
                                            <?php echo e(__('affiliate.payout_status.' . $payout->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?php echo e(route('admin.affiliate.payouts.show', $payout)); ?>" 
                                            class="text-blue-600 hover:text-blue-800">
                                            <?php echo e($payout->status === 'pending' ? __('messages.review') : __('messages.view')); ?>

                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($payouts->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="mt-4 text-sm text-gray-500"><?php echo e(__('affiliate.no_payouts') ?: 'No payouts yet'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\affiliate\payouts.blade.php ENDPATH**/ ?>