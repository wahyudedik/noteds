<?php $__env->startSection('title', __('messages.cms_pages')); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.cms_pages')); ?></h1>
                <p class="mt-2 text-sm text-gray-600">
                    <?php echo e(__('messages.cms_pages_intro')); ?>

                </p>
            </div>

            <?php if($pages->count() > 0): ?>
                <div class="space-y-6">
                    <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <article class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100 text-blue-700">
                                    <?php echo e(__('messages.cms_pages')); ?>

                                </span>
                                <span class="text-xs text-gray-400"><?php echo e($page->updated_at?->format('M d, Y')); ?></span>
                            </div>
                            <a href="<?php echo e(route('cms.show', $page)); ?>" class="group">
                                <h2 class="text-xl font-semibold text-gray-900 group-hover:text-blue-600 transition mb-2">
                                    <?php echo e($page->title); ?>

                                </h2>
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    <?php echo e(\Illuminate\Support\Str::limit(strip_tags($page->content), 200)); ?>

                                </p>
                            </a>
                            <div class="mt-4">
                                <a href="<?php echo e(route('cms.show', $page)); ?>"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1">
                                    <?php echo e(__('messages.view')); ?>

                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="mt-10">
                    <?php echo e($pages->links()); ?>

                </div>
            <?php else: ?>
                <div class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <p class="mt-4 text-sm text-gray-600"><?php echo e(__('messages.no_cms_pages_yet')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\cms\index.blade.php ENDPATH**/ ?>