<?php $__env->startSection('title', 'Review Certification Application'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.certifications.applications')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Applications
            </a>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Review Certification Application</h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">User</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($application->user->name); ?></p>
                        <p class="text-sm text-gray-600"><?php echo e($application->user->email); ?></p>
                    </div>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Certification</h3>
                        <p class="text-lg font-semibold text-gray-900"><?php echo e($application->certification->name); ?></p>
                        <p class="text-sm text-gray-600"><?php echo e($application->certification->description); ?></p>
                    </div>

                    <?php if($application->application_notes): ?>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Application Notes</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <p class="text-gray-700 whitespace-pre-line"><?php echo e($application->application_notes); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($application->evidence && count($application->evidence) > 0): ?>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Evidence/Portfolio</h3>
                            <ul class="space-y-2">
                                <?php $__currentLoopData = $application->evidence; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>
                                        <a href="<?php echo e($link); ?>" target="_blank" rel="noopener noreferrer" 
                                           class="text-blue-600 hover:text-blue-800 underline">
                                            <?php echo e($link); ?>

                                        </a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div>
                        <h3 class="text-sm font-medium text-gray-500 mb-1">Applied At</h3>
                        <p class="text-gray-900"><?php echo e($application->applied_at ? $application->applied_at->format('F d, Y H:i') : 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Review Action</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-6">
                    <!-- Approve Form -->
                    <div class="border border-green-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-green-900 mb-4">Approve Application</h3>
                        <form action="<?php echo e(route('admin.certifications.applications.approve', $application)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Admin Notes (optional)
                                </label>
                                <textarea name="admin_notes" id="approve_notes" rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                                    Expires At (optional)
                                </label>
                                <input type="date" name="expires_at" id="expires_at" min="<?php echo e(date('Y-m-d')); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            </div>
                            <button type="submit" 
                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-md">
                                Approve Certification
                            </button>
                        </form>
                    </div>

                    <!-- Reject Form -->
                    <div class="border border-red-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-red-900 mb-4">Reject Application</h3>
                        <form action="<?php echo e(route('admin.certifications.applications.reject', $application)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Rejection Reason <span class="text-red-600">*</span>
                                </label>
                                <textarea name="admin_notes" id="reject_notes" rows="3" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"></textarea>
                            </div>
                            <button type="submit" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-md">
                                Reject Application
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\certifications\application-show.blade.php ENDPATH**/ ?>