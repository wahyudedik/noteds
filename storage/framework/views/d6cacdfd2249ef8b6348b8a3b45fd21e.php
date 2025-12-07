<?php $__env->startSection('title', $cmsPage->title); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo e($cmsPage->title); ?></h1>
        </div>

        <!-- Content -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="prose max-w-none text-gray-700">
                <?php echo $cmsPage->content; ?>

            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-8">
            <a href="javascript:history.back()" 
                class="inline-flex items-center text-blue-600 hover:text-blue-700 transition-colors duration-200">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('messages.back')); ?>

            </a>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\cms\show.blade.php ENDPATH**/ ?>