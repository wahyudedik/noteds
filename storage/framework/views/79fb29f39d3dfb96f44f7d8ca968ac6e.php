<?php $__env->startSection('title', 'Subscription Plans'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Choose Your Plan</h1>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                Get unlimited access to premium notes with our subscription plans. Cancel anytime.
            </p>
        </div>

        <?php if($activeSubscription): ?>
        <div class="mb-8 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-900">
                        You currently have an active subscription: <strong><?php echo e($activeSubscription->plan->name); ?></strong>
                    </p>
                    <p class="text-sm text-blue-700 mt-1">
                        Expires: <?php echo e($activeSubscription->current_period_end->format('M d, Y')); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('subscriptions.my-subscription')); ?>" 
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Manage Subscription
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Subscription Plans -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white rounded-xl shadow-lg border-2 <?php echo e($plan->slug === 'pro' ? 'border-blue-500 ring-4 ring-blue-100' : 'border-gray-200'); ?> overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <?php if($plan->slug === 'pro'): ?>
                <div class="bg-blue-500 text-white text-center py-2 text-sm font-semibold">
                    Most Popular
                </div>
                <?php endif; ?>

                <div class="p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo e($plan->name); ?></h3>
                    <p class="text-gray-600 mb-6"><?php echo e($plan->description); ?></p>

                    <!-- Pricing -->
                    <div class="mb-6">
                        <div class="flex items-baseline">
                            <span class="text-4xl font-bold text-gray-900">
                                <?php echo e(currency($plan->monthly_price)); ?>

                            </span>
                            <span class="text-gray-600 ml-2">/month</span>
                        </div>
                        <div class="mt-2">
                            <span class="text-lg text-gray-700">
                                <?php echo e(currency($plan->yearly_price)); ?>

                            </span>
                            <span class="text-gray-600 ml-2">/year</span>
                            <span class="ml-2 text-green-600 font-semibold">
                                Save <?php echo e($plan->yearly_discount_percent); ?>%
                            </span>
                        </div>
                        <?php if($plan->yearly_discount_percent > 0): ?>
                        <p class="text-sm text-gray-500 mt-1">
                            Save <?php echo e(currency($plan->getYearlySavings())); ?> per year
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Features -->
                    <ul class="space-y-3 mb-8">
                        <?php $__currentLoopData = $plan->features ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-700"><?php echo e($feature); ?></span>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                    <!-- CTA Button -->
                    <?php if($activeSubscription && $activeSubscription->plan_id === $plan->id): ?>
                    <button disabled class="w-full py-3 px-4 bg-gray-300 text-gray-600 rounded-lg font-semibold cursor-not-allowed">
                        Current Plan
                    </button>
                    <?php else: ?>
                    <a href="<?php echo e(route('subscriptions.show', $plan)); ?>" 
                       class="block w-full py-3 px-4 <?php echo e($plan->slug === 'pro' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-900 hover:bg-gray-800'); ?> text-white text-center rounded-lg font-semibold transition-colors">
                        <?php echo e($activeSubscription ? 'Switch Plan' : 'Get Started'); ?>

                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Benefits Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Subscription Benefits</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Unlimited Access</h3>
                    <p class="text-sm text-gray-600">Access all premium notes without limits</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Exclusive Discounts</h3>
                    <p class="text-sm text-gray-600">Get 10-30% off on all purchases</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Early Access</h3>
                    <p class="text-sm text-gray-600">Be the first to try new features</p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Priority Support</h3>
                    <p class="text-sm text-gray-600">Get help faster with priority support</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Frequently Asked Questions</h2>
            <div class="space-y-4 max-w-3xl mx-auto">
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Can I cancel anytime?</h3>
                    <p class="text-gray-600">Yes, you can cancel your subscription at any time. You'll continue to have access until the end of your billing period.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">What payment methods are accepted?</h3>
                    <p class="text-gray-600">We accept wallet payments and Midtrans (credit card, bank transfer, e-wallet).</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Do subscriptions auto-renew?</h3>
                    <p class="text-gray-600">Yes, subscriptions automatically renew unless you cancel. You can disable auto-renewal anytime.</p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900 mb-2">Can I upgrade or downgrade my plan?</h3>
                    <p class="text-gray-600">Yes, you can change your plan anytime. We'll prorate the difference for upgrades or downgrades.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\subscriptions\plans.blade.php ENDPATH**/ ?>