<div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group relative">
    <?php if(isset($isFeatured) && $isFeatured): ?>
        <div class="absolute top-2 right-2 z-10">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-400 text-yellow-900">
                ⭐ <?php echo e(__('messages.featured_note')); ?>

            </span>
        </div>
    <?php endif; ?>

    <!-- Viral/Hot Badge -->
    <?php if($note->isViral() || $note->isHot()): ?>
        <div class="absolute top-2 left-2 z-10">
            <?php if($note->isViral()): ?>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-500 to-pink-600 text-white shadow-lg animate-pulse">
                    🔥 VIRAL
                </span>
            <?php elseif($note->isHot()): ?>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                    🔥 HOT
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Thumbnail -->
    <?php if($note->hasThumbnails()): ?>
        <div class="h-48 overflow-hidden bg-gray-100 relative">
            <img src="<?php echo e(Storage::url($note->thumbnails[0])); ?>" 
                 alt="<?php echo e($note->title); ?>" 
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
    <?php endif; ?>
    <div class="p-6">
        <!-- Title and Content -->
        <div class="mb-4">
            <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                <a href="<?php echo e(route('marketplace.show', $note)); ?>"><?php echo e($note->title); ?></a>
            </h3>
            <p class="text-sm text-gray-600 line-clamp-3"><?php echo Str::limit(strip_tags($note->content ?? ''), 120); ?></p>
        </div>

        <!-- Tags -->
        <?php if($note->tags && $note->tags->count() > 0): ?>
            <div class="flex flex-wrap gap-2 mb-4">
                <?php $__currentLoopData = $note->tags->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <?php echo e($tag->name); ?>

                    </span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- Badges and Meta -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <?php if($note->sale_mode): ?>
                <?php if($note->isScarcityMode()): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800">
                        Scarcity
                    </span>
                <?php elseif($note->isStandardMode()): ?>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">
                        Standard
                    </span>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($note->average_rating > 0): ?>
                <div class="flex items-center gap-0.5">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <svg class="w-3 h-3 <?php echo e($i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300'); ?>" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    <?php endfor; ?>
                    <span class="text-xs text-gray-600 ml-1"><?php echo e($note->average_rating); ?></span>
                </div>
            <?php endif; ?>
            <?php if($note->price > 0): ?>
                <?php if($note->hasDiscount()): ?>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100">
                        <div class="flex flex-col items-end">
                            <span class="text-gray-500 line-through text-[10px]"><?php echo e(currency($note->price)); ?></span>
                            <span class="text-yellow-800 font-semibold"><?php echo e(currency($note->discount_price)); ?></span>
                        </div>
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-500 text-white">
                            -<?php echo e($note->discount_percent); ?>%
                        </span>
                    </div>
                <?php else: ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 font-semibold">
                        <?php echo e(currency($note->price)); ?>

                    </span>
                <?php endif; ?>
            <?php else: ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                    <?php echo e(__('messages.free')); ?>

                </span>
            <?php endif; ?>
        </div>

        <!-- Author -->
        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
            <a href="<?php echo e(route('public.profile.show', $note->user->username)); ?>" 
               class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200 group">
                <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                    <?php if($note->user->avatar): ?>
                        <?php if(str_starts_with($note->user->avatar, 'http')): ?>
                            <img src="<?php echo e($note->user->avatar); ?>" 
                                 alt="<?php echo e($note->user->name); ?>" 
                                 loading="lazy"
                                 class="w-6 h-6 rounded-full object-cover">
                        <?php else: ?>
                            <img src="<?php echo e(Storage::url($note->user->avatar)); ?>" 
                                 alt="<?php echo e($note->user->name); ?>" 
                                 loading="lazy"
                                 class="w-6 h-6 rounded-full object-cover">
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-xs font-semibold text-gray-600"><?php echo e(substr($note->user->name, 0, 1)); ?></span>
                    <?php endif; ?>
                </div>
                <span class="group-hover:text-blue-600"><?php echo e($note->user->name); ?></span>
            </a>
            <span class="text-xs text-gray-500"><?php echo e($note->created_at->diffForHumans()); ?></span>
        </div>
    </div>
</div>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/home/partials/note-card.blade.php ENDPATH**/ ?>