<?php $__env->startSection('title', __('messages.codecanyon') . ' — ' . __('messages.plugins_code_scripts')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900"><?php echo e(__('messages.codecanyon')); ?></h1>
            <p class="mt-3 text-slate-600"><?php echo e(__('messages.codecanyon_description')); ?></p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900"><?php echo e(__('messages.use_cases')); ?></h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li><?php echo e(__('messages.code_use_accelerate')); ?></li>
                <li><?php echo e(__('messages.code_use_plugins')); ?></li>
                <li><?php echo e(__('messages.code_use_components')); ?></li>
            </ul>
            <div class="mt-6">
                
                
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\ecosystem\code.blade.php ENDPATH**/ ?>