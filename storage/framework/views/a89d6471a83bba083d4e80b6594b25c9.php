<?php $__env->startSection('title', 'Create Badge'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.badges.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Badges
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Create Custom Badge</h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('admin.badges.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['name'];
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

                        <div>
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                Slug (auto-generated if empty)
                            </label>
                            <input type="text" name="slug" id="slug" value="<?php echo e(old('slug')); ?>"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea name="description" id="description" rows="3"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('description')); ?></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                                    Icon (emoji)
                                </label>
                                <input type="text" name="icon" id="icon" value="<?php echo e(old('icon')); ?>" placeholder="🏆"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Color <span class="text-red-600">*</span>
                                </label>
                                <select name="color" id="color" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="blue" <?php echo e(old('color') === 'blue' ? 'selected' : ''); ?>>Blue</option>
                                    <option value="green" <?php echo e(old('color') === 'green' ? 'selected' : ''); ?>>Green</option>
                                    <option value="red" <?php echo e(old('color') === 'red' ? 'selected' : ''); ?>>Red</option>
                                    <option value="yellow" <?php echo e(old('color') === 'yellow' ? 'selected' : ''); ?>>Yellow</option>
                                    <option value="purple" <?php echo e(old('color') === 'purple' ? 'selected' : ''); ?>>Purple</option>
                                    <option value="orange" <?php echo e(old('color') === 'orange' ? 'selected' : ''); ?>>Orange</option>
                                    <option value="gold" <?php echo e(old('color') === 'gold' ? 'selected' : ''); ?>>Gold</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-600">*</span>
                            </label>
                            <select name="category" id="category" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="milestone" <?php echo e(old('category') === 'milestone' ? 'selected' : ''); ?>>Milestone</option>
                                <option value="quality" <?php echo e(old('category') === 'quality' ? 'selected' : ''); ?>>Quality</option>
                                <option value="community" <?php echo e(old('category') === 'community' ? 'selected' : ''); ?>>Community</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="criteria_type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Criteria Type
                                </label>
                                <select name="criteria_type" id="criteria_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Manual (no auto-award)</option>
                                    <option value="sales_count" <?php echo e(old('criteria_type') === 'sales_count' ? 'selected' : ''); ?>>Sales Count</option>
                                    <option value="rating" <?php echo e(old('criteria_type') === 'rating' ? 'selected' : ''); ?>>Rating</option>
                                    <option value="helpful_reviews" <?php echo e(old('criteria_type') === 'helpful_reviews' ? 'selected' : ''); ?>>Helpful Reviews</option>
                                    <option value="activity" <?php echo e(old('criteria_type') === 'activity' ? 'selected' : ''); ?>>Activity</option>
                                </select>
                            </div>

                            <div>
                                <label for="criteria_value" class="block text-sm font-medium text-gray-700 mb-2">
                                    Criteria Value
                                </label>
                                <input type="number" name="criteria_value" id="criteria_value" value="<?php echo e(old('criteria_value')); ?>" min="0"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" value="<?php echo e(old('sort_order', 0)); ?>"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="is_custom" value="1" checked
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Custom Badge</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="<?php echo e(route('admin.badges.index')); ?>" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Create Badge
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\badges\create.blade.php ENDPATH**/ ?>