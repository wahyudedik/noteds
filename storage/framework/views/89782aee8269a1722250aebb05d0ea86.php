<?php $__env->startSection('title', 'Share Analytics - Earn Commission'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Share Analytics</h1>
            <p class="mt-2 text-base text-gray-600">Track your note shares and earnings from commission</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Shares -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.885 12.938 9 12.482 9 12c0-.482-.115-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Shares</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e($stats['total_shares']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Clicks -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Clicks</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['total_clicks'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Purchases -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Purchases</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['total_purchases'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Commission Earned -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Commission Earned</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo e(currency($stats['total_commission_earned'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Generated Card -->
        <div class="bg-gradient-to-r from-green-500 to-emerald-600 overflow-hidden shadow-sm rounded-lg border border-green-500 mb-8">
            <div class="p-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-2">Total Revenue Generated</h2>
                        <p class="text-green-100 mb-4">Total purchase value from your shared notes</p>
                        <p class="text-4xl font-bold text-white"><?php echo e(currency($stats['total_revenue_generated'])); ?></p>
                    </div>
                    <div class="flex-shrink-0">
                        <svg class="w-24 h-24 text-white opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Share Referrals List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">Your Shared Notes</h2>
                <p class="text-sm text-gray-600 mt-1">Track performance for each note you've shared</p>
            </div>

            <?php if($shareReferrals->count() > 0): ?>
                <div class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $shareReferrals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $shareReferral): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-3">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <?php if($shareReferral['note']): ?>
                                                <a href="<?php echo e(route('marketplace.show', $shareReferral['note']->id)); ?>" class="hover:text-blue-600">
                                                    <?php echo e($shareReferral['note']->title); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-500">Note deleted</span>
                                            <?php endif; ?>
                                        </h3>
                                        <?php if($shareReferral['note']): ?>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                <?php echo e(currency($shareReferral['note']->price)); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Stats Grid -->
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                        <div class="bg-blue-50 rounded-lg p-3">
                                            <p class="text-xs text-gray-600 mb-1">Clicks</p>
                                            <p class="text-lg font-bold text-blue-600"><?php echo e(number_format($shareReferral['click_count'])); ?></p>
                                        </div>
                                        <div class="bg-green-50 rounded-lg p-3">
                                            <p class="text-xs text-gray-600 mb-1">Purchases</p>
                                            <p class="text-lg font-bold text-green-600"><?php echo e(number_format($shareReferral['purchase_count'])); ?></p>
                                        </div>
                                        <div class="bg-purple-50 rounded-lg p-3">
                                            <p class="text-xs text-gray-600 mb-1">Commission</p>
                                            <p class="text-lg font-bold text-purple-600"><?php echo e(currency($shareReferral['total_commission_earned'])); ?></p>
                                        </div>
                                        <div class="bg-yellow-50 rounded-lg p-3">
                                            <p class="text-xs text-gray-600 mb-1">Revenue</p>
                                            <p class="text-lg font-bold text-yellow-600"><?php echo e(currency($shareReferral['total_revenue_generated'])); ?></p>
                                        </div>
                                    </div>

                                    <!-- Share Link -->
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <code class="text-xs text-gray-700 flex-1 mr-3 truncate"><?php echo e($shareReferral['share_url']); ?></code>
                                        <button onclick="copyToClipboard('<?php echo e($shareReferral['share_url']); ?>')" 
                                            class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Copy
                                        </button>
                                    </div>

                                    <!-- Purchase Details -->
                                    <?php if(isset($shareReferral['purchases']) && $shareReferral['purchases']->count() > 0): ?>
                                        <div class="mt-4">
                                            <p class="text-sm font-medium text-gray-700 mb-2">Recent Purchases:</p>
                                            <div class="space-y-2">
                                                <?php $__currentLoopData = $shareReferral['purchases']->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $purchase): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <div class="flex items-center justify-between text-xs bg-gray-50 rounded p-2">
                                                        <div>
                                                            <span class="font-medium"><?php echo e($purchase->buyer->name ?? 'Unknown'); ?></span>
                                                            <span class="text-gray-500">purchased for</span>
                                                            <span class="font-semibold"><?php echo e(currency($purchase->purchase_amount)); ?></span>
                                                        </div>
                                                        <div class="text-gray-500">
                                                            <?php echo e($purchase->created_at->diffForHumans()); ?>

                                                        </div>
                                                    </div>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($shareReferral['purchases']->count() > 3): ?>
                                                    <p class="text-xs text-gray-500 text-center">
                                                        +<?php echo e($shareReferral['purchases']->count() - 3); ?> more purchases
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4 text-right">
                                    <p class="text-xs text-gray-500 mb-1">Shared on</p>
                                    <p class="text-sm font-medium text-gray-900"><?php echo e($shareReferral['created_at']->format('M d, Y')); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.885 12.938 9 12.482 9 12c0-.482-.115-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No shares yet</h3>
                    <p class="text-sm text-gray-600 mb-6">Start sharing notes to earn commission!</p>
                    <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Browse Marketplace
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Copied!',
                        text: 'Share link copied to clipboard',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('Link copied to clipboard!');
                }
            }).catch(function(err) {
                console.error('Failed to copy:', err);
            });
        } else {
            // Fallback
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            document.body.appendChild(textArea);
            textArea.select();
            try {
                document.execCommand('copy');
                alert('Link copied to clipboard!');
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            document.body.removeChild(textArea);
        }
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/share/analytics.blade.php ENDPATH**/ ?>