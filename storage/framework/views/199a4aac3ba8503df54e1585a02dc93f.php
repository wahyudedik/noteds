<?php $__env->startSection('title', __('Request Refund')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Request Refund')); ?></h1>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo e(__('You can request a refund within 7 days of purchase.')); ?>

            </p>
        </div>

        <!-- Transaction Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('Transaction Details')); ?></h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600"><?php echo e(__('Note')); ?>:</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo e($transaction->note->title); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600"><?php echo e(__('Amount')); ?>:</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo e(currency($transaction->amount)); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-600"><?php echo e(__('Purchase Date')); ?>:</span>
                    <span class="text-sm font-medium text-gray-900"><?php echo e($transaction->created_at->format('M d, Y H:i')); ?></span>
                </div>
            </div>
        </div>

        <!-- Refund Form -->
        <form action="<?php echo e(route('refunds.store', $transaction)); ?>" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <?php echo csrf_field(); ?>

            <!-- Reason -->
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo e(__('Refund Reason')); ?> <span class="text-red-500">*</span>
                </label>
                <select name="reason" id="reason" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <option value=""><?php echo e(__('Select a reason')); ?></option>
                    <option value="not_as_described" <?php echo e(old('reason') === 'not_as_described' ? 'selected' : ''); ?>>
                        <?php echo e(__('Not as described')); ?>

                    </option>
                    <option value="duplicate_purchase" <?php echo e(old('reason') === 'duplicate_purchase' ? 'selected' : ''); ?>>
                        <?php echo e(__('Duplicate purchase')); ?>

                    </option>
                    <option value="technical_issue" <?php echo e(old('reason') === 'technical_issue' ? 'selected' : ''); ?>>
                        <?php echo e(__('Technical issue')); ?>

                    </option>
                    <option value="changed_mind" <?php echo e(old('reason') === 'changed_mind' ? 'selected' : ''); ?>>
                        <?php echo e(__('Changed my mind')); ?>

                    </option>
                    <option value="other" <?php echo e(old('reason') === 'other' ? 'selected' : ''); ?>>
                        <?php echo e(__('Other')); ?>

                    </option>
                </select>
                <?php $__errorArgs = ['reason'];
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

            <!-- Reason Description -->
            <div class="mb-6">
                <label for="reason_description" class="block text-sm font-medium text-gray-700 mb-2">
                    <?php echo e(__('Please provide more details')); ?> <span class="text-red-500">*</span>
                </label>
                <textarea name="reason_description" id="reason_description" rows="5" required
                    placeholder="<?php echo e(__('Please explain why you are requesting a refund...')); ?>"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['reason_description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('reason_description')); ?></textarea>
                <p class="mt-1 text-xs text-gray-500">
                    <?php echo e(__('Minimum 20 characters. Please provide as much detail as possible.')); ?>

                </p>
                <?php $__errorArgs = ['reason_description'];
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
                <a href="<?php echo e(route('wallet.index')); ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <?php echo e(__('Cancel')); ?>

                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    <?php echo e(__('Submit Refund Request')); ?>

                </button>
            </div>
        </form>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-blue-800">
                    <p class="font-medium"><?php echo e(__('Refund Policy')); ?></p>
                    <p class="mt-1">
                        <?php echo e(__('Refund requests are reviewed within 24-48 hours. Once approved, the amount will be credited back to your wallet.')); ?>

                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\refunds\create.blade.php ENDPATH**/ ?>