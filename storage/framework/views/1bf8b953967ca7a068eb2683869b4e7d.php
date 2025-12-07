<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['audioUrl', 'title' => null, 'duration' => null]));

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

foreach (array_filter((['audioUrl', 'title' => null, 'duration' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<div class="mb-6 bg-gradient-to-r from-yellow-50 via-amber-50 to-orange-50 rounded-xl border-2 border-yellow-200 p-6 shadow-lg">
    <div class="flex items-center gap-3 mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-bold text-gray-900">🎵 Audio Preview</h3>
            <?php if($title): ?>
                <p class="text-sm text-gray-600 truncate"><?php echo e($title); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <audio 
        controls 
        class="w-full rounded-lg shadow-md"
        preload="metadata"
        x-data="{ 
            duration: null,
            currentTime: 0,
            formatTime(seconds) {
                if (!seconds) return '0:00';
                const mins = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            }
        }"
        @loadedmetadata="$el.duration && (duration = $el.duration)"
        @timeupdate="currentTime = $el.currentTime"
    >
        <source src="<?php echo e($audioUrl); ?>" type="audio/mpeg">
        <source src="<?php echo e($audioUrl); ?>" type="audio/ogg">
        <source src="<?php echo e($audioUrl); ?>" type="audio/wav">
        Your browser does not support the audio element.
    </audio>
    
    <?php if($duration): ?>
        <p class="mt-2 text-xs text-gray-600 text-center">Duration: <?php echo e(gmdate('i:s', $duration)); ?></p>
    <?php endif; ?>
</div>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\rich-media\audio-preview.blade.php ENDPATH**/ ?>