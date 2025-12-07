<?php $__env->startSection('title', $certification->name . ' - Certification'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo e(route('certifications.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Certifications
            </a>
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

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            <div class="p-8">
                <div class="flex items-start gap-4 mb-6">
                    <?php if($certification->icon): ?>
                        <span class="text-5xl"><?php echo e($certification->icon); ?></span>
                    <?php endif; ?>
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e($certification->name); ?></h1>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 capitalize">
                            <?php echo e($certification->category); ?>

                        </span>
                    </div>
                </div>

                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700 text-lg"><?php echo e($certification->description); ?></p>
                </div>

                <?php if($certification->requirements): ?>
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Requirements</h2>
                        <ul class="space-y-2">
                            <?php $__currentLoopData = $certification->requirements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="flex items-start text-gray-700">
                                    <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span><?php echo e($req); ?></span>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if($certification->benefits): ?>
                    <div class="mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-3">Benefits</h2>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-gray-700 whitespace-pre-line"><?php echo e($certification->benefits); ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if($userCertification): ?>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Your Application Status</h3>
                        <?php if($userCertification->status === 'approved'): ?>
                            <p class="text-green-700 font-medium mb-2">✓ Approved</p>
                            <?php if($userCertification->approved_at): ?>
                                <p class="text-sm text-gray-600">Approved on: <?php echo e($userCertification->approved_at->format('F d, Y')); ?></p>
                            <?php endif; ?>
                            <?php if($userCertification->expires_at): ?>
                                <p class="text-sm text-gray-600">Expires on: <?php echo e($userCertification->expires_at->format('F d, Y')); ?></p>
                            <?php endif; ?>
                        <?php elseif($userCertification->status === 'pending'): ?>
                            <p class="text-yellow-700 font-medium mb-2">⏳ Pending Review</p>
                            <p class="text-sm text-gray-600">Your application is being reviewed by our team.</p>
                        <?php elseif($userCertification->status === 'rejected'): ?>
                            <p class="text-red-700 font-medium mb-2">✗ Rejected</p>
                            <?php if($userCertification->admin_notes): ?>
                                <p class="text-sm text-gray-600 mt-2"><?php echo e($userCertification->admin_notes); ?></p>
                            <?php endif; ?>
                            <a href="<?php echo e(route('certifications.show', $certification)); ?>" class="mt-4 inline-block px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                                Reapply
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if(!$userCertification || $userCertification->status === 'rejected'): ?>
                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Apply for Certification</h2>
                        <form action="<?php echo e(route('certifications.apply', $certification)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            
                            <div class="mb-4">
                                <label for="application_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Why should you be certified? <span class="text-red-600">*</span>
                                </label>
                                <textarea 
                                    id="application_notes"
                                    name="application_notes"
                                    rows="6"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['application_notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    placeholder="Tell us about your experience, skills, and why you deserve this certification..."><?php echo e(old('application_notes')); ?></textarea>
                                <?php $__errorArgs = ['application_notes'];
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

                            <div class="mb-4">
                                <label for="evidence" class="block text-sm font-medium text-gray-700 mb-2">
                                    Evidence/Portfolio Links (optional)
                                </label>
                                <div id="evidence-container">
                                    <div class="flex gap-2 mb-2">
                                        <input 
                                            type="url"
                                            name="evidence[]"
                                            placeholder="https://example.com/portfolio"
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <button type="button" onclick="addEvidenceField()" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-sm">
                                            +
                                        </button>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-500">Add links to your portfolio, GitHub, or other evidence of your expertise.</p>
                            </div>

                            <div class="flex items-center justify-end gap-4">
                                <a href="<?php echo e(route('certifications.index')); ?>" 
                                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                    Cancel
                                </a>
                                <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors">
                                    Submit Application
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function addEvidenceField() {
    const container = document.getElementById('evidence-container');
    const div = document.createElement('div');
    div.className = 'flex gap-2 mb-2';
    div.innerHTML = `
        <input 
            type="url"
            name="evidence[]"
            placeholder="https://example.com/portfolio"
            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-200 hover:bg-red-300 text-red-700 rounded-md text-sm">
            ×
        </button>
    `;
    container.appendChild(div);
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\certifications\show.blade.php ENDPATH**/ ?>