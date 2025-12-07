<?php $__env->startSection('title', __('messages.admin_create_subscription')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.create_subscription')); ?></h1>
            </div>
            <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.manually_create_subscription')); ?></p>
        </div>

        <?php if(session('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <form action="<?php echo e(route('admin.subscriptions.store')); ?>" method="POST" class="p-6">
                <?php echo csrf_field(); ?>

                <!-- User Selection -->
                <div class="mb-6">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.select_user')); ?></label>
                    <select name="user_id" id="user_id" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        <option value=""><?php echo e(__('messages.choose_user')); ?></option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($user->id); ?>" <?php echo e(old('user_id') === $user->id ? 'selected' : ''); ?>>
                                <?php echo e($user->name); ?> (<?php echo e($user->email); ?>) - <?php echo e(ucfirst($user->role)); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <?php $__errorArgs = ['user_id'];
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

                <!-- Plan Selection -->
                <div class="mb-6">
                    <label for="plan" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.plan_type')); ?></label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none <?php echo e(old('plan') === 'basic' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="plan" value="basic" <?php echo e(old('plan') === 'basic' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.basic')); ?></span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500">10 <?php echo e(__('messages.notes_total')); ?></span>
                                </div>
                            </div>
                            <svg class="<?php echo e(old('plan') === 'basic' ? 'text-blue-600' : 'invisible'); ?> h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-4 focus:outline-none <?php echo e(old('plan') === 'premium' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="plan" value="premium" <?php echo e(old('plan') === 'premium' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="flex flex-1">
                                <div class="flex flex-col">
                                    <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.premium')); ?></span>
                                    <span class="mt-1 flex items-center text-sm text-gray-500"><?php echo e(__('messages.unlimited_notes')); ?></span>
                                </div>
                            </div>
                            <svg class="<?php echo e(old('plan') === 'premium' ? 'text-blue-600' : 'invisible'); ?> h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>
                    <?php $__errorArgs = ['plan'];
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

                <!-- Expiration Date -->
                <div class="mb-6">
                    <label for="expired_at" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.expiration_date')); ?></label>
                    <input type="date" name="expired_at" id="expired_at" value="<?php echo e(old('expired_at')); ?>" required
                        min="<?php echo e(date('Y-m-d')); ?>"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                    <?php $__errorArgs = ['expired_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    <p class="mt-1 text-xs text-gray-500"><?php echo e(__('messages.select_when_expires')); ?></p>
                </div>

                <!-- Admin Notes -->
                <div class="mb-6">
                    <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.admin_notes_optional')); ?></label>
                    <textarea name="admin_notes" id="admin_notes" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200"
                        :placeholder="__('messages.add_notes_context')"><?php echo e(old('admin_notes')); ?></textarea>
                    <?php $__errorArgs = ['admin_notes'];
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

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3">
                    <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <?php echo e(__('messages.cancel')); ?>

                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <?php echo e(__('messages.create_subscription')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\subscriptions\create.blade.php ENDPATH**/ ?>