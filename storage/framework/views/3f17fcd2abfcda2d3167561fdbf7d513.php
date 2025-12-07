

<?php $__env->startSection('title', 'Edit Pricing Configuration'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('admin.points-pricing.index')); ?>" class="text-blue-600 hover:text-blue-900">&larr; Back to
                Pricing</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Edit Pricing Configuration</h1>
        </div>

        <?php if($errors->any()): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="font-semibold text-red-900 mb-2">Please fix the following errors:</h3>
                <ul class="text-sm text-red-800">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li>• <?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="max-w-2xl mx-auto">
            <form action="<?php echo e(route('admin.points-pricing.update', $pointsPricingConfig)); ?>" method="POST"
                class="bg-white rounded-lg shadow p-8">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>

                <!-- Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                    <input type="text" name="name" id="name"
                        value="<?php echo e(old('name', $pointsPricingConfig->name)); ?>"
                        placeholder="e.g., 10% Discount, Premium Feature" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Redemption Type *</label>
                    <select name="type" id="type" required onchange="updateTypeFields()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Type</option>
                        <option value="discount"
                            <?php echo e(old('type', $pointsPricingConfig->type) === 'discount' ? 'selected' : ''); ?>>Discount (on
                            purchases)</option>
                        <option value="premium_feature"
                            <?php echo e(old('type', $pointsPricingConfig->type) === 'premium_feature' ? 'selected' : ''); ?>>Premium
                            Feature (days)</option>
                    </select>
                    <?php $__errorArgs = ['type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Points Required -->
                <div class="mb-6">
                    <label for="points_required" class="block text-sm font-medium text-gray-700 mb-2">Points Required
                        *</label>
                    <input type="number" name="points_required" id="points_required"
                        value="<?php echo e(old('points_required', $pointsPricingConfig->points_required)); ?>" min="1" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <?php $__errorArgs = ['points_required'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Discount Amount (for discount type) -->
                <div class="mb-6" id="discount_amount_field"
                    style="display: <?php echo e(old('type', $pointsPricingConfig->type) === 'discount' ? 'block' : 'none'); ?>;">
                    <label for="discount_amount" class="block text-sm font-medium text-gray-700 mb-2">Discount Amount
                        (Rupiah)</label>
                    <input type="number" name="discount_amount" id="discount_amount"
                        value="<?php echo e(old('discount_amount', $pointsPricingConfig->discount_amount)); ?>" step="0.01"
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 50000">
                    <p class="text-gray-600 text-sm mt-1">Leave empty if using percentage discount instead</p>
                    <?php $__errorArgs = ['discount_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Discount Percent (for discount type) -->
                <div class="mb-6" id="discount_percent_field"
                    style="display: <?php echo e(old('type', $pointsPricingConfig->type) === 'discount' ? 'block' : 'none'); ?>;">
                    <label for="discount_percent" class="block text-sm font-medium text-gray-700 mb-2">Discount Percent
                        (%)</label>
                    <input type="number" name="discount_percent" id="discount_percent"
                        value="<?php echo e(old('discount_percent', $pointsPricingConfig->discount_percent)); ?>" min="0"
                        max="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 10">
                    <p class="text-gray-600 text-sm mt-1">Leave empty if using fixed discount amount instead</p>
                    <?php $__errorArgs = ['discount_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Premium Days (for premium_feature type) -->
                <div class="mb-6" id="premium_days_field"
                    style="display: <?php echo e(old('type', $pointsPricingConfig->type) === 'premium_feature' ? 'block' : 'none'); ?>;">
                    <label for="premium_days" class="block text-sm font-medium text-gray-700 mb-2">Premium Days *</label>
                    <input type="number" name="premium_days" id="premium_days"
                        value="<?php echo e(old('premium_days', $pointsPricingConfig->premium_days)); ?>" min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., 30">
                    <?php $__errorArgs = ['premium_days'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Describe this offer..."><?php echo e(old('description', $pointsPricingConfig->description)); ?></textarea>
                    <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Limits Section -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-4">⚠️ Safety Limits (Recommended)</h3>

                    <div class="mb-4">
                        <label for="daily_limit" class="block text-sm font-medium text-gray-700 mb-2">Daily Redemption Limit
                            (across all users)</label>
                        <input type="number" name="daily_limit" id="daily_limit"
                            value="<?php echo e(old('daily_limit', $pointsPricingConfig->daily_limit)); ?>" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., 50">
                        <p class="text-gray-600 text-sm mt-1">Maximum times this offer can be redeemed in a single day.
                            Leave empty for unlimited.</p>
                        <?php $__errorArgs = ['daily_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div class="mb-4">
                        <label for="user_limit" class="block text-sm font-medium text-gray-700 mb-2">Per-User Redemption
                            Limit</label>
                        <input type="number" name="user_limit" id="user_limit"
                            value="<?php echo e(old('user_limit', $pointsPricingConfig->user_limit)); ?>" min="1"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="e.g., 3">
                        <p class="text-gray-600 text-sm mt-1">Maximum times a single user can redeem this offer. Leave
                            empty for unlimited.</p>
                        <?php $__errorArgs = ['user_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>

                <!-- Expiration -->
                <div class="mb-6">
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                    <input type="datetime-local" name="expires_at" id="expires_at"
                        value="<?php echo e(old('expires_at', $pointsPricingConfig->expires_at ? $pointsPricingConfig->expires_at->format('Y-m-d\TH:i') : '')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-gray-600 text-sm mt-1">Leave empty for no expiration date.</p>
                    <?php $__errorArgs = ['expires_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="text-red-600 text-sm mt-1"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Active Status -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1"
                            <?php echo e(old('is_active', $pointsPricingConfig->is_active) ? 'checked' : ''); ?>

                            class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <span class="ml-2 text-gray-700 font-medium">Active</span>
                    </label>
                    <p class="text-gray-600 text-sm mt-1">Inactive offers won't be shown to users.</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Update Configuration
                    </button>
                    <a href="<?php echo e(route('admin.points-pricing.index')); ?>"
                        class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function updateTypeFields() {
            const type = document.getElementById('type').value;
            const discountAmountField = document.getElementById('discount_amount_field');
            const discountPercentField = document.getElementById('discount_percent_field');
            const premiumDaysField = document.getElementById('premium_days_field');

            if (type === 'discount') {
                discountAmountField.style.display = 'block';
                discountPercentField.style.display = 'block';
                premiumDaysField.style.display = 'none';
            } else if (type === 'premium_feature') {
                discountAmountField.style.display = 'none';
                discountPercentField.style.display = 'none';
                premiumDaysField.style.display = 'block';
            } else {
                discountAmountField.style.display = 'none';
                discountPercentField.style.display = 'none';
                premiumDaysField.style.display = 'none';
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/admin/points-pricing/edit.blade.php ENDPATH**/ ?>