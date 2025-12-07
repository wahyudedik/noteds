

<?php $__env->startSection('title', 'Points Pricing Management'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Points Pricing & Rewards</h1>
                <p class="mt-2 text-gray-600">Configure point redemption options and monitor user redemptions</p>
            </div>
            <a href="<?php echo e(route('admin.points-pricing.create')); ?>"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                + Add New Pricing Option
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Total Configs</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_configs']); ?></p>
                    </div>
                    <div class="text-3xl text-blue-600">⚙️</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Active Configs</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e($stats['active_configs']); ?></p>
                    </div>
                    <div class="text-3xl text-green-600">✅</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Total Redemptions</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_redemptions']); ?></p>
                    </div>
                    <div class="text-3xl text-purple-600">📊</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Active Redemptions</p>
                        <p class="text-2xl font-bold text-orange-600"><?php echo e($stats['active_redemptions']); ?></p>
                    </div>
                    <div class="text-3xl text-orange-600">🎁</div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="mb-8 flex gap-4">
            <a href="<?php echo e(route('admin.points.monitoring')); ?>"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center gap-2">
                📈 Redemption Monitoring
            </a>
            <a href="<?php echo e(route('admin.points.export')); ?>"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                📥 Export Report
            </a>
        </div>

        <!-- Configurations Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Pricing Configurations</h2>
            </div>

            <?php if($configs->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Points Required
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Limits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-gray-900"><?php echo e($config->name); ?></span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded-lg <?php echo e($config->type === 'discount' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $config->type))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-900"><?php echo e($config->points_required); ?> pts</td>
                                    <td class="px-6 py-4 text-gray-900">
                                        <?php if($config->type === 'discount'): ?>
                                            <?php if($config->discount_amount): ?>
                                                <?php echo e(currency($config->discount_amount)); ?>

                                            <?php else: ?>
                                                <?php echo e($config->discount_percent); ?>%
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php echo e($config->premium_days); ?> days
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        <?php if($config->daily_limit || $config->user_limit): ?>
                                            Daily: <?php echo e($config->daily_limit ?? '∞'); ?> | User:
                                            <?php echo e($config->user_limit ?? '∞'); ?>

                                        <?php else: ?>
                                            No limits
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded-lg <?php echo e($config->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'); ?>">
                                            <?php echo e($config->is_active ? 'Active' : 'Inactive'); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <a href="<?php echo e(route('admin.points-pricing.edit', $config)); ?>"
                                            class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                        <form action="<?php echo e(route('admin.points-pricing.destroy', $config)); ?>" method="POST"
                                            style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($configs->links()); ?>

                </div>
            <?php else: ?>
                <div class="p-6 text-center text-gray-500">
                    <p>No pricing configurations found. <a href="<?php echo e(route('admin.points-pricing.create')); ?>"
                            class="text-blue-600 hover:text-blue-900">Create one now</a></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Information Section -->
        <div class="mt-8 bg-blue-50 rounded-lg p-6 border border-blue-200">
            <h3 class="font-semibold text-blue-900 mb-2">💡 Tips for Managing Points Safely</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✓ Set daily limits to prevent abuse and excessive bonus distribution</li>
                <li>✓ Monitor redemption patterns regularly for suspicious activities</li>
                <li>✓ Balance discount amounts so sellers still profit from sales</li>
                <li>✓ Use user limits to prevent single users from redeeming too many times</li>
                <li>✓ Set expiration dates for temporary promotional offers</li>
            </ul>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\points-pricing\index.blade.php ENDPATH**/ ?>