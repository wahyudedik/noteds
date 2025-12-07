<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['plan' => null, 'compact' => false]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['plan' => null, 'compact' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $user = auth()->user();
    $activeSubscription = $user?->activeBuyerSubscription();
    $discount = $user?->getSubscriptionDiscount() ?? 0;
?>

<?php if($activeSubscription || $plan): ?>
<div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
    <div class="flex items-start gap-3">
        <div class="flex-shrink-0">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
        </div>
        <div class="flex-1">
            <?php if($activeSubscription): ?>
                <p class="text-sm font-semibold text-gray-900 mb-1">
                    <?php echo e($activeSubscription->plan->name); ?> Member
                </p>
                <p class="text-xs text-gray-600">
                    You're saving <?php echo e($discount); ?>% on all purchases with your subscription!
                </p>
            <?php elseif($plan): ?>
                <p class="text-sm font-semibold text-gray-900 mb-1">
                    Subscribe to <?php echo e($plan->name); ?> Plan
                </p>
                <p class="text-xs text-gray-600">
                    Get <?php echo e(match($plan->slug) { 'basic' => '10%', 'pro' => '20%', 'enterprise' => '30%', default => '0%' }); ?> off on all purchases
                </p>
            <?php endif; ?>
        </div>
        <?php if(!$activeSubscription && !$compact): ?>
        <a href="<?php echo e(route('subscriptions.index')); ?>" 
           class="text-xs font-medium text-blue-600 hover:text-blue-700 whitespace-nowrap">
            View Plans →
        </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\subscription-benefits.blade.php ENDPATH**/ ?>