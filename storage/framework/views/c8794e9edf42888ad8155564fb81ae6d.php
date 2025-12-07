<?php $__env->startSection('title', $categories[$category] ?? 'Documentation'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <a href="<?php echo e(route('docs.index')); ?>" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <?php echo e(__('messages.back_to_all_documentation')); ?>

                </a>
            </div>
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e($categories[$category] ?? __('messages.documentation')); ?></h1>
            <p class="mt-2 text-base text-gray-600">
                <?php if($category === 'wiki'): ?>
                    <?php echo e(__('messages.comprehensive_guides_noteds')); ?>

                <?php elseif($category === 'screenshot_guide'): ?>
                    <?php echo e(__('messages.step_by_step_guides')); ?>

                <?php elseif($category === 'link_reference'): ?>
                    <?php echo e(__('messages.useful_links_resources')); ?>

                <?php elseif($category === 'troubleshooting'): ?>
                    <?php echo e(__('messages.solutions_common_problems')); ?>

                <?php elseif($category === 'api_documentation'): ?>
                    <?php echo e(__('messages.technical_api_documentation')); ?>

                <?php else: ?>
                    <?php echo e(__('messages.video_tutorials_walkthroughs')); ?>

                <?php endif; ?>
            </p>
        </div>

        <!-- Search -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="<?php echo e(route('docs.category', $category)); ?>" class="flex gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" 
                    :placeholder="__('messages.search_in_category', ['category' => $categories[$category] ?? __('messages.documentation')])"
                    class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <?php echo e(__('messages.search')); ?>

                </button>
            </form>
        </div>

        <!-- Documentation List -->
        <?php if($documentations->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $documentations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('docs.show', [$doc->category, $doc->slug])); ?>" class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                        <div class="p-6">
                            <?php if($doc->icon): ?>
                                <div class="text-3xl mb-3 text-blue-500">
                                    <iconify-icon icon="<?php echo e($doc->icon); ?>" width="28" height="28"></iconify-icon>
                                </div>
                            <?php endif; ?>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                <?php echo e($doc->title); ?>

                            </h3>
                            <?php if($doc->summary): ?>
                                <p class="text-sm text-gray-600 line-clamp-3 mb-4">
                                    <?php echo Str::limit(strip_tags($doc->summary), 100); ?>

                                </p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><?php echo e($doc->creator->name ?? 'Admin'); ?></span>
                                <span><?php echo e($doc->view_count); ?> <?php echo e($doc->view_count == 1 ? __('messages.view') : __('messages.views')); ?></span>
                            </div>
                            <?php if($doc->tags && count($doc->tags) > 0): ?>
                                <div class="flex flex-wrap gap-1 mt-3">
                                    <?php $__currentLoopData = array_slice($doc->tags, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                            <?php echo e($tag); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <?php echo e($documentations->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('messages.no_documentation_found')); ?></h3>
                <p class="mt-2 text-sm text-gray-500">
                    <?php if(request('search')): ?>
                        <?php echo e(__('messages.try_adjusting_search')); ?>

                    <?php else: ?>
                        <?php echo e(__('messages.no_articles_available')); ?>

                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\docs\category.blade.php ENDPATH**/ ?>