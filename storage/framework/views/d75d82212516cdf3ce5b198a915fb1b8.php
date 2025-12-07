<?php $__env->startSection('title', __('messages.reading_history')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Reading History</h1>
                    <p class="mt-2 text-sm text-gray-600">History of all notes you've viewed</p>
                </div>
                <a href="<?php echo e(route('marketplace.index')); ?>" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    Browse Marketplace
                </a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Views</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($totalViews)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Unique Notes</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($uniqueNotes)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">This Month</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($viewsThisMonth)); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading History List -->
        <?php if($viewHistory->count() > 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recently Viewed</h2>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $viewHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $view): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-start gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                <!-- Note Thumbnail -->
                                <div class="flex-shrink-0">
                                    <?php if($view->note->hasThumbnails()): ?>
                                        <img src="<?php echo e(Storage::url($view->note->thumbnails[0])); ?>" 
                                             alt="<?php echo e($view->note->title); ?>"
                                             class="w-20 h-20 object-cover rounded-lg">
                                    <?php else: ?>
                                        <div class="w-20 h-20 bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Note Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <a href="<?php echo e(route('marketplace.show', $view->note)); ?>" 
                                               class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                                <?php echo e($view->note->title); ?>

                                            </a>
                                            <?php if($view->note->summary): ?>
                                                <p class="mt-1 text-sm text-gray-600 line-clamp-2"><?php echo e($view->note->summary); ?></p>
                                            <?php endif; ?>
                                            <div class="mt-2 flex items-center gap-4 text-xs text-gray-500">
                                                <a href="<?php echo e(route('public.profile.show', $view->note->user->username)); ?>" 
                                                   class="hover:text-blue-600 transition-colors duration-200">
                                                    <?php echo e(__('messages.by_label')); ?> <?php echo e($view->note->user->name); ?>

                                                </a>
                                                <span>•</span>
                                                <span><?php echo e(__('messages.viewed_time_ago', ['time' => $view->viewed_at->diffForHumans()])); ?></span>
                                                <?php if($view->note->price > 0): ?>
                                                    <span>•</span>
                                                    <span class="font-semibold text-green-600">
                                                        <?php echo e(currency($view->note->price)); ?>

                                                    </span>
                                                <?php else: ?>
                                                    <span>•</span>
                                                    <span class="font-semibold text-gray-600"><?php echo e(__('messages.free')); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <a href="<?php echo e(route('marketplace.show', $view->note)); ?>" 
                                               class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                                <?php echo e(__('messages.view_note')); ?>

                                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        <?php echo e($viewHistory->links()); ?>

                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No reading history</h3>
                <p class="mt-1 text-sm text-gray-500">Start browsing notes to build your reading history.</p>
                <div class="mt-6">
                    <a href="<?php echo e(route('marketplace.index')); ?>" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Marketplace
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\buyer\reading-history\index.blade.php ENDPATH**/ ?>