<?php $__env->startSection('title', 'Complete Payment'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Complete Your Payment</h1>
            
            <div class="mb-6">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600">Plan</span>
                        <span class="font-semibold text-gray-900"><?php echo e($subscription->plan->name); ?> (<?php echo e(ucfirst($subscription->billing_cycle)); ?>)</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Amount</span>
                        <span class="text-2xl font-bold text-gray-900"><?php echo e(currency($subscription->price)); ?></span>
                    </div>
                </div>
            </div>

            <?php if($subscription->midtrans_token): ?>
            <div id="snap-container" class="mb-6">
                <!-- Midtrans Snap will be rendered here -->
            </div>
            <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800">Payment token not available. Please try again.</p>
            </div>
            <?php endif; ?>

            <div class="text-center">
                <a href="<?php echo e(route('subscriptions.index')); ?>" class="text-blue-600 hover:text-blue-700">
                    Cancel and return to plans
                </a>
            </div>
        </div>
    </div>
</div>

<?php if($subscription->midtrans_token): ?>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?php echo e(config('services.midtrans.client_key')); ?>"></script>
<script>
    window.snap.pay('<?php echo e($subscription->midtrans_token); ?>', {
        onSuccess: function(result) {
            window.location.href = '<?php echo e(route('subscriptions.my-subscription')); ?>?payment=success';
        },
        onPending: function(result) {
            window.location.href = '<?php echo e(route('subscriptions.my-subscription')); ?>?payment=pending';
        },
        onError: function(result) {
            window.location.href = '<?php echo e(route('subscriptions.my-subscription')); ?>?payment=error';
        },
        onClose: function() {
            // User closed the popup without finishing the payment
        }
    });
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\subscriptions\payment.blade.php ENDPATH**/ ?>