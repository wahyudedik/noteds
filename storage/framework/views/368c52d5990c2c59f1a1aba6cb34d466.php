<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

    <!-- Fix Vite URLs in development - Client-side fix -->
    <?php if(config('app.env') === 'local'): ?>
        <script>
            (function() {
                // Fix Vite URLs from HTTPS to HTTP
                const host = window.location.hostname;
                const port = ':5173';

                // Fix all Vite asset URLs
                document.addEventListener('DOMContentLoaded', function() {
                    // Fix link tags
                    document.querySelectorAll('link[href*=":5173"]').forEach(function(link) {
                        link.href = link.href.replace('https://' + host + port, 'http://' + host + port);
                    });

                    // Fix script tags
                    document.querySelectorAll('script[src*=":5173"]').forEach(function(script) {
                        script.src = script.src.replace('https://' + host + port, 'http://' + host + port);
                    });
                });

                // Also fix dynamically added scripts
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.tagName === 'LINK' && node.href && node.href.includes(
                                        ':5173')) {
                                    node.href = node.href.replace('https://' + host + port,
                                        'http://' + host + port);
                                }
                                if (node.tagName === 'SCRIPT' && node.src && node.src.includes(
                                        ':5173')) {
                                    node.src = node.src.replace('https://' + host + port,
                                        'http://' + host + port);
                                }
                            }
                        });
                    });
                });

                observer.observe(document.head, {
                    childList: true,
                    subtree: true
                });
            })();
        </script>
    <?php endif; ?>
</head>

<body class="font-sans antialiased bg-white text-slate-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <nav class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="<?php echo e(url('/')); ?>"
                        class="flex items-center gap-2 text-xl font-bold text-slate-900 hover:text-blue-600 transition-colors">
                        <?php if (isset($component)) { $__componentOriginal8892e718f3d0d7a916180885c6f012e7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8892e718f3d0d7a916180885c6f012e7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.application-logo','data' => ['class' => 'w-8 h-8 text-blue-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('application-logo'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'w-8 h-8 text-blue-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $attributes = $__attributesOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__attributesOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8892e718f3d0d7a916180885c6f012e7)): ?>
<?php $component = $__componentOriginal8892e718f3d0d7a916180885c6f012e7; ?>
<?php unset($__componentOriginal8892e718f3d0d7a916180885c6f012e7); ?>
<?php endif; ?>
                        <span><?php echo e(config('app.name', 'Noteds')); ?></span>
                    </a>
                    <div class="flex items-center gap-4">
                        <a href="<?php echo e(route('login')); ?>"
                            class="text-sm font-medium text-slate-700 hover:text-blue-600 transition-colors">
                            <?php echo e(__('messages.login')); ?>

                        </a>
                        <a href="<?php echo e(route('register')); ?>"
                            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all shadow-sm hover:shadow-md">
                            <?php echo e(__('messages.register')); ?>

                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="w-full max-w-md">
                <div class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-blue-100/50 p-8 sm:p-10">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-slate-500">
                    © <?php echo e(date('Y')); ?> <?php echo e(config('app.name', 'Noteds')); ?>. <?php echo e(__('messages.all_rights_reserved')); ?>

                </p>
            </div>
        </footer>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/layouts/guest.blade.php ENDPATH**/ ?>