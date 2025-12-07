<?php $__env->startSection('title', __('affiliate.leaderboard_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('affiliate.leaderboard_title')); ?></h1>
            <p class="mt-2 text-base text-gray-600 dark:text-gray-400"><?php echo e(__('affiliate.leaderboard_description')); ?></p>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <form method="GET" action="<?php echo e(route('affiliate.leaderboard')); ?>" class="flex flex-wrap gap-4">
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <?php echo e(__('affiliate.period')); ?>

                    </label>
                    <select name="period" id="period" 
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="all" <?php echo e($period === 'all' ? 'selected' : ''); ?>><?php echo e(__('affiliate.period_all')); ?></option>
                        <option value="7d" <?php echo e($period === '7d' ? 'selected' : ''); ?>><?php echo e(__('affiliate.period_7d')); ?></option>
                        <option value="30d" <?php echo e($period === '30d' ? 'selected' : ''); ?>><?php echo e(__('affiliate.period_30d')); ?></option>
                        <option value="90d" <?php echo e($period === '90d' ? 'selected' : ''); ?>><?php echo e(__('affiliate.period_90d')); ?></option>
                    </select>
                </div>
                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <?php echo e(__('affiliate.sort_by')); ?>

                    </label>
                    <select name="sort" id="sort" 
                        class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="revenue" <?php echo e($sortBy === 'revenue' ? 'selected' : ''); ?>><?php echo e(__('affiliate.sort_revenue')); ?></option>
                        <option value="conversions" <?php echo e($sortBy === 'conversions' ? 'selected' : ''); ?>><?php echo e(__('affiliate.sort_conversions')); ?></option>
                        <option value="commissions" <?php echo e($sortBy === 'commissions' ? 'selected' : ''); ?>><?php echo e(__('affiliate.sort_commissions')); ?></option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                        <?php echo e(__('affiliate.apply_filters')); ?>

                    </button>
                </div>
            </form>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.rank')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.affiliate')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.revenue')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.conversions')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.commissions')); ?>

                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <?php echo e(__('affiliate.conversion_rate')); ?>

                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__empty_1 = true; $__currentLoopData = $affiliates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if($data['rank'] <= 3): ?>
                                            <span class="text-2xl font-bold 
                                                <?php echo e($data['rank'] === 1 ? 'text-yellow-500' : ($data['rank'] === 2 ? 'text-gray-400' : 'text-amber-600')); ?>">
                                                <?php echo e($data['rank'] === 1 ? '🥇' : ($data['rank'] === 2 ? '🥈' : '🥉')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                                                #<?php echo e($data['rank']); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                src="<?php echo e($data['user']->avatar ? asset('storage/' . $data['user']->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($data['user']->name)); ?>" 
                                                alt="<?php echo e($data['user']->name); ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                <?php echo e($data['user']->name); ?>

                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                <?php echo e($data['user']->email); ?>

                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    <?php echo e(currency($data['total_revenue'])); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e(number_format($data['total_conversions'])); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">
                                    <?php echo e(currency($data['total_commissions'])); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e(number_format($data['conversion_rate'], 2)); ?>%
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <?php echo e(__('affiliate.no_affiliates')); ?>

                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\affiliate\leaderboard.blade.php ENDPATH**/ ?>