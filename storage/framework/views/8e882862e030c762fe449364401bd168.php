<?php $__env->startSection('title', __('messages.dashboard_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Message for New Users -->
        <?php if(session('just_registered') || (auth()->user()->created_at->diffInDays(now()) <= 1 && auth()->user()->notes()->count() === 0)): ?>
            <div class="mb-6 bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 rounded-xl border-2 border-blue-200 p-6 shadow-lg" id="welcome-banner">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                🎉 Selamat Datang di Noteds, <?php echo e(auth()->user()->name); ?>!
                            </h2>
                            <p class="text-base text-gray-700 mb-4">
                                Platform terbaik untuk menjual dan membeli catatan digital. Mari mulai perjalanan Anda!
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                <div class="bg-white rounded-lg p-3 border border-blue-100">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="font-semibold text-gray-900 text-sm">Buat Note Pertama</span>
                                    </div>
                                    <p class="text-xs text-gray-600">Mulai menulis dan jual catatan digital Anda</p>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-green-100">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                        </svg>
                                        <span class="font-semibold text-gray-900 text-sm">Jelajahi Marketplace</span>
                                    </div>
                                    <p class="text-xs text-gray-600">Temukan catatan menarik dari seller lain</p>
                                </div>
                                <div class="bg-white rounded-lg p-3 border border-yellow-100">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-semibold text-gray-900 text-sm">Top Up Wallet</span>
                                    </div>
                                    <p class="text-xs text-gray-600">Isi saldo untuk mulai berbelanja</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <?php if(auth()->user()->role === 'seller' || auth()->user()->hasRole('admin')): ?>
                                    <a href="<?php echo e(route('notes.create')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg shadow-md hover:from-blue-700 hover:to-indigo-700 hover:shadow-lg transform hover:scale-105 transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Buat Note Pertama
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo e(route('marketplace.index')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border-2 border-gray-300 hover:border-blue-500 hover:text-blue-600 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                    Jelajahi Marketplace
                                </a>
                                <button onclick="document.getElementById('welcome-banner').style.display='none'; sessionStorage.setItem('welcome_banner_dismissed', 'true');" class="inline-flex items-center gap-2 px-4 py-2.5 text-gray-600 hover:text-gray-800 font-medium rounded-lg hover:bg-gray-100 transition-all duration-200">
                                    Nanti saja
                                </button>
                            </div>
                        </div>
                    </div>
                    <button onclick="document.getElementById('welcome-banner').style.display='none'; sessionStorage.setItem('welcome_banner_dismissed', 'true');" class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.welcome')); ?>, <?php echo e(auth()->user()->name); ?>!</h1>
                        <?php if(auth()->user()->hasRole('admin')): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                👑 <?php echo e(__('messages.admin', [], app()->getLocale())); ?>

                            </span>
                        <?php elseif(auth()->user()->hasRole('seller')): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                💰 <?php echo e(__('messages.seller')); ?>

                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                🛒 <?php echo e(__('messages.buyer')); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                    <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.dashboard_subtitle')); ?></p>
                </div>
                
                <!-- Currency & Timezone Selector -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <!-- Currency Selector -->
                    <form action="<?php echo e(route('locale.set-currency')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <select name="currency" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="IDR" <?php echo e((auth()->user()->currency ?? 'IDR') === 'IDR' ? 'selected' : ''); ?>><?php echo e(__('messages.currency_option_idr')); ?></option>
                            <option value="USD" <?php echo e((auth()->user()->currency ?? 'IDR') === 'USD' ? 'selected' : ''); ?>><?php echo e(__('messages.currency_option_usd')); ?></option>
                        </select>
                    </form>
                    
                    <!-- Timezone Selector -->
                    <form action="<?php echo e(route('locale.set-timezone')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <select name="timezone" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="Asia/Jakarta" <?php echo e((auth()->user()->timezone ?? 'Asia/Jakarta') === 'Asia/Jakarta' ? 'selected' : ''); ?>><?php echo e(__('messages.timezone_option_wib')); ?></option>
                            <option value="Asia/Riyadh" <?php echo e((auth()->user()->timezone ?? 'Asia/Jakarta') === 'Asia/Riyadh' ? 'selected' : ''); ?>><?php echo e(__('messages.timezone_option_ast')); ?></option>
                            <option value="UTC" <?php echo e((auth()->user()->timezone ?? 'Asia/Jakarta') === 'UTC' ? 'selected' : ''); ?>><?php echo e(__('messages.timezone_option_utc')); ?></option>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_notes')); ?></p>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo e(auth()->user()->notes()->count()); ?>

                            <?php if(!auth()->user()->hasPremium()): ?>
                                / <?php echo e(auth()->user()->getNoteCreationLimit()); ?>

                            <?php endif; ?>
                        </p>
                        <?php if(!auth()->user()->hasPremium()): ?>
                            <p class="text-xs text-gray-500 mt-1"><?php echo e(__('messages.basic_plan')); ?></p>
                        <?php else: ?>
                            <p class="text-xs text-green-600 mt-1">✓ <?php echo e(__('messages.unlimited')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.public_notes')); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(auth()->user()->notes()->where('is_public', true)->count()); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.wallet_balance')); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(currency(auth()->user()->wallet_balance ?? 0, auth()->user()->currency)); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow duration-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_sales')); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(auth()->user()->transactionsAsSeller()->where('status', 'success')->count()); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php if(auth()->user()->role === 'seller' || auth()->user()->hasRole('admin')): ?>
                <a href="<?php echo e(route('notes.create')); ?>" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3 group-hover:bg-blue-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200"><?php echo e(__('messages.create_note')); ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?php echo e(__('messages.start_writing_note')); ?></p>
                    </div>
                </div>
            </a>
            <?php endif; ?>

            <a href="<?php echo e(route('marketplace.index')); ?>" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3 group-hover:bg-green-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200"><?php echo e(__('messages.browse_marketplace')); ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?php echo e(__('messages.discover_public_notes')); ?></p>
                    </div>
                </div>
            </a>

            <a href="<?php echo e(route('wallet.index')); ?>" class="bg-white overflow-hidden shadow-sm rounded-lg p-6 border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3 group-hover:bg-yellow-200 transition-colors duration-200">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v2a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200"><?php echo e(__('messages.manage_wallet')); ?></h3>
                        <p class="text-sm text-gray-500 mt-1"><?php echo e(__('messages.topup_withdraw_funds')); ?></p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Recent Notes -->
        <?php
            $recentNotes = auth()->user()->notes()->latest()->limit(5)->get();
        ?>

        <?php if($recentNotes->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.recent_notes')); ?></h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php $__currentLoopData = $recentNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <a href="<?php echo e(route('notes.show', $note)); ?>" class="text-base font-medium text-gray-900 hover:text-blue-600 transition-colors duration-200">
                                        <?php echo e($note->title); ?>

                                    </a>
                                    <p class="mt-1 text-sm text-gray-500"><?php echo Str::limit(strip_tags($note->content), 80); ?></p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <?php if($note->is_public): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                <?php echo e(__('messages.public')); ?>

                                            </span>
                                        <?php endif; ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo e($note->status); ?>

                                        </span>
                                        <span class="text-xs text-gray-500"><?php echo e(localized_diff_for_humans($note->created_at)); ?></span>
                                    </div>
                                </div>
                    <div class="ml-4">
                        <a href="<?php echo e(route('notes.show', $note)); ?>" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                            <?php echo e(__('messages.view')); ?> →
                        </a>
                    </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    <a href="<?php echo e(route('notes.index')); ?>" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                        <?php echo e(__('messages.view_all_notes')); ?> →
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 text-center py-12 px-6">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900"><?php echo e(__('messages.no_notes_yet')); ?></h3>
                <p class="mt-2 text-sm text-gray-500"><?php echo e(__('messages.start_creating')); ?></p>
                <?php if(auth()->user()->role === 'seller' || auth()->user()->hasRole('admin')): ?>
                    <div class="mt-6">
                        <a href="<?php echo e(route('notes.create')); ?>" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <?php echo e(__('messages.create_note')); ?>

                        </a>
                    </div>
                <?php else: ?>
                    <div class="mt-6">
                        <p class="text-sm text-gray-600 mb-3"><?php echo e(__('messages.seller_only_feature_notice')); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/dashboard.blade.php ENDPATH**/ ?>