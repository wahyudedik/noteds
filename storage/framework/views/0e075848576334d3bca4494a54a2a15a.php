<?php $__env->startSection('title', $bundle->title); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('bundles.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Bundles')); ?>

            </a>
        </div>

        <!-- Bundle Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo e($bundle->title); ?></h1>
            
            <?php if($bundle->description): ?>
                <p class="text-gray-700 mb-6"><?php echo e($bundle->description); ?></p>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <div class="text-sm text-gray-600 mb-1"><?php echo e(__('Created by')); ?></div>
                    <div class="flex items-center">
                        <a href="<?php echo e(route('public.profile.show', $bundle->user->username)); ?>"
                            class="font-medium text-gray-900 hover:text-blue-600">
                            <?php echo e($bundle->user->name); ?>

                        </a>
                    </div>
                </div>
                <div class="text-right">
                    <?php if($bundle->discount_percentage > 0): ?>
                        <div class="text-sm text-gray-500 line-through mb-1">
                            <?php echo e(currency($bundle->total_original_price)); ?>

                        </div>
                        <div class="text-xs text-red-600 font-medium mb-1">
                            <?php echo e(number_format($bundle->discount_percentage, 0)); ?>% <?php echo e(__('off')); ?>

                        </div>
                    <?php endif; ?>
                    <div class="text-3xl font-bold text-green-600">
                        <?php echo e(currency($bundle->price)); ?>

                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        <?php echo e(__('for')); ?> <?php echo e($bundle->items->count()); ?> <?php echo e(__('notes')); ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- Notes in Bundle -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
                <?php echo e(__('Notes in this Bundle')); ?> (<?php echo e($bundle->items->count()); ?>)
            </h2>
            <div class="space-y-4">
                <?php $__currentLoopData = $bundle->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-start justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex-1">
                            <a href="<?php echo e(route('marketplace.show', $item->note)); ?>"
                                class="text-lg font-medium text-gray-900 hover:text-blue-600">
                                <?php echo e($item->note->title); ?>

                            </a>
                            <?php if($item->note->summary): ?>
                                <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                    <?php echo e(Str::limit(strip_tags($item->note->summary), 150)); ?>

                                </p>
                            <?php endif; ?>
                            <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                <span><?php echo e(__('Price')); ?>: <?php echo e(currency($item->note->price)); ?></span>
                                <span><?php echo e(__('By')); ?>: <?php echo e($item->note->user->name); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo e(route('marketplace.show', $item->note)); ?>"
                            class="ml-4 text-blue-600 hover:text-blue-800 text-sm font-medium">
                            <?php echo e(__('View')); ?>

                        </a>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Purchase Button -->
        <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->id() !== $bundle->user_id): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <form action="<?php echo e(route('bundles.purchase', $bundle)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <div class="text-sm text-gray-600"><?php echo e(__('Total Price')); ?></div>
                                <div class="text-2xl font-bold text-gray-900"><?php echo e(currency($bundle->price)); ?></div>
                            </div>
                            <button type="submit"
                                class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                                <?php echo e(__('Purchase Bundle')); ?>

                            </button>
                        </div>
                        <p class="text-xs text-gray-500">
                            <?php echo e(__('All notes will be added to your library after purchase.')); ?>

                        </p>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-sm text-blue-800 mb-4"><?php echo e(__('Please log in to purchase this bundle.')); ?></p>
                <a href="<?php echo e(route('login')); ?>"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <?php echo e(__('Log In')); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\bundles\show.blade.php ENDPATH**/ ?>