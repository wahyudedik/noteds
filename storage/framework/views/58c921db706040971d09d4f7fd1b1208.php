<?php $__env->startSection('title', __('messages.videohive') . ' — ' . __('messages.video_motion_graphics')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900"><?php echo e(__('messages.videohive')); ?></h1>
            <p class="mt-3 text-slate-600"><?php echo e(__('messages.videohive_description')); ?></p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900"><?php echo e(__('messages.use_cases')); ?></h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li><?php echo e(__('messages.videos_use_intro')); ?></li>
                <li><?php echo e(__('messages.videos_use_template')); ?></li>
                <li><?php echo e(__('messages.videos_use_motion')); ?></li>
            </ul>
            
            
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\ecosystem\videos.blade.php ENDPATH**/ ?>