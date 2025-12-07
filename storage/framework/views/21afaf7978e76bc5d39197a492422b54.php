<?php $__env->startSection('title', 'Content Protection Settings'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Content Protection: <?php echo e($note->title); ?></h2>
            <div class="flex gap-4">
                <a href="<?php echo e(route('admin.notes.index')); ?>" class="text-blue-600 hover:text-blue-800">← Back to Notes</a>
                <a href="<?php echo e(route('notes.show', $note)); ?>" class="text-blue-600 hover:text-blue-800" target="_blank">View Note</a>
            </div>
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

        <!-- DRM Statistics -->
        <?php if($drmSetting->enabled): ?>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm font-medium text-gray-500">Total Access</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo e(number_format($drmStats['total_access'])); ?></div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm font-medium text-gray-500">Unique Users</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo e(number_format($drmStats['unique_users'])); ?></div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm font-medium text-gray-500">Unique Devices</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo e(number_format($drmStats['unique_devices'])); ?></div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm font-medium text-gray-500">Total Licenses</div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo e(number_format($drmStats['total_licenses'])); ?></div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm font-medium text-gray-500">Active Licenses</div>
                    <div class="text-2xl font-bold text-green-600"><?php echo e(number_format($drmStats['active_licenses'])); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Watermark Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Watermark Settings</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="<?php echo e(route('admin.content-protection.watermark', $note)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enabled" value="1" <?php echo e($watermarkSetting->enabled ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Enable Watermarking</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Type</label>
                                <select name="type" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="text" <?php echo e($watermarkSetting->type === 'text' ? 'selected' : ''); ?>>Text</option>
                                    <option value="image" <?php echo e($watermarkSetting->type === 'image' ? 'selected' : ''); ?>>Image/Logo</option>
                                    <option value="invisible" <?php echo e($watermarkSetting->type === 'invisible' ? 'selected' : ''); ?>>Invisible (Steganography)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Text</label>
                                <input type="text" name="text" value="<?php echo e($watermarkSetting->text); ?>" placeholder="Protected" class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                                    <input type="color" name="text_color" value="<?php echo e($watermarkSetting->text_color ?? '#000000'); ?>" class="w-full h-10 rounded-md border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Text Size</label>
                                    <input type="number" name="text_size" value="<?php echo e($watermarkSetting->text_size ?? 24); ?>" min="8" max="200" class="w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                                <select name="position" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="top-left" <?php echo e($watermarkSetting->position === 'top-left' ? 'selected' : ''); ?>>Top Left</option>
                                    <option value="top-right" <?php echo e($watermarkSetting->position === 'top-right' ? 'selected' : ''); ?>>Top Right</option>
                                    <option value="center" <?php echo e($watermarkSetting->position === 'center' ? 'selected' : ''); ?>>Center</option>
                                    <option value="bottom-left" <?php echo e($watermarkSetting->position === 'bottom-left' ? 'selected' : ''); ?>>Bottom Left</option>
                                    <option value="bottom-right" <?php echo e($watermarkSetting->position === 'bottom-right' ? 'selected' : ''); ?>>Bottom Right</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Opacity (0-100)</label>
                                <input type="range" name="opacity" value="<?php echo e($watermarkSetting->opacity ?? 50); ?>" min="0" max="100" class="w-full">
                                <div class="text-sm text-gray-500 text-center"><?php echo e($watermarkSetting->opacity ?? 50); ?>%</div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="apply_to_images" value="1" <?php echo e($watermarkSetting->apply_to_images ? 'checked' : ''); ?> class="rounded border-gray-300">
                                        <span class="ml-2 text-sm text-gray-700">Apply to Images</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="apply_to_pdfs" value="1" <?php echo e($watermarkSetting->apply_to_pdfs ? 'checked' : ''); ?> class="rounded border-gray-300">
                                        <span class="ml-2 text-sm text-gray-700">Apply to PDFs</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                    Save Watermark Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- DRM Settings -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">DRM Settings</h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="<?php echo e(route('admin.content-protection.drm', $note)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="space-y-4">
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="enabled" value="1" <?php echo e($drmSetting->enabled ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Enable DRM Protection</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="encrypt_files" value="1" <?php echo e($drmSetting->encrypt_files ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Encrypt Files</span>
                                </label>
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="time_limited_access" value="1" <?php echo e($drmSetting->time_limited_access ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Time-Limited Access</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Access Duration (Days)</label>
                                <input type="number" name="access_duration_days" value="<?php echo e($drmSetting->access_duration_days); ?>" min="1" max="3650" class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="device_limit_enabled" value="1" <?php echo e($drmSetting->device_limit_enabled ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">Device Limit</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Max Devices</label>
                                <input type="number" name="max_devices" value="<?php echo e($drmSetting->max_devices ?? 3); ?>" min="1" max="100" class="w-full rounded-md border-gray-300 shadow-sm">
                            </div>

                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" name="license_key_enabled" value="1" <?php echo e($drmSetting->license_key_enabled ? 'checked' : ''); ?> class="rounded border-gray-300">
                                    <span class="ml-2 text-sm text-gray-700">License Key Required</span>
                                </label>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">License Key Type</label>
                                <select name="license_key_type" class="w-full rounded-md border-gray-300 shadow-sm">
                                    <option value="per_user" <?php echo e($drmSetting->license_key_type === 'per_user' ? 'selected' : ''); ?>>Per User</option>
                                    <option value="per_device" <?php echo e($drmSetting->license_key_type === 'per_device' ? 'selected' : ''); ?>>Per Device</option>
                                    <option value="per_download" <?php echo e($drmSetting->license_key_type === 'per_download' ? 'selected' : ''); ?>>Per Download</option>
                                </select>
                            </div>

                            <div>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                                    Save DRM Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if($drmSetting->enabled): ?>
        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
            </div>
            <div class="p-6">
                <div class="flex gap-4">
                    <a href="<?php echo e(route('admin.content-protection.access-logs', $note)); ?>" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-md">
                        View Access Logs
                    </a>
                    <a href="<?php echo e(route('admin.content-protection.license-keys-list', $note)); ?>" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-md">
                        View License Keys
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\content-protection\show.blade.php ENDPATH**/ ?>