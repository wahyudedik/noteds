<?php $__env->startSection('title', $folder->name); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="<?php echo e(route('folders.index')); ?>" class="text-gray-500 hover:text-gray-700 inline-flex items-center mb-4">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('messages.back_to_folders')); ?>

            </a>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <?php if($folder->color): ?>
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: <?php echo e($folder->color); ?>20;">
                            <svg class="w-7 h-7" style="color: <?php echo e($folder->color); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                    <?php else: ?>
                        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center">
                            <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e($folder->name); ?></h1>
                        <?php if($folder->description): ?>
                            <p class="text-gray-600 mt-1"><?php echo e($folder->description); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="<?php echo e(route('folders.edit', $folder)); ?>" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <?php echo e(__('messages.edit')); ?>

                    </a>
                </div>
            </div>
        </div>

        <!-- Subfolders -->
        <?php if($folder->children->count() > 0): ?>
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo e(__('messages.subfolders')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <?php $__currentLoopData = $folder->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('folders.show', $child)); ?>" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <?php if($child->color): ?>
                                    <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: <?php echo e($child->color); ?>20;">
                                        <svg class="w-5 h-5" style="color: <?php echo e($child->color); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-medium text-gray-900"><?php echo e($child->name); ?></div>
                                    <div class="text-sm text-gray-600"><?php echo e($child->notes()->count()); ?> <?php echo e($child->notes()->count() == 1 ? __('messages.note') : __('messages.notes')); ?></div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notes -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900"><?php echo e(__('messages.notes_in_this_folder')); ?></h2>
                    <span class="text-sm text-gray-600"><?php echo e($folder->notes->count()); ?> <?php echo e($folder->notes->count() == 1 ? __('messages.note') : __('messages.notes')); ?></span>
                </div>
            </div>
            <div class="p-6">
                <?php if($folder->notes->count() > 0): ?>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $folder->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <h3 class="font-semibold text-gray-900 mb-2">
                                    <a href="<?php echo e(route('notes.show', $note)); ?>" class="hover:text-blue-600 transition-colors">
                                        <?php echo e($note->title); ?>

                                    </a>
                                </h3>
                                <?php if($note->summary): ?>
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo e($note->summary); ?></p>
                                <?php endif; ?>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span><?php echo e($note->created_at->diffForHumans()); ?></span>
                                    <?php if($note->is_public): ?>
                                        <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-800"><?php echo e(__('messages.public')); ?></span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-800"><?php echo e(__('messages.private')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12 text-gray-500">
                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p><?php echo e(__('messages.no_notes_in_folder')); ?></p>
                        <a href="<?php echo e(route('notes.create')); ?>" class="mt-4 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <?php echo e(__('messages.create_first_note_folder')); ?>

                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\folders\show.blade.php ENDPATH**/ ?>