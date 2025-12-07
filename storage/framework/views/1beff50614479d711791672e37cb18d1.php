<?php $__env->startSection('title', __('messages.studio_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900"><?php echo e(__('messages.studio')); ?></h1>
            <p class="mt-3 text-slate-600"><?php echo e(__('messages.studio_description')); ?></p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900"><?php echo e(__('messages.example_services')); ?></h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li><?php echo e(__('messages.service_logo_design')); ?></li>
                <li><?php echo e(__('messages.service_video_editing')); ?></li>
                <li><?php echo e(__('messages.service_web_development')); ?></li>
                <li><?php echo e(__('messages.service_voice_over')); ?></li>
            </ul>
            <div class="mt-6">
                <a href="<?php echo e(route('studio.orders.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md"><?php echo e(__('messages.create_order')); ?></a>
                <a href="<?php echo e(route('studio.orders.index')); ?>" class="inline-flex items-center px-4 py-2 ml-2 border rounded-md"><?php echo e(__('messages.my_orders')); ?></a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\studio\index.blade.php ENDPATH**/ ?>