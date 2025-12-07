<?php $__env->startSection('title', __('affiliate.admin_title') . ' - ' . __('affiliate.recent_commissions')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('affiliate.recent_commissions')); ?></h2>
                <p class="text-gray-600 mt-1"><?php echo e(__('messages.manage')); ?> <?php echo e(__('affiliate.recent_commissions')); ?></p>
            </div>
            <a href="<?php echo e(route('admin.affiliate.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← <?php echo e(__('affiliate.admin_title')); ?>

            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.total_commissions')); ?></p>
                <p class="text-2xl font-bold text-gray-900 mt-2"><?php echo e(currency($totalCommissions)); ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.pending_commissions')); ?></p>
                <p class="text-2xl font-bold text-yellow-600 mt-2"><?php echo e(currency($pendingCommissions)); ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.approved_commissions')); ?></p>
                <p class="text-2xl font-bold text-blue-600 mt-2"><?php echo e(currency($approvedCommissions)); ?></p>
            </div>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
                <p class="text-sm font-medium text-gray-500"><?php echo e(__('affiliate.statuses.paid')); ?></p>
                <p class="text-2xl font-bold text-green-600 mt-2"><?php echo e(currency($paidCommissions)); ?></p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.affiliate.commissions')); ?>" class="flex gap-4 flex-wrap">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_status')); ?></option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>><?php echo e(__('affiliate.statuses.pending')); ?></option>
                    <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>><?php echo e(__('affiliate.statuses.approved')); ?></option>
                    <option value="paid" <?php echo e(request('status') === 'paid' ? 'selected' : ''); ?>><?php echo e(__('affiliate.statuses.paid')); ?></option>
                </select>
                <select name="tier" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_tiers') ?: 'All Tiers'); ?></option>
                    <option value="1" <?php echo e(request('tier') === '1' ? 'selected' : ''); ?>>Tier 1</option>
                    <option value="2" <?php echo e(request('tier') === '2' ? 'selected' : ''); ?>>Tier 2</option>
                    <option value="3" <?php echo e(request('tier') === '3' ? 'selected' : ''); ?>>Tier 3</option>
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
                <?php if(request('status') || request('tier') || request('affiliate_id')): ?>
                    <a href="<?php echo e(route('admin.affiliate.commissions')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Approve Commissions Form -->
        <?php if($commissions->where('status', 'pending')->count() > 0): ?>
            <form action="<?php echo e(route('admin.affiliate.commissions.approve')); ?>" method="POST" id="approve-form" class="mb-6">
                <?php echo csrf_field(); ?>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <?php echo e(__('affiliate.approve_selected') ?: 'Approve Selected'); ?>

                </button>
            </form>
        <?php endif; ?>

        <!-- Commissions Table -->
        <?php if($commissions->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <?php if($commissions->where('status', 'pending')->count() > 0): ?>
                                    <th class="px-6 py-3 text-left">
                                        <input type="checkbox" id="select-all" class="rounded border-gray-300">
                                    </th>
                                <?php endif; ?>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.date')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.user')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.tier')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.rate')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.amount')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('affiliate.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $commission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <?php if($commissions->where('status', 'pending')->count() > 0): ?>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($commission->status === 'pending'): ?>
                                                <input type="checkbox" name="commission_ids[]" value="<?php echo e($commission->id); ?>" 
                                                    class="commission-checkbox rounded border-gray-300">
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($commission->created_at->format('d M Y, H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e($commission->affiliate->name); ?><br>
                                        <span class="text-xs text-gray-500"><?php echo e($commission->affiliate->email); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e(__('affiliate.tier')); ?> <?php echo e($commission->tier); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($commission->commission_rate); ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e(currency($commission->commission_amount)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            <?php echo e($commission->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                               ($commission->status === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                                            <?php echo e(__('affiliate.statuses.' . $commission->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($commission->conversion): ?>
                                            <a href="<?php echo e(route('admin.affiliate.conversions.show', $commission->conversion)); ?>" 
                                                class="text-blue-600 hover:text-blue-800">
                                                <?php echo e(__('messages.view')); ?>

                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($commissions->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-12 text-center">
                <p class="text-sm text-gray-500"><?php echo e(__('affiliate.no_commissions')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.getElementById('select-all')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.commission-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\affiliate\commissions.blade.php ENDPATH**/ ?>