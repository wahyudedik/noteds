<?php $__env->startSection('title', __('messages.topup_checkout')); ?>

<?php $__env->startSection('content'); ?>
    <!-- CRITICAL: Define functions IMMEDIATELY before any content to ensure they're available -->
    <script>
        // Define variables and functions in global scope IMMEDIATELY (before page loads)
        window.snapToken = '<?php echo e($snapToken); ?>';
        window.snapClientKey = '<?php echo e(config('services.midtrans.client_key')); ?>';
        window.snapLoaded = false;
        window.snapPayCalled = false;
        window.snapRetryCount = 0;
        window.snapMaxRetries = 10;

        // Define functions IMMEDIATELY so they're available when Snap.js onload fires
        window.initSnapPayment = function() {
            if (window.snapPayCalled) {
                console.log('snap.pay() already called, skipping...');
                return;
            }
            window.snapLoaded = true;
            setTimeout(function() {
                if (window.snapPayCalled) {
                    console.log('snap.pay() already called, skipping...');
                    return;
                }
                if (typeof window.snap !== 'undefined' && window.snap.pay) {
                    try {
                        window.snapPayCalled = true;
                        window.snap.pay(window.snapToken, {
                            onSuccess: function(result) {
                                console.log('Payment success:', result);
                                // Redirect to payment.finish to check status and update wallet
                                const orderId = result.order_id ||
                                    '<?php echo e($transaction->midtrans_order_id); ?>';
                                window.location.href = '<?php echo e(route('payment.finish')); ?>?order_id=' +
                                    encodeURIComponent(orderId);
                            },
                            onPending: function(result) {
                                console.log('Payment pending:', result);
                                // IMPORTANT: Even for pending status, redirect to payment.finish
                                // to check actual status from Midtrans API (for sandbox, pending might be success)
                                const orderId = result.order_id ||
                                    '<?php echo e($transaction->midtrans_order_id); ?>';
                                window.location.href = '<?php echo e(route('payment.finish')); ?>?order_id=' +
                                    encodeURIComponent(orderId);
                            },
                            onError: function(result) {
                                console.log('Payment error:', result);
                                if (result && (result.status_code === '404' || result
                                        .status_message)) {
                                    alert(
                                        'Payment method tidak tersedia atau terjadi error. Silakan pilih payment method lain atau coba lagi.'
                                    );
                                }
                                window.location.href = '<?php echo e(route('wallet.index')); ?>?status=error';
                            },
                            onClose: function() {
                                console.log('Customer closed payment popup');
                                window.location.href =
                                    '<?php echo e(route('wallet.index')); ?>?status=cancelled';
                            }
                        });
                    } catch (error) {
                        console.error('Error initializing Snap payment:', error);
                        window.snapPayCalled = false;
                        if (error.message && error.message.includes('Invalid state transition')) {
                            console.log('Snap popup already open, waiting for user interaction...');
                            return;
                        }
                        document.getElementById('snap-container').innerHTML = `
                            <div class="text-center p-6">
                                <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">Gagal memuat payment gateway</h3>
                                <p class="mt-2 text-sm text-gray-500">${error.message}</p>
                                <a href="<?php echo e(route('wallet.index')); ?>" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Kembali ke Wallet
                                </a>
                            </div>
                        `;
                    }
                } else {
                    if (window.snapRetryCount < window.snapMaxRetries) {
                        window.snapRetryCount++;
                        setTimeout(window.initSnapPayment, 500);
                    } else {
                        window.handleSnapError();
                    }
                }
            }, 500);
        };

        window.handleSnapError = function() {
            document.getElementById('snap-container').innerHTML = `
                <div class="text-center p-6">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Gagal memuat payment gateway</h3>
                    <p class="mt-2 text-sm text-gray-500">Tidak dapat memuat Midtrans Snap.js. Silakan coba lagi atau hubungi support.</p>
                    <div class="mt-4 space-y-2">
                        <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Muat Ulang Halaman
                        </button>
                        <a href="<?php echo e(route('wallet.index')); ?>" class="block mt-2 text-sm text-blue-600 hover:text-blue-700">
                            Kembali ke Wallet
                        </a>
                    </div>
                </div>
            `;
        };
    </script>

    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.topup_checkout')); ?></h1>
                <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.complete_wallet_topup_payment')); ?></p>
            </div>

            <!-- Payment Details Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.payment_summary')); ?></h2>
                </div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.topup_amount')); ?></p>
                                <p class="text-3xl font-bold text-green-600">Rp
                                    <?php echo e(number_format($transaction->amount, 0, ',', '.')); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-3">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="font-medium text-gray-900"><?php echo e(__('messages.secure_payment_by_midtrans')); ?></span>
                        </div>
                        <p class="text-xs text-gray-500 ml-7"><?php echo e(__('messages.redirected_to_secure_payment')); ?></p>
                        <?php if(!config('services.midtrans.is_production', false)): ?>
                            <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-xs text-yellow-800">
                                    <strong>⚠️ Testing Mode (Sandbox):</strong> Beberapa payment method seperti BCA KlikPay
                                    mungkin tidak tersedia. Gunakan Credit Card, Bank Transfer, atau Virtual Account untuk
                                    testing.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div id="snap-container"
                        class="min-h-[200px] flex items-center justify-center bg-gray-50 rounded-lg border border-gray-200 border-dashed">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 animate-spin" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <p class="mt-4 text-sm text-gray-600"><?php echo e(__('messages.loading_payment_gateway')); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-medium text-blue-800 mb-1"><?php echo e(__('messages.payment_instructions')); ?></h3>
                        <ul class="list-disc list-inside text-sm text-blue-700 space-y-1">
                            <li><?php echo e(__('messages.select_payment_method')); ?></li>
                            <li><?php echo e(__('messages.complete_payment_instructed')); ?></li>
                            <li><?php echo e(__('messages.wallet_balance_updated_automatically')); ?></li>
                            <li><?php echo e(__('messages.if_payment_pending_wait')); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Snap.js script IMMEDIATELY after functions are defined -->
    <script>
        // Load Snap.js immediately (functions are already defined above)
        (function() {
            function loadSnapScript() {
                // Check if script already exists to prevent duplicate loading
                const existingScript = document.querySelector('script[src*="snap.js"]');
                if (existingScript) {
                    console.log('Snap.js already loaded, using existing script');
                    // If script already loaded, try to initialize
                    if (typeof window.snap !== 'undefined' && typeof window.initSnapPayment === 'function' && !window
                        .snapPayCalled) {
                        setTimeout(window.initSnapPayment, 500);
                    }
                    return;
                }

                const script = document.createElement('script');
                script.src =
                    '<?php echo e(config('services.midtrans.is_production', false) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js'); ?>';
                script.setAttribute('data-client-key', window.snapClientKey);
                script.onload = function() {
                    console.log('Snap.js loaded successfully');
                    // Verify function exists before calling
                    if (typeof window.initSnapPayment === 'function' && !window.snapPayCalled) {
                        window.initSnapPayment();
                    } else {
                        console.warn('initSnapPayment not available or already called');
                    }
                };
                script.onerror = function() {
                    console.error('Failed to load Snap.js');
                    if (typeof window.handleSnapError === 'function') {
                        window.handleSnapError();
                    }
                };

                // Append to head immediately
                if (document.head) {
                    document.head.appendChild(script);
                } else {
                    // Fallback: wait for head to be available
                    document.addEventListener('DOMContentLoaded', function() {
                        document.head.appendChild(script);
                    });
                }
            }

            // Load immediately if head is available, otherwise wait
            if (document.head) {
                loadSnapScript();
            } else {
                document.addEventListener('DOMContentLoaded', loadSnapScript);
            }
        })();

        // Fallback: Check if snap is loaded after page load
        // Only run if snap.pay() hasn't been called yet
        document.addEventListener('DOMContentLoaded', function() {
            if (!window.snapLoaded && !window.snapPayCalled) {
                setTimeout(function() {
                    // Double check before calling
                    if (window.snapPayCalled) {
                        return;
                    }

                    if (typeof window.snap === 'undefined') {
                        if (window.snapRetryCount < window.snapMaxRetries) {
                            window.snapRetryCount++;
                            setTimeout(window.initSnapPayment, 500);
                        } else {
                            window.handleSnapError();
                        }
                    } else {
                        window.initSnapPayment();
                    }
                }, 1000);
            }
        });

        // Check client key
        if (!window.snapClientKey || window.snapClientKey === '') {
            console.error('Midtrans Client Key is not configured!');
            window.handleSnapError();
        }
    </script>

    <?php $__env->startPush('scripts'); ?>
        <!-- Additional scripts if needed -->
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\wallet\topup-checkout.blade.php ENDPATH**/ ?>