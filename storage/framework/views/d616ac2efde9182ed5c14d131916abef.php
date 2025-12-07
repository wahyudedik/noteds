<?php $__env->startSection('title', __('messages.my_wallet')); ?>

<?php $__env->startSection('content'); ?>
<?php
    $currencyService = app(\App\Services\CurrencyService::class);
    $baseCurrency = $currencyService->getBaseCurrency();
    $userCurrency = $currencyService->getUserCurrency(auth()->user());
    $walletCurrency = $wallet->currency ?? $baseCurrency;
    $currencyInfo = \App\Helpers\CurrencyHelper::getCurrencyInfo($userCurrency);
    $topupMinBase = 10000;
    $topupMaxBase = 100000000;
    $topupMinDisplay = $currencyService->convert($topupMinBase, $baseCurrency, $userCurrency);
    $topupMaxDisplay = $currencyService->convert($topupMaxBase, $baseCurrency, $userCurrency);
    $withdrawMinBase = 50000;
    $withdrawMinDisplay = $currencyService->convert($withdrawMinBase, $baseCurrency, $userCurrency);
    $decimalPlaces = $currencyInfo['decimal_places'] ?? 0;
    $stepValue = $decimalPlaces > 0 ? 1 / (10 ** $decimalPlaces) : 1;
    $stepAttribute = number_format($stepValue, $decimalPlaces, '.', '');
    $minAttribute = number_format($topupMinDisplay, $decimalPlaces, '.', '');
    $maxAttribute = number_format($topupMaxDisplay, $decimalPlaces, '.', '');
    $currencySymbol = $currencyInfo['symbol'] ?? '';
?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.my_wallet')); ?></h1>
            <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.manage_balance_view_history')); ?></p>
        </div>

        <!-- Flash Messages -->
        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Wallet Balance Card -->
        <div class="bg-gradient-to-br from-green-50 via-blue-50 to-indigo-50 overflow-hidden shadow-lg rounded-xl border border-green-200 mb-8">
            <div class="px-6 py-8">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <!-- Balance Display -->
                    <div class="flex-1">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl p-4 shadow-md">
                                <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('messages.wallet_balance_title')); ?></p>
                                <p class="text-5xl font-bold text-gray-900 tracking-tight">
                                    <?php echo e(currency($wallet->balance, $userCurrency, $walletCurrency)); ?>

                                </p>
                                <p class="text-xs text-gray-500 mt-1"><?php echo e(__('messages.available_balance')); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
                        <form action="<?php echo e(route('wallet.topup')); ?>" method="POST" class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto flex-1">
                            <?php echo csrf_field(); ?>
                            <div class="relative flex-1 min-w-[200px]">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5"><?php echo e(__('messages.enter_amount')); ?></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm font-medium"><?php echo e($currencySymbol); ?></span>
                                    </div>
                                    <input
                                        type="number"
                                        name="amount"
                                        min="<?php echo e($minAttribute); ?>"
                                        max="<?php echo e($maxAttribute); ?>"
                                        step="<?php echo e($stepAttribute); ?>"
                                        value="<?php echo e(old('amount')); ?>"
                                        placeholder="0"
                                        required
                                        class="block w-full pl-10 pr-4 py-2.5 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 text-sm h-[42px]">
                                </div>
                                
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md hover:shadow-lg transition-all duration-200 whitespace-nowrap w-full sm:w-auto h-[42px]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <?php echo e(__('messages.top_up')); ?>

                                </button>
                            </div>
                        </form>
                        <div class="flex items-end">
                            <?php if($wallet->balance >= 50000): ?>
                                <a href="<?php echo e(route('wallet.withdraw.create')); ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-md hover:shadow-lg transition-all duration-200 w-full sm:w-auto h-[42px]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <?php echo e(__('messages.withdraw')); ?>

                                </a>
                            <?php else: ?>
                                <button type="button" disabled title="<?php echo e(__('messages.minimum_withdraw', ['amount' => currency($withdrawMinBase, $userCurrency, $baseCurrency)])); ?>" class="inline-flex items-center justify-center px-6 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-white bg-gray-400 cursor-not-allowed shadow-sm transition-all duration-200 opacity-60 w-full sm:w-auto h-[42px]">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <?php echo e(__('messages.withdraw')); ?>

                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction History -->
        <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="bg-blue-100 rounded-lg p-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.transaction_history')); ?></h2>
                    </div>
                    <span class="text-sm text-gray-500"><?php echo e($transactions->total()); ?> <?php echo e(__('messages.transactions')); ?></span>
                </div>
            </div>
            <div class="p-6">
                <?php if($transactions->count() > 0): ?>
                    <div class="overflow-x-auto -mx-6 px-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?php echo e(__('messages.date')); ?></th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?php echo e(__('messages.type')); ?></th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?php echo e(__('messages.description')); ?></th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?php echo e(__('messages.amount')); ?></th>
                                    <th class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"><?php echo e(__('messages.status')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <?php echo e($transaction->created_at->format('d M Y, H:i')); ?>

                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($transaction->buyer_id === auth()->id() && $transaction->seller_id === auth()->id()): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                    </svg>
                                                    Top-up
                                                </span>
                                            <?php elseif($transaction->buyer_id === auth()->id()): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Purchase
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Sale
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php if($transaction->note): ?>
                                                <a href="<?php echo e(route('marketplace.show', $transaction->note)); ?>" class="text-blue-600 hover:text-blue-700 hover:underline transition-colors duration-200 font-medium">
                                                    <?php echo e($transaction->note->title); ?>

                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-600"><?php echo e($transaction->notes ?? '-'); ?></span>
                                            <?php endif; ?>
                                            <?php if($transaction->buyer_id !== auth()->id() && $transaction->seller_id === auth()->id()): ?>
                                                <p class="text-xs text-gray-500 mt-1">Buyer: <?php echo e($transaction->buyer->name); ?></p>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php
                                                $transactionCurrency = $transaction->currency ?? $baseCurrency;
                                                $formattedAmount = currency($transaction->amount, $userCurrency, $transactionCurrency);
                                            ?>
                                            <?php if($transaction->buyer_id === auth()->id() && $transaction->seller_id === auth()->id()): ?>
                                                <span class="text-sm font-bold text-green-600">+<?php echo e($formattedAmount); ?></span>
                                            <?php elseif($transaction->buyer_id === auth()->id()): ?>
                                                <span class="text-sm font-bold text-red-600">-<?php echo e($formattedAmount); ?></span>
                                            <?php else: ?>
                                                <?php
                                                    $netAmount = currency($transaction->amount - $transaction->commission, $userCurrency, $transactionCurrency);
                                                    $commissionAmount = currency($transaction->commission, $userCurrency, $transactionCurrency);
                                                ?>
                                                <div>
                                                    <span class="text-sm font-bold text-green-600">+<?php echo e($netAmount); ?></span>
                                                    <p class="text-xs text-gray-500">Commission: <?php echo e($commissionAmount); ?></p>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($transaction->status === 'success'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Success
                                                </span>
                                            <?php elseif($transaction->status === 'pending'): ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                    </svg>
                                                    Pending
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                    </svg>
                                                    Failed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        <?php echo e($transactions->links()); ?>

                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('messages.no_transactions_yet')); ?></h3>
                        <p class="mt-2 text-sm text-gray-500">Start by topping up your wallet or purchasing notes from the marketplace.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\wallet\index.blade.php ENDPATH**/ ?>