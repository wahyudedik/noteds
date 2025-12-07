

<?php $__env->startSection('title', 'Points Redemption Monitoring'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="<?php echo e(route('admin.points-pricing.index')); ?>" class="text-blue-600 hover:text-blue-900">&larr; Back to
                    Pricing</a>
                <h1 class="text-3xl font-bold text-gray-900 mt-2">Redemption Monitoring</h1>
                <p class="mt-2 text-gray-600">Monitor real-time point redemptions and track patterns</p>
            </div>
            <a href="<?php echo e(route('admin.points.export')); ?>"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                📥 Export Report
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Today's Redemptions</p>
                        <p class="text-2xl font-bold text-blue-600"><?php echo e($stats['today_count']); ?></p>
                    </div>
                    <div class="text-3xl">📊</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Points Used Today</p>
                        <p class="text-2xl font-bold text-purple-600"><?php echo e(number_format($stats['today_value'])); ?></p>
                    </div>
                    <div class="text-3xl">💎</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">This Week</p>
                        <p class="text-2xl font-bold text-green-600"><?php echo e($stats['week_count']); ?></p>
                    </div>
                    <div class="text-3xl">📈</div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-1">
                        <p class="text-gray-600 text-sm">Active Redemptions</p>
                        <p class="text-2xl font-bold text-orange-600"><?php echo e($stats['active_count']); ?></p>
                    </div>
                    <div class="text-3xl">🎁</div>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="bg-white rounded-lg shadow p-4 mb-8">
            <form method="GET" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" name="from" value="<?php echo e(request('from')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" name="to" value="<?php echo e(request('to')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filter</button>
                <a href="<?php echo e(route('admin.points.monitoring')); ?>"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Reset</a>
            </form>
        </div>

        <!-- Redemptions Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Today's Redemptions</h2>
            </div>

            <?php if($today_redemptions->count()): ?>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Points Used</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Value</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php $__currentLoopData = $today_redemptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $redemption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo e($redemption->created_at->format('Y-m-d H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm">
                                            <p class="font-medium text-gray-900"><?php echo e($redemption->user->name ?? 'Unknown'); ?>

                                            </p>
                                            <p class="text-gray-600"><?php echo e($redemption->user->email ?? 'N/A'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded-lg <?php echo e($redemption->redemption_type === 'discount' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $redemption->redemption_type))); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo e($redemption->points_used); ?> pts
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php if($redemption->discount_amount): ?>
                                            <?php echo e(currency($redemption->discount_amount)); ?>

                                        <?php elseif($redemption->discount_percent): ?>
                                            <?php echo e($redemption->discount_percent); ?>%
                                        <?php elseif($redemption->premium_days): ?>
                                            <?php echo e($redemption->premium_days); ?> days
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="inline-block px-2 py-1 text-xs font-semibold rounded-lg <?php echo e($redemption->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                            <?php echo e(ucfirst($redemption->status)); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($today_redemptions->links()); ?>

                </div>
            <?php else: ?>
                <div class="p-6 text-center text-gray-500">
                    <p>No redemptions found for the selected period.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Safety Tips -->
        <div class="mt-8 bg-yellow-50 rounded-lg p-6 border border-yellow-200">
            <h3 class="font-semibold text-yellow-900 mb-3">⚠️ Monitoring Guidelines</h3>
            <ul class="text-sm text-yellow-800 space-y-1">
                <li>✓ Check daily redemption trends for unusual spikes</li>
                <li>✓ Monitor individual users who redeem frequently</li>
                <li>✓ Review redemption patterns to prevent abuse</li>
                <li>✓ Adjust limits if excessive redemptions occur</li>
                <li>✓ Use daily limits to cap potential losses</li>
            </ul>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/admin/points-pricing/monitoring.blade.php ENDPATH**/ ?>