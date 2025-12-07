

<?php $__env->startSection('title', 'Leaderboard Settings'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 flex items-center gap-3">
                        <svg class="w-10 h-10 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Leaderboard Settings
                    </h1>
                    <p class="mt-2 text-gray-600">Configure points, rewards, and leaderboard behavior</p>
                </div>
                <a href="<?php echo e(route('admin.dashboard')); ?>"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </a>
            </div>

            <!-- Alerts -->
            <?php if($errors->any()): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-red-900">Validation Errors</h3>
                            <ul class="mt-2 space-y-1 text-sm text-red-800">
                                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li>• <?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-green-900"><?php echo e(session('success')); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('admin.leaderboard-settings.update')); ?>" class="space-y-6">
                <?php echo csrf_field(); ?>

                <!-- Points Configuration Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Points Configuration
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="share_points_per_share" class="block text-sm font-medium text-gray-700 mb-2">
                                    Points per Share
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['share_points_per_share'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="share_points_per_share" name="share_points_per_share"
                                        value="<?php echo e($settingsData['share_points_per_share']); ?>" required min="0">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">pts</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Poin yang didapat user ketika share produk</p>
                                <?php $__errorArgs = ['share_points_per_share'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="share_points_per_click" class="block text-sm font-medium text-gray-700 mb-2">
                                    Points per Click
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['share_points_per_click'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="share_points_per_click" name="share_points_per_click"
                                        value="<?php echo e($settingsData['share_points_per_click']); ?>" required min="0">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">pts</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Poin yang didapat ketika link di-klik</p>
                                <?php $__errorArgs = ['share_points_per_click'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="share_points_per_purchase" class="block text-sm font-medium text-gray-700 mb-2">
                                    Points per Purchase
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent <?php $__errorArgs = ['share_points_per_purchase'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="share_points_per_purchase" name="share_points_per_purchase"
                                        value="<?php echo e($settingsData['share_points_per_purchase']); ?>" required min="0">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">pts</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Poin yang didapat ketika ada pembelian</p>
                                <?php $__errorArgs = ['share_points_per_purchase'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Leaderboard Configuration Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Leaderboard Configuration
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="leaderboard_monthly_point_cap"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly Point Cap
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent <?php $__errorArgs = ['leaderboard_monthly_point_cap'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="leaderboard_monthly_point_cap" name="leaderboard_monthly_point_cap"
                                        value="<?php echo e($settingsData['leaderboard_monthly_point_cap']); ?>" required
                                        min="0">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">pts</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Maksimal poin yang bisa dikumpulkan per bulan</p>
                                <?php $__errorArgs = ['leaderboard_monthly_point_cap'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="leaderboard_monthly_target"
                                    class="block text-sm font-medium text-gray-700 mb-2">
                                    Monthly Target
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-9 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent <?php $__errorArgs = ['leaderboard_monthly_target'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="leaderboard_monthly_target" name="leaderboard_monthly_target"
                                        value="<?php echo e($settingsData['leaderboard_monthly_target']); ?>" required
                                        min="0">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">pts</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Target poin bulanan untuk leaderboard</p>
                                <?php $__errorArgs = ['leaderboard_monthly_target'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="leaderboard_reset_day" class="block text-sm font-medium text-gray-700 mb-2">
                                    Reset Day (Monthly)
                                </label>
                                <div class="relative">
                                    <input type="number"
                                        class="w-full pr-10 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent <?php $__errorArgs = ['leaderboard_reset_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="leaderboard_reset_day" name="leaderboard_reset_day"
                                        value="<?php echo e($settingsData['leaderboard_reset_day']); ?>" required min="1"
                                        max="31">
                                    <span
                                        class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">day</span>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hari dalam bulan untuk reset leaderboard (1-31)</p>
                                <?php $__errorArgs = ['leaderboard_reset_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex items-end">
                                <label
                                    class="flex items-center gap-3 p-4 bg-amber-50 rounded-lg border border-amber-200 w-full cursor-pointer hover:bg-amber-100 transition-colors">
                                    <input type="checkbox" id="duplicate_share_prevention"
                                        name="duplicate_share_prevention" value="1"
                                        <?php echo e($settingsData['duplicate_share_prevention'] ? 'checked' : ''); ?>

                                        class="w-5 h-5 text-amber-600 rounded focus:ring-2 focus:ring-amber-500">
                                    <div>
                                        <p class="font-medium text-gray-900">Prevent Duplicate Shares</p>
                                        <p class="text-sm text-gray-600">Satu produk hanya bisa di-share 1x untuk mendapat
                                            poin</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Monthly Rewards Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Monthly Rewards
                        </h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="monthly_reward_rank_1" class="block text-sm font-medium text-gray-700 mb-2">
                                    🥇 Reward for Rank 1
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">Rp</span>
                                    <input type="number"
                                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['monthly_reward_rank_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="monthly_reward_rank_1" name="monthly_reward_rank_1"
                                        value="<?php echo e($settingsData['monthly_reward_rank_1']); ?>" required min="0">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hadiah untuk juara 1</p>
                                <?php $__errorArgs = ['monthly_reward_rank_1'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="monthly_reward_rank_2" class="block text-sm font-medium text-gray-700 mb-2">
                                    🥈 Reward for Rank 2
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">Rp</span>
                                    <input type="number"
                                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['monthly_reward_rank_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="monthly_reward_rank_2" name="monthly_reward_rank_2"
                                        value="<?php echo e($settingsData['monthly_reward_rank_2']); ?>" required min="0">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hadiah untuk juara 2</p>
                                <?php $__errorArgs = ['monthly_reward_rank_2'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="monthly_reward_rank_3" class="block text-sm font-medium text-gray-700 mb-2">
                                    🥉 Reward for Rank 3
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">Rp</span>
                                    <input type="number"
                                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['monthly_reward_rank_3'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="monthly_reward_rank_3" name="monthly_reward_rank_3"
                                        value="<?php echo e($settingsData['monthly_reward_rank_3']); ?>" required min="0">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hadiah untuk juara 3</p>
                                <?php $__errorArgs = ['monthly_reward_rank_3'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="monthly_reward_top_10" class="block text-sm font-medium text-gray-700 mb-2">
                                    ⭐ Reward for Top 4-10
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">Rp</span>
                                    <input type="number"
                                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['monthly_reward_top_10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="monthly_reward_top_10" name="monthly_reward_top_10"
                                        value="<?php echo e($settingsData['monthly_reward_top_10']); ?>" required min="0">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hadiah untuk top 4-10</p>
                                <?php $__errorArgs = ['monthly_reward_top_10'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div>
                                <label for="monthly_reward_top_50" class="block text-sm font-medium text-gray-700 mb-2">
                                    ✨ Reward for Top 11-50
                                </label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">Rp</span>
                                    <input type="number"
                                        class="w-full pl-11 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['monthly_reward_top_50'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                        id="monthly_reward_top_50" name="monthly_reward_top_50"
                                        value="<?php echo e($settingsData['monthly_reward_top_50']); ?>" required min="0">
                                </div>
                                <p class="mt-1 text-sm text-gray-500">Hadiah untuk top 11-50</p>
                                <?php $__errorArgs = ['monthly_reward_top_50'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>

                            <div class="flex items-end">
                                <label
                                    class="flex items-center gap-3 p-4 bg-green-50 rounded-lg border border-green-200 w-full cursor-pointer hover:bg-green-100 transition-colors">
                                    <input type="checkbox" id="auto_transfer_rewards" name="auto_transfer_rewards"
                                        value="1" <?php echo e($settingsData['auto_transfer_rewards'] ? 'checked' : ''); ?>

                                        class="w-5 h-5 text-green-600 rounded focus:ring-2 focus:ring-green-500">
                                    <div>
                                        <p class="font-medium text-gray-900">Auto Transfer Rewards</p>
                                        <p class="text-sm text-gray-600">Otomatis transfer hadiah ke user tiap bulan</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200">
                            <label for="reward_transfer_day" class="block text-sm font-medium text-gray-700 mb-2">
                                Reward Transfer Day
                            </label>
                            <div class="relative max-w-xs">
                                <input type="number"
                                    class="w-full pr-10 pl-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent <?php $__errorArgs = ['reward_transfer_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                    id="reward_transfer_day" name="reward_transfer_day"
                                    value="<?php echo e($settingsData['reward_transfer_day']); ?>" required min="1"
                                    max="31">
                                <span
                                    class="absolute right-3 top-2.5 text-gray-600 text-sm font-medium pointer-events-none">day</span>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Hari dalam bulan untuk transfer hadiah (1-31)</p>
                            <?php $__errorArgs = ['reward_transfer_day'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>

                <!-- System Settings Card -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white flex items-center gap-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            System Settings
                        </h2>
                    </div>
                    <div class="p-6">
                        <label
                            class="flex items-center gap-3 p-4 bg-purple-50 rounded-lg border border-purple-200 cursor-pointer hover:bg-purple-100 transition-colors">
                            <input type="checkbox" id="leaderboard_enabled" name="leaderboard_enabled" value="1"
                                <?php echo e($settingsData['leaderboard_enabled'] ? 'checked' : ''); ?>

                                class="w-5 h-5 text-purple-600 rounded focus:ring-2 focus:ring-purple-500">
                            <div>
                                <p class="font-medium text-gray-900">Enable Leaderboard</p>
                                <p class="text-sm text-gray-600">Aktifkan/nonaktifkan sistem leaderboard</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex gap-3 pb-8">
                    <button type="submit"
                        class="px-8 py-3 bg-gradient-to-r from-violet-600 to-violet-700 hover:from-violet-700 hover:to-violet-800 text-white font-semibold rounded-lg transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                    <a href="<?php echo e(route('admin.dashboard')); ?>"
                        class="px-8 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        input[type="number"]::-webkit-outer-spin-button,
        input[type="number"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/admin/leaderboard-settings/index.blade.php ENDPATH**/ ?>