<?php $__env->startSection('title', __('buyer.analytics.title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8"><?php echo e(__('buyer.analytics.title')); ?></h1>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400"><?php echo e(__('buyer.analytics.total_purchased')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($totalPurchased); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400"><?php echo e(__('buyer.analytics.total_spent')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(currency($totalSpent)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400"><?php echo e(__('buyer.analytics.total_downloads')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e($totalDownloads); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 dark:bg-yellow-900 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400"><?php echo e(__('buyer.analytics.completion_rate')); ?></p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($completionRate, 1)); ?>%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reading Time Tracking -->
        <?php if($totalReadingProgress > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.reading_time_tracking')); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.total_reading_time')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">
                        <?php if($totalReadingTimeHours >= 1): ?>
                            <?php echo e(number_format($totalReadingTimeHours, 1)); ?> <?php echo e(__('buyer.analytics.hours')); ?>

                        <?php else: ?>
                            <?php echo e(number_format($totalReadingTimeMinutes, 1)); ?> <?php echo e(__('buyer.analytics.minutes')); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.average_reading_time')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($averageReadingTimePerNote); ?> <?php echo e(__('buyer.analytics.minutes')); ?></p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><?php echo e(__('buyer.analytics.per_note')); ?></p>
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.notes_with_progress')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($totalReadingProgress); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Completion Rate Per Note -->
        <?php if($completionRatePerNote->count() > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.completion_rate_per_note')); ?></h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?php echo e(__('buyer.analytics.note')); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?php echo e(__('buyer.analytics.completion_rate')); ?></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider"><?php echo e(__('buyer.analytics.reading_time')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php $__currentLoopData = $completionRatePerNote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white"><?php echo e($item['note_title']); ?></td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                        <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" style="width: <?php echo e($item['completion_rate']); ?>%"></div>
                                    </div>
                                    <span class="text-sm text-gray-900 dark:text-white"><?php echo e($item['completion_rate']); ?>%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                <?php if($item['reading_time_minutes'] > 0): ?>
                                    <?php echo e(number_format($item['reading_time_minutes'], 1)); ?> <?php echo e(__('buyer.analytics.minutes')); ?>

                                <?php else: ?>
                                    <?php echo e(__('buyer.analytics.not_started')); ?>

                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Favorite Categories/Topics -->
        <?php if($favoriteCategories->count() > 0 || $favoriteTopics->count() > 0): ?>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <?php if($favoriteCategories->count() > 0): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.favorite_categories')); ?></h2>
                <div class="space-y-3">
                    <?php $__currentLoopData = $favoriteCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($category->name); ?></span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" style="width: <?php echo e(($category->note_count / max($totalPurchased, 1)) * 100); ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white w-8 text-right"><?php echo e($category->note_count); ?></span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($favoriteTopics->count() > 0): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.favorite_topics')); ?></h2>
                <div class="space-y-3">
                    <?php $__currentLoopData = $favoriteTopics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">#<?php echo e($topic->name); ?></span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full" style="width: <?php echo e(($topic->note_count / max($totalPurchased, 1)) * 100); ?>%"></div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white w-8 text-right"><?php echo e($topic->note_count); ?></span>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Spending Patterns -->
        <?php if($monthlySpending->count() > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.spending_patterns')); ?></h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Monthly Spending -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('buyer.analytics.monthly_spending')); ?></h3>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $monthlySpending; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($month['month_name']); ?> <?php echo e($month['year']); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($month['count']); ?> <?php echo e(__('buyer.analytics.purchases')); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(currency($month['total'])); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('buyer.analytics.avg')); ?>: <?php echo e(currency($month['average'])); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>

                <!-- Spending by Category -->
                <?php if($spendingByCategory->count() > 0): ?>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('buyer.analytics.spending_by_category')); ?></h3>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $spendingByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($item->category_name); ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400"><?php echo e($item->purchase_count); ?> <?php echo e(__('buyer.analytics.purchases')); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-bold text-gray-900 dark:text-white"><?php echo e(currency($item->total_spent)); ?></p>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Wishlist Analytics -->
        <?php if($totalCollections > 0 || $totalWishlistNotes > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.wishlist_analytics')); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.total_collections')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($totalCollections); ?></p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.total_wishlist_notes')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($totalWishlistNotes); ?></p>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2"><?php echo e(__('buyer.analytics.avg_per_collection')); ?></p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e($totalCollections > 0 ? round($totalWishlistNotes / $totalCollections, 1) : 0); ?></p>
                </div>
            </div>

            <?php if($wishlistByCollection->count() > 0): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('buyer.analytics.collections_breakdown')); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php $__currentLoopData = $wishlistByCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $collection): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($collection->name); ?></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1"><?php echo e($collection->notes_count); ?> <?php echo e(__('buyer.analytics.notes')); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if($wishlistCategories->count() > 0): ?>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><?php echo e(__('buyer.analytics.wishlist_categories')); ?></h3>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $wishlistCategories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm font-medium">
                        <?php echo e($item->category_name); ?> (<?php echo e($item->note_count); ?>)
                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Purchases -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e(__('buyer.analytics.recent_purchases')); ?></h2>
                <a href="<?php echo e(route('buyer-analytics.purchase-history')); ?>" class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                    <?php echo e(__('buyer.analytics.view_all')); ?>

                </a>
            </div>
            <?php if($recentPurchases->count() > 0): ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $recentPurchases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center flex-1">
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($purchase->note->title); ?></h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                        <?php echo e(__('buyer.analytics.purchased_on', [
                                            'date' => $purchase->purchased_at->format('d M Y'),
                                            'time' => $purchase->purchased_at->format('H:i')
                                        ])); ?>

                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e(currency($purchase->purchase_price)); ?></p>
                                <a href="<?php echo e(route('marketplace.show', $purchase->note)); ?>" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300">
                                    <?php echo e(__('buyer.analytics.view_note')); ?>

                                </a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-600 dark:text-gray-400 py-8">
                    <p class="font-medium"><?php echo e(__('buyer.analytics.empty_recent_title')); ?></p>
                    <p class="text-sm mt-1"><?php echo e(__('buyer.analytics.empty_recent_message')); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Categories (Legacy - for backward compatibility) -->
        <?php if(isset($categories) && $categories->count() > 0 && $totalPurchased > 0): ?>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6"><?php echo e(__('buyer.analytics.top_categories')); ?></h2>
                <div class="space-y-3">
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?php echo e($category->name); ?></span>
                            <div class="flex items-center">
                                <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-3">
                                    <div class="bg-blue-600 dark:bg-blue-500 h-2 rounded-full" style="width: <?php echo e(($category->count / $totalPurchased) * 100); ?>%"></div>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white w-8 text-right"><?php echo e($category->count); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\buyer\analytics\index.blade.php ENDPATH**/ ?>