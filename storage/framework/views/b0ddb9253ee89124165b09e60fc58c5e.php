<!-- Content Protection Section -->
<div class="mt-6 border-t border-gray-200 pt-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <p class="text-xs text-yellow-800">
            🔒 <strong>Content Protection:</strong> Tambahkan watermark dan DRM protection untuk melindungi konten Anda. 
            Settings ini juga dapat dikonfigurasi di Admin Panel setelah note dibuat.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Watermark Settings -->
        <div class="border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Watermark Settings</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="watermark_enabled" value="1" <?php echo e(old('watermark_enabled', isset($note) && $note->watermarkSetting && $note->watermarkSetting->enabled) ? 'checked' : ''); ?> 
                            class="rounded border-gray-300" id="watermark_enabled">
                        <span class="ml-2 text-sm text-gray-700">Enable Watermarking</span>
                    </label>
                </div>

                <div id="watermark-options" style="display: <?php echo e(old('watermark_enabled', isset($note) && $note->watermarkSetting && $note->watermarkSetting->enabled) ? 'block' : 'none'); ?>;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Type</label>
                        <select name="watermark_type" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="text" <?php echo e(old('watermark_type', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->type : 'text') === 'text' ? 'selected' : ''); ?>>Text</option>
                            <option value="image" <?php echo e(old('watermark_type', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->type : '') === 'image' ? 'selected' : ''); ?>>Image/Logo</option>
                            <option value="invisible" <?php echo e(old('watermark_type', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->type : '') === 'invisible' ? 'selected' : ''); ?>>Invisible (Steganography)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Watermark Text</label>
                        <input type="text" name="watermark_text" value="<?php echo e(old('watermark_text', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->text : '')); ?>" 
                            placeholder="Protected" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Color</label>
                            <input type="color" name="watermark_text_color" value="<?php echo e(old('watermark_text_color', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->text_color : '#000000')); ?>" 
                                class="w-full h-10 rounded-md border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Text Size</label>
                            <input type="number" name="watermark_text_size" value="<?php echo e(old('watermark_text_size', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->text_size : 24)); ?>" 
                                min="8" max="200" class="w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Position</label>
                        <select name="watermark_position" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="center" <?php echo e(old('watermark_position', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->position : 'center') === 'center' ? 'selected' : ''); ?>>Center</option>
                            <option value="top-left" <?php echo e(old('watermark_position', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->position : '') === 'top-left' ? 'selected' : ''); ?>>Top Left</option>
                            <option value="top-right" <?php echo e(old('watermark_position', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->position : '') === 'top-right' ? 'selected' : ''); ?>>Top Right</option>
                            <option value="bottom-left" <?php echo e(old('watermark_position', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->position : '') === 'bottom-left' ? 'selected' : ''); ?>>Bottom Left</option>
                            <option value="bottom-right" <?php echo e(old('watermark_position', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->position : '') === 'bottom-right' ? 'selected' : ''); ?>>Bottom Right</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Opacity: <span id="watermark-opacity-value"><?php echo e(old('watermark_opacity', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->opacity : 50)); ?></span>%</label>
                        <input type="range" name="watermark_opacity" value="<?php echo e(old('watermark_opacity', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->opacity : 50)); ?>" 
                            min="0" max="100" class="w-full" id="watermark-opacity-slider">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="watermark_apply_to_images" value="1" 
                                    <?php echo e(old('watermark_apply_to_images', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->apply_to_images : true) ? 'checked' : ''); ?> 
                                    class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Apply to Images</span>
                            </label>
                        </div>
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="watermark_apply_to_pdfs" value="1" 
                                    <?php echo e(old('watermark_apply_to_pdfs', isset($note) && $note->watermarkSetting ? $note->watermarkSetting->apply_to_pdfs : true) ? 'checked' : ''); ?> 
                                    class="rounded border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">Apply to PDFs</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DRM Settings -->
        <div class="border border-gray-200 rounded-lg p-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">DRM Protection</h3>
            
            <div class="space-y-4">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="drm_enabled" value="1" 
                            <?php echo e(old('drm_enabled', isset($note) && $note->drmSetting && $note->drmSetting->enabled) ? 'checked' : ''); ?> 
                            class="rounded border-gray-300" id="drm_enabled">
                        <span class="ml-2 text-sm text-gray-700">Enable DRM Protection</span>
                    </label>
                </div>

                <div id="drm-options" style="display: <?php echo e(old('drm_enabled', isset($note) && $note->drmSetting && $note->drmSetting->enabled) ? 'block' : 'none'); ?>;">
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="drm_encrypt_files" value="1" 
                                <?php echo e(old('drm_encrypt_files', isset($note) && $note->drmSetting ? $note->drmSetting->encrypt_files : false) ? 'checked' : ''); ?> 
                                class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Encrypt Files</span>
                        </label>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="drm_time_limited_access" value="1" 
                                <?php echo e(old('drm_time_limited_access', isset($note) && $note->drmSetting ? $note->drmSetting->time_limited_access : false) ? 'checked' : ''); ?> 
                                class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Time-Limited Access</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Access Duration (Days)</label>
                        <input type="number" name="drm_access_duration_days" 
                            value="<?php echo e(old('drm_access_duration_days', isset($note) && $note->drmSetting ? $note->drmSetting->access_duration_days : '')); ?>" 
                            min="1" max="3650" placeholder="30" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="drm_device_limit_enabled" value="1" 
                                <?php echo e(old('drm_device_limit_enabled', isset($note) && $note->drmSetting ? $note->drmSetting->device_limit_enabled : false) ? 'checked' : ''); ?> 
                                class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Device Limit</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Devices</label>
                        <input type="number" name="drm_max_devices" 
                            value="<?php echo e(old('drm_max_devices', isset($note) && $note->drmSetting ? $note->drmSetting->max_devices : 3)); ?>" 
                            min="1" max="100" class="w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="drm_license_key_enabled" value="1" 
                                <?php echo e(old('drm_license_key_enabled', isset($note) && $note->drmSetting ? $note->drmSetting->license_key_enabled : false) ? 'checked' : ''); ?> 
                                class="rounded border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">License Key Required</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">License Key Type</label>
                        <select name="drm_license_key_type" class="w-full rounded-md border-gray-300 shadow-sm">
                            <option value="per_user" <?php echo e(old('drm_license_key_type', isset($note) && $note->drmSetting ? $note->drmSetting->license_key_type : 'per_user') === 'per_user' ? 'selected' : ''); ?>>Per User</option>
                            <option value="per_device" <?php echo e(old('drm_license_key_type', isset($note) && $note->drmSetting ? $note->drmSetting->license_key_type : '') === 'per_device' ? 'selected' : ''); ?>>Per Device</option>
                            <option value="per_download" <?php echo e(old('drm_license_key_type', isset($note) && $note->drmSetting ? $note->drmSetting->license_key_type : '') === 'per_download' ? 'selected' : ''); ?>>Per Download</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Watermark toggle
        const watermarkEnabled = document.getElementById('watermark_enabled');
        const watermarkOptions = document.getElementById('watermark-options');
        
        if (watermarkEnabled) {
            watermarkEnabled.addEventListener('change', function() {
                watermarkOptions.style.display = this.checked ? 'block' : 'none';
            });
        }

        // DRM toggle
        const drmEnabled = document.getElementById('drm_enabled');
        const drmOptions = document.getElementById('drm-options');
        
        if (drmEnabled) {
            drmEnabled.addEventListener('change', function() {
                drmOptions.style.display = this.checked ? 'block' : 'none';
            });
        }

        // Watermark opacity slider
        const opacitySlider = document.getElementById('watermark-opacity-slider');
        const opacityValue = document.getElementById('watermark-opacity-value');
        
        if (opacitySlider && opacityValue) {
            opacitySlider.addEventListener('input', function() {
                opacityValue.textContent = this.value;
            });
        }
    });
</script>
<?php $__env->stopPush(); ?>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\notes\partials\content-protection.blade.php ENDPATH**/ ?>