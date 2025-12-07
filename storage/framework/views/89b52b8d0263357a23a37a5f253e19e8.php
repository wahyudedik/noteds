<?php $__env->startSection('title', 'Leaderboards'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Leaderboards</h1>
            <p class="mt-2 text-base text-gray-600">See who's leading the platform in sales, purchases, and contributions!</p>
        </div>

        <!-- Type Selector -->
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="<?php echo e(route('leaderboard.index', ['type' => 'sellers', 'metric' => $type === 'sellers' ? $metric : 'revenue', 'period' => $period])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($type === 'sellers' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                Top Sellers
            </a>
            <a href="<?php echo e(route('leaderboard.index', ['type' => 'buyers', 'metric' => $type === 'buyers' ? $metric : 'purchases', 'period' => $period])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($type === 'buyers' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                Top Buyers
            </a>
            <a href="<?php echo e(route('leaderboard.index', ['type' => 'contributors', 'metric' => $type === 'contributors' ? $metric : 'reviews', 'period' => $period])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($type === 'contributors' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                Top Contributors
            </a>
        </div>

        <!-- Metric Selector (based on type) -->
        <?php if($type === 'sellers'): ?>
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'sellers', 'metric' => 'revenue', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'revenue' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Revenue
                </a>
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'sellers', 'metric' => 'sales', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'sales' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Sales Count
                </a>
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'sellers', 'metric' => 'ratings', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'ratings' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Ratings
                </a>
            </div>
        <?php elseif($type === 'buyers'): ?>
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'buyers', 'metric' => 'purchases', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'purchases' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Purchase Count
                </a>
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'buyers', 'metric' => 'spending', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'spending' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Spending
                </a>
            </div>
        <?php elseif($type === 'contributors'): ?>
            <div class="mb-6 flex flex-wrap gap-2">
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'contributors', 'metric' => 'reviews', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'reviews' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Reviews
                </a>
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'contributors', 'metric' => 'forum', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'forum' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Forum Posts
                </a>
                <a href="<?php echo e(route('leaderboard.index', ['type' => 'contributors', 'metric' => 'shares', 'period' => $period])); ?>" 
                   class="px-4 py-2 rounded-lg font-semibold <?php echo e($metric === 'shares' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                    By Shares
                </a>
            </div>
        <?php endif; ?>

        <!-- Period Selector -->
        <div class="mb-6 flex flex-wrap gap-2">
            <a href="<?php echo e(route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'weekly'])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($period === 'weekly' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                Weekly
            </a>
            <a href="<?php echo e(route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'monthly'])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($period === 'monthly' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                Monthly
            </a>
            <a href="<?php echo e(route('leaderboard.index', ['type' => $type, 'metric' => $metric, 'period' => 'all-time'])); ?>" 
               class="px-4 py-2 rounded-lg font-semibold <?php echo e($period === 'all-time' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'); ?>">
                All-Time
            </a>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h2 class="text-lg font-semibold text-gray-900">
                    <?php echo e($title); ?> - <?php echo e(ucfirst($period)); ?>

                    <?php if($type === 'sellers' && $metric === 'revenue'): ?>
                        (By Revenue)
                    <?php elseif($type === 'sellers' && $metric === 'sales'): ?>
                        (By Sales Count)
                    <?php elseif($type === 'sellers' && $metric === 'ratings'): ?>
                        (By Ratings)
                    <?php elseif($type === 'buyers' && $metric === 'purchases'): ?>
                        (By Purchase Count)
                    <?php elseif($type === 'buyers' && $metric === 'spending'): ?>
                        (By Spending)
                    <?php elseif($type === 'contributors' && $metric === 'reviews'): ?>
                        (By Reviews)
                    <?php elseif($type === 'contributors' && $metric === 'forum'): ?>
                        (By Forum Posts)
                    <?php elseif($type === 'contributors' && $metric === 'shares'): ?>
                        (By Shares)
                    <?php endif; ?>
                </h2>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <?php if($type === 'sellers' && $metric === 'revenue'): ?>
                                        Total Revenue
                                    <?php elseif($type === 'sellers' && $metric === 'sales'): ?>
                                        Sales Count
                                    <?php elseif($type === 'sellers' && $metric === 'ratings'): ?>
                                        Average Rating
                                    <?php elseif($type === 'buyers' && $metric === 'purchases'): ?>
                                        Purchase Count
                                    <?php elseif($type === 'buyers' && $metric === 'spending'): ?>
                                        Total Spending
                                    <?php elseif($type === 'contributors' && $metric === 'reviews'): ?>
                                        Review Count
                                    <?php elseif($type === 'contributors' && $metric === 'forum'): ?>
                                        Post Count
                                    <?php elseif($type === 'contributors' && $metric === 'shares'): ?>
                                        Share Count
                                    <?php endif; ?>
                                </th>
                                <?php if($type === 'sellers' && $metric === 'ratings'): ?>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-lg font-semibold text-gray-900">
                                            <?php if($entry['rank'] == 1): ?> 🥇
                                            <?php elseif($entry['rank'] == 2): ?> 🥈
                                            <?php elseif($entry['rank'] == 3): ?> 🥉
                                            <?php endif; ?>
                                            #<?php echo e($entry['rank']); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($entry['user']): ?>
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full" src="<?php echo e($entry['user']->avatar_url); ?>" alt="<?php echo e($entry['user']->name); ?>">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="<?php echo e(route('public.profile.show', $entry['user']->username)); ?>" class="hover:text-blue-600">
                                                            <?php echo e($entry['user']->name); ?>

                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">@ <?php echo e($entry['user']->username); ?></div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">Unknown User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php if(isset($entry['total_revenue'])): ?>
                                            <?php echo e(currency($entry['total_revenue'])); ?>

                                        <?php elseif(isset($entry['sales_count'])): ?>
                                            <?php echo e(number_format($entry['sales_count'])); ?> sales
                                        <?php elseif(isset($entry['average_rating'])): ?>
                                            <div class="flex items-center">
                                                <span class="text-lg font-bold"><?php echo e(number_format($entry['average_rating'], 1)); ?></span>
                                                <svg class="w-5 h-5 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            </div>
                                        <?php elseif(isset($entry['purchase_count'])): ?>
                                            <?php echo e(number_format($entry['purchase_count'])); ?> purchases
                                        <?php elseif(isset($entry['total_spending'])): ?>
                                            <?php echo e(currency($entry['total_spending'])); ?>

                                        <?php elseif(isset($entry['review_count'])): ?>
                                            <?php echo e(number_format($entry['review_count'])); ?> reviews
                                        <?php elseif(isset($entry['post_count'])): ?>
                                            <?php echo e(number_format($entry['post_count'])); ?> posts
                                        <?php elseif(isset($entry['share_count'])): ?>
                                            <?php echo e(number_format($entry['share_count'])); ?> shares
                                        <?php endif; ?>
                                    </td>
                                    <?php if($type === 'sellers' && $metric === 'ratings'): ?>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo e(number_format($entry['review_count'] ?? 0)); ?> reviews
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="<?php echo e($type === 'sellers' && $metric === 'ratings' ? '4' : '3'); ?>" class="px-6 py-4 text-center text-sm text-gray-500">
                                        No data available for this period.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\leaderboard\index.blade.php ENDPATH**/ ?>