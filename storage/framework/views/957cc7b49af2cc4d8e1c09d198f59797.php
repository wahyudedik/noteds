<?php $__env->startSection('title', __('messages.moderate_note')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.moderate_note')); ?></h1>
                <p class="mt-1 text-sm text-gray-600">Review reports and manage this note's visibility.</p>
            </div>
            <a href="<?php echo e(route('admin.notes.moderation.index')); ?>" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                ← Back to Note Moderation
            </a>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900"><?php echo e($note->title); ?></h2>
                        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                            <span>ID: <?php echo e($note->id); ?></span>
                            <span>Created: <?php echo e($note->created_at->format('d M Y H:i')); ?></span>
                            <span>Updated: <?php echo e($note->updated_at->format('d M Y H:i')); ?></span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                            <?php if($note->status === 'active'): ?> bg-green-100 text-green-800
                            <?php elseif($note->status === 'sold'): ?> bg-blue-100 text-blue-800
                            <?php else: ?> bg-red-100 text-red-800
                            <?php endif; ?>">
                            <?php echo e(ucfirst($note->status)); ?>

                        </span>
                        <div class="flex gap-2">
                            <?php if($note->status !== 'inactive'): ?>
                                <form method="POST" action="<?php echo e(route('admin.notes.moderation.suspend', $note)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">
                                        Set Inactive
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if($note->status !== 'active'): ?>
                                <form method="POST" action="<?php echo e(route('admin.notes.moderation.activate', $note)); ?>">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">
                                        Activate
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3 text-sm text-gray-700">
                        <div>
                            <span class="font-medium text-gray-900">Owner:</span>
                            <?php echo e($note->user->name); ?> (<?php echo e('@' . $note->user->username); ?>)
                        </div>
                        <div>
                            <span class="font-medium text-gray-900">Visibility:</span>
                            <?php echo e($note->is_public ? 'Public' : 'Private'); ?>

                        </div>
                        <div>
                            <span class="font-medium text-gray-900">Price:</span>
                            <?php echo e(currency($note->price)); ?>

                        </div>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?php echo e($note->reports()->where('status', 'pending')->count() > 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'); ?>">
                            Pending: <?php echo e($note->reports()->where('status', 'pending')->count()); ?>

                        </span>
                        <span class="text-xs text-gray-500">Total Reports: <?php echo e($note->reports()->count()); ?></span>
                    </div>
                </div>

                <div class="border rounded-lg p-4 bg-gray-50">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-2">Content Preview</h3>
                    <div class="prose max-w-none text-sm text-gray-800">
                        <?php echo \Illuminate\Support\Str::limit($note->content, 800); ?>

                        <?php if(strlen(strip_tags($note->content)) > 800): ?>
                            <p class="mt-2 text-xs text-gray-500 italic">Content truncated for preview.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Reports</h2>
                <p class="text-sm text-gray-600 mt-1">Manage user reports for this note.</p>
            </div>

            <div class="divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-6">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-gray-900">Reason:</span> <?php echo e(ucfirst($report->reason)); ?>

                                    <span class="text-gray-400">•</span>
                                    <span>Reported <?php echo e($report->created_at->diffForHumans()); ?></span>
                                </div>
                                <div class="text-sm text-gray-600 mb-2">
                                    <span class="font-semibold text-gray-900">Reported by:</span>
                                    <?php echo e($report->user->name); ?> (<?php echo e('@' . $report->user->username); ?>)
                                </div>
                                <?php if($report->description): ?>
                                    <div class="mt-2 text-sm text-gray-800">
                                        <span class="font-semibold text-gray-900 block mb-1">Description</span>
                                        <p class="bg-gray-100 border border-gray-200 rounded-md px-3 py-2">
                                            <?php echo e($report->description); ?>

                                        </p>
                                    </div>
                                <?php endif; ?>
                                <?php if($report->admin_notes): ?>
                                    <div class="mt-2 text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900 block mb-1">Admin Notes</span>
                                        <p class="bg-blue-50 border border-blue-200 rounded-md px-3 py-2">
                                            <?php echo e($report->admin_notes); ?>

                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="w-full md:w-64">
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                        <?php if($report->status === 'pending'): ?> bg-red-100 text-red-800
                                        <?php elseif($report->status === 'resolved'): ?> bg-green-100 text-green-800
                                        <?php elseif($report->status === 'dismissed'): ?> bg-gray-200 text-gray-700
                                        <?php else: ?> bg-blue-100 text-blue-800
                                        <?php endif; ?>">
                                        <?php echo e(ucfirst($report->status)); ?>

                                    </span>
                                    <?php if($report->reviewer): ?>
                                        <div class="mt-1 text-xs text-gray-500">
                                            Reviewed by <?php echo e($report->reviewer->name); ?> <?php echo e($report->reviewed_at?->format('d M Y H:i')); ?>

                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form method="POST" action="<?php echo e(route('admin.notes.moderation.report.status', $report)); ?>" class="space-y-3">
                                    <?php echo csrf_field(); ?>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Update Status</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                            <option value="pending" <?php echo e($report->status === 'pending' ? 'selected' : ''); ?>>Pending</option>
                                            <option value="reviewed" <?php echo e($report->status === 'reviewed' ? 'selected' : ''); ?>>Reviewed</option>
                                            <option value="resolved" <?php echo e($report->status === 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                                            <option value="dismissed" <?php echo e($report->status === 'dismissed' ? 'selected' : ''); ?>>Dismissed</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Admin Notes</label>
                                        <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" placeholder="Add context or follow-up details (optional)"><?php echo e(old('admin_notes', $report->admin_notes)); ?></textarea>
                                    </div>
                                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                                        Save Update
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-6 text-center text-sm text-gray-500">
                        No reports found for this note.
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($reports->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\notes\moderation\show.blade.php ENDPATH**/ ?>