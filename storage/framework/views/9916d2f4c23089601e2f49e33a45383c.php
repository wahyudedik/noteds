<?php $__env->startSection('title', __('messages.my_orders') . ' — ' . __('messages.studio')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-slate-900"><?php echo e(__('messages.my_orders')); ?></h1>
            <a href="<?php echo e(route('studio.orders.create')); ?>" class="px-4 py-2 rounded-md bg-blue-600 text-white"><?php echo e(__('messages.create_order')); ?></a>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-6">
            <?php if($orders->count() === 0): ?>
                <p class="text-slate-600"><?php echo e(__('messages.no_orders')); ?></p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('studio.orders.show', $order)); ?>" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-semibold text-slate-900"><?php echo e($order->title); ?></h2>
                                    <p class="text-sm text-slate-600 mt-1 line-clamp-2"><?php echo e(Str::limit($order->description, 140)); ?></p>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs uppercase text-slate-500"><?php echo e(__('messages.order_status')); ?></div>
                                    <div class="font-semibold"><?php echo e(ucfirst(str_replace('_',' ', $order->status))); ?></div>
                                    <?php if($order->budget > 0): ?>
                                        <div class="text-xs text-slate-500 mt-1"><?php echo e(__('messages.order_budget')); ?>: <?php echo e(currency($order->budget)); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-6">
                    <?php echo e($orders->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\studio\orders\index.blade.php ENDPATH**/ ?>