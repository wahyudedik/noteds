<?php $__env->startSection('title', __('messages.admin_create_exchange_rate')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.admin_create_exchange_rate')); ?></h1>
            <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.add_new_exchange_rate')); ?></p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
            <form action="<?php echo e(route('admin.exchange-rates.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="from_currency" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.from_currency')); ?></label>
                        <select name="from_currency" id="from_currency" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('messages.select_currency')); ?></option>
                            <option value="IDR" <?php echo e(old('from_currency') === 'IDR' ? 'selected' : ''); ?>><?php echo e(__('messages.idr_indonesian_rupiah')); ?></option>
                            <option value="USD" <?php echo e(old('from_currency') === 'USD' ? 'selected' : ''); ?>><?php echo e(__('messages.usd_us_dollar')); ?></option>
                        </select>
                        <?php $__errorArgs = ['from_currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="to_currency" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.to_currency')); ?></label>
                        <select name="to_currency" id="to_currency" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('messages.select_currency')); ?></option>
                            <option value="IDR" <?php echo e(old('to_currency') === 'IDR' ? 'selected' : ''); ?>><?php echo e(__('messages.idr_indonesian_rupiah')); ?></option>
                            <option value="USD" <?php echo e(old('to_currency') === 'USD' ? 'selected' : ''); ?>><?php echo e(__('messages.usd_us_dollar')); ?></option>
                        </select>
                        <?php $__errorArgs = ['to_currency'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label for="rate" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.exchange_rate')); ?></label>
                        <input type="number" name="rate" id="rate" step="0.0001" min="0.0001" required value="<?php echo e(old('rate')); ?>" 
                            :placeholder="__('messages.rate_example')"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-2 text-xs text-gray-500"><?php echo e(__('messages.enter_rate_convert')); ?></p>
                        <?php $__errorArgs = ['rate'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700"><?php echo e(__('messages.active')); ?></span>
                        </label>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.notes_optional')); ?></label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"><?php echo e(old('notes')); ?></textarea>
                        <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('admin.exchange-rates.index')); ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <?php echo e(__('messages.cancel')); ?>

                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <?php echo e(__('messages.create_exchange_rate')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\exchange-rates\create.blade.php ENDPATH**/ ?>