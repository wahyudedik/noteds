<?php $__env->startSection('title', 'Review Entry'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.contests.entries', $entry->contest)); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Entries
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Review Entry</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Contest</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($entry->contest->title); ?></p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">User</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($entry->user->name); ?></p>
                        <p class="text-sm text-gray-600"><?php echo e($entry->user->email); ?></p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Note</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($entry->note->title); ?></p>
                        <a href="<?php echo e(route('notes.show', $entry->note)); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                            View Note →
                        </a>
                    </div>

                    <?php if($entry->submission_notes): ?>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Submission Notes</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-line"><?php echo e($entry->submission_notes); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Status</h3>
                        <span class="px-3 py-1 rounded-full text-sm font-medium 
                            <?php if($entry->status === 'approved'): ?> bg-green-100 text-green-800
                            <?php elseif($entry->status === 'rejected'): ?> bg-red-100 text-red-800
                            <?php else: ?> bg-yellow-100 text-yellow-800
                            <?php endif; ?>">
                            <?php echo e(ucfirst($entry->status)); ?>

                        </span>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Votes</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($entry->vote_count); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if($entry->status === 'pending'): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Review Action</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Approve Form -->
                        <div class="border border-green-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-green-900 mb-4">Approve Entry</h3>
                            <form action="<?php echo e(route('admin.contests.entries.approve', $entry)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <button type="submit" 
                                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                                    Approve Entry
                                </button>
                            </form>
                        </div>

                        <!-- Reject Form -->
                        <div class="border border-red-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-red-900 mb-4">Reject Entry</h3>
                            <form action="<?php echo e(route('admin.contests.entries.reject', $entry)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-4">
                                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                                        Rejection Reason <span class="text-red-600">*</span>
                                    </label>
                                    <textarea name="reason" id="reason" rows="3" required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                                </div>
                                <button type="submit" 
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md">
                                    Reject Entry
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\contests\entry-show.blade.php ENDPATH**/ ?>