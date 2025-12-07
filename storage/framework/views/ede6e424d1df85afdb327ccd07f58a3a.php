<?php $__env->startSection('title', 'Edit Certification'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.certifications.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Certifications
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Edit Certification</h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('admin.certifications.update', $certification)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PATCH'); ?>

                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Name <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="<?php echo e(old('name', $certification->name)); ?>" required
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
                                Slug
                            </label>
                            <input type="text" name="slug" id="slug" value="<?php echo e(old('slug', $certification->slug)); ?>"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description <span class="text-red-600">*</span>
                            </label>
                            <textarea name="description" id="description" rows="4" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description', $certification->description)); ?></textarea>
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

                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-600">*</span>
                            </label>
                            <select name="category" id="category" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select category...</option>
                                <option value="development" <?php echo e(old('category', $certification->category) === 'development' ? 'selected' : ''); ?>>Development</option>
                                <option value="design" <?php echo e(old('category', $certification->category) === 'design' ? 'selected' : ''); ?>>Design</option>
                                <option value="marketing" <?php echo e(old('category', $certification->category) === 'marketing' ? 'selected' : ''); ?>>Marketing</option>
                                <option value="business" <?php echo e(old('category', $certification->category) === 'business' ? 'selected' : ''); ?>>Business</option>
                                <option value="other" <?php echo e(old('category', $certification->category) === 'other' ? 'selected' : ''); ?>>Other</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                                    Icon (emoji)
                                </label>
                                <input type="text" name="icon" id="icon" value="<?php echo e(old('icon', $certification->icon)); ?>" placeholder="🎓"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>

                            <div>
                                <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                                    Color (hex)
                                </label>
                                <input type="color" name="color" id="color" value="<?php echo e(old('color', $certification->color)); ?>"
                                    class="w-full h-10 rounded-md border-gray-300 shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label for="requirements" class="block text-sm font-medium text-gray-700 mb-2">
                                Requirements (one per line)
                            </label>
                            <textarea name="requirements_text" id="requirements_text" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Minimum 10 sales&#10;Average rating 4.5+&#10;Active for 6 months"><?php echo e(old('requirements_text', $certification->requirements ? implode("\n", $certification->requirements) : '')); ?></textarea>
                            <p class="mt-1 text-sm text-gray-500">Enter one requirement per line.</p>
                        </div>

                        <div>
                            <label for="benefits" class="block text-sm font-medium text-gray-700 mb-2">
                                Benefits
                            </label>
                            <textarea name="benefits" id="benefits" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"><?php echo e(old('benefits', $certification->benefits)); ?></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="requires_application" value="1" <?php echo e(old('requires_application', $certification->requires_application) ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Requires Application</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="requires_approval" value="1" <?php echo e(old('requires_approval', $certification->requires_approval) ? 'checked' : ''); ?>

                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">Requires Approval</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-2">
                                Sort Order
                            </label>
                            <input type="number" name="sort_order" id="sort_order" value="<?php echo e(old('sort_order', $certification->sort_order)); ?>"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', $certification->is_active) ? 'checked' : ''); ?>

                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Active</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-4">
                            <a href="<?php echo e(route('admin.certifications.index')); ?>" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </a>
                            <button type="submit" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                Update Certification
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\certifications\edit.blade.php ENDPATH**/ ?>