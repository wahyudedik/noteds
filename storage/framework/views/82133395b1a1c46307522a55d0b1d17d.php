<?php $__env->startSection('title', 'Virus Scan Details'); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Virus Scan Details</h2>
                <a href="<?php echo e(route('admin.virus-scans.index')); ?>" class="text-blue-600 hover:text-blue-800">← Back to List</a>
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">File Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Name</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($virusScan->file_name); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Type</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($virusScan->file_type ?? 'Unknown'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Size</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if($virusScan->file_size): ?>
                                    <?php echo e(number_format($virusScan->file_size / 1024, 2)); ?> KB
                                <?php else: ?>
                                    Unknown
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">File Path</dt>
                            <dd class="mt-1 text-sm text-gray-900 break-all"><?php echo e($virusScan->file_path); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Scan Information</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <?php if($virusScan->isScanClean()): ?>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Clean</span>
                                <?php elseif($virusScan->isInfected()): ?>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Infected</span>
                                <?php elseif($virusScan->isQuarantined()): ?>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800">Quarantined</span>
                                <?php elseif($virusScan->hasError()): ?>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Error</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Pending</span>
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scan Type</dt>
                            <dd class="mt-1 text-sm text-gray-900 capitalize"><?php echo e($virusScan->scan_type); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scan Duration</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <?php if($virusScan->scan_duration_ms): ?>
                                    <?php echo e(number_format($virusScan->scan_duration_ms, 2)); ?>ms
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scanned By</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($virusScan->scannedByUser->name ?? 'System'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Scanned At</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($virusScan->created_at->format('Y-m-d H:i:s')); ?></dd>
                        </div>
                        <?php if($virusScan->note): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Related Note</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <a href="<?php echo e(route('notes.show', $virusScan->note)); ?>"
                                        class="text-blue-600 hover:text-blue-800">
                                        <?php echo e($virusScan->note->title); ?>

                                    </a>
                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if($virusScan->isInfected() || $virusScan->isQuarantined()): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                        <h3 class="text-lg font-semibold text-red-900">Threat Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Threat Name</dt>
                                <dd class="mt-1 text-sm text-red-900 font-semibold">
                                    <?php echo e($virusScan->threat_name ?? 'Unknown'); ?></dd>
                            </div>
                            <?php if($virusScan->threat_details): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Threat Details</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                                        <?php echo e($virusScan->threat_details); ?></dd>
                                </div>
                            <?php endif; ?>
                            <?php if($virusScan->scan_result): ?>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Scan Result</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
                                        <?php echo e($virusScan->scan_result); ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($virusScan->isQuarantined()): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-orange-50">
                        <h3 class="text-lg font-semibold text-orange-900">Quarantine Information</h3>
                    </div>
                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quarantine Path</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-all"><?php echo e($virusScan->quarantine_path); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Quarantined At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($virusScan->quarantined_at->format('Y-m-d H:i:s')); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($virusScan->hasError()): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 border-b border-gray-200 bg-yellow-50">
                        <h3 class="text-lg font-semibold text-yellow-900">Error Information</h3>
                    </div>
                    <div class="p-6">
                        <div class="text-sm text-gray-900 whitespace-pre-wrap"><?php echo e($virusScan->error_message); ?></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Actions</h3>
                </div>
                <div class="p-6">
                    <div class="flex gap-4">
                        <?php if($virusScan->isInfected() && !$virusScan->isQuarantined()): ?>
                            <form method="POST" action="<?php echo e(route('admin.virus-scans.quarantine', $virusScan)); ?>"
                                class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-md">
                                    Quarantine File
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if($virusScan->isQuarantined()): ?>
                            <form method="POST" action="<?php echo e(route('admin.virus-scans.restore', $virusScan)); ?>"
                                class="inline">
                                <?php echo csrf_field(); ?>
                                <div class="flex gap-2">
                                    <input type="text" name="restore_path" value="<?php echo e($virusScan->file_path); ?>"
                                        class="rounded-md border-gray-300 shadow-sm" required>
                                    <button type="submit"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                                        Restore File
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>

                        <?php if($virusScan->isQuarantined()): ?>
                            <form method="POST" action="<?php echo e(route('admin.virus-scans.destroy', $virusScan)); ?>"
                                class="inline"
                                onsubmit="return confirm('Are you sure you want to permanently delete this quarantined file?');">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md">
                                    Delete Permanently
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\virus-scans\show.blade.php ENDPATH**/ ?>