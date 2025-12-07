<?php $__env->startSection('title', __('messages.account_moderation')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.account_moderation')); ?></h1>
                <p class="mt-1 text-sm text-gray-600">Monitor reports and keep user accounts compliant.</p>
            </div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to Admin Dashboard
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <form method="GET" action="<?php echo e(route('admin.accounts.moderation.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search name, username, or email..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Report Status</label>
                    <select name="report_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="pending" <?php echo e($reportStatus === 'pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="reviewed" <?php echo e($reportStatus === 'reviewed' ? 'selected' : ''); ?>>Reviewed</option>
                        <option value="resolved" <?php echo e($reportStatus === 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                        <option value="dismissed" <?php echo e($reportStatus === 'dismissed' ? 'selected' : ''); ?>>Dismissed</option>
                        <option value="unreported" <?php echo e($reportStatus === 'unreported' ? 'selected' : ''); ?>>No Reports</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm">
                        Filter
                    </button>
                    <?php if($search || $reportStatus): ?>
                        <a href="<?php echo e(route('admin.accounts.moderation.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">
                            Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moderatedUser): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($moderatedUser->name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e('@' . $moderatedUser->username); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($moderatedUser->email); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <?php echo e(ucfirst($moderatedUser->role)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($moderatedUser->pending_reports_count > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($moderatedUser->pending_reports_count); ?> pending
                                        </span>
                                        <span class="text-xs text-gray-500">Total: <?php echo e($moderatedUser->account_reports_count); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($moderatedUser->created_at->format('d M Y')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo e(route('admin.accounts.moderation.show', $moderatedUser)); ?>" class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800">
                                        View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No user accounts found for the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-top border-gray-200">
                <?php echo e($users->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\accounts\moderation\index.blade.php ENDPATH**/ ?>