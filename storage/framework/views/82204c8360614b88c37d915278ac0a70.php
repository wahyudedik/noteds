<?php $__env->startSection('title', __('messages.create_workspace')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="<?php echo e(route('workspaces.index')); ?>" class="text-gray-500 hover:text-gray-700 inline-flex items-center mb-4">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('messages.back_to_workspaces')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.create_new_workspace')); ?></h1>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <form action="<?php echo e(route('workspaces.store')); ?>" method="POST" class="p-6">
                <?php echo csrf_field(); ?>

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.workspace_name')); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required
                            :placeholder="__('messages.workspace_name_placeholder')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                        <?php $__errorArgs = ['name'];
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

                    <!-- Type -->
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.workspace_type')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                            <option value=""><?php echo e(__('messages.select_type')); ?></option>
                            <option value="personal" <?php echo e(old('type') === 'personal' ? 'selected' : ''); ?>><?php echo e(__('messages.personal')); ?></option>
                            <option value="team" <?php echo e(old('type') === 'team' ? 'selected' : ''); ?>><?php echo e(__('messages.team')); ?></option>
                            <option value="organization" <?php echo e(old('type') === 'organization' ? 'selected' : ''); ?>><?php echo e(__('messages.organization')); ?></option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">
                            <strong><?php echo e(__('messages.personal')); ?>:</strong> <?php echo e(__('messages.personal_for_own_use')); ?><br>
                            <strong><?php echo e(__('messages.team')); ?>:</strong> <?php echo e(__('messages.team_for_small_teams')); ?><br>
                            <strong><?php echo e(__('messages.organization')); ?>:</strong> <?php echo e(__('messages.organization_for_larger')); ?>

                        </p>
                        <?php $__errorArgs = ['type'];
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

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.description')); ?> (<?php echo e(__('messages.optional')); ?>)
                        </label>
                        <textarea name="description" id="description" rows="3"
                            :placeholder="__('messages.describe_workspace_purpose')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500"><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
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

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                        <a href="<?php echo e(route('workspaces.index')); ?>" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                            <?php echo e(__('messages.cancel')); ?>

                        </a>
                        <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors">
                            <?php echo e(__('messages.create_workspace_button')); ?>

                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\workspaces\create.blade.php ENDPATH**/ ?>