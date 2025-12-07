<?php $__env->startSection('title', $link->name ?: __('affiliate.affiliate_landing_page')); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-12">
        <?php if($link->landing_page_content): ?>
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8">
                <div class="prose dark:prose-invert max-w-none">
                    <?php echo $link->landing_page_content; ?>

                </div>
                
                <div class="mt-8 text-center">
                    <a href="<?php echo e(route('marketplace.index', ['ref' => $link->code])); ?>" 
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors">
                        <?php echo e(__('affiliate.visit_marketplace')); ?>

                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    <?php echo e(__('affiliate.welcome')); ?>

                </h1>
                <p class="text-gray-600 dark:text-gray-300 mb-8">
                    <?php echo e(__('affiliate.landing_page_default_message')); ?>

                </p>
                <a href="<?php echo e(route('marketplace.index', ['ref' => $link->code])); ?>" 
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors">
                    <?php echo e(__('affiliate.visit_marketplace')); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\affiliate\landing.blade.php ENDPATH**/ ?>