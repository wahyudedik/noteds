<?php $__env->startSection('title', __('messages.tuts') . ' — ' . __('messages.education_creative_coding')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.tuts')); ?></h1>
            <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.tuts_description')); ?></p>
        </div>

        <!-- Search and Filter Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="<?php echo e(route('tuts.index')); ?>" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.search')); ?></label>
                        <input type="text" name="search" id="search" value="<?php echo e(request('search')); ?>" 
                            placeholder="<?php echo e(__('messages.search_tutorials')); ?>"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.category')); ?></label>
                        <select name="category" id="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('messages.all_categories')); ?></option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($key); ?>" <?php echo e(request('category') === $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label for="featured" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.filter')); ?></label>
                        <select name="featured" id="featured" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value=""><?php echo e(__('messages.all_tutorials')); ?></option>
                            <option value="1" <?php echo e(request('featured') === '1' ? 'selected' : ''); ?>><?php echo e(__('messages.featured_only')); ?></option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                            <?php echo e(__('messages.filter')); ?>

                        </button>
                        <?php if(request()->hasAny(['search', 'category', 'featured'])): ?>
                            <a href="<?php echo e(route('tuts.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                <?php echo e(__('messages.clear')); ?>

                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Categories Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4"><?php echo e(__('messages.categories')); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('tuts.index', ['category' => $key])); ?>" 
                       class="p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                                <h3 class="font-semibold text-gray-900"><?php echo e($label); ?></h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <?php
                                $count = \App\Models\Tutorial::published()->category($key)->count();
                            ?>
                            <?php echo e($count); ?> <?php echo e($count === 1 ? __('messages.tutorial') : __('messages.tutorials')); ?>

                        </p>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <!-- Tutorials Grid -->
        <?php if($tutorials->count() > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $tutorials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tutorial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('tuts.show', $tutorial)); ?>" class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow border border-gray-200 overflow-hidden group">
                        <?php if($tutorial->thumbnail): ?>
                            <div class="aspect-video w-full overflow-hidden bg-gray-100">
                                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($tutorial->thumbnail)); ?>" alt="<?php echo e($tutorial->title); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        <?php else: ?>
                            <div class="aspect-video w-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?php echo e($tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : ''); ?>

                                    <?php echo e($tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : ''); ?>

                                    <?php echo e($tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : ''); ?>

                                    <?php echo e($tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : ''); ?>">
                                    <?php echo e($tutorial->category_label); ?>

                                </span>
                                <?php if($tutorial->featured): ?>
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                                <?php echo e($tutorial->title); ?>

                            </h3>
                            <?php if($tutorial->description): ?>
                                <p class="text-sm text-gray-600 mb-4 line-clamp-2">
                                    <?php echo e($tutorial->description); ?>

                                </p>
                            <?php endif; ?>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                <span><?php echo e($tutorial->author->name); ?></span>
                                <div class="flex items-center gap-2">
                                    <span><?php echo e(number_format($tutorial->views_count)); ?> <?php echo e(__('messages.views')); ?></span>
                                    <span>•</span>
                                    <span><?php echo e($tutorial->created_at->diffForHumans()); ?></span>
                                </div>
                            </div>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <?php echo e($tutorials->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white shadow-sm rounded-lg p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php echo e(__('messages.no_tutorials_found')); ?></h3>
                <p class="text-gray-600"><?php echo e(__('messages.try_adjusting_search')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\tuts\index.blade.php ENDPATH**/ ?>