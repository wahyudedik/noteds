<?php $__env->startSection('title', __('messages.create_commission_tier')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.commission-tiers.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← <?php echo e(__('messages.back_to_commission_tiers')); ?>

            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.create_commission_tier')); ?></h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('admin.commission-tiers.store')); ?>" method="POST">
                    <?php echo $__env->make('admin.commission-tiers._form', ['submitLabel' => __('messages.save_tier')], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\commission-tiers\create.blade.php ENDPATH**/ ?>