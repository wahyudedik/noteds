<?php $__env->startSection('title', __('messages.home') . ' - ' . config('app.name')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <?php echo e(__('messages.welcome_back', ['name' => auth()->user()->name])); ?>

            </h1>
            <p class="mt-2 text-base text-gray-600">
                <?php echo e(__('messages.personalized_feed_description')); ?>

            </p>
        </div>

        <!-- Featured Hero -->
        <?php if(isset($featuredHero) && $featuredHero): ?>
            <div class="mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <span class="inline-block px-3 py-1 bg-white rounded-full text-xs font-semibold text-orange-600 mb-2">⭐ <?php echo e(__('messages.featured_note')); ?></span>
                        <h3 class="text-2xl font-bold text-white mb-2">
                            <a href="<?php echo e(route('marketplace.show', $featuredHero->note)); ?>" 
                               class="hover:underline">
                                <?php echo e($featuredHero->note->title); ?>

                            </a>
                        </h3>
                        <p class="text-white/90 mb-3"><?php echo e(Str::limit($featuredHero->note->summary ?? strip_tags($featuredHero->note->content), 100)); ?></p>
                        <div class="flex items-center gap-4">
                            <?php if($featuredHero->note->price > 0): ?>
                                <span class="text-white font-semibold"><?php echo e(currency($featuredHero->note->price)); ?></span>
                            <?php else: ?>
                                <span class="text-white font-semibold"><?php echo e(__('messages.free')); ?></span>
                            <?php endif; ?>
                            <a href="<?php echo e(route('marketplace.show', $featuredHero->note)); ?>" 
                               class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition">
                                <?php echo e(__('messages.view_note')); ?> →
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Recently Viewed Notes -->
        <?php if(isset($recentlyViewed) && count($recentlyViewed) > 0): ?>
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">📚 <?php echo e(__('messages.recently_viewed')); ?></h2>
                    <a href="<?php echo e(route('reading-history.index')); ?>" class="text-sm text-blue-600 hover:text-blue-800">
                        <?php echo e(__('messages.view_all')); ?> →
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $recentlyViewed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('home.partials.note-card', ['note' => $note], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Personalized Recommendations -->
        <?php if(isset($recommendations) && count($recommendations) > 0): ?>
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">✨ <?php echo e(__('messages.recommended_for_you')); ?></h2>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php echo e(__('messages.based_on_your_interests')); ?>

                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo $__env->make('home.partials.note-card', ['note' => $note], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Category Preferences -->
        <?php if(isset($preferences) && $preferences && ($preferences->preferred_categories || $preferences->preferred_tags)): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 <?php echo e(__('messages.your_interests')); ?></h2>
                <div class="bg-white rounded-lg border border-gray-200 p-6">
                    <?php if($preferences->preferred_categories && count($preferences->preferred_categories) > 0): ?>
                        <div class="mb-4">
                            <h3 class="text-sm font-semibold text-gray-700 mb-2"><?php echo e(__('messages.preferred_categories')); ?></h3>
                            <div class="flex flex-wrap gap-2">
                                <?php $__currentLoopData = $preferences->preferred_categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e(ucfirst($category)); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if($preferences->preferred_tags && count($preferences->preferred_tags) > 0): ?>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-700 mb-2"><?php echo e(__('messages.preferred_tags')); ?></h3>
                            <div class="flex flex-wrap gap-2">
                                <?php
                                    $tags = \App\Models\Tag::whereIn('id', $preferences->preferred_tags)->get();
                                ?>
                                <?php $__currentLoopData = $tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?php echo e($tag->name); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="mt-4">
                        <a href="<?php echo e(route('profile.edit')); ?>" class="text-sm text-blue-600 hover:text-blue-800">
                            <?php echo e(__('messages.update_preferences')); ?> →
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Featured Carousel -->
        <?php if(isset($featuredCarousel) && $featuredCarousel->count() > 0): ?>
            <div class="mb-8">
                <h2 class="text-xl font-bold text-gray-900 mb-4">⭐ <?php echo e(__('messages.featured_notes')); ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $featuredCarousel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php ($note = $featured->note); ?>
                        <?php echo $__env->make('home.partials.note-card', ['note' => $note, 'isFeatured' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Browse Marketplace CTA -->
        <div class="text-center py-8">
            <a href="<?php echo e(route('marketplace.index')); ?>" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                <?php echo e(__('messages.browse_marketplace')); ?> →
            </a>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\home\personalized.blade.php ENDPATH**/ ?>