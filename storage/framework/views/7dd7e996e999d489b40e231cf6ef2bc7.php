<?php $__env->startSection('title', $webhook->name); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="<?php echo e(route('webhooks.index')); ?>"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('Back to Webhooks')); ?>

            </a>
        </div>

        <!-- Webhook Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e($webhook->name); ?></h1>
                    <?php if($webhook->is_active): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <?php echo e(__('Active')); ?>

                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            <?php echo e(__('Inactive')); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Event')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e(str_replace('.', ' ', $webhook->event)); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Status')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php if($webhook->is_active): ?>
                            <span class="text-green-600"><?php echo e(__('Active')); ?></span>
                        <?php else: ?>
                            <span class="text-gray-600"><?php echo e(__('Inactive')); ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('URL')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900 break-all"><?php echo e($webhook->url); ?></dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('Secret')); ?></dt>
                    <dd class="mt-1">
                        <div class="flex items-center gap-2">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded"><?php echo e($webhook->secret); ?></code>
                            <button onclick="copySecret()" class="text-xs text-blue-600 hover:text-blue-800">
                                <?php echo e(__('Copy')); ?>

                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1"><?php echo e(__('Use this secret to verify webhook requests.')); ?></p>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <form action="<?php echo e(route('webhooks.test', $webhook)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        <?php echo e(__('Test Webhook')); ?>

                    </button>
                </form>
                <form action="<?php echo e(route('webhooks.destroy', $webhook)); ?>" method="POST" class="inline"
                    onsubmit="return confirm('<?php echo e(__('Are you sure you want to delete this webhook?')); ?>');">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                        <?php echo e(__('Delete')); ?>

                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copySecret() {
    const secret = '<?php echo e($webhook->secret); ?>';
    navigator.clipboard.writeText(secret).then(() => {
        alert('<?php echo e(__('Secret copied to clipboard')); ?>');
    });
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\webhooks\show.blade.php ENDPATH**/ ?>