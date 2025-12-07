<?php $__env->startSection('title', __('Note Bundles')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Note Bundles')); ?></h1>
                <p class="mt-2 text-sm text-gray-600"><?php echo e(__('Purchase multiple notes at a discounted price')); ?></p>
            </div>
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->role === 'seller' || auth()->user()->hasRole('admin')): ?>
                    <a href="<?php echo e(route('bundles.create')); ?>"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <?php echo e(__('Create Bundle')); ?>

                    </a>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Search -->
        <?php if(request()->has('search') || $bundles->count() > 0): ?>
            <form method="GET" action="<?php echo e(route('bundles.index')); ?>" class="mb-6">
                <div class="flex gap-2">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                        placeholder="<?php echo e(__('Search bundles...')); ?>"
                        class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                        <?php echo e(__('Search')); ?>

                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Bundles Grid -->
        <?php if($bundles->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $bundles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bundle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                        <a href="<?php echo e(route('bundles.show', $bundle)); ?>" class="block">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <?php echo e($bundle->title); ?>

                                </h3>
                                <?php if($bundle->description): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        <?php echo e(Str::limit($bundle->description, 100)); ?>

                                    </p>
                                <?php endif; ?>

                                <div class="flex items-center justify-between mb-4">
                                    <div class="text-sm text-gray-600">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <?php echo e($bundle->items->count()); ?> <?php echo e(__('notes')); ?>

                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-green-600">
                                            <?php echo e(currency($bundle->price)); ?>

                                        </div>
                                        <?php if($bundle->discount_percentage > 0): ?>
                                            <div class="text-xs text-gray-500 line-through">
                                                <?php echo e(currency($bundle->total_original_price)); ?>

                                            </div>
                                            <div class="text-xs text-red-600 font-medium">
                                                <?php echo e(number_format($bundle->discount_percentage, 0)); ?>% <?php echo e(__('off')); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="flex items-center text-xs text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <?php echo e($bundle->user->name); ?>

                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                <?php echo e($bundles->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('No bundles found')); ?></h3>
                <p class="mt-2 text-sm text-gray-500">
                    <?php echo e(__('No bundles are available at the moment.')); ?>

                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\bundles\index.blade.php ENDPATH**/ ?>