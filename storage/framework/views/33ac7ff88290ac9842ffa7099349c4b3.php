<?php $__env->startSection('title', 'My Subscription'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">My Subscription</h1>
            <p class="text-gray-600">Manage your subscription and billing</p>
        </div>

        <?php if(session('success')): ?>
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
            <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
        </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
            <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
        </div>
        <?php endif; ?>

        <!-- Active Subscription -->
        <?php if($subscription && $subscription->isActive()): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                        <?php if($subscription->is_gift): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                            Gift Subscription
                        </span>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-1"><?php echo e($subscription->plan->name); ?> Plan</h2>
                    <p class="text-gray-600"><?php echo e($subscription->plan->description); ?></p>
                </div>
            </div>

            <!-- Subscription Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-1">Billing Cycle</p>
                    <p class="text-lg font-semibold text-gray-900 capitalize"><?php echo e($subscription->billing_cycle); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-1">Price</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e(currency($subscription->price)); ?> / <?php echo e($subscription->billing_cycle === 'monthly' ? 'month' : 'year'); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-1">Current Period</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo e($subscription->current_period_start->format('M d, Y')); ?> - <?php echo e($subscription->current_period_end->format('M d, Y')); ?>

                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500 mb-1">Next Billing Date</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo e($subscription->next_billing_date ? $subscription->next_billing_date->format('M d, Y') : 'N/A'); ?>

                    </p>
                </div>
            </div>

            <!-- Auto-renewal Status -->
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">Auto-renewal</p>
                        <p class="text-sm text-gray-600">
                            <?php if($subscription->auto_renew): ?>
                                Your subscription will automatically renew on <?php echo e($subscription->next_billing_date->format('M d, Y')); ?>

                            <?php else: ?>
                                Auto-renewal is disabled. Your subscription will expire on <?php echo e($subscription->current_period_end->format('M d, Y')); ?>

                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-4">
                <a href="<?php echo e(route('subscriptions.index')); ?>" 
                   class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Change Plan
                </a>
                <?php if($subscription->auto_renew): ?>
                <form action="<?php echo e(route('subscriptions.cancel', $subscription)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" 
                            onclick="return confirm('Are you sure you want to cancel your subscription? You will continue to have access until <?php echo e($subscription->current_period_end->format('M d, Y')); ?>.')"
                            class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors">
                        Cancel Subscription
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Benefits Display -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Your Subscription Benefits</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    <span class="text-gray-700">Unlimited access to premium notes</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-700"><?php echo e(auth()->user()->getSubscriptionDiscount()); ?>% discount on all purchases</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-gray-700">Early access to new features</span>
                </div>
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-gray-700">Priority customer support</span>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- No Active Subscription -->
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Active Subscription</h3>
            <p class="text-gray-600 mb-6">Subscribe to get unlimited access to premium notes and exclusive discounts.</p>
            <a href="<?php echo e(route('subscriptions.index')); ?>" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Browse Plans
            </a>
        </div>
        <?php endif; ?>

        <!-- Subscription History -->
        <?php if($subscriptions->count() > 0): ?>
        <div class="bg-white rounded-xl shadow-lg p-8 mt-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6">Subscription History</h3>
            <div class="space-y-4">
                <?php $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                    <div>
                        <p class="font-semibold text-gray-900"><?php echo e($sub->plan->name); ?> Plan</p>
                        <p class="text-sm text-gray-600">
                            <?php echo e($sub->started_at->format('M d, Y')); ?> - 
                            <?php if($sub->current_period_end): ?>
                                <?php echo e($sub->current_period_end->format('M d, Y')); ?>

                            <?php else: ?>
                                Ongoing
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-gray-900"><?php echo e(currency($sub->price)); ?></p>
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                            <?php echo e($sub->status === 'active' ? 'bg-green-100 text-green-800' : 
                               ($sub->status === 'cancelled' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800')); ?>">
                            <?php echo e(ucfirst($sub->status)); ?>

                        </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <?php echo e($subscriptions->links()); ?>

        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\subscriptions\my-subscription.blade.php ENDPATH**/ ?>