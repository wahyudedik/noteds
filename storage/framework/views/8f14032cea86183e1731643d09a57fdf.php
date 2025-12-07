<?php $__env->startSection('title', __('messages.repurchase_statistics_report')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.repurchase_statistics_report')); ?></h2>
                <p class="text-sm text-gray-600 mt-1"><?php echo e(__('messages.repurchase_statistics_description')); ?></p>
            </div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_dashboard')); ?></a>
        </div>

        <!-- Date Filter -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.repurchase-report')); ?>" class="flex items-end gap-4">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.start_date')); ?></label>
                    <input type="date" name="start_date" id="start_date" value="<?php echo e($startDate); ?>" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.end_date')); ?></label>
                    <input type="date" name="end_date" id="end_date" value="<?php echo e($endDate); ?>" 
                        class="rounded-md border-gray-300 shadow-sm">
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                        <?php echo e(__('messages.filter')); ?>

                    </button>
                </div>
                <div>
                    <a href="<?php echo e(route('admin.repurchase-report')); ?>" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md">
                        <?php echo e(__('messages.reset')); ?>

                    </a>
                </div>
            </form>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_repurchases')); ?></div>
                <div class="text-3xl font-bold text-gray-900 mt-2"><?php echo e(number_format($stats['total_repurchases'])); ?></div>
                <div class="text-xs text-gray-600 mt-1"><?php echo e(__('messages.repurchase_rate')); ?>: <?php echo e(number_format($stats['repurchase_rate'], 2)); ?>%</div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_revenue')); ?></div>
                <div class="text-3xl font-bold text-green-600 mt-2"><?php echo e(currency($stats['total_revenue'])); ?></div>
                <div class="text-xs text-gray-600 mt-1"><?php echo e(__('messages.from_repurchases')); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.average_time')); ?></div>
                <div class="text-3xl font-bold text-blue-600 mt-2"><?php echo e(number_format($stats['avg_time_days'], 1)); ?></div>
                <div class="text-xs text-gray-600 mt-1"><?php echo e(__('messages.days_to_repurchase')); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.average_price')); ?></div>
                <div class="text-3xl font-bold text-purple-600 mt-2"><?php echo e(currency($stats['avg_price'])); ?></div>
                <div class="text-xs text-gray-600 mt-1"><?php echo e(__('messages.per_repurchase')); ?></div>
            </div>
        </div>

        <!-- Grace Period Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-green-900"><?php echo e(__('messages.within_grace_period')); ?></h3>
                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo e(number_format($stats['within_grace_period'])); ?> <?php echo e(__('messages.repurchases')); ?>

                    </span>
                </div>
                <div class="text-2xl font-bold text-green-900 mb-2">
                    <?php echo e(currency($stats['within_grace_period_revenue'])); ?>

                </div>
                <div class="text-sm text-green-700">
                    <?php echo e(__('messages.revenue_from_repurchases_at_original_price')); ?>

                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-yellow-900"><?php echo e(__('messages.after_grace_period')); ?></h3>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo e(number_format($stats['after_grace_period'])); ?> <?php echo e(__('messages.repurchases')); ?>

                    </span>
                </div>
                <div class="text-2xl font-bold text-yellow-900 mb-2">
                    <?php echo e(currency($stats['after_grace_period_revenue'])); ?>

                </div>
                <div class="text-sm text-yellow-700">
                    <?php echo e(__('messages.revenue_from_repurchases_at_premium_price')); ?>

                </div>
            </div>
        </div>

        <!-- Top Notes by Repurchases -->
        <?php if($repurchasesByNote->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.top_notes_by_repurchases')); ?></h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.note')); ?></th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.repurchases')); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.total_revenue')); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.total_amount')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $repurchasesByNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e(Str::limit($item['note']->title, 50)); ?></div>
                                        <div class="text-xs text-gray-500">by <?php echo e($item['note']->user->name); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900"><?php echo e($item['count']); ?></td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-green-600"><?php echo e(currency($item['revenue'])); ?></td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-900"><?php echo e(currency($item['total_amount'])); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Top Buyers by Repurchases -->
        <?php if($repurchasesByBuyer->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.top_buyers_by_repurchases')); ?></h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.buyer')); ?></th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.repurchases')); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.total_spent')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $repurchasesByBuyer; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($item['buyer']->name); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo e($item['buyer']->email); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm text-gray-900"><?php echo e($item['count']); ?></td>
                                    <td class="px-6 py-4 text-right text-sm font-medium text-blue-600"><?php echo e(currency($item['total_spent'])); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Repurchase Transactions List -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.all_repurchase_transactions')); ?></h3>
            <?php if($repurchaseTransactions->count() > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.date')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.note')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.buyer')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.seller')); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.amount')); ?></th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.total_revenue')); ?></th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.grace_period')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $repurchaseTransactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $firstPurchase = \App\Models\Transaction::where('note_id', $transaction->note_id)
                                        ->where('buyer_id', $transaction->buyer_id)
                                        ->where('status', 'success')
                                        ->where('id', '<', $transaction->id)
                                        ->orderBy('created_at', 'asc')
                                        ->first();
                                    
                                    $isWithinGracePeriod = false;
                                    if ($firstPurchase && $firstPurchase->grace_period_ends_at) {
                                        $isWithinGracePeriod = $transaction->created_at->lte($firstPurchase->grace_period_ends_at);
                                    }
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($transaction->created_at->format('d M Y H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e(Str::limit($transaction->note->title, 40)); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo e($transaction->buyer->name); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900"><?php echo e($transaction->seller->name); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                        <?php echo e(currency($transaction->amount)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-green-600">
                                        <?php echo e(currency($transaction->platform_fee)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <?php if($isWithinGracePeriod): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <?php echo e(__('messages.within')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <?php echo e(__('messages.after')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900"><?php echo e(__('messages.no_repurchases_found')); ?></h3>
                    <p class="mt-1 text-sm text-gray-500"><?php echo e(__('messages.no_repurchase_transactions_found')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\repurchase-report.blade.php ENDPATH**/ ?>