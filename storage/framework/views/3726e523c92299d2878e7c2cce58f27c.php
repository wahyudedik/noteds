<?php
    $isActive = request()->has('folder') && request()->folder == $folder->id;
    $hasChildren = $folder->relationLoaded('children') ? $folder->children->count() > 0 : false;
?>

<div class="folder-tree-item" x-data="{ expanded: <?php echo e($isActive ? 'true' : 'false'); ?> }">
    <div class="flex items-center gap-1">
        <?php if($hasChildren): ?>
            <button @click="expanded = !expanded" class="p-1 hover:bg-gray-100 rounded">
                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="expanded ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        <?php else: ?>
            <div class="w-6"></div>
        <?php endif; ?>
        
        <a href="<?php echo e(route('workspaces.show', ['workspace' => $workspace->id, 'folder' => $folder->id])); ?>" 
           class="flex items-center gap-2 flex-1 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors <?php echo e($isActive ? 'bg-blue-50 text-blue-700' : 'text-gray-700'); ?>"
           style="padding-left: <?php echo e(($level * 16) + 12); ?>px;">
            <?php if($folder->color): ?>
                <div class="w-4 h-4 rounded flex-shrink-0" style="background-color: <?php echo e($folder->color); ?>;"></div>
            <?php else: ?>
                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
            <?php endif; ?>
            <span class="text-sm font-medium truncate flex-1"><?php echo e($folder->name); ?></span>
            <span class="text-xs text-gray-500 ml-2 flex-shrink-0"><?php echo e($folder->notes()->count()); ?></span>
        </a>
    </div>
    
    <?php if($hasChildren): ?>
        <div x-show="expanded" x-transition class="mt-1 space-y-1 ml-6">
            <?php $__currentLoopData = $folder->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('workspaces.partials.folder-tree-item', ['folder' => $child, 'workspace' => $workspace, 'level' => $level + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\workspaces\partials\folder-tree-item.blade.php ENDPATH**/ ?>