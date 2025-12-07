<?php $__env->startSection('title', __('messages.create_service_order')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6"><?php echo e(__('messages.create_service_order')); ?></h1>
            <form action="<?php echo e(route('studio.orders.store')); ?>" method="POST" class="space-y-6">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.order_title')); ?></label>
                    <input type="text" name="title" value="<?php echo e(old('title')); ?>" required class="w-full rounded-lg border-gray-300">
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.order_description')); ?></label>
                    <textarea name="description" rows="6" required class="w-full rounded-lg border-gray-300" placeholder="<?php echo e(__('messages.order_description_placeholder')); ?>"><?php echo e(old('description')); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.order_budget')); ?> (<?php echo e(__('messages.optional')); ?>)</label>
                    <input type="number" name="budget" value="<?php echo e(old('budget', 0)); ?>" step="0.01" min="0" class="w-full rounded-lg border-gray-300">
                    <?php $__errorArgs = ['budget'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('studio.orders.index')); ?>" class="px-4 py-2 rounded-md border"><?php echo e(__('messages.cancel')); ?></a>
                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white"><?php echo e(__('messages.submit_brief')); ?></button>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\studio\orders\create.blade.php ENDPATH**/ ?>