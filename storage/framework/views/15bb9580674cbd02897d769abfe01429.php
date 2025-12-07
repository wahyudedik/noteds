<?php $__env->startSection('title', __('Categories')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Categories')); ?></h1>
            <p class="mt-2 text-sm text-gray-600"><?php echo e(__('Browse notes by category')); ?></p>
        </div>

        <!-- Categories Grid -->
        <?php if($categories->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('categories.show', $category)); ?>"
                        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow p-6 block">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                            <?php echo e($category->name); ?>

                        </h3>
                        <?php if($category->description): ?>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                <?php echo e(Str::limit($category->description, 100)); ?>

                            </p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <?php echo e($category->notes->count()); ?> <?php echo e(__('notes')); ?>

                            </span>
                            <?php if($category->children->count() > 0): ?>
                                <span class="text-xs text-blue-600">
                                    <?php echo e($category->children->count()); ?> <?php echo e(__('subcategories')); ?>

                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php echo e($categories->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('No categories found')); ?></h3>
                <p class="mt-2 text-sm text-gray-500"><?php echo e(__('No categories are available at the moment.')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\categories\index.blade.php ENDPATH**/ ?>