<?php $__env->startSection('title', __('messages.commission_tiers')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.commission_tiers')); ?></h2>
                <p class="text-sm text-gray-600 mt-1">
                    <?php echo e(__('messages.commission_tiers_description')); ?>

                </p>
            </div>
            <div class="flex flex-wrap gap-3 items-center">
                <form method="GET" action="<?php echo e(route('admin.commission-tiers.index')); ?>" class="flex items-center gap-2 bg-white shadow-sm rounded-lg px-3 py-2">
                    <label for="period" class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                        <?php echo e(__('messages.reporting_period')); ?>

                    </label>
                    <select id="period" name="period" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                        <?php $__currentLoopData = $periodOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($value); ?>" <?php if($period === $value): echo 'selected'; endif; ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </form>
                <a href="<?php echo e(route('admin.commission-tiers.create')); ?>"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200">
                    <?php echo e(__('messages.create_commission_tier')); ?>

                </a>
            </div>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="mb-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <?php echo e(__('messages.commission_tier_performance')); ?>

                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        <?php echo e(__('messages.commission_tier_performance_hint', ['period' => $periodOptions[$period]])); ?>

                    </p>
                </div>
                <div class="px-6 py-5">
                    <?php if($reports->isEmpty()): ?>
                        <p class="text-sm text-gray-500">
                            <?php echo e(__('messages.commission_tier_performance_empty')); ?>

                        </p>
                    <?php else: ?>
                        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                            <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-base font-semibold text-gray-900">
                                                <?php echo e($report['tier']->name); ?>

                                            </h4>
                                            <p class="text-xs text-gray-500"><?php echo e(__('messages.volume_threshold_label')); ?>: <?php echo e(currency($report['tier']->volume_threshold ?? 0)); ?></p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($report['tier']->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-700'); ?>">
                                            <?php echo e($report['tier']->is_active ? __('messages.active') : __('messages.inactive')); ?>

                                        </span>
                                    </div>

                                    <dl class="space-y-3">
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <dt><?php echo e(__('messages.transactions_count')); ?></dt>
                                            <dd class="font-semibold text-gray-900"><?php echo e(number_format($report['transactions_count'])); ?></dd>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <dt><?php echo e(__('messages.total_gross_volume')); ?></dt>
                                            <dd class="font-semibold text-gray-900"><?php echo e(currency($report['gross_volume'])); ?></dd>
                                        </div>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <dt><?php echo e(__('messages.net_payout')); ?></dt>
                                            <dd class="font-semibold text-gray-900"><?php echo e(currency($report['net_payout_total'])); ?></dd>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.total_platform_fee')); ?></p>
                                                <p class="text-gray-900"><?php echo e(currency($report['platform_fee_total'])); ?></p>
                                            </div>
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.total_creator_commission')); ?></p>
                                                <p class="text-gray-900"><?php echo e(currency($report['creator_commission_total'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.unique_sellers')); ?></p>
                                                <p class="text-gray-900"><?php echo e(number_format($report['unique_sellers'])); ?></p>
                                            </div>
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.current_sellers_period')); ?></p>
                                                <p class="text-gray-900"><?php echo e(number_format($report['current_sellers'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-3 text-xs text-gray-600">
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.average_order_value')); ?></p>
                                                <p class="text-gray-900"><?php echo e(currency($report['average_order_value'])); ?></p>
                                            </div>
                                            <div>
                                                <p class="uppercase tracking-wide font-semibold text-gray-500"><?php echo e(__('messages.average_seller_volume')); ?></p>
                                                <p class="text-gray-900"><?php echo e(currency($report['average_seller_volume'])); ?></p>
                                            </div>
                                        </div>
                                    </dl>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if($tiers->isEmpty()): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-600">
                <?php echo e(__('messages.no_commission_tiers')); ?>

            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.tier_name')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.volume_threshold_label')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.platform_fee_percent')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.creator_commission_percent')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.tier_status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900"><?php echo e($tier->name); ?></div>
                                        <?php if($tier->description): ?>
                                            <div class="text-xs text-gray-500 mt-1"><?php echo e($tier->description); ?></div>
                                        <?php endif; ?>
                                        <div class="text-xs text-gray-400 mt-1"><?php echo e(__('messages.sort_order')); ?>: <?php echo e($tier->sort_order ?? 0); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e(currency($tier->volume_threshold ?? 0)); ?>

                                        <div class="text-xs text-gray-500"><?php echo e(__('messages.volume_threshold_window')); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e(number_format($tier->platform_fee_percent, 1)); ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        <?php echo e(number_format($tier->creator_commission_percent, 1)); ?>%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($tier->is_active): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <?php echo e(__('messages.active')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700">
                                                <?php echo e(__('messages.inactive')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center gap-3">
                                            <a href="<?php echo e(route('admin.commission-tiers.edit', $tier)); ?>" class="text-blue-600 hover:text-blue-800">
                                                <?php echo e(__('messages.edit')); ?>

                                            </a>
                                            <form action="<?php echo e(route('admin.commission-tiers.destroy', $tier)); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('messages.confirm_delete_commission_tier')); ?>');">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="text-red-600 hover:text-red-800">
                                                    <?php echo e(__('messages.delete')); ?>

                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\commission-tiers\index.blade.php ENDPATH**/ ?>