<?php $__env->startSection('title', $category->name); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('categories.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Categories')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e($category->name); ?></h1>
            <?php if($category->description): ?>
                <p class="mt-2 text-sm text-gray-600"><?php echo e($category->description); ?></p>
            <?php endif; ?>
        </div>

        <!-- Subcategories -->
        <?php if($category->children->count() > 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Subcategories')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $category->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subcategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('categories.show', $subcategory)); ?>"
                            class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                            <h3 class="font-medium text-gray-900"><?php echo e($subcategory->name); ?></h3>
                            <p class="text-xs text-gray-500 mt-1"><?php echo e($subcategory->notes->count()); ?> <?php echo e(__('notes')); ?></p>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notes in Category -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                <?php echo e(__('Notes')); ?> (<?php echo e($notes->total()); ?>)
            </h2>
            <?php if($notes->count() > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="border border-gray-200 rounded-lg hover:shadow-md transition-shadow overflow-hidden">
                            <a href="<?php echo e(route('marketplace.show', $note)); ?>" class="block">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                        <?php echo e($note->title); ?>

                                    </h3>
                                    <?php if($note->summary): ?>
                                        <p class="text-sm text-gray-600 mb-3 line-clamp-3">
                                            <?php echo e(Str::limit(strip_tags($note->summary), 100)); ?>

                                        </p>
                                    <?php endif; ?>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-semibold text-green-600">
                                            <?php echo e(currency($note->price)); ?>

                                        </span>
                                        <span class="text-xs text-gray-500">
                                            <?php echo e(__('By')); ?> <?php echo e($note->user->name); ?>

                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-6">
                    <?php echo e($notes->links()); ?>

                </div>
            <?php else: ?>
                <p class="text-center text-gray-500 py-8"><?php echo e(__('No notes in this category yet.')); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\categories\show.blade.php ENDPATH**/ ?>