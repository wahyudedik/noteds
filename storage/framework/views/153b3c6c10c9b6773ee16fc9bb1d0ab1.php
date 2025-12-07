<?php $__env->startSection('title', __('Recently Viewed Notes')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Recently Viewed Notes')); ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?php echo e(__('Notes you have recently viewed')); ?></p>
        </div>

        <!-- Notes Grid -->
        <?php if($viewedNotes->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $viewedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $viewHistory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $note = $viewHistory->note;
                    ?>
                    <?php if($note): ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <a href="<?php echo e(route('marketplace.show', $note)); ?>" class="block">
                                <div class="p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <?php echo e($note->title); ?>

                                    </h3>
                                    <?php if($note->summary): ?>
                                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                            <?php echo e(Str::limit(strip_tags($note->summary), 100)); ?>

                                        </p>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-semibold text-green-600">
                                            <?php echo e(currency($note->price)); ?>

                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <?php echo e(__('By')); ?> <?php echo e($note->user->name); ?>

                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo e(__('Viewed')); ?> <?php echo e($viewHistory->viewed_at->diffForHumans()); ?>

                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php echo e($viewedNotes->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('No viewed notes yet')); ?></h3>
                <p class="mt-2 text-sm text-gray-500"><?php echo e(__('Your recently viewed notes will appear here.')); ?></p>
                <a href="<?php echo e(route('marketplace.index')); ?>" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                    <?php echo e(__('Browse Marketplace')); ?> →
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\viewed-notes\index.blade.php ENDPATH**/ ?>