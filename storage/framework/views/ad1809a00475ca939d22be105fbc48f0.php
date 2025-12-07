<?php use Illuminate\Support\Str; ?>

<?php $__env->startSection('title', __('messages.post_moderation')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo e(route('admin.forum.moderation.index')); ?>" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to moderation list
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-2xl font-bold text-gray-900">Post Details</h2>
                        <?php if($post->is_hidden): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Hidden</span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Visible</span>
                        <?php endif; ?>
                        <?php if($post->is_pinned): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">Pinned</span>
                        <?php endif; ?>
                    </div>
                    <div class="text-sm text-gray-500">Post ID: <?php echo e($post->id); ?></div>
                    <div class="text-sm text-gray-500">Created at: <?php echo e($post->created_at->format('d M Y H:i')); ?></div>
                    <?php if($post->hidden_at): ?>
                        <div class="text-sm text-gray-500">Hidden at: <?php echo e($post->hidden_at->format('d M Y H:i')); ?></div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <?php if($post->is_hidden): ?>
                        <form method="POST" action="<?php echo e(route('admin.forum.moderation.unhide', $post)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-lg">
                                Unhide Post
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="POST" action="<?php echo e(route('admin.forum.moderation.hide', $post)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">
                                Hide Post
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('admin.forum.moderation.destroy', $post)); ?>" onsubmit="return confirm('Are you sure you want to permanently delete this post? This action cannot be undone.');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-gray-500 hover:bg-gray-600 rounded-lg">
                            Delete Post
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 p-5 border border-gray-200 rounded-lg bg-gray-50">
                <div class="flex items-center gap-3 mb-3">
                    <div class="text-sm font-semibold text-gray-900"><?php echo e($post->user->name); ?></div>
                    <div class="text-xs text-gray-500"><?php echo e('@' . $post->user->username); ?></div>
                </div>
                <div class="text-gray-800 whitespace-pre-wrap leading-relaxed"><?php echo e($post->content); ?></div>
                <?php if($post->note): ?>
                    <div class="mt-4 text-sm text-gray-600">
                        <span class="font-medium text-gray-800">Shared note:</span>
                        <a href="<?php echo e(route('marketplace.show', $post->note)); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($post->note->title); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Reports (<?php echo e($reports->total()); ?>)</h3>
                    <p class="text-sm text-gray-600">Manage individual reports and update their status.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporter</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($report->user->name); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo e('@' . $report->user->username); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm capitalize"><?php echo e($report->reason); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700 max-w-sm">
                                    <?php echo e($report->description ? Str::limit($report->description, 160) : '—'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold <?php switch($report->status):
                                        case ('pending'): ?> bg-red-100 text-red-800 <?php break; ?>
                                        <?php case ('reviewed'): ?> bg-yellow-100 text-yellow-800 <?php break; ?>
                                        <?php case ('resolved'): ?> bg-green-100 text-green-800 <?php break; ?>
                                        <?php case ('dismissed'): ?> bg-gray-100 text-gray-800 <?php break; ?>
                                        <?php default: ?> bg-blue-100 text-blue-800
                                    <?php endswitch; ?>">
                                        <?php echo e(ucfirst($report->status)); ?>

                                    </span>
                                    <?php if($report->reviewed_at): ?>
                                        <div class="text-xs text-gray-500 mt-1">
                                            Updated <?php echo e($report->reviewed_at->diffForHumans()); ?>

                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($report->created_at->format('d M Y H:i')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    <form method="POST" action="<?php echo e(route('admin.forum.moderation.report.status', $report)); ?>" class="space-y-2">
                                        <?php echo csrf_field(); ?>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="pending" <?php echo e($report->status === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                            <option value="reviewed" <?php echo e($report->status === 'reviewed' ? 'selected' : ''); ?>>Reviewed</option>
                                            <option value="resolved" <?php echo e($report->status === 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                                            <option value="dismissed" <?php echo e($report->status === 'dismissed' ? 'selected' : ''); ?>>Dismissed</option>
                                        </select>
                                        <textarea name="admin_notes" rows="2" placeholder="Add internal notes (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"><?php echo e(old('admin_notes', $report->admin_notes)); ?></textarea>
                                        <button type="submit" class="inline-flex items-center px-3 py-1.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                                            Update
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No reports submitted for this post.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($reports->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\forum\moderation\show.blade.php ENDPATH**/ ?>