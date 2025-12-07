<?php $__env->startSection('title', __('Note Templates')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Note Templates')); ?></h1>
                <p class="mt-2 text-sm text-gray-600"><?php echo e(__('Create and use templates to speed up note creation')); ?></p>
            </div>
            <a href="<?php echo e(route('templates.create')); ?>"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <?php echo e(__('Create Template')); ?>

            </a>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button onclick="showTab('my-templates')" id="tab-my-templates"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                    <?php echo e(__('My Templates')); ?> (<?php echo e($myTemplates->total()); ?>)
                </button>
                <button onclick="showTab('public-templates')" id="tab-public-templates"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <?php echo e(__('Public Templates')); ?> (<?php echo e($publicTemplates->total()); ?>)
                </button>
            </nav>
        </div>

        <!-- My Templates -->
        <div id="tab-content-my-templates" class="tab-content">
            <?php if($myTemplates->count() > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $myTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <?php echo e($template->name); ?>

                                </h3>
                                <?php if($template->category): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        <?php echo e($template->category); ?>

                                    </span>
                                <?php endif; ?>
                                <?php if($template->description): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        <?php echo e(Str::limit($template->description, 150)); ?>

                                    </p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between">
                                    <a href="<?php echo e(route('templates.show', $template)); ?>"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                        <?php echo e(__('View')); ?>

                                    </a>
                                    <form action="<?php echo e(route('templates.use', $template)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                            class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            <?php echo e(__('Use Template')); ?>

                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-6">
                    <?php echo e($myTemplates->links()); ?>

                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('No templates yet')); ?></h3>
                    <p class="mt-2 text-sm text-gray-500"><?php echo e(__('Create your first template to get started.')); ?></p>
                    <a href="<?php echo e(route('templates.create')); ?>" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                        <?php echo e(__('Create Template')); ?> →
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Public Templates -->
        <div id="tab-content-public-templates" class="tab-content hidden">
            <?php if($publicTemplates->count() > 0): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $publicTemplates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    <?php echo e($template->name); ?>

                                </h3>
                                <?php if($template->category): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        <?php echo e($template->category); ?>

                                    </span>
                                <?php endif; ?>
                                <div class="text-xs text-gray-500 mb-2">
                                    <?php echo e(__('By')); ?> <?php echo e($template->user->name); ?>

                                </div>
                                <?php if($template->description): ?>
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        <?php echo e(Str::limit($template->description, 150)); ?>

                                    </p>
                                <?php endif; ?>
                                <div class="flex items-center justify-between">
                                    <a href="<?php echo e(route('templates.show', $template)); ?>"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                        <?php echo e(__('View')); ?>

                                    </a>
                                    <form action="<?php echo e(route('templates.use', $template)); ?>" method="POST" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit"
                                            class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            <?php echo e(__('Use Template')); ?>

                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-6">
                    <?php echo e($publicTemplates->links()); ?>

                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('No public templates')); ?></h3>
                    <p class="mt-2 text-sm text-gray-500"><?php echo e(__('No public templates are available at the moment.')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    
    const button = document.getElementById('tab-' + tab);
    button.classList.remove('border-transparent', 'text-gray-500');
    button.classList.add('border-blue-500', 'text-blue-600');
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\templates\index.blade.php ENDPATH**/ ?>