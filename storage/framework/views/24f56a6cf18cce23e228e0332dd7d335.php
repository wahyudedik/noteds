<?php use Illuminate\Support\Str; ?>

<?php $__env->startSection('title', __('messages.forum_moderation')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Forum Moderation</h1>
                <p class="mt-1 text-sm text-gray-600">Review reports, hide posts, and keep the community safe.</p>
            </div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to Admin Dashboard
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <form method="GET" action="<?php echo e(route('admin.forum.moderation.index')); ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" value="<?php echo e($search); ?>" placeholder="Search content or user..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All</option>
                        <option value="visible" <?php echo e($status === 'visible' ? 'selected' : ''); ?>>Visible</option>
                        <option value="hidden" <?php echo e($status === 'hidden' ? 'selected' : ''); ?>>Hidden</option>
                    </select>
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
                    <?php if($search || $status || $reportStatus): ?>
                        <a href="<?php echo e(route('admin.forum.moderation.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg">
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Post</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reports</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibility</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900 max-w-md">
                                    <p class="font-medium text-gray-900"><?php echo e(Str::limit($post->content, 120)); ?></p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($post->parent_id ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'); ?>">
                                            <?php echo e($post->parent_id ? 'Reply' : 'Top Level'); ?>

                                        </span>
                                        <span class="text-xs text-gray-500">ID: <?php echo e($post->id); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($post->user->name); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e('@' . $post->user->username); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($post->pending_reports_count > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                                            <?php echo e($post->pending_reports_count); ?> pending
                                        </span>
                                        <span class="text-xs text-gray-500">Total: <?php echo e($post->reports_count); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($post->is_hidden): ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Hidden</span>
                                        <?php if($post->hidden_at): ?>
                                            <div class="text-xs text-gray-500 mt-1">since <?php echo e(optional($post->hidden_at)->format('d M Y H:i')); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Visible</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($post->created_at->format('d M Y H:i')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-y-2">
                                    <a href="<?php echo e(route('admin.forum.moderation.show', $post)); ?>" class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800">
                                        View
                                    </a>
                                    <div class="flex justify-end gap-2">
                                        <?php if($post->is_hidden): ?>
                                            <form method="POST" action="<?php echo e(route('admin.forum.moderation.unhide', $post)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-md">
                                                    Unhide
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?php echo e(route('admin.forum.moderation.hide', $post)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md">
                                                    Hide
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('admin.forum.moderation.destroy', $post)); ?>" onsubmit="return confirm('Are you sure you want to permanently delete this post? This action cannot be undone.');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm text-white bg-gray-500 hover:bg-gray-600 rounded-md">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No posts found for the selected filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($posts->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\forum\moderation\index.blade.php ENDPATH**/ ?>