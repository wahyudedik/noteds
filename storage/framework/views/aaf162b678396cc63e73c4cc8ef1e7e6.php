<?php $__env->startSection('title', __('messages.admin_edit_faq')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.edit')); ?> <?php echo e(__('messages.question')); ?></h2>
            <a href="<?php echo e(route('admin.faqs.index')); ?>" class="text-gray-600 hover:text-gray-800">← <?php echo e(__('messages.back_to_faqs')); ?></a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="<?php echo e(route('admin.faqs.update', $faq)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Question -->
                <div class="mb-6">
                    <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.question')); ?> <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                        id="question"
                        name="question"
                        value="<?php echo e(old('question', $faq->question)); ?>"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['question'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Answer -->
                <div class="mb-6">
                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.answer')); ?> <span class="text-red-600">*</span>
                    </label>
                    <textarea 
                        id="answer"
                        name="answer"
                        rows="8"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('answer', $faq->answer)); ?></textarea>
                    <p class="mt-1 text-sm text-gray-500"><?php echo e(__('messages.use_new_lines_for_formatting')); ?></p>
                    <?php $__errorArgs = ['answer'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Order -->
                <div class="mb-6">
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.display_order')); ?>

                    </label>
                    <input type="number" 
                        id="order"
                        name="order"
                        value="<?php echo e(old('order', $faq->order)); ?>"
                        min="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500"><?php echo e(__('messages.lower_numbers_appear_first')); ?></p>
                    <?php $__errorArgs = ['order'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Is Active -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                            name="is_active"
                            value="1"
                            <?php echo e(old('is_active', $faq->is_active) ? 'checked' : ''); ?>

                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700"><?php echo e(__('messages.active_visible_public_faq')); ?></span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="<?php echo e(route('admin.faqs.index')); ?>" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        <?php echo e(__('messages.cancel')); ?>

                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors duration-200">
                        <?php echo e(__('messages.update_faq')); ?>

                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\faqs\edit.blade.php ENDPATH**/ ?>