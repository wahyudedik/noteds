<?php $__env->startSection('title', $collection->name); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center">
                <a href="<?php echo e(route('collections.index')); ?>" class="mr-4 text-gray-600 hover:text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($collection->name); ?></h1>
                    <?php if($collection->description): ?>
                        <p class="mt-2 text-sm text-gray-600"><?php echo e($collection->description); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <?php if(isset($purchasedNotes) && $purchasedNotes->count() > 0): ?>
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <?php echo e(__('messages.collection_add_purchased_button')); ?>

                        </button>
                        <div x-show="open" @click.away="open = false" x-transition
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 py-2 z-50 max-h-96 overflow-y-auto">
                            <div class="px-4 py-2 border-b border-gray-200">
                                <p class="text-sm font-semibold text-gray-900"><?php echo e(__('messages.collection_select_notes_title')); ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo e(__('messages.collection_purchased_available', ['count' => $purchasedNotes->count()])); ?></p>
                            </div>
                            <?php $__currentLoopData = $purchasedNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <form action="<?php echo e(route('collections.add-note', $collection)); ?>" method="POST" class="px-4 py-2 hover:bg-gray-50 transition-colors">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="note_id" value="<?php echo e($note->id); ?>">
                                    <button type="submit" class="w-full text-left">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-1"><?php echo e($note->title); ?></p>
                                        <p class="text-xs text-gray-500 mt-1"><?php echo e(__('messages.by_label')); ?> <?php echo e($note->user->name); ?></p>
                                    </button>
                                </form>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($purchasedNotes->count() === 0): ?>
                                <div class="px-4 py-4 text-center text-sm text-gray-500">
                                    <?php echo e(__('messages.collection_no_purchased')); ?>

                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <?php echo e(__('messages.collection_browse_marketplace')); ?>

                </a>
                <a href="<?php echo e(route('collections.edit', $collection)); ?>" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <?php echo e(__('messages.edit')); ?>

                </a>
            </div>
        </div>

        <!-- Notes Grid -->
        <?php if($collection->notes->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $collection->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 overflow-hidden">
                        <a href="<?php echo e(route('marketplace.show', $note)); ?>" class="block">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2"><?php echo e($note->title); ?></h3>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?php echo e(Str::limit(strip_tags($note->summary ?? $note->content), 100)); ?></p>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <?php echo e($note->user->name); ?>

                                    </div>
                                    <?php if($note->price > 0): ?>
                                        <span class="text-sm font-semibold text-green-600">
                                            <?php echo e(currency($note->hasDiscount() ? $note->discount_price : $note->price)); ?>

                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm font-semibold text-blue-600"><?php echo e(__('messages.free')); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                        
                        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                            <form action="<?php echo e(route('collections.remove-note', ['collection' => $collection, 'note' => $note])); ?>" method="POST" onsubmit="return confirm('<?php echo e(__('messages.collection_remove_confirm')); ?>')">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="text-sm text-red-600 hover:text-red-700 font-medium">
                                    <?php echo e(__('messages.collection_remove_button')); ?>

                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900"><?php echo e(__('messages.collection_empty_title')); ?></h3>
                <p class="mt-1 text-sm text-gray-500 mb-4">
                    <?php if(isset($purchasedNotes) && $purchasedNotes->count() > 0): ?>
                        <?php echo e(__('messages.collection_empty_with_purchased', ['count' => $purchasedNotes->count(), 'button' => __('messages.collection_add_purchased_button')])); ?>

                    <?php else: ?>
                        <?php echo e(__('messages.collection_empty_without_purchased')); ?>

                    <?php endif; ?>
                </p>
                <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <?php echo e(__('messages.collection_browse_marketplace')); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\buyer\collections\show.blade.php ENDPATH**/ ?>