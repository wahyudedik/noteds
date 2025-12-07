<?php $__env->startSection('title', 'Submit Entry - ' . $contest->title); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo e(route('contests.show', $contest)); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Contest
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">Submit Entry to <?php echo e($contest->title); ?></h2>
            </div>
            <div class="p-6">
                <form action="<?php echo e(route('contests.submit-entry', $contest)); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <div class="mb-6">
                        <label for="note_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Note <span class="text-red-600">*</span>
                        </label>
                        <select name="note_id" id="note_id" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 <?php $__errorArgs = ['note_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Choose a note...</option>
                            <?php $__currentLoopData = $userNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($note->id); ?>"><?php echo e($note->title); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['note_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php if($userNotes->isEmpty()): ?>
                            <p class="mt-2 text-sm text-yellow-600">You don't have any eligible notes to submit. Create a public note first!</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-6">
                        <label for="submission_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Why should this note win? (optional)
                        </label>
                        <textarea name="submission_notes" id="submission_notes" rows="4"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Tell us why this note deserves to win..."></textarea>
                        <p class="mt-1 text-sm text-gray-500">This will help judges understand your submission better.</p>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="<?php echo e(route('contests.show', $contest)); ?>" 
                           class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors"
                            <?php if($userNotes->isEmpty()): ?> disabled <?php endif; ?>>
                            Submit Entry
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\contests\submit.blade.php ENDPATH**/ ?>