<?php $__env->startSection('title', __('messages.edit_support_ticket')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="<?php echo e(route('support-tickets.show', $supportTicket)); ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.edit_support_ticket')); ?></h1>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <form action="<?php echo e(route('support-tickets.update', $supportTicket)); ?>" method="POST" class="p-6">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.title')); ?> <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="<?php echo e(old('title', $supportTicket->title)); ?>" required
                        :placeholder="__('messages.brief_description_issue')"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    <?php $__errorArgs = ['title'];
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

                <!-- Priority -->
                <div class="mb-6">
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.priority')); ?> <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 <?php echo e(old('priority', $supportTicket->priority) === 'low' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="priority" value="low" <?php echo e(old('priority', $supportTicket->priority) === 'low' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.low')); ?></span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 <?php echo e(old('priority', $supportTicket->priority) === 'medium' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="priority" value="medium" <?php echo e(old('priority', $supportTicket->priority) === 'medium' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.medium')); ?></span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 <?php echo e(old('priority', $supportTicket->priority) === 'high' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="priority" value="high" <?php echo e(old('priority', $supportTicket->priority) === 'high' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.high')); ?></span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border-2 p-3 <?php echo e(old('priority', $supportTicket->priority) === 'urgent' ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-300'); ?>">
                            <input type="radio" name="priority" value="urgent" <?php echo e(old('priority', $supportTicket->priority) === 'urgent' ? 'checked' : ''); ?> required class="sr-only">
                            <div class="text-center flex-1">
                                <span class="block text-sm font-medium text-gray-900"><?php echo e(__('messages.urgent')); ?></span>
                            </div>
                        </label>
                    </div>
                    <?php $__errorArgs = ['priority'];
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

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.description')); ?> <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="8" required
                        :placeholder="__('messages.provide_detailed_information')"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200"><?php echo e(old('description', $supportTicket->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
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

                <!-- Links -->
                <div class="mb-6">
                    <label for="links" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.related_links_optional')); ?>

                    </label>
                    <input type="text" name="links" id="links" value="<?php echo e(old('links', is_array($supportTicket->links) ? implode(', ', $supportTicket->links) : '')); ?>"
                        :placeholder="__('messages.paste_urls_separated')"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    <?php $__errorArgs = ['links'];
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
                    <a href="<?php echo e(route('support-tickets.show', $supportTicket)); ?>" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <?php echo e(__('messages.cancel')); ?>

                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        <?php echo e(__('messages.update_ticket')); ?>

                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\support-tickets\edit.blade.php ENDPATH**/ ?>