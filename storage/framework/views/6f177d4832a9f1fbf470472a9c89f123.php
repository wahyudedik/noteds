<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['modelUrl', 'format' => 'obj', 'title' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['modelUrl', 'format' => 'obj', 'title' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-6 bg-gradient-to-r from-purple-50 via-pink-50 to-rose-50 rounded-xl border-2 border-purple-200 p-6 shadow-lg">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-bold text-gray-900">🎨 3D Model Viewer</h3>
            <?php if($title): ?>
                <p class="text-sm text-gray-600 truncate"><?php echo e($title); ?></p>
            <?php endif; ?>
            <?php if($format): ?>
                <p class="text-xs text-gray-500">Format: <?php echo e(strtoupper($format)); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div 
        x-data="{
            loading: true,
            error: false,
            init() {
                // Simple 3D viewer using iframe or embed
                // For production, you might want to use Three.js or model-viewer
                this.loading = false;
            }
        }"
        class="relative bg-gray-900 rounded-lg overflow-hidden shadow-xl min-h-[400px] flex items-center justify-center">
        <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-gray-900">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-500"></div>
        </div>
        <div x-show="!loading && !error" class="w-full h-full">
            <!-- Using model-viewer for better 3D support (GLB/GLTF) -->
            <?php if(str_contains($modelUrl, '.glb') || str_contains($modelUrl, '.gltf')): ?>
                <model-viewer
                    src="<?php echo e($modelUrl); ?>"
                    alt="3D Model"
                    auto-rotate
                    camera-controls
                    style="width: 100%; height: 400px; background-color: #1f2937;"
                    loading="lazy"
                    interaction-policy="allow-when-focused"
                >
                    <div slot="poster" class="flex items-center justify-center h-full">
                        <div class="text-center text-gray-400">
                            <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <p>Loading 3D Model...</p>
                        </div>
                    </div>
                </model-viewer>
            <?php else: ?>
                <!-- Fallback for other 3D formats -->
                <div class="flex items-center justify-center h-full">
                    <div class="text-center text-gray-400 p-8">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="mb-4">3D model preview is available for GLB/GLTF formats.</p>
                        <p class="text-sm mb-4">Format: <?php echo e(strtoupper($format)); ?></p>
                        <a href="<?php echo e($modelUrl); ?>" download class="inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            Download Model
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div x-show="error" class="text-center text-gray-400 p-8">
            <svg class="w-16 h-16 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p>Unable to load 3D model. Please try downloading the file.</p>
            <a href="<?php echo e($modelUrl); ?>" download class="mt-4 inline-block px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                Download Model
            </a>
        </div>
    </div>
    
    <p class="mt-3 text-xs text-gray-600 text-center">
        💡 Drag to rotate • Scroll to zoom • Right-click to pan
    </p>
</div>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\rich-media\3d-viewer.blade.php ENDPATH**/ ?>