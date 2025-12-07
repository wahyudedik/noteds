<?php $__env->startSection('title', __('Create Webhook')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('webhooks.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Webhooks')); ?>

            </a>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Create Webhook')); ?></h1>
        </div>

        <form action="<?php echo e(route('webhooks.store')); ?>" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <?php echo csrf_field(); ?>

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo e(__('Webhook Name')); ?> <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" required value="<?php echo e(old('name')); ?>"
                    placeholder="<?php echo e(__('My Webhook')); ?>"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <?php $__errorArgs = ['name'];
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

            <!-- URL -->
            <div class="mb-6">
                <label for="url" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo e(__('Webhook URL')); ?> <span class="text-red-500">*</span>
                </label>
                <input type="url" name="url" id="url" required value="<?php echo e(old('url')); ?>"
                    placeholder="https://example.com/webhook"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                <p class="mt-1 text-xs text-gray-500"><?php echo e(__('The URL where webhook events will be sent.')); ?></p>
                <?php $__errorArgs = ['url'];
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

            <!-- Event -->
            <div class="mb-6">
                <label for="event" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo e(__('Event Type')); ?> <span class="text-red-500">*</span>
                </label>
                <select name="event" id="event" required
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['event'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value=""><?php echo e(__('Select an event')); ?></option>
                    <option value="note.purchased" <?php echo e(old('event') === 'note.purchased' ? 'selected' : ''); ?>><?php echo e(__('Note Purchased')); ?></option>
                    <option value="note.created" <?php echo e(old('event') === 'note.created' ? 'selected' : ''); ?>><?php echo e(__('Note Created')); ?></option>
                    <option value="note.updated" <?php echo e(old('event') === 'note.updated' ? 'selected' : ''); ?>><?php echo e(__('Note Updated')); ?></option>
                    <option value="transaction.completed" <?php echo e(old('event') === 'transaction.completed' ? 'selected' : ''); ?>><?php echo e(__('Transaction Completed')); ?></option>
                    <option value="withdraw.approved" <?php echo e(old('event') === 'withdraw.approved' ? 'selected' : ''); ?>><?php echo e(__('Withdraw Approved')); ?></option>
                    <option value="subscription.renewed" <?php echo e(old('event') === 'subscription.renewed' ? 'selected' : ''); ?>><?php echo e(__('Subscription Renewed')); ?></option>
                </select>
                <?php $__errorArgs = ['event'];
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

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="<?php echo e(route('webhooks.index')); ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?php echo e(__('Cancel')); ?>

                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <?php echo e(__('Create Webhook')); ?>

                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\webhooks\create.blade.php ENDPATH**/ ?>