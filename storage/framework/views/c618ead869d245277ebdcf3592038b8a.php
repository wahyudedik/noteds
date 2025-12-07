<?php $__env->startSection('title', 'Contests'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Contests</h1>
            <p class="mt-2 text-base text-gray-600">Participate in monthly challenges and themed contests to win prizes!</p>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php $__empty_1 = true; $__currentLoopData = $contests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <?php if($contest->banner_image): ?>
                        <img src="<?php echo e(Storage::url($contest->banner_image)); ?>" alt="<?php echo e($contest->title); ?>" class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                            <span class="text-6xl">🏆</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($contest->title); ?></h3>
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                <?php if($contest->status === 'open'): ?> bg-green-100 text-green-800
                                <?php elseif($contest->status === 'voting'): ?> bg-blue-100 text-blue-800
                                <?php elseif($contest->status === 'closed'): ?> bg-gray-100 text-gray-800
                                <?php else: ?> bg-yellow-100 text-yellow-800
                                <?php endif; ?>">
                                <?php echo e(ucfirst($contest->status)); ?>

                            </span>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-2"><?php echo e($contest->description); ?></p>

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div>
                                <?php if($contest->type === 'monthly'): ?>
                                    <span class="inline-flex items-center">
                                        📅 Monthly Challenge
                                    </span>
                                <?php elseif($contest->type === 'themed'): ?>
                                    <span class="inline-flex items-center">
                                        🎨 <?php echo e($contest->theme); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center">
                                        🏆 Custom Contest
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php echo e($contest->entries()->count()); ?> entries
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="<?php echo e(route('contests.show', $contest)); ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View Details →
                            </a>
                            <?php if($contest->status === 'open' && auth()->check()): ?>
                                <a href="<?php echo e(route('contests.submit', $contest)); ?>" 
                                   class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md transition-colors">
                                    Submit Entry
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No contests available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-6">
            <?php echo e($contests->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\contests\index.blade.php ENDPATH**/ ?>