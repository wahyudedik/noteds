<?php $__env->startSection('title', 'Share Leaderboard - Earn Points'); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e($title); ?></h1>
                <p class="mt-2 text-base text-gray-600">Compete with other users and earn monthly rewards!</p>
            </div>

            <!-- Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="<?php echo e(route('share.leaderboard', ['type' => 'monthly', 'month' => $month])); ?>"
                            class="<?php echo e($type === 'monthly' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Monthly Leaderboard
                        </a>
                        <a href="<?php echo e(route('share.leaderboard', ['type' => 'alltime'])); ?>"
                            class="<?php echo e($type === 'alltime' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            All-Time Leaderboard
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Month Selector (for monthly view) -->
            <?php if($type === 'monthly'): ?>
                <div class="mb-6">
                    <form method="GET" action="<?php echo e(route('share.leaderboard')); ?>" class="flex items-center gap-4">
                        <input type="hidden" name="type" value="monthly">
                        <label for="month" class="text-sm font-medium text-gray-700">Select Month:</label>
                        <input type="month" name="month" id="month" value="<?php echo e($month); ?>"
                            class="rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            onchange="this.form.submit()">
                    </form>
                </div>
            <?php endif; ?>

            <!-- User Stats Card -->
            <?php if(auth()->guard()->check()): ?>
                <div class="mb-6 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium opacity-90">Your Points</p>
                            <p class="text-3xl font-bold mt-1"><?php echo e(number_format($userPoints)); ?></p>
                        </div>
                        <div class="text-right">
                            <?php if($userRank): ?>
                                <p class="text-sm font-medium opacity-90">Your Rank</p>
                                <p class="text-3xl font-bold mt-1">#<?php echo e($userRank); ?></p>
                            <?php else: ?>
                                <p class="text-sm font-medium opacity-90">Keep sharing to rank!</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Leaderboard Table -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Rank
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Points
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__empty_1 = true; $__currentLoopData = $leaderboard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr
                                    class="<?php echo e($entry['user'] && auth()->check() && $entry['user']->id === auth()->id() ? 'bg-blue-50' : 'hover:bg-gray-50'); ?>">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php if($entry['rank'] <= 3): ?>
                                                <span class="text-2xl mr-2">
                                                    <?php if($entry['rank'] === 1): ?>
                                                        🥇
                                                    <?php elseif($entry['rank'] === 2): ?>
                                                        🥈
                                                    <?php elseif($entry['rank'] === 3): ?>
                                                        🥉
                                                    <?php endif; ?>
                                                </span>
                                            <?php endif; ?>
                                            <span class="text-lg font-bold text-gray-900">#<?php echo e($entry['rank']); ?></span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($entry['user']): ?>
                                            <div class="flex items-center">
                                                <?php if($entry['user']->avatar): ?>
                                                    <img class="h-10 w-10 rounded-full mr-3"
                                                        src="<?php echo e(Storage::url($entry['user']->avatar)); ?>"
                                                        alt="<?php echo e($entry['user']->name); ?>">
                                                <?php else: ?>
                                                    <div
                                                        class="h-10 w-10 rounded-full bg-gray-300 mr-3 flex items-center justify-center">
                                                        <span
                                                            class="text-gray-600 font-medium"><?php echo e(substr($entry['user']->name, 0, 1)); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <a href="<?php echo e(route('public.profile.show', $entry['user']->username)); ?>"
                                                            class="hover:text-blue-600">
                                                            <?php echo e($entry['user']->name); ?>

                                                        </a>
                                                    </div>
                                                    <div class="text-sm text-gray-500">@ <?php echo e($entry['user']->username); ?>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">Unknown User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span
                                            class="text-lg font-bold text-gray-900"><?php echo e(number_format($entry['total_points'])); ?></span>
                                        <span class="text-sm text-gray-500 ml-1">pts</span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                        No data available for this period.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Info Section -->
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">How to Earn Points</h3>
                <ul class="space-y-2 text-blue-800">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Share a note:</strong> Earn <?php echo e(number_format($settings['share_points_per_share'])); ?>

                            points every time you share a note</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Get a click:</strong> Earn <?php echo e(number_format($settings['share_points_per_click'])); ?>

                            points when someone clicks your share link</span>
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span><strong>Generate a purchase:</strong> Earn
                            <?php echo e(number_format($settings['share_points_per_purchase'])); ?> points when someone buys through
                            your share link</span>
                    </li>
                </ul>
                <?php if($settings['leaderboard_monthly_point_cap'] > 0): ?>
                    <p class="mt-4 text-sm text-blue-700 bg-blue-100 p-2 rounded">
                        <strong>Monthly Cap:</strong> Maximum
                        <?php echo e(number_format($settings['leaderboard_monthly_point_cap'])); ?> points per month
                    </p>
                <?php endif; ?>
                <?php if($settings['duplicate_share_prevention']): ?>
                    <p class="mt-2 text-sm text-blue-700 bg-blue-100 p-2 rounded">
                        <strong>Duplicate Prevention:</strong> You can only earn points from sharing the same note once
                    </p>
                <?php endif; ?>
            </div>

            <!-- Monthly Rewards Info -->
            <?php if($type === 'monthly'): ?>
                <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-yellow-900 mb-3">Monthly Rewards</h3>
                    <p class="text-yellow-800 mb-3">Top sharers receive monthly cash rewards:</p>
                    <ul class="space-y-2 text-yellow-800">
                        <li>🥇 <strong>Rank 1:</strong> Rp <?php echo e(number_format($settings['monthly_reward_rank_1'])); ?></li>
                        <li>🥈 <strong>Rank 2:</strong> Rp <?php echo e(number_format($settings['monthly_reward_rank_2'])); ?></li>
                        <li>🥉 <strong>Rank 3:</strong> Rp <?php echo e(number_format($settings['monthly_reward_rank_3'])); ?></li>
                        <li>🏆 <strong>Rank 4-10:</strong> Rp <?php echo e(number_format($settings['monthly_reward_top_10'])); ?></li>
                        <li>⭐ <strong>Rank 11-50:</strong> Rp <?php echo e(number_format($settings['monthly_reward_top_50'])); ?></li>
                    </ul>
                    <p class="text-sm text-yellow-700 mt-4">Rewards are automatically distributed at the end of each month.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/share/leaderboard.blade.php ENDPATH**/ ?>