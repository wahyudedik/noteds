<?php $__env->startSection('title', __('affiliate.admin_title') . ' - ' . __('messages.withdraw_detail')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.affiliate.payouts')); ?>" class="text-blue-600 hover:text-blue-800">
                ← <?php echo e(__('affiliate.recent_payouts')); ?>

            </a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6"><?php echo e(__('messages.withdraw_detail')); ?></h2>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="bg-red-100 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.user_information')); ?></h3>
                    <div class="space-y-2">
                        <p><strong><?php echo e(__('messages.name')); ?>:</strong> <?php echo e($payout->affiliate->name); ?></p>
                        <p><strong><?php echo e(__('messages.email')); ?>:</strong> <?php echo e($payout->affiliate->email); ?></p>
                        <p><strong><?php echo e(__('messages.current_balance')); ?>:</strong> <?php echo e(currency($payout->affiliate->wallet_balance ?? 0)); ?></p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.withdraw_detail')); ?></h3>
                    <div class="space-y-2">
                        <p><strong><?php echo e(__('affiliate.amount')); ?>:</strong> 
                            <span class="text-lg font-bold text-green-600"><?php echo e(currency($payout->amount)); ?></span>
                        </p>
                        <p><strong><?php echo e(__('affiliate.method')); ?>:</strong> <?php echo e(__('affiliate.payout_methods.' . $payout->payout_method)); ?></p>
                        <p><strong><?php echo e(__('affiliate.commissions')); ?>:</strong> <?php echo e($payout->commission_count); ?></p>
                        <p><strong><?php echo e(__('affiliate.status')); ?>:</strong> 
                            <span class="px-2 py-1 rounded text-xs font-medium 
                                <?php echo e($payout->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                   ($payout->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                   ($payout->status === 'failed' ? 'bg-red-100 text-red-800' : 
                                   ($payout->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800')))); ?>">
                                <?php echo e(__('affiliate.payout_status.' . $payout->status)); ?>

                            </span>
                        </p>
                        <p><strong><?php echo e(__('messages.requested')); ?>:</strong> <?php echo e($payout->created_at->format('d M Y, H:i')); ?></p>
                        <?php if($payout->processed_at): ?>
                            <p><strong><?php echo e(__('messages.processed')); ?>:</strong> <?php echo e($payout->processed_at->format('d M Y, H:i')); ?></p>
                            <p><strong><?php echo e(__('messages.processed_by')); ?>:</strong> <?php echo e($payout->processedBy->name ?? '-'); ?></p>
                        <?php endif; ?>
                        <?php if($payout->payout_reference): ?>
                            <p><strong><?php echo e(__('affiliate.payout_reference')); ?>:</strong> <?php echo e($payout->payout_reference); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($payout->payout_details): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2"><?php echo e(__('affiliate.payout_details')); ?></h4>
                    <pre class="bg-gray-50 rounded-lg p-4 text-sm text-gray-700"><?php echo e(json_encode($payout->payout_details, JSON_PRETTY_PRINT)); ?></pre>
                </div>
            <?php endif; ?>

            <?php if($payout->notes): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2"><?php echo e(__('messages.notes')); ?></h4>
                    <p class="text-gray-700"><?php echo e($payout->notes); ?></p>
                </div>
            <?php endif; ?>

            <!-- Commissions included in this payout -->
            <?php if($payout->commissions->count() > 0): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4"><?php echo e(__('affiliate.commissions')); ?> (<?php echo e($payout->commissions->count()); ?>)</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.tier')); ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.amount')); ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.rate')); ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.date')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $payout->commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900"><?php echo e(__('affiliate.tier')); ?> <?php echo e($commission->tier); ?></td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e(currency($commission->commission_amount)); ?></td>
                                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($commission->commission_rate); ?>%</td>
                                        <td class="px-4 py-3 text-sm text-gray-500"><?php echo e($commission->created_at->format('d M Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(in_array($payout->status, ['pending', 'processing'])): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4"><?php echo e(__('messages.process_payout')); ?></h4>
                    <form action="<?php echo e(route('admin.affiliate.payouts.update', $payout)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e(__('affiliate.status')); ?> <span class="text-red-500">*</span>
                                </label>
                                <select name="status" id="status" required
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                    <option value="pending" <?php echo e($payout->status === 'pending' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.pending')); ?></option>
                                    <option value="processing" <?php echo e($payout->status === 'processing' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.processing')); ?></option>
                                    <option value="completed" <?php echo e($payout->status === 'completed' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.completed')); ?></option>
                                    <option value="failed" <?php echo e($payout->status === 'failed' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.failed')); ?></option>
                                    <option value="cancelled" <?php echo e($payout->status === 'cancelled' ? 'selected' : ''); ?>><?php echo e(__('affiliate.payout_status.cancelled')); ?></option>
                                </select>
                            </div>
                            <div>
                                <label for="payout_reference" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e(__('affiliate.payout_reference')); ?> (<?php echo e(__('affiliate.optional')); ?>)
                                </label>
                                <input type="text" name="payout_reference" id="payout_reference" 
                                    value="<?php echo e(old('payout_reference', $payout->payout_reference)); ?>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <p class="mt-1 text-xs text-gray-500"><?php echo e(__('affiliate.payout_reference_hint')); ?></p>
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    <?php echo e(__('messages.notes')); ?> (<?php echo e(__('affiliate.optional')); ?>)
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"><?php echo e(old('notes', $payout->notes)); ?></textarea>
                            </div>
                            <div class="flex items-center justify-end gap-3">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    <?php echo e(__('messages.update')); ?>

                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\affiliate\payout-show.blade.php ENDPATH**/ ?>