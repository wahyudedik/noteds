<?php $__env->startSection('title', __('messages.admin_transactions')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.transactions')); ?></h2>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_dashboard')); ?></a>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_revenue')); ?></div>
                <div class="text-2xl font-bold text-green-600"><?php echo e(currency($totalRevenue)); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_transaction_value')); ?></div>
                <div class="text-2xl font-bold text-blue-600"><?php echo e(currency($totalTransactions)); ?></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.transactions.index')); ?>" class="flex gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" :placeholder="__('messages.search_buyer_seller')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_status')); ?></option>
                    <option value="success" <?php echo e(request('status') === 'success' ? 'selected' : ''); ?>><?php echo e(__('messages.success')); ?></option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                    <option value="failed" <?php echo e(request('status') === 'failed' ? 'selected' : ''); ?>><?php echo e(__('messages.failed')); ?></option>
                </select>
                <select name="payment_method" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_payment_methods')); ?></option>
                    <option value="wallet" <?php echo e(request('payment_method') === 'wallet' ? 'selected' : ''); ?>><?php echo e(__('messages.wallet')); ?></option>
                    <option value="withdraw" <?php echo e(request('payment_method') === 'withdraw' ? 'selected' : ''); ?>><?php echo e(__('messages.withdraw')); ?></option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.filter')); ?>

                </button>
                <?php if(request()->hasAny(['search', 'status', 'payment_method'])): ?>
                    <a href="<?php echo e(route('admin.transactions.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if($transactions->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.date')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.buyer')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.seller')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.note')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.amount')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.commission')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.method')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.status')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($transaction->created_at->format('d M Y, H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($transaction->buyer->name); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($transaction->seller->name); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php if($transaction->note): ?>
                                            <?php echo e(Str::limit($transaction->note->title, 30)); ?>

                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php echo e(currency($transaction->amount, null, $transaction->currency ?? config('currency.base_currency'))); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                        <?php echo e(currency($transaction->commission, null, $transaction->currency ?? config('currency.base_currency'))); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($transaction->payment_method ?? '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($transaction->status === 'success'): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.success')); ?></span>
                                        <?php elseif($transaction->status === 'pending'): ?>
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.pending')); ?></span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.failed')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    <?php echo e($transactions->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600"><?php echo e(__('messages.no_transactions_found')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\transactions\index.blade.php ENDPATH**/ ?>