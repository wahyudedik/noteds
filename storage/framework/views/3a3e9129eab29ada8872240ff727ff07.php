<?php $__env->startSection('title', __('messages.marketplace_page_title', ['title' => $note->title])); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-8 sm:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <a href="<?php echo e(route('marketplace.index')); ?>"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <?php echo e(__('messages.back_to_marketplace')); ?>

                </a>
            </div>

            <!-- Flash Messages -->
            <?php if(session('error')): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800"><?php echo e(session('error')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if($conversation): ?>
                <?php
                    $otherUser =
                        $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
                    $lastMessage = $conversation->latestMessage;
                ?>
                <div
                    class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide mb-1">
                            <?php echo e(__('messages.product_conversation_title')); ?></h3>
                        <p class="text-sm text-blue-800">
                            <?php echo __('messages.product_conversation_description', ['name' => '<strong>' . e($otherUser->name) . '</strong>']); ?>

                            <?php if($lastMessage): ?>
                                <span class="block mt-1 text-xs text-blue-700">
                                    <?php echo e($lastMessage->sender_id === auth()->id()
                                        ? __('messages.product_conversation_last_message_you')
                                        : __('messages.product_conversation_last_message_other', ['name' => $lastMessage->sender->name])); ?>

                                    “<?php echo e(\Illuminate\Support\Str::limit($lastMessage->message, 80)); ?>”
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <a href="<?php echo e(route('note-conversations.show', $conversation)); ?>"
                        class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        <?php echo e(__('messages.product_conversation_open_chat')); ?>

                    </a>
                </div>
            <?php endif; ?>

            <!-- Note Details Card -->
            <div class="bg-white overflow-hidden shadow-lg rounded-xl border border-gray-200 mb-8">
                <div class="p-6 sm:p-8">
                    <!-- Badges and Rating -->
                    <div class="flex flex-wrap items-center gap-2 mb-5">
                        <?php if($note->is_public): ?>
                            <span
                                class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e(__('messages.public')); ?>

                            </span>
                        <?php endif; ?>
                        <span
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                            <?php echo e(ucfirst($note->status)); ?>

                        </span>
                        <?php if($note->average_rating > 0): ?>
                            <div
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-yellow-50 border border-yellow-200">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-3.5 h-3.5 <?php echo e($i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300'); ?>"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                <?php endfor; ?>
                                <span class="text-sm font-semibold text-gray-800 ml-0.5"><?php echo e($note->average_rating); ?></span>
                                <span class="text-xs text-gray-600">(<?php echo e($note->total_reviews); ?>

                                    <?php echo e($note->total_reviews == 1 ? __('messages.review') : __('messages.reviews_count')); ?>)</span>
                            </div>
                        <?php endif; ?>

                        <!-- Sale Mode Badge -->
                        <?php if($note->sale_mode): ?>
                            <?php if($note->isScarcityMode()): ?>
                                <div class="relative inline-block group">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200 cursor-help">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Scarcity Mode
                                    </span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                                        <div class="font-semibold mb-2">Scarcity Mode</div>
                                        <ul class="space-y-1 text-gray-300">
                                            <li>• Buyer hanya bisa beli 1x per user</li>
                                            <li>• Buyer bisa resell dengan harga custom</li>
                                            <li>• Original creator dapat komisi di setiap penjualan</li>
                                            <li>• Grace period <?php echo e($note->grace_period_days); ?> hari untuk pembelian ulang
                                            </li>
                                            <li>• Setelah grace period, harga = original ×
                                                <?php echo e($note->relist_price_multiplier); ?>x</li>
                                        </ul>
                                        <div
                                            class="absolute left-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                        </div>
                                    </div>
                                </div>
                            <?php elseif($note->isStandardMode()): ?>
                                <div class="relative inline-block group">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200 cursor-help">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Standard Mode - Multiple Sales
                                    </span>
                                    <!-- Tooltip -->
                                    <div
                                        class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                                        <div class="font-semibold mb-2">Standard Mode</div>
                                        <ul class="space-y-1 text-gray-300">
                                            <li>• Multiple sales (bisa dijual ke banyak buyer)</li>
                                            <li>• Buyer tidak bisa resell</li>
                                            <li>• Tidak ada komisi untuk original creator</li>
                                            <li>• Ownership tetap dengan seller</li>
                                            <li>• Cocok untuk konten yang perlu diakses ulang</li>
                                        </ul>
                                        <div
                                            class="absolute left-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Viral/Hot Badge -->
                        <?php if($note->isViral() || $note->isHot()): ?>
                            <div class="mt-2">
                                <?php if($note->isViral()): ?>
                                    <div class="relative inline-block group">
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-red-500 to-pink-600 text-white shadow-lg animate-pulse">
                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            🔥 VIRAL
                                        </span>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                                            <div class="font-semibold mb-2">🔥 Viral Note</div>
                                            <p class="text-gray-300">This note is trending! It has
                                                <?php echo e(number_format($note->views_24_hours)); ?> views in the last 24 hours.</p>
                                            <div
                                                class="absolute left-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif($note->isHot()): ?>
                                    <div class="relative inline-block group">
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                                            <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.16-.85-.434-1.675-.82-2.45a5.549 5.549 0 00-5.8-2.13A4.5 4.5 0 001 6.477v6c0 1.968.785 3.747 2.05 5.043a4.5 4.5 0 006.95-1.95c0-.64-.13-1.25-.36-1.81a5.389 5.389 0 01-.22-3.68 4.5 4.5 0 00-1.88-2.547 2.5 2.5 0 01-1.32-2.88 1.5 1.5 0 00-1.14-1.86 1.5 1.5 0 00-1.12.12c-1.24.82-2.27 1.9-3.01 3.18-.75 1.3-1.23 2.78-1.23 4.38 0 1.56.48 3.03 1.23 4.33.74 1.28 1.77 2.36 3.01 3.18a1.5 1.5 0 001.12.12c.5-.07.93-.46 1.14-1.86.2-1.4.6-2.88 1.32-2.88.72 0 1.12 1.48 1.32 2.88.21 1.4.64 1.79 1.14 1.86a1.5 1.5 0 001.12-.12c1.24-.82 2.27-1.9 3.01-3.18.75-1.3 1.23-2.78 1.23-4.33 0-1.6-.48-3.08-1.23-4.38z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            🔥 HOT
                                        </span>
                                        <!-- Tooltip -->
                                        <div
                                            class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                                            <div class="font-semibold mb-2">🔥 Hot Note</div>
                                            <p class="text-gray-300">This note is getting popular! It has
                                                <?php echo e(number_format($note->views_24_hours)); ?> views in the last 24 hours.</p>
                                            <div
                                                class="absolute left-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pricing Section -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <?php if($note->price > 0): ?>
                            <div class="flex flex-wrap items-center gap-3">
                                <?php
                                    $basePrice = $note->hasDiscount() ? $note->discount_price : $note->price;
                                    // Apply subscription discount if available
                                    $displayPrice =
                                        isset($subscriptionDiscountPrice) && $subscriptionDiscountPrice < $basePrice
                                            ? $subscriptionDiscountPrice
                                            : $basePrice;
                                ?>

                                <div class="flex items-baseline gap-3">
                                    <?php if($note->hasDiscount()): ?>
                                        <div class="flex flex-col">
                                            <span
                                                class="text-xs text-gray-500 line-through mb-0.5"><?php echo e(currency($note->price)); ?></span>
                                            <span
                                                class="text-2xl font-bold text-green-600"><?php echo e(currency($note->discount_price)); ?></span>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">
                                            -<?php echo e($note->discount_percent); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="text-2xl font-bold text-gray-900"><?php echo e(currency($basePrice)); ?></span>
                                    <?php endif; ?>

                                    <?php if(isset($subscriptionDiscount) && $subscriptionDiscount > 0 && $displayPrice < $basePrice): ?>
                                        <div class="flex flex-col">
                                            <?php if(!$note->hasDiscount()): ?>
                                                <span
                                                    class="text-xs text-gray-500 line-through mb-0.5"><?php echo e(currency($basePrice)); ?></span>
                                            <?php endif; ?>
                                            <span
                                                class="text-2xl font-bold text-green-600"><?php echo e(currency($displayPrice)); ?></span>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-md">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                            </svg>
                                            -<?php echo e($subscriptionDiscount); ?>% <?php echo e($activeSubscription->plan->name); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div
                                class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 border border-gray-200">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-lg font-semibold"><?php echo e(__('messages.free')); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if($taxPreview): ?>
                            <div
                                class="w-full mt-4 bg-gradient-to-br from-slate-50 to-slate-100 border border-slate-200 rounded-xl p-4 shadow-sm">
                                <div class="space-y-2.5">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700"><?php echo e(__('messages.tax_subtotal_label')); ?></span>
                                        <span
                                            class="text-sm font-semibold text-gray-900"><?php echo e(currency($taxPreview['price_excluding_tax'])); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-700">
                                            <?php echo e(__('messages.tax_label')); ?> (<?php echo e($taxPreview['tax_percent']); ?>%)
                                            <?php echo $taxPreview['tax_inclusive']
                                                ? '<span class="text-xs text-emerald-600 font-semibold ml-1.5 px-1.5 py-0.5 bg-emerald-50 rounded">' .
                                                    __('messages.tax_inclusive_badge') .
                                                    '</span>'
                                                : ''; ?>

                                        </span>
                                        <span
                                            class="text-sm font-semibold text-gray-900"><?php echo e(currency($taxPreview['tax_amount'])); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2.5 border-t border-slate-300">
                                        <span
                                            class="text-base font-bold text-gray-900"><?php echo e(__('messages.tax_total_label')); ?></span>
                                        <span
                                            class="text-base font-bold text-gray-900"><?php echo e(currency($taxPreview['total_amount'])); ?></span>
                                    </div>
                                </div>
                                <p class="mt-3 text-xs text-gray-600 leading-relaxed">
                                    <?php echo e($taxPreview['tax_inclusive'] ? __('messages.tax_inclusive_help') : __('messages.tax_exclusive_help')); ?>

                                    <?php if(!empty($taxPreview['country_code'])): ?>
                                        <span
                                            class="font-semibold text-gray-700">(<?php echo e(strtoupper($taxPreview['country_code'])); ?>)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Subscription Benefits -->
                        <?php if(auth()->check() && $note->price > 0): ?>
                            <?php if($activeSubscription): ?>
                                <div class="mt-4">
                                    <?php if (isset($component)) { $__componentOriginal7b53e06f2a62d0c042e35093d03494f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b53e06f2a62d0c042e35093d03494f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.subscription-benefits','data' => ['plan' => $activeSubscription->plan]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('subscription-benefits'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['plan' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($activeSubscription->plan)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b53e06f2a62d0c042e35093d03494f9)): ?>
<?php $attributes = $__attributesOriginal7b53e06f2a62d0c042e35093d03494f9; ?>
<?php unset($__attributesOriginal7b53e06f2a62d0c042e35093d03494f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b53e06f2a62d0c042e35093d03494f9)): ?>
<?php $component = $__componentOriginal7b53e06f2a62d0c042e35093d03494f9; ?>
<?php unset($__componentOriginal7b53e06f2a62d0c042e35093d03494f9); ?>
<?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="mt-4">
                                    <?php if (isset($component)) { $__componentOriginal7b53e06f2a62d0c042e35093d03494f9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal7b53e06f2a62d0c042e35093d03494f9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.subscription-benefits','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('subscription-benefits'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal7b53e06f2a62d0c042e35093d03494f9)): ?>
<?php $attributes = $__attributesOriginal7b53e06f2a62d0c042e35093d03494f9; ?>
<?php unset($__attributesOriginal7b53e06f2a62d0c042e35093d03494f9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal7b53e06f2a62d0c042e35093d03494f9)): ?>
<?php $component = $__componentOriginal7b53e06f2a62d0c042e35093d03494f9; ?>
<?php unset($__componentOriginal7b53e06f2a62d0c042e35093d03494f9); ?>
<?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-5 leading-tight"><?php echo e($note->title); ?></h1>

                    <!-- Tags -->
                    <?php if($note->tags->count() > 0): ?>
                        <div class="mb-6 flex flex-wrap gap-2">
                            <?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span
                                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition-colors">
                                    <?php echo e($tag->name); ?>

                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Author, Meta Info, and Share Buttons -->
                    <div class="mb-6 text-sm text-gray-600 border-b border-gray-200 pb-6">
                        <div class="flex items-center justify-between flex-wrap gap-4">
                            <div class="flex items-center gap-4 flex-wrap">
                                <a href="<?php echo e(route('public.profile.show', $note->user->username)); ?>"
                                    class="flex items-center text-gray-900 hover:text-blue-600 transition-colors duration-200 group"
                                    title="<?php echo e(__('messages.view_all_notes_from', ['name' => $note->user->name])); ?>">
                                    <div
                                        class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-2 group-hover:ring-2 group-hover:ring-blue-500 transition-all duration-200">
                                        <?php if($note->user->avatar): ?>
                                            <?php if(str_starts_with($note->user->avatar, 'http')): ?>
                                                <img src="<?php echo e($note->user->avatar); ?>" alt="<?php echo e($note->user->name); ?>"
                                                    loading="lazy" class="w-10 h-10 rounded-full object-cover"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <?php else: ?>
                                                <?php
                                                    $avatarPath = $note->user->avatar;
                                                    // Remove leading slash if exists
                                                    $avatarPath = ltrim($avatarPath, '/');
                                                    // Remove marketplace/ prefix if exists (legacy fix)
                                                    $avatarPath = preg_replace('#^marketplace/#', '', $avatarPath);
                                                ?>
                                                <img src="<?php echo e(asset('storage/' . $avatarPath)); ?>"
                                                    alt="<?php echo e($note->user->name); ?>" loading="lazy"
                                                    class="w-10 h-10 rounded-full object-cover"
                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span
                                                class="text-sm font-semibold text-gray-600"><?php echo e(substr($note->user->name, 0, 1)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span
                                                class="font-medium text-gray-900 group-hover:text-blue-600 transition-colors duration-200"><?php echo e($note->user->name); ?></span>
                                            <?php if($note->user->badges && $note->user->badges->count() > 0): ?>
                                                <?php $__currentLoopData = $note->user->badges->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $badge): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="inline-flex items-center text-xs font-medium"
                                                        style="color: <?php echo e($badge->color_hex); ?>;"
                                                        title="<?php echo e($badge->name); ?>">
                                                        <?php if($badge->icon): ?>
                                                            <?php echo e($badge->icon); ?>

                                                        <?php endif; ?>
                                                    </span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            <?php endif; ?>
                                            
                                            
                                            <?php if($note->user->role === 'seller'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                    </svg>
                                                    <?php echo e(__('messages.seller')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($note->user->location): ?>
                                            <span class="text-xs text-gray-500">• <?php echo e($note->user->location); ?></span>
                                        <?php endif; ?>
                                        <div
                                            class="text-xs text-gray-900 group-hover:text-blue-600 opacity-0 group-hover:opacity-100 transition-all duration-200">
                                            <?php echo e(__('messages.view_all_notes_arrow')); ?>

                                        </div>
                                        <div class="mt-2 flex items-center gap-2">
                                            <?php if(($sellerReviewStats['count'] ?? 0) > 0): ?>
                                                <div class="flex items-center gap-1">
                                                    <div class="flex items-center">
                                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                                            <svg class="w-4 h-4 <?php echo e($i <= round($sellerReviewStats['average']) ? 'text-yellow-400' : 'text-gray-300'); ?>"
                                                                fill="currentColor" viewBox="0 0 20 20">
                                                                <path
                                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                            </svg>
                                                        <?php endfor; ?>
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-800">
                                                        <?php echo e(number_format($sellerReviewStats['average'], 1)); ?>

                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        (<?php echo e(trans_choice('messages.rating_count', $sellerReviewStats['count'], ['count' => $sellerReviewStats['count']])); ?>)
                                                    </span>
                                                </div>
                                            <?php else: ?>
                                                <span
                                                    class="text-xs text-gray-500"><?php echo e(__('messages.seller_no_ratings_yet')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                                <div class="text-xs text-gray-500">
                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <?php echo e(__('messages.published')); ?> <?php echo e(localized_time($note->created_at, 'date')); ?>

                                </div>
                            </div>

                            <!-- Share Buttons -->
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 mr-2"><?php echo e(__('messages.share_label')); ?></span>
                                <?php
                                    $shareUrl = route('marketplace.show', $note);
                                    $shareTitle = urlencode($note->title);
                                    $shareText = urlencode(Str::limit(strip_tags($note->content), 100));
                                ?>

                                <?php if(auth()->guard()->check()): ?>
                                    <?php if(isset($shareUrl) && $shareUrl): ?>
                                        <!-- Share with Referral Link (Earn Commission) -->
                                        <div class="relative group">
                                            <button type="button" onclick="copyShareReferralLink('<?php echo e($shareUrl); ?>')"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-r from-purple-600 to-pink-600 text-white hover:from-purple-700 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg"
                                                title="Copy share link with referral (Earn commission when someone purchases)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 4v16m8-8H4" />
                                                </svg>
                                            </button>
                                            <!-- Tooltip -->
                                            <div
                                                class="absolute right-0 bottom-full mb-2 hidden group-hover:block w-64 p-3 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                                                <div class="font-semibold mb-2">🎁 Share & Earn</div>
                                                <p class="text-gray-300">Copy this link to share. You'll earn commission when
                                                    someone purchases through your link!</p>
                                                <div
                                                    class="absolute right-4 top-full w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-900">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Share Statistics (if user has shared this note) -->
                                        <?php
                                            $userShareStats = null;
                                            if (auth()->check()) {
                                                $shareService = app(\App\Services\NoteShareService::class);
                                                $userShareStats = $shareService->getUserShareStats(auth()->user());
                                                $currentNoteShare = $userShareStats['share_referrals']
                                                    ->where('note_id', $note->id)
                                                    ->first();
                                            }
                                        ?>
                                        <?php if(isset($currentNoteShare) && $currentNoteShare): ?>
                                            <div class="relative group">
                                                <button type="button" onclick="showShareStatsModal()"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 text-white hover:from-green-600 hover:to-emerald-700 transition-all duration-200 shadow-md"
                                                    title="View share statistics">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                    </svg>
                                                    Stats
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Facebook -->
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo e(urlencode($shareUrl)); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                                    title="<?php echo e(__('messages.share_on_facebook')); ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                </a>

                                <!-- Twitter -->
                                <a href="https://twitter.com/intent/tweet?url=<?php echo e(urlencode($shareUrl)); ?>&text=<?php echo e($shareTitle); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors duration-200"
                                    title="<?php echo e(__('messages.share_on_twitter')); ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                                    </svg>
                                </a>

                                <!-- WhatsApp -->
                                <a href="https://wa.me/?text=<?php echo e($shareTitle); ?>%20<?php echo e(urlencode($shareUrl)); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors duration-200"
                                    title="<?php echo e(__('messages.share_on_whatsapp')); ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                    </svg>
                                </a>

                                <!-- LinkedIn -->
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo e(urlencode($shareUrl)); ?>"
                                    target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-700 text-white hover:bg-blue-800 transition-colors duration-200"
                                    title="<?php echo e(__('messages.share_on_linkedin')); ?>">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                    </svg>
                                </a>

                                <!-- Copy Link -->
                                <button onclick="copyToClipboard('<?php echo e($shareUrl); ?>')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors duration-200"
                                    title="<?php echo e(__('messages.copy_link')); ?>">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </button>
                                <?php if(auth()->guard()->check()): ?>
                                    <button type="button" onclick="showNoteReportModal()"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                                        title="<?php echo e(__('messages.report_note_tooltip')); ?>">
                                        <?php echo e(__('messages.report_note')); ?>

                                    </button>
                                <?php else: ?>
                                    <a href="<?php echo e(route('login')); ?>"
                                        class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors duration-200"
                                        title="<?php echo e(__('messages.report_note_login_tooltip')); ?>">
                                        <?php echo e(__('messages.report_note')); ?>

                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Video Preview -->
                    <?php if($note->hasVideoPreview()): ?>
                        <div class="mb-6" x-data="{ isPlaying: false }"
                            @mouseenter="if (!isPlaying) { $refs.videoPlayer.play(); isPlaying = true; }"
                            @mouseleave="if (isPlaying) { $refs.videoPlayer.pause(); isPlaying = false; }">
                            <div class="relative bg-gray-900 rounded-lg overflow-hidden shadow-lg">
                                <video x-ref="videoPlayer" class="w-full h-auto" controls preload="metadata"
                                    poster="<?php echo e($note->video_preview_thumbnail_url); ?>">
                                    <source src="<?php echo e($note->video_preview_url); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                                <?php if($note->video_preview_duration): ?>
                                    <div
                                        class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                        <?php echo e(gmdate('i:s', $note->video_preview_duration)); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 text-center">
                                🎬 Video Preview - Hover untuk auto-play
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Note Content (Protected for paid notes) -->
                    <?php if($showFullContent ?? false): ?>
                        <?php if(auth()->guard()->check()): ?>
                            <?php if(auth()->user()->hasPremium() && auth()->user()->role === 'buyer' && ($alreadyPurchased ?? false)): ?>
                                <!-- Reading Progress Bar -->
                                <div id="reading-progress-container" class="mb-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span
                                            class="text-sm font-medium text-gray-700"><?php echo e(__('messages.reading_progress')); ?></span>
                                        <span id="progress-percentage" class="text-sm font-semibold text-blue-600">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div id="progress-bar"
                                            class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                            style="width: 0%"></div>
                                    </div>
                                </div>

                                <!-- Premium Features Toolbar -->
                                <div
                                    class="mb-4 flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center space-x-3">
                                        <button type="button" id="add-bookmark-btn" onclick="showAddBookmarkModal()"
                                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-purple-700 bg-purple-50 border border-purple-200 rounded-lg hover:bg-purple-100 transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            <?php echo e(__('messages.add_bookmark')); ?>

                                        </button>
                                        <div class="flex items-center space-x-2">
                                            <a href="<?php echo e(route('export.pdf', $note)); ?>"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <?php echo e(__('messages.export_pdf')); ?>

                                            </a>
                                            <a href="<?php echo e(route('export.docx', $note)); ?>"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <?php echo e(__('messages.export_docx')); ?>

                                            </a>
                                            <a href="<?php echo e(route('export.markdown', $note)); ?>"
                                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                                <?php echo e(__('messages.export_md')); ?>

                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bookmarks List -->
                                <div id="bookmarks-section" class="mb-4 hidden">
                                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="text-sm font-semibold text-purple-900 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                </svg>
                                                <?php echo e(__('messages.bookmarks')); ?>

                                            </h4>
                                            <button type="button" onclick="toggleBookmarks()"
                                                class="text-sm text-purple-700 hover:text-purple-900">
                                                <span
                                                    id="bookmarks-toggle-text"><?php echo e(__('messages.bookmarks_toggle_show')); ?></span>
                                            </button>
                                        </div>
                                        <div id="bookmarks-list" class="space-y-2">
                                            <!-- Bookmarks will be loaded here -->
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Rich Media Previews -->
                        <?php if($note->ecosystem_category === 'audio'): ?>
                            <?php
                                $audioUrl = $note->audio_link;
                                // Check if there's an audio file in attachments
if (!$audioUrl) {
    $audioAttachment = collect($note->attachments ?? [])->first(function ($att) {
        $filename = is_array($att) ? $att['filename'] ?? '' : basename($att);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['mp3', 'wav', 'ogg', 'm4a', 'aac', 'flac']);
    });
    if ($audioAttachment) {
        $audioFilename = is_array($audioAttachment)
            ? $audioAttachment['filename'] ?? ''
            : basename($audioAttachment);
        $audioUrl = route('notes.attachments.download', [
            'note' => $note->id,
            'filename' => $audioFilename,
                                        ]);
                                    }
                                }
                            ?>
                            <?php if($audioUrl): ?>
                                <?php echo $__env->make('components.rich-media.audio-preview', [
                                    'audioUrl' => $audioUrl,
                                    'title' => $note->title,
                                    'duration' => $note->audio_duration,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if($note->ecosystem_category === 'code'): ?>
                            <?php
                                $codeContent = null;
                                $codeAttachment = collect($note->attachments ?? [])->first(function ($att) {
                                    $filename = is_array($att) ? $att['filename'] ?? '' : basename($att);
                                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                    return in_array($ext, [
                                        'js',
                                        'jsx',
                                        'ts',
                                        'tsx',
                                        'php',
                                        'py',
                                        'java',
                                        'cpp',
                                        'c',
                                        'html',
                                        'css',
                                        'scss',
                                        'vue',
                                        'jsx',
                                    ]);
                                });
                            ?>
                            <?php echo $__env->make('components.rich-media.code-preview', [
                                'code' => $codeContent,
                                'language' => $note->code_language ?? 'javascript',
                                'codeUrl' => $codeAttachment
                                    ? route('notes.attachments.download', [
                                        'note' => $note->id,
                                        'filename' => is_array($codeAttachment)
                                            ? $codeAttachment['filename'] ?? ''
                                            : basename($codeAttachment),
                                    ])
                                    : null,
                                'demoLink' => $note->code_demo_link ?? $note->demo_link,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <?php if($note->ecosystem_category === '3d'): ?>
                            <?php
                                $modelUrl = $note->three_d_preview_link;
                                // Check if there's a 3D model file in attachments
if (!$modelUrl) {
    $modelAttachment = collect($note->attachments ?? [])->first(function ($att) {
        $filename = is_array($att) ? $att['filename'] ?? '' : basename($att);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, ['glb', 'gltf', 'obj', 'fbx', 'dae', '3ds']);
    });
    if ($modelAttachment) {
        $modelFilename = is_array($modelAttachment)
            ? $modelAttachment['filename'] ?? ''
            : basename($modelAttachment);
        $modelUrl = route('notes.attachments.download', [
            'note' => $note->id,
            'filename' => $modelFilename,
                                        ]);
                                    }
                                }
                            ?>
                            <?php if($modelUrl): ?>
                                <?php echo $__env->make('components.rich-media.3d-viewer', [
                                    'modelUrl' => $modelUrl,
                                    'format' => $note->three_d_format ?? 'obj',
                                    'title' => $note->title,
                                ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="prose prose-lg max-w-none mb-6" id="note-content">
                            <div
                                class="ql-editor text-gray-900 leading-relaxed prose-headings:font-bold prose-p:mb-4 prose-ul:mb-4 prose-ol:mb-4 prose-li:mb-2 prose-a:text-blue-600 prose-a:underline hover:prose-a:text-blue-800 prose-strong:font-semibold prose-code:bg-gray-100 prose-code:px-1 prose-code:rounded prose-code:text-sm prose-pre:bg-gray-900 prose-pre:text-gray-100 prose-pre:p-4 prose-pre:rounded-lg prose-pre:overflow-x-auto prose-img:rounded-lg prose-img:shadow-md prose-img:my-4 prose-blockquote:border-l-4 prose-blockquote:border-gray-300 prose-blockquote:pl-4 prose-blockquote:italic prose-blockquote:text-gray-700">
                                <?php echo $note->content; ?></div>
                        </div>

                        <!-- Demo Link (Prominent Display) -->
                        <?php if($note->demo_link): ?>
                            <div class="mb-6">
                                <div
                                    class="bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 rounded-xl border-2 border-green-300 p-5 shadow-lg hover:shadow-xl transition-all duration-300">
                                    <div class="flex items-center justify-between flex-wrap gap-4">
                                        <div class="flex items-center gap-4 flex-1 min-w-0">
                                            <div
                                                class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                                    <span
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-600 text-white uppercase tracking-wide">
                                                        🚀 Demo Live
                                                    </span>
                                                    <h3 class="text-base font-bold text-gray-900">Coba Demo Sekarang!</h3>
                                                </div>
                                                <p class="text-sm text-gray-700 mb-1">Lihat dan coba produk ini secara
                                                    langsung sebelum membeli</p>
                                                <p class="text-xs text-gray-500 truncate"><?php echo e($note->demo_link); ?></p>
                                            </div>
                                        </div>
                                        <a href="<?php echo e($note->demo_link); ?>" target="_blank" rel="noopener noreferrer"
                                            class="flex-shrink-0 inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-lg shadow-md hover:from-green-700 hover:to-emerald-700 hover:shadow-xl transform hover:scale-105 transition-all duration-200 text-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                            Buka Demo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- PDF Preview (if PDF attachment exists) -->
                        <?php
                            $pdfAttachment = collect($note->attachments ?? [])->first(function ($att) {
                                $filename = is_array($att) ? $att['filename'] ?? '' : basename($att);
                                return strtolower(pathinfo($filename, PATHINFO_EXTENSION)) === 'pdf';
                            });
                        ?>
                        <?php if($pdfAttachment): ?>
                            <?php
                                $pdfFilename = is_array($pdfAttachment)
                                    ? $pdfAttachment['filename'] ?? ''
                                    : basename($pdfAttachment);
                                $pdfUrl = route('notes.attachments.download', [
                                    'note' => $note->id,
                                    'filename' => $pdfFilename,
                                ]);
                            ?>
                            <?php echo $__env->make('components.rich-media.pdf-preview', [
                                'pdfUrl' => $pdfUrl,
                                'filename' => $pdfFilename,
                            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        <?php endif; ?>

                        <!-- Attachments (if purchased or free) -->
                        <?php if($note->hasAttachments()): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <?php echo e(__('messages.attachments')); ?> (<?php echo e($note->file_count); ?>)
                                </h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <?php $__currentLoopData = $note->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $filename = is_array($attachment)
                                                ? $attachment['filename'] ?? __('messages.unknown_file_name')
                                                : basename($attachment);
                                            $isExternal =
                                                is_array($attachment) &&
                                                isset($attachment['type']) &&
                                                $attachment['type'] === 'external';
                                            $externalUrl =
                                                $isExternal && isset($attachment['url']) ? $attachment['url'] : null;
                                            $href =
                                                $isExternal && $externalUrl
                                                    ? $externalUrl
                                                    : route('notes.attachments.download', [
                                                        'note' => $note->id,
                                                        'filename' => $filename,
                                                    ]);
                                            $target = $isExternal ? '_blank' : '_self';
                                            $rel = $isExternal ? 'noopener noreferrer' : '';
                                        ?>
                                        <a href="<?php echo e($href); ?>" target="<?php echo e($target); ?>"
                                            rel="<?php echo e($rel); ?>"
                                            class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-300 transition-all duration-200">
                                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    <?php echo e($filename); ?>

                                                    <?php if($isExternal): ?>
                                                        <span class="ml-1 text-xs text-blue-600">(External Link)</span>
                                                    <?php endif; ?>
                                                </p>
                                                <?php if(is_array($attachment) && isset($attachment['size'])): ?>
                                                    <p class="text-xs text-gray-500">
                                                        <?php echo e(number_format($attachment['size'] / 1024, 2)); ?> KB</p>
                                                <?php endif; ?>
                                            </div>
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if($note->hasThumbnails()): ?>
                        <?php echo $__env->make('components.media-gallery', [
                            'images' => $note->thumbnails,
                            'title' => __('messages.media_gallery'),
                        ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>

                    <?php if(!($showFullContent ?? false)): ?>
                        <!-- Preview Content (for paid notes, before purchase) -->
                        <div class="prose max-w-none mb-6 relative">
                            <?php
                                $previewMode = 'custom';
                                $previewContent = null;
                                $showBlur = false;
                                $totalLines = null;
                                $visibleLines = null;

                                if ($note->preview_percentage > 0) {
                                    $previewMode = 'percentage';
                                    $previewContent = $note->getPreviewContentByPercentage();
                                    $showBlur = $note->preview_percentage < 100;

                                    if ($showBlur) {
                                        $totalLines = count(preg_split('/\r\n|\r|\n/', $note->content));
                                        $visibleLines = (int) ceil($totalLines * ($note->preview_percentage / 100));
                                    }
                                } else {
                                    $previewContent = $note->preview_content ?: $note->summary;
                                    if (!$previewContent) {
                                        $previewContent = null;
                                    }
                                }
                            ?>

                            <?php if($previewMode === 'percentage' && !empty($previewContent)): ?>
                                <div class="prose max-w-none">
                                    <div class="ql-editor text-gray-900 leading-relaxed whitespace-pre-wrap">
                                        <?php echo $previewContent; ?>

                                        <?php if($note->preview_percentage < 100): ?>
                                            <span class="text-gray-500 italic">...</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if($showBlur): ?>
                                    <div
                                        class="absolute inset-0 bg-gradient-to-b from-transparent via-white/80 to-white backdrop-blur-sm pointer-events-none flex items-end justify-center pb-8">
                                        <div class="text-center px-4">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">
                                                <?php echo e(__('messages.full_content_available_after_purchase')); ?></p>
                                            <p class="text-xs text-gray-600"><?php echo e(__('messages.buy_note_to_unlock')); ?></p>
                                            <?php if(isset($visibleLines, $totalLines)): ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?php echo e(__('messages.preview_lines_detail', ['visible' => $visibleLines, 'total' => $totalLines, 'percentage' => $note->preview_percentage])); ?>

                                                </p>
                                            <?php else: ?>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?php echo e(__('messages.preview_percentage_detail', ['percentage' => $note->preview_percentage])); ?>

                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="relative rounded-xl border border-dashed border-gray-300 bg-gray-50 p-6">
                                    <div class="flex flex-col items-center text-center space-y-3">
                                        <div
                                            class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 text-gray-600">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 11c.5304 0 1.0391-.2107 1.4142-.5858C13.7893 10.0391 14 9.5304 14 9c0-1.1046-.8954-2-2-2s-2 .8954-2 2c0 .5304.2107 1.0391.5858 1.4142C10.9609 10.7893 11.4696 11 12 11Zm0 0v3m-6 4h12a2 2 0 0 0 2-2v-2.382a2 2 0 0 0-.684-1.5L13.316 6.5a2 2 0 0 0-2.632 0L4.684 10.118A2 2 0 0 0 4 11.618V14a2 2 0 0 0 2 2Z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800">
                                                <?php echo e(__('messages.preview_locked_title')); ?>

                                            </p>
                                            <p class="text-xs text-gray-600">
                                                <?php echo e(__('messages.preview_locked_description')); ?>

                                            </p>
                                        </div>
                                        <?php if(!empty($previewContent)): ?>
                                            <?php
                                                $previewContainsHtml =
                                                    is_string($previewContent) &&
                                                    $previewContent !== strip_tags($previewContent);
                                            ?>
                                            <div class="max-w-2xl text-xs text-gray-500 leading-relaxed">
                                                <?php echo $previewContainsHtml ? $previewContent : nl2br(e($previewContent)); ?>

                                            </div>
                                        <?php else: ?>
                                            <p class="text-xs text-gray-500">
                                                <?php echo e(__('messages.preview_locked_no_excerpt')); ?>

                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- What You'll Get Section -->
                        <?php if($note->price > 0): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200 bg-blue-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <?php echo e(__('messages.what_youll_get')); ?>

                                </h3>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span><?php echo e(__('messages.full_note_content')); ?></span>
                                    </li>
                                    <?php if($note->hasAttachments()): ?>
                                        <li class="flex items-start">
                                            <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <span><?php echo e($note->file_count); ?> <?php echo e(__('messages.downloadable_files')); ?>

                                                <span
                                                    class="text-xs text-gray-600">(<?php echo e(__('messages.locked_until_purchase')); ?>)</span></span>
                                        </li>
                                    <?php endif; ?>
                                    <li class="flex items-start">
                                        <svg class="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span><?php echo e(__('messages.lifetime_access')); ?></span>
                                    </li>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Trust Indicators -->
                        <?php if($note->purchase_count > 0): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center text-gray-700">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-semibold"><?php echo e($note->purchase_count); ?></span>
                                        <span
                                            class="ml-1"><?php echo e($note->purchase_count == 1 ? __('messages.purchase') : __('messages.purchases')); ?></span>
                                    </div>
                                    <?php if($note->purchase_count >= 10): ?>
                                        <div class="flex items-center text-yellow-600">
                                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <span class="font-semibold"><?php echo e(__('messages.popular')); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Purchase/Action Buttons -->
                    <?php if(auth()->guard()->check()): ?>
                        <?php if($alreadyPurchased ?? false): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="text-green-600 font-semibold"><?php echo e(__('messages.you_have_purchased')); ?></span>
                                    </div>
                                    <a href="<?php echo e(route('notes.show', $note)); ?>"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        <?php echo e(__('messages.view_full_note_arrow')); ?>

                                    </a>
                                </div>

                                <?php if(auth()->user()->hasPremium() && auth()->user()->role === 'buyer'): ?>
                                    <div class="mt-4 pt-4 border-t border-gray-200">
                                        <button type="button" onclick="showCollectionModal('<?php echo e($note->id); ?>')"
                                            class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                            </svg>
                                            <?php echo e(__('messages.add_to_collection')); ?>

                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif($canBuy && $note->price > 0): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <?php if($note->isStandardMode()): ?>
                                    <!-- Standard Mode Info for Buyer -->
                                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-blue-800 mb-1">Standard Mode - Multiple
                                                    Sales</p>
                                                <p class="text-xs text-blue-700">
                                                    Note ini menggunakan Standard Mode. Anda bisa membeli dan mengakses note ini
                                                    kapan saja,
                                                    tetapi <strong>tidak bisa menjual kembali</strong> ke buyer lain.
                                                    Note ini bisa dibeli oleh banyak buyer secara bersamaan.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <form action="<?php echo e(route('marketplace.purchase', $note)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-sm hover:shadow-md transition-all duration-200">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <?php
                                            $displayPrice =
                                                $premiumDiscountPrice ??
                                                ($note->hasDiscount() ? $note->discount_price : $note->price);
                                            $formattedDisplayPrice = currency($displayPrice);
                                        ?>
                                        <?php echo e(__('messages.buy_note_price', ['price' => $formattedDisplayPrice])); ?>

                                        <?php if(isset($premiumDiscountPercent) && $premiumDiscountPercent > 0): ?>
                                            <span
                                                class="ml-2 text-xs bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-2 py-0.5 rounded-full">
                                                -<?php echo e($premiumDiscountPercent); ?>% <?php echo e(__('messages.premium_badge_label')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </button>
                                </form>
                                <p class="text-sm text-gray-600 mt-3">
                                    <?php echo e(__('messages.wallet_balance_label')); ?>

                                    <strong
                                        class="font-semibold text-gray-900"><?php echo e(currency((float) auth()->user()->wallet_balance, auth()->user()->currency)); ?></strong>
                                    <?php
                                        $finalPrice =
                                            $premiumDiscountPrice ??
                                            ($note->hasDiscount() ? $note->discount_price : $note->price);
                                    ?>
                                    <?php if(isset($premiumDiscountPercent) && $premiumDiscountPercent > 0): ?>
                                        <div class="mt-2 text-xs text-gray-500">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 text-white">
                                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                                <?php echo e(__('messages.premium_discount_badge', ['amount' => currency($basePrice - $finalPrice), 'percent' => $premiumDiscountPercent])); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if(auth()->user()->wallet_balance < $finalPrice): ?>
                                        <span class="text-red-600 font-medium">
                                            <?php echo e(__('messages.wallet_insufficient_amount', ['amount' => currency($finalPrice - auth()->user()->wallet_balance, auth()->user()->currency)])); ?>

                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php elseif(auth()->user()->role === 'seller' && $note->price > 0 && !$alreadyPurchased): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800 mb-1">
                                                <?php echo e(__('messages.seller_cannot_purchase_title')); ?></p>
                                            <p class="text-xs text-yellow-700">
                                                <?php echo e(__('messages.seller_cannot_purchase_description')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php elseif($note->user_id === auth()->id()): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <?php if(auth()->user()->role === 'seller'): ?>
                                    <p class="text-gray-600 mb-3"><?php echo e(__('messages.note_owner_message')); ?></p>
                                    <a href="<?php echo e(route('notes.edit', $note)); ?>"
                                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        <?php echo e(__('messages.edit_note_arrow')); ?>

                                    </a>
                                <?php elseif(auth()->user()->role === 'buyer'): ?>
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div>
                                                <p class="text-sm font-medium text-blue-800 mb-1">
                                                    <?php echo e(__('messages.buyer_owns_note_title')); ?></p>
                                                <p class="text-xs text-blue-700 mb-2">
                                                    <?php echo e(__('messages.buyer_owns_note_description')); ?>

                                                </p>
                                                <div class="bg-yellow-100 border border-yellow-300 rounded p-2 mt-2">
                                                    <p class="text-xs font-semibold text-yellow-800 mb-1">
                                                        <?php echo e(__('messages.buyer_resale_warning_title')); ?>

                                                    </p>
                                                    <p class="text-xs text-yellow-700">
                                                        <?php echo __('messages.buyer_resale_warning_description'); ?>

                                                    </p>
                                                </div>
                                                <?php if($note->originalCreator): ?>
                                                    <p class="text-xs text-blue-600 mt-2">
                                                        <?php echo __('messages.original_creator_notice', [
                                                            'name' => '<strong>' . e($note->originalCreator->name) . '</strong>',
                                                        ]); ?>

                                                        <?php if($note->originalCreator->id !== auth()->id()): ?>
                                                            <?php echo e(__('messages.original_creator_commission_note')); ?>

                                                        <?php endif; ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-3">
                                        <?php echo __('messages.buyer_resale_notice'); ?>

                                    </p>
                                    <div class="mt-4">
                                        <a href="<?php echo e(route('notes.resale.form', $note)); ?>"
                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Set Harga & Jual Kembali
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif($hasPurchasedBefore && !$isNoteOwner): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <?php if($canRepurchase && $note->isScarcityMode()): ?>
                                    <!-- Can Repurchase (Scarcity Mode) -->
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5" fill="currentColor"
                                                viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-blue-800 mb-1">Beli Kembali Catatan Ini</p>
                                                <p class="text-xs text-blue-700 mb-3">
                                                    Anda sudah pernah membeli dan menjual catatan ini. Anda bisa membeli kembali
                                                    sekarang.
                                                </p>
                                                <?php if($isWithinGracePeriod && $gracePeriodEndsAt): ?>
                                                    <div class="bg-green-100 border border-green-300 rounded p-2 mb-3">
                                                        <p class="text-xs font-semibold text-green-800 mb-1">
                                                            ⏰ Grace Period Aktif
                                                        </p>
                                                        <p class="text-xs text-green-700 mb-2">
                                                            Beli kembali dengan harga original hingga:
                                                        </p>
                                                        <div id="grace-period-countdown"
                                                            class="text-xs font-bold text-green-900"
                                                            data-end-time="<?php echo e($gracePeriodEndsAt->timestamp); ?>">
                                                            Menghitung...
                                                        </div>
                                                    </div>
                                                <?php elseif($gracePeriodEndsAt && !$isWithinGracePeriod): ?>
                                                    <div class="bg-yellow-100 border border-yellow-300 rounded p-2 mb-3">
                                                        <p class="text-xs font-semibold text-yellow-800 mb-1">
                                                            ⚠️ Grace Period Berakhir
                                                        </p>
                                                        <p class="text-xs text-yellow-700">
                                                            Grace period berakhir pada
                                                            <?php echo e($gracePeriodEndsAt->format('d M Y H:i')); ?>. Harga pembelian
                                                            ulang sekarang lebih tinggi.
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="flex items-center gap-3">
                                                    <div>
                                                        <p class="text-xs text-gray-600">Harga Pembelian Ulang:</p>
                                                        <p class="text-lg font-bold text-blue-900">
                                                            <?php echo e(currency($repurchasePrice)); ?></p>
                                                    </div>
                                                    <form action="<?php echo e(route('marketplace.purchase', $note)); ?>" method="POST"
                                                        class="ml-auto">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit"
                                                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                                                            Beli Kembali
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Access Revoked Message -->
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-yellow-800 mb-1">
                                                <?php echo e(__('messages.access_revoked_title')); ?></p>
                                            <p class="text-xs text-yellow-700">
                                                <?php echo __('messages.access_revoked_description'); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php elseif($note->price == 0): ?>
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <div class="flex items-center bg-green-50 border border-green-200 rounded-lg p-4">
                                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span class="text-green-800 font-semibold"><?php echo e(__('messages.note_free_enjoy')); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-gray-600 mb-3"><?php echo e(__('messages.to_purchase_please_login')); ?></p>
                            <a href="<?php echo e(route('login')); ?>"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                Login to Continue
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reviews Section -->
            <?php if($note->total_reviews > 0 || (auth()->check() && isset($canReview) && $canReview)): ?>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.reviews')); ?>

                            (<?php echo e($note->total_reviews); ?>)</h2>
                    </div>
                    <div class="p-6">
                        <!-- Review Form (if user can review) -->
                        <?php if(auth()->check() && isset($canReview) && $canReview): ?>
                            <div class="mb-6 pb-6 border-b border-gray-200">
                                <h3 class="text-base font-semibold text-gray-900 mb-4"><?php echo e(__('messages.write_review')); ?>

                                </h3>
                                <form action="<?php echo e(route('reviews.store', $note)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="mb-4">
                                        <label for="rating"
                                            class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.rating')); ?></label>
                                        <div class="flex gap-1" id="rating-container">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <button type="button"
                                                    class="star-rating text-gray-300 hover:text-yellow-400 transition-colors duration-200"
                                                    data-rating="<?php echo e($i); ?>">
                                                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="rating" id="rating-input" required>
                                        <?php $__errorArgs = ['rating'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <div class="mb-4">
                                        <label for="comment"
                                            class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.comment_optional')); ?></label>
                                        <textarea name="comment" id="comment" rows="4"
                                            placeholder="<?php echo e(__('messages.share_thoughts_about_note')); ?>"
                                            class="w-full rounded-lg border shadow-sm focus:ring-2 focus:ring-opacity-50 transition-all duration-200 <?php echo e($errors->has('comment') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500'); ?>"></textarea>
                                        <?php $__errorArgs = ['comment'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>

                                    <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                        <?php echo e(__('messages.submit_review')); ?>

                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Reviews List -->
                        <?php if($note->total_reviews > 0): ?>
                            <div class="space-y-6">
                                <?php $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $currentUser = auth()->user();
                                        $canReplyToReview =
                                            $currentUser &&
                                            ($currentUser->id === $note->user_id ||
                                                $currentUser->id === $review->user_id ||
                                                $currentUser->hasRole('admin'));
                                        $canDeleteReview =
                                            $currentUser &&
                                            ($currentUser->id === $review->user_id || $currentUser->hasRole('admin'));
                                    ?>
                                    <div id="review-<?php echo e($review->id); ?>"
                                        class="pb-6 <?php echo e(!$loop->last ? 'border-b border-gray-200' : ''); ?>">
                                        <div class="flex gap-4" x-data="{ replyOpen: false }">
                                            <div class="flex-shrink-0">
                                                <div
                                                    class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                    <?php if($review->user->avatar): ?>
                                                        <?php if(str_starts_with($review->user->avatar, 'http')): ?>
                                                            <img src="<?php echo e($review->user->avatar); ?>"
                                                                alt="<?php echo e($review->user->name); ?>" loading="lazy"
                                                                class="w-10 h-10 rounded-full object-cover"
                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <?php else: ?>
                                                            <?php
                                                                $avatarPath = $review->user->avatar;
                                                                $avatarPath = ltrim($avatarPath, '/');
                                                                $avatarPath = preg_replace(
                                                                    '#^marketplace/#',
                                                                    '',
                                                                    $avatarPath,
                                                                );
                                                            ?>
                                                            <img src="<?php echo e(asset('storage/' . $avatarPath)); ?>"
                                                                alt="<?php echo e($review->user->name); ?>" loading="lazy"
                                                                class="w-10 h-10 rounded-full object-cover"
                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <span
                                                            class="text-sm font-semibold text-gray-600"><?php echo e(substr($review->user->name, 0, 1)); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="flex-1">
                                                <div class="flex items-start justify-between mb-2">
                                                    <div>
                                                        <p class="text-sm font-semibold text-gray-900">
                                                            <?php echo e($review->user->name); ?></p>
                                                        <p class="text-xs text-gray-500">
                                                            <?php echo e(localized_diff_for_humans($review->created_at)); ?></p>
                                                    </div>
                                                    <div class="flex items-center gap-3">
                                                        <?php if($canReplyToReview): ?>
                                                            <button type="button" @click="replyOpen = !replyOpen"
                                                                class="text-xs font-medium text-blue-600 hover:text-blue-700 transition-colors">
                                                                <?php echo e(__('messages.reply')); ?>

                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if($canDeleteReview): ?>
                                                            <form action="<?php echo e(route('reviews.destroy', $review)); ?>"
                                                                method="POST" class="delete-review-form"
                                                                onsubmit="return confirm('<?php echo e(__('messages.review_delete_confirmation')); ?>');">
                                                                <?php echo csrf_field(); ?>
                                                                <?php echo method_field('DELETE'); ?>
                                                                <button type="submit"
                                                                    class="text-xs text-red-600 hover:text-red-700 transition-colors duration-200"><?php echo e(__('messages.delete')); ?></button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="flex gap-0.5 mb-2">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <svg class="w-4 h-4 <?php echo e($i <= $review->rating ? 'text-yellow-400' : 'text-gray-300'); ?>"
                                                            fill="currentColor" viewBox="0 0 20 20">
                                                            <path
                                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                    <?php endfor; ?>
                                                </div>

                                                <?php if($review->comment): ?>
                                                    <p class="text-sm text-gray-700 whitespace-pre-wrap">
                                                        <?php echo e($review->comment); ?></p>
                                                <?php endif; ?>

                                                <?php if($canReplyToReview): ?>
                                                    <form x-show="replyOpen" x-cloak
                                                        action="<?php echo e(route('reviews.replies.store', $review)); ?>"
                                                        method="POST" class="mt-4 space-y-2">
                                                        <?php echo csrf_field(); ?>
                                                        <textarea name="message" rows="3" required maxlength="2000"
                                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 text-sm"
                                                            placeholder="<?php echo e(__('messages.review_reply_placeholder')); ?>"></textarea>
                                                        <div class="flex justify-end">
                                                            <button type="submit"
                                                                class="inline-flex items-center px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                                                                <?php echo e(__('messages.review_reply_submit')); ?>

                                                            </button>
                                                        </div>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if($review->replies && $review->replies->count() > 0): ?>
                                            <div class="mt-4 space-y-4">
                                                <?php $__currentLoopData = $review->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php echo $__env->make('marketplace.partials.review-reply', [
                                                        'reply' => $reply,
                                                        'review' => $review,
                                                    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                                <div class="pt-4">
                                    <?php echo e($reviews->links()); ?>

                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-center text-gray-500 py-8"><?php echo e(__('messages.no_reviews_yet_be_first')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reactions Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('Reactions')); ?></h2>
                </div>
                <div class="p-6">
                    <?php if(auth()->guard()->check()): ?>
                        <div class="mb-4">
                            <div class="flex items-center gap-2 flex-wrap">
                                <button onclick="toggleReaction('like')"
                                    class="reaction-btn inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors <?php echo e($userReaction && $userReaction->reaction_type === 'like' ? 'bg-blue-50 border-blue-300' : ''); ?>"
                                    data-reaction="like">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.834a1 1 0 001.364.97l5-.833a1 1 0 00.636-.97v-5.834a1 1 0 00-.636-.97l-5-.833a1 1 0 00-1.364.97zM15.5 2a1.5 1.5 0 011.5 1.5v7a1.5 1.5 0 01-1.5 1.5h-4a1.5 1.5 0 01-1.5-1.5v-7A1.5 1.5 0 0111.5 2h4z" />
                                    </svg>
                                    <span><?php echo e(__('Like')); ?></span>
                                    <span class="ml-2 text-sm text-gray-600"
                                        id="reaction-count-like"><?php echo e($reactionsSummary['like'] ?? 0); ?></span>
                                </button>
                                <button onclick="toggleReaction('love')"
                                    class="reaction-btn inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors <?php echo e($userReaction && $userReaction->reaction_type === 'love' ? 'bg-red-50 border-red-300' : ''); ?>"
                                    data-reaction="love">
                                    <svg class="w-5 h-5 mr-2 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span><?php echo e(__('Love')); ?></span>
                                    <span class="ml-2 text-sm text-gray-600"
                                        id="reaction-count-love"><?php echo e($reactionsSummary['love'] ?? 0); ?></span>
                                </button>
                                <button onclick="toggleReaction('helpful')"
                                    class="reaction-btn inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors <?php echo e($userReaction && $userReaction->reaction_type === 'helpful' ? 'bg-green-50 border-green-300' : ''); ?>"
                                    data-reaction="helpful">
                                    <svg class="w-5 h-5 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span><?php echo e(__('Helpful')); ?></span>
                                    <span class="ml-2 text-sm text-gray-600"
                                        id="reaction-count-helpful"><?php echo e($reactionsSummary['helpful'] ?? 0); ?></span>
                                </button>
                                <button onclick="toggleReaction('insightful')"
                                    class="reaction-btn inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors <?php echo e($userReaction && $userReaction->reaction_type === 'insightful' ? 'bg-purple-50 border-purple-300' : ''); ?>"
                                    data-reaction="insightful">
                                    <svg class="w-5 h-5 mr-2 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                    <span><?php echo e(__('Insightful')); ?></span>
                                    <span class="ml-2 text-sm text-gray-600"
                                        id="reaction-count-insightful"><?php echo e($reactionsSummary['insightful'] ?? 0); ?></span>
                                </button>
                                <button onclick="toggleReaction('thanks')"
                                    class="reaction-btn inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors <?php echo e($userReaction && $userReaction->reaction_type === 'thanks' ? 'bg-yellow-50 border-yellow-300' : ''); ?>"
                                    data-reaction="thanks">
                                    <svg class="w-5 h-5 mr-2 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.986 1.164l-1 7A1 1 0 0115 17H4a1 1 0 01-.986-1.164l1-7A1 1 0 015 8h4V2a1 1 0 011.3-.954z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <span><?php echo e(__('Thanks')); ?></span>
                                    <span class="ml-2 text-sm text-gray-600"
                                        id="reaction-count-thanks"><?php echo e($reactionsSummary['thanks'] ?? 0); ?></span>
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-gray-500"><?php echo e(__('Log in to react to this note')); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Comments Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('Comments')); ?> (<?php echo e($comments->total()); ?>)
                    </h2>
                </div>
                <div class="p-6">
                    <?php if(auth()->guard()->check()): ?>
                        <!-- Comment Form -->
                        <form action="<?php echo e(route('notes.comments.store', $note)); ?>" method="POST"
                            class="mb-6 pb-6 border-b border-gray-200">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <textarea name="content" rows="3" required placeholder="<?php echo e(__('Write a comment...')); ?>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                <?php echo e(__('Post Comment')); ?>

                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mb-4"><?php echo e(__('Log in to comment')); ?></p>
                    <?php endif; ?>

                    <!-- Comments List -->
                    <?php if($comments->count() > 0): ?>
                        <div class="space-y-6">
                            <?php $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div id="comment-<?php echo e($comment->id); ?>"
                                    class="pb-6 <?php echo e(!$loop->last ? 'border-b border-gray-200' : ''); ?>">
                                    <div class="flex gap-4">
                                        <div class="flex-shrink-0">
                                            <div
                                                class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                                <?php if($comment->user->avatar): ?>
                                                    <?php if(str_starts_with($comment->user->avatar, 'http')): ?>
                                                        <img src="<?php echo e($comment->user->avatar); ?>"
                                                            alt="<?php echo e($comment->user->name); ?>" loading="lazy"
                                                            class="w-10 h-10 rounded-full object-cover"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <?php else: ?>
                                                        <?php
                                                            $avatarPath = $comment->user->avatar;
                                                            $avatarPath = ltrim($avatarPath, '/');
                                                            $avatarPath = preg_replace(
                                                                '#^marketplace/#',
                                                                '',
                                                                $avatarPath,
                                                            );
                                                        ?>
                                                        <img src="<?php echo e(asset('storage/' . $avatarPath)); ?>"
                                                            alt="<?php echo e($comment->user->name); ?>" loading="lazy"
                                                            class="w-10 h-10 rounded-full object-cover"
                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span
                                                        class="text-sm font-semibold text-gray-600"><?php echo e(substr($comment->user->name, 0, 1)); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-start justify-between mb-2">
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-900">
                                                        <?php echo e($comment->user->name); ?></p>
                                                    <p class="text-xs text-gray-500">
                                                        <?php echo e($comment->created_at->diffForHumans()); ?></p>
                                                </div>
                                                <?php if(auth()->guard()->check()): ?>
                                                    <?php if($comment->user_id === auth()->id() || auth()->user()->hasRole('admin')): ?>
                                                        <form action="<?php echo e(route('comments.destroy', $comment)); ?>"
                                                            method="POST" class="inline">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit"
                                                                onclick="return confirm('<?php echo e(__('Delete this comment?')); ?>')"
                                                                class="text-xs text-red-600 hover:text-red-800"><?php echo e(__('Delete')); ?></button>
                                                        </form>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap"><?php echo e($comment->content); ?>

                                            </p>

                                            <?php if(auth()->guard()->check()): ?>
                                                <button onclick="showReplyForm(<?php echo e($comment->id); ?>)"
                                                    class="mt-2 text-xs text-blue-600 hover:text-blue-800">
                                                    <?php echo e(__('Reply')); ?>

                                                </button>

                                                <!-- Reply Form -->
                                                <form id="reply-form-<?php echo e($comment->id); ?>"
                                                    action="<?php echo e(route('comments.reply', $comment)); ?>" method="POST"
                                                    class="mt-2 hidden">
                                                    <?php echo csrf_field(); ?>
                                                    <textarea name="content" rows="2" required
                                                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                                                    <div class="mt-2 flex gap-2">
                                                        <button type="submit"
                                                            class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">
                                                            <?php echo e(__('Reply')); ?>

                                                        </button>
                                                        <button type="button" onclick="hideReplyForm(<?php echo e($comment->id); ?>)"
                                                            class="px-3 py-1 text-xs bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                                                            <?php echo e(__('Cancel')); ?>

                                                        </button>
                                                    </div>
                                                </form>
                                            <?php endif; ?>

                                            <!-- Replies -->
                                            <?php if($comment->replies->count() > 0): ?>
                                                <div class="mt-4 ml-6 space-y-4 border-l-2 border-gray-200 pl-4">
                                                    <?php $__currentLoopData = $comment->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="flex gap-3">
                                                            <div class="flex-shrink-0">
                                                                <div
                                                                    class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                                                    <?php if($reply->user->avatar): ?>
                                                                        <?php if(str_starts_with($reply->user->avatar, 'http')): ?>
                                                                            <img src="<?php echo e($reply->user->avatar); ?>"
                                                                                alt="<?php echo e($reply->user->name); ?>"
                                                                                loading="lazy"
                                                                                class="w-8 h-8 rounded-full object-cover"
                                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                        <?php else: ?>
                                                                            <?php
                                                                                $avatarPath = $reply->user->avatar;
                                                                                $avatarPath = ltrim($avatarPath, '/');
                                                                                $avatarPath = preg_replace(
                                                                                    '#^marketplace/#',
                                                                                    '',
                                                                                    $avatarPath,
                                                                                );
                                                                            ?>
                                                                            <img src="<?php echo e(asset('storage/' . $avatarPath)); ?>"
                                                                                alt="<?php echo e($reply->user->name); ?>"
                                                                                loading="lazy"
                                                                                class="w-8 h-8 rounded-full object-cover"
                                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span
                                                                            class="text-xs font-semibold text-gray-600"><?php echo e(substr($reply->user->name, 0, 1)); ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                            <div class="flex-1">
                                                                <p class="text-xs font-semibold text-gray-900">
                                                                    <?php echo e($reply->user->name); ?></p>
                                                                <p class="text-xs text-gray-700 mt-1">
                                                                    <?php echo e($reply->content); ?></p>
                                                                <p class="text-xs text-gray-500 mt-1">
                                                                    <?php echo e($reply->created_at->diffForHumans()); ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-6">
                            <?php echo e($comments->links()); ?>

                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-8"><?php echo e(__('No comments yet. Be the first to comment!')); ?>

                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Q&A Section -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e(__('Questions & Answers')); ?>

                        (<?php echo e($questions->total()); ?>)</h2>
                </div>
                <div class="p-6">
                    <?php if(auth()->guard()->check()): ?>
                        <!-- Ask Question Form -->
                        <form action="<?php echo e(route('notes.questions.store', $note)); ?>" method="POST"
                            class="mb-6 pb-6 border-b border-gray-200">
                            <?php echo csrf_field(); ?>
                            <div class="mb-4">
                                <textarea name="question" rows="3" required placeholder="<?php echo e(__('Ask a question about this note...')); ?>"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                            </div>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                                <?php echo e(__('Ask Question')); ?>

                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-sm text-gray-500 mb-4"><?php echo e(__('Log in to ask questions')); ?></p>
                    <?php endif; ?>

                    <!-- Questions List -->
                    <?php if($questions->count() > 0): ?>
                        <div class="space-y-6">
                            <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div id="question-<?php echo e($question->id); ?>"
                                    class="pb-6 <?php echo e(!$loop->last ? 'border-b border-gray-200' : ''); ?>">
                                    <div class="mb-3">
                                        <div class="flex items-start justify-between mb-2">
                                            <div class="flex-1">
                                                <p class="text-sm font-semibold text-gray-900 mb-1">
                                                    <?php echo e($question->user->name); ?></p>
                                                <p class="text-sm text-gray-700"><?php echo e($question->question); ?></p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    <?php echo e($question->created_at->diffForHumans()); ?></p>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if($question->isAnswered()): ?>
                                        <div class="ml-6 pl-4 border-l-2 border-green-200 bg-green-50 rounded p-4">
                                            <div class="flex items-start justify-between mb-2">
                                                <div class="flex-1">
                                                    <p class="text-xs font-semibold text-green-800 mb-1">
                                                        <?php echo e(__('Answered by')); ?> <?php echo e($question->answeredBy->name); ?>

                                                    </p>
                                                    <p class="text-sm text-gray-700"><?php echo e($question->answer); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        <?php echo e($question->answered_at->diffForHumans()); ?></p>
                                                </div>
                                            </div>
                                            <?php if(auth()->guard()->check()): ?>
                                                <button onclick="markHelpful(<?php echo e($question->id); ?>, this)"
                                                    class="mt-2 text-xs text-green-600 hover:text-green-800">
                                                    <?php echo e(__('Helpful')); ?> (<?php echo e($question->helpful_count); ?>)
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php elseif(auth()->check() && auth()->id() === $note->user_id): ?>
                                        <!-- Answer Form (for seller) -->
                                        <form action="<?php echo e(route('questions.answer', $question)); ?>" method="POST"
                                            class="ml-6 mt-3">
                                            <?php echo csrf_field(); ?>
                                            <textarea name="answer" rows="3" required placeholder="<?php echo e(__('Write your answer...')); ?>"
                                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                                            <button type="submit"
                                                class="mt-2 px-3 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                <?php echo e(__('Answer')); ?>

                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <p class="text-xs text-gray-500 ml-6"><?php echo e(__('Waiting for seller to answer...')); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div class="mt-6">
                            <?php echo e($questions->links()); ?>

                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 py-8"><?php echo e(__('No questions yet. Be the first to ask!')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    </div>

    <?php $__env->startPush('styles'); ?>
        <!-- Prism.js for code syntax highlighting -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>
    <?php $__env->stopPush(); ?>

    <?php $__env->startPush('scripts'); ?>
        <!-- Media Gallery Script -->
        <script>
            window.mediaGallery = function() {
                return {
                    isOpen: false,
                    currentIndex: 0,
                    zoomLevel: 1,
                    translateX: 0,
                    translateY: 0,
                    imageLoaded: false,
                    imageCount: <?php echo e($note->hasThumbnails() ? count($note->thumbnails) : 0); ?>,
                    images: <?php echo \Illuminate\Support\Js::from($note->hasThumbnails() ? $note->thumbnails : [])->toHtml() ?>,

                    // Touch/swipe handling
                    touchStartX: 0,
                    touchStartY: 0,
                    touchEndX: 0,
                    touchEndY: 0,
                    minSwipeDistance: 50,
                    isDragging: false,
                    dragStartX: 0,
                    dragStartY: 0,

                    init() {
                        // Keyboard navigation
                        document.addEventListener('keydown', (e) => {
                            if (!this.isOpen) return;

                            if (e.key === 'ArrowLeft') {
                                this.previousImage();
                            } else if (e.key === 'ArrowRight') {
                                this.nextImage();
                            } else if (e.key === '+' || e.key === '=') {
                                this.zoomIn();
                            } else if (e.key === '-') {
                                this.zoomOut();
                            } else if (e.key === '0') {
                                this.resetZoom();
                            }
                        });

                        // Prevent body scroll when lightbox is open
                        this.$watch('isOpen', (value) => {
                            if (value) {
                                document.body.style.overflow = 'hidden';
                            } else {
                                document.body.style.overflow = '';
                            }
                        });
                    },

                    get currentImageUrl() {
                        if (this.currentIndex < 0 || this.currentIndex >= this.images.length) return '';
                        const image = this.images[this.currentIndex];
                        if (typeof image === 'string') {
                            return '<?php echo e(asset('storage/')); ?>/' + image;
                        } else if (typeof image === 'object' && image !== null) {
                            return image.url || ('<?php echo e(asset('storage/')); ?>/' + (image.path || ''));
                        }
                        return image;
                    },

                    openLightbox(index) {
                        this.currentIndex = index;
                        this.isOpen = true;
                        this.resetZoom();
                        this.imageLoaded = false;
                    },

                    closeLightbox() {
                        this.isOpen = false;
                        this.resetZoom();
                    },

                    nextImage() {
                        if (this.currentIndex < this.imageCount - 1) {
                            this.currentIndex++;
                            this.resetZoom();
                            this.imageLoaded = false;
                        }
                    },

                    previousImage() {
                        if (this.currentIndex > 0) {
                            this.currentIndex--;
                            this.resetZoom();
                            this.imageLoaded = false;
                        }
                    },

                    zoomIn() {
                        if (this.zoomLevel < 3) {
                            this.zoomLevel = Math.min(this.zoomLevel + 0.25, 3);
                        }
                    },

                    zoomOut() {
                        if (this.zoomLevel > 1) {
                            this.zoomLevel = Math.max(this.zoomLevel - 0.25, 1);
                            if (this.zoomLevel === 1) {
                                this.resetZoom();
                            }
                        }
                    },

                    resetZoom() {
                        this.zoomLevel = 1;
                        this.translateX = 0;
                        this.translateY = 0;
                    },

                    // Touch/Swipe handlers
                    handleTouchStart(e) {
                        if (this.zoomLevel > 1) {
                            // If zoomed, allow panning
                            this.isDragging = true;
                            this.dragStartX = e.touches[0].clientX - this.translateX;
                            this.dragStartY = e.touches[0].clientY - this.translateY;
                        } else {
                            // If not zoomed, detect swipe
                            this.touchStartX = e.touches[0].clientX;
                            this.touchStartY = e.touches[0].clientY;
                        }
                    },

                    handleTouchMove(e) {
                        if (this.isDragging && this.zoomLevel > 1) {
                            // Panning when zoomed
                            this.translateX = e.touches[0].clientX - this.dragStartX;
                            this.translateY = e.touches[0].clientY - this.dragStartY;
                        }
                    },

                    handleTouchEnd(e) {
                        if (this.isDragging) {
                            this.isDragging = false;
                            return;
                        }

                        // Swipe detection
                        this.touchEndX = e.changedTouches[0].clientX;
                        this.touchEndY = e.changedTouches[0].clientY;

                        const deltaX = this.touchEndX - this.touchStartX;
                        const deltaY = this.touchEndY - this.touchStartY;

                        // Only process horizontal swipes
                        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > this.minSwipeDistance) {
                            if (deltaX > 0) {
                                // Swipe right - previous image
                                this.previousImage();
                            } else {
                                // Swipe left - next image
                                this.nextImage();
                            }
                        }
                    }
                }
            };
        </script>

        <!-- PDF.js for PDF preview -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            if (typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc =
                    'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }
        </script>

        <!-- model-viewer for 3D models -->
        <script type="module" src="https://ajax.googleapis.com/ajax/libs/model-viewer/3.3.0/model-viewer.min.js"></script>

        <!-- Prism.js for code syntax highlighting -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
        <script>
            // Generate browser fingerprint for bot detection
            function generateFingerprint() {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                ctx.textBaseline = 'top';
                ctx.font = '14px Arial';
                ctx.fillText('Fingerprint', 2, 2);

                const fingerprint = [
                    navigator.userAgent,
                    navigator.language,
                    screen.width + 'x' + screen.height,
                    new Date().getTimezoneOffset(),
                    canvas.toDataURL(),
                    navigator.hardwareConcurrency || 0,
                    navigator.deviceMemory || 0,
                    navigator.platform,
                ].join('|');

                // Simple hash function
                let hash = 0;
                for (let i = 0; i < fingerprint.length; i++) {
                    const char = fingerprint.charCodeAt(i);
                    hash = ((hash << 5) - hash) + char;
                    hash = hash & hash; // Convert to 32bit integer
                }

                return Math.abs(hash).toString(16);
            }

            // Set fingerprint in header for free notes
            <?php if($note->price == 0): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    const fingerprint = generateFingerprint();
                    // Store in sessionStorage to reuse
                    if (!sessionStorage.getItem('browser_fingerprint')) {
                        sessionStorage.setItem('browser_fingerprint', fingerprint);
                    }
                });
            <?php endif; ?>
        </script>
        <script>
            const copyTranslations = <?php echo \Illuminate\Support\Js::from([
    'success_title' => __('messages.copy_link_success_title'),
    'success_message' => __('messages.copy_link_success_message'),
    'error_message' => __('messages.copy_link_error_message'),
    'error_title' => __('messages.error'),
])->toHtml() ?>;

            const reportTranslations = <?php echo \Illuminate\Support\Js::from([
    'title' => __('messages.report_modal_title'),
    'reason_label' => __('messages.report_reason_label'),
    'reason_placeholder' => __('messages.report_reason_placeholder'),
    'option_spam' => __('messages.report_reason_spam'),
    'option_harassment' => __('messages.report_reason_harassment'),
    'option_inappropriate' => __('messages.report_reason_inappropriate'),
    'option_fraud' => __('messages.report_reason_fraud'),
    'option_copyright' => __('messages.report_reason_copyright'),
    'option_other' => __('messages.report_reason_other'),
    'description_label' => __('messages.report_description_label'),
    'description_placeholder' => __('messages.report_description_placeholder'),
    'submit' => __('messages.report_submit_button'),
    'cancel' => __('messages.cancel'),
    'validation_reason' => __('messages.report_validation_reason'),
    'success_title' => __('messages.report_success_title'),
    'success_message' => __('messages.report_success_message'),
    'success_alert' => __('messages.report_success_alert'),
    'error_title' => __('messages.error'),
    'error_message' => __('messages.report_error_message'),
    'prompt_reason' => __('messages.report_prompt_reason'),
    'prompt_description' => __('messages.report_prompt_description'),
])->toHtml() ?>;

            // Copy to clipboard function
            function copyToClipboard(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: copyTranslations.success_title,
                                text: copyTranslations.success_message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(copyTranslations.success_message);
                        }
                    }).catch(function(err) {
                        console.error('Failed to copy:', err);
                        // Fallback for older browsers
                        fallbackCopyToClipboard(text);
                    });
                } else {
                    fallbackCopyToClipboard(text);
                }
            }

            // Copy share referral link with commission tracking
            async function copyShareReferralLink(baseUrl) {
                try {
                    // Get referral token from server
                    const response = await fetch(`/api/notes/<?php echo e($note->id); ?>/share-link`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    });

                    let shareUrl = baseUrl;

                    if (response.ok) {
                        const data = await response.json();
                        if (data.share_url) {
                            shareUrl = data.share_url;
                        }
                    }

                    // Copy to clipboard
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(shareUrl);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Link Copied!',
                                html: '<p class="text-sm">Share link with referral copied to clipboard!</p><p class="text-xs text-gray-600 mt-2">You\'ll earn commission when someone purchases through your link.</p>',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Share link copied! You\'ll earn commission when someone purchases.');
                        }
                    } else {
                        fallbackCopyToClipboard(shareUrl);
                    }
                } catch (error) {
                    console.error('Error getting share link:', error);
                    // Fallback to regular URL
                    copyToClipboard(baseUrl);
                }
            }

            // Show share statistics modal
            function showShareStatsModal() {
                <?php if(isset($currentNoteShare) && $currentNoteShare): ?>
                    const stats = {
                        clicks: <?php echo e($currentNoteShare->click_count); ?>,
                        purchases: <?php echo e($currentNoteShare->purchase_count); ?>,
                        commission: <?php echo e($currentNoteShare->total_commission_earned); ?>,
                        revenue: <?php echo e($currentNoteShare->total_revenue_generated); ?>

                    };

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'info',
                            title: 'Share Statistics',
                            html: `
                                <div class="text-left space-y-3">
                                    <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                        <span class="font-medium">Total Clicks:</span>
                                        <span class="font-bold text-blue-600">${stats.clicks}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                        <span class="font-medium">Total Purchases:</span>
                                        <span class="font-bold text-green-600">${stats.purchases}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                        <span class="font-medium">Commission Earned:</span>
                                        <span class="font-bold text-purple-600">Rp ${stats.commission.toLocaleString('id-ID')}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-yellow-50 rounded-lg">
                                        <span class="font-medium">Revenue Generated:</span>
                                        <span class="font-bold text-yellow-600">Rp ${stats.revenue.toLocaleString('id-ID')}</span>
                                    </div>
                                </div>
                            `,
                            confirmButtonText: 'View Full Dashboard',
                            showCancelButton: true,
                            cancelButtonText: 'Close'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?php echo e(route('share.analytics')); ?>';
                            }
                        });
                    } else {
                        alert(
                            `Clicks: ${stats.clicks}\nPurchases: ${stats.purchases}\nCommission: Rp ${stats.commission}\nRevenue: Rp ${stats.revenue}`
                        );
                    }
                <?php endif; ?>
            }

            function fallbackCopyToClipboard(text) {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: copyTranslations.success_title,
                            text: copyTranslations.success_message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert(copyTranslations.success_message);
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire(copyTranslations.error_title, copyTranslations.error_message, 'error');
                    } else {
                        alert(copyTranslations.error_message);
                    }
                }
                document.body.removeChild(textArea);
            }

            const noteReportEndpoint = "<?php echo e(route('notes.report', $note)); ?>";

            function showNoteReportModal() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: reportTranslations.title,
                        html: `
                            <form id="noteReportForm" class="text-left">
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">${reportTranslations.reason_label}</label>
                                    <select id="noteReportReason" name="reason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" required>
                                        <option value="">${reportTranslations.reason_placeholder}</option>
                                        <option value="spam">${reportTranslations.option_spam}</option>
                                        <option value="harassment">${reportTranslations.option_harassment}</option>
                                        <option value="inappropriate">${reportTranslations.option_inappropriate}</option>
                                        <option value="fraud">${reportTranslations.option_fraud}</option>
                                        <option value="copyright">${reportTranslations.option_copyright}</option>
                                        <option value="other">${reportTranslations.option_other}</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">${reportTranslations.description_label}</label>
                                    <textarea id="noteReportDescription" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500" placeholder="${reportTranslations.description_placeholder}" maxlength="1000"></textarea>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: reportTranslations.submit,
                        confirmButtonColor: '#dc2626',
                        cancelButtonText: reportTranslations.cancel,
                        focusConfirm: false,
                        preConfirm: () => {
                            const reason = document.getElementById('noteReportReason').value;
                            const description = document.getElementById('noteReportDescription').value;

                            if (!reason) {
                                Swal.showValidationMessage(reportTranslations.validation_reason);
                                return false;
                            }

                            return {
                                reason,
                                description
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitNoteReport(result.value.reason, result.value.description);
                        }
                    });
                } else {
                    const reason = prompt(reportTranslations.prompt_reason);
                    if (reason) {
                        const description = prompt(reportTranslations.prompt_description);
                        submitNoteReport(reason, description || '');
                    }
                }
            }

            function submitNoteReport(reason, description) {
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfTokenMeta) {
                    console.error('Missing CSRF token meta tag.');
                    return;
                }

                fetch(noteReportEndpoint, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfTokenMeta.content,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            reason,
                            description
                        })
                    })
                    .then(async (response) => {
                        if (!response.ok) {
                            let message = reportTranslations.error_message;
                            try {
                                const data = await response.json();
                                message = data.message || message;
                            } catch (e) {
                                // ignore JSON parse errors
                            }
                            throw new Error(message);
                        }
                        return response.json();
                    })
                    .then((data) => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: reportTranslations.success_title,
                                text: data.message || reportTranslations.success_message,
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert(data.message || reportTranslations.success_alert);
                        }
                    })
                    .catch((error) => {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire(reportTranslations.error_title, error.message || reportTranslations.error_message,
                                'error');
                        } else {
                            alert(error.message || reportTranslations.error_message);
                        }
                    });
            }

            document.addEventListener('DOMContentLoaded', function() {
                const container = document.getElementById('rating-container');
                if (!container) return;

                const ratingInput = document.getElementById('rating-input');
                const stars = container.querySelectorAll('.star-rating');
                let selectedRating = 0;

                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        selectedRating = parseInt(this.dataset.rating);
                        ratingInput.value = selectedRating;

                        stars.forEach((s, index) => {
                            s.querySelector('svg').classList.remove('text-gray-300',
                                'text-yellow-400');
                            if (index < selectedRating) {
                                s.querySelector('svg').classList.add('text-yellow-400');
                            } else {
                                s.querySelector('svg').classList.add('text-gray-300');
                            }
                        });
                    });

                    star.addEventListener('mouseenter', function() {
                        const hoverRating = parseInt(this.dataset.rating);
                        stars.forEach((s, index) => {
                            s.querySelector('svg').classList.remove('text-gray-300',
                                'text-yellow-400');
                            if (index < hoverRating) {
                                s.querySelector('svg').classList.add('text-yellow-400');
                            } else {
                                s.querySelector('svg').classList.add('text-gray-300');
                            }
                        });
                    });
                });

                container.addEventListener('mouseleave', function() {
                    stars.forEach((s, index) => {
                        s.querySelector('svg').classList.remove('text-gray-300', 'text-yellow-400');
                        if (index < selectedRating) {
                            s.querySelector('svg').classList.add('text-yellow-400');
                        } else {
                            s.querySelector('svg').classList.add('text-gray-300');
                        }
                    });
                });

                // Handle review delete confirmation with SweetAlert2
                document.querySelectorAll('.delete-review-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formElement = this;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '<?php echo e(__('messages.are_you_sure')); ?>',
                                text: '<?php echo e(__('messages.delete_confirmation')); ?>',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc2626',
                                cancelButtonColor: '#6b7280',
                                confirmButtonText: '<?php echo e(__('messages.yes_delete')); ?>',
                                cancelButtonText: '<?php echo e(__('messages.no_cancel')); ?>'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    formElement.submit();
                                }
                            });
                        } else {
                            if (confirm('<?php echo e(__('messages.delete_confirmation')); ?>')) {
                                formElement.submit();
                            }
                        }
                    });
                });
            });

            // Grace Period Countdown Timer
            function initGracePeriodCountdown() {
                const countdownElement = document.getElementById('grace-period-countdown');
                if (!countdownElement) {
                    return; // Element doesn't exist, exit gracefully
                }

                const endTimeAttr = countdownElement.getAttribute('data-end-time');
                if (!endTimeAttr) {
                    console.warn('Grace period countdown: data-end-time attribute is missing');
                    return; // Attribute missing, exit gracefully
                }

                const endTime = parseInt(endTimeAttr, 10);
                if (isNaN(endTime) || endTime <= 0) {
                    console.warn('Grace period countdown: invalid data-end-time value:', endTimeAttr);
                    countdownElement.textContent = 'Grace period telah berakhir';
                    countdownElement.classList.remove('text-green-900');
                    countdownElement.classList.add('text-red-600');
                    return; // Invalid value, exit gracefully
                }

                let countdownInterval = null;

                function updateCountdown() {
                    const now = Math.floor(Date.now() / 1000);
                    const timeLeft = endTime - now;

                    if (timeLeft <= 0) {
                        countdownElement.textContent = 'Grace period telah berakhir';
                        countdownElement.classList.remove('text-green-900');
                        countdownElement.classList.add('text-red-600');

                        // Clear interval to stop unnecessary updates
                        if (countdownInterval) {
                            clearInterval(countdownInterval);
                            countdownInterval = null;
                        }
                        return;
                    }

                    const days = Math.floor(timeLeft / 86400);
                    const hours = Math.floor((timeLeft % 86400) / 3600);
                    const minutes = Math.floor((timeLeft % 3600) / 60);
                    const seconds = timeLeft % 60;

                    let countdownText = '';
                    if (days > 0) {
                        countdownText = `${days} hari ${hours} jam ${minutes} menit`;
                    } else if (hours > 0) {
                        countdownText = `${hours} jam ${minutes} menit ${seconds} detik`;
                    } else if (minutes > 0) {
                        countdownText = `${minutes} menit ${seconds} detik`;
                    } else {
                        countdownText = `${seconds} detik`;
                    }

                    countdownElement.textContent = countdownText;
                }

                // Initialize countdown immediately
                updateCountdown();

                // Update countdown every second
                countdownInterval = setInterval(updateCountdown, 1000);
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initGracePeriodCountdown);
            } else {
                // DOM is already ready
                initGracePeriodCountdown();
            }

            <?php if(auth()->check() && auth()->user()->hasPremium() && auth()->user()->role === 'buyer'): ?>
                function showCollectionModal(noteId) {
                    const collections = <?php echo json_encode(auth()->user()->collections()->get(['id', 'name']), 512) ?>;

                    if (collections.length === 0) {
                        Swal.fire({
                            icon: 'info',
                            title: '<?php echo e(__('messages.no_collections_title')); ?>',
                            text: '<?php echo e(__('messages.no_collections_message')); ?>',
                            showCancelButton: true,
                            confirmButtonText: '<?php echo e(__('messages.create_collection_button')); ?>',
                            cancelButtonText: '<?php echo e(__('messages.cancel')); ?>'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '<?php echo e(route('collections.create')); ?>';
                            }
                        });
                        return;
                    }

                    Swal.fire({
                        title: '<?php echo e(__('messages.collection_modal_title')); ?>',
                        input: 'select',
                        inputOptions: Object.fromEntries(collections.map(c => [c.id, c.name])),
                        inputPlaceholder: '<?php echo e(__('messages.collection_modal_placeholder')); ?>',
                        showCancelButton: true,
                        confirmButtonText: '<?php echo e(__('messages.collection_modal_confirm')); ?>',
                        cancelButtonText: '<?php echo e(__('messages.cancel')); ?>',
                        inputValidator: (value) => {
                            if (!value) {
                                return '<?php echo e(__('messages.collection_modal_validation')); ?>';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `/collections/${result.value}/add-note`;
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                                'content');
                            form.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="note_id" value="${noteId}">
                `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }
            <?php endif; ?>
        </script>
        <?php if(auth()->guard()->check()): ?>
            <?php if(auth()->user()->hasPremium() && auth()->user()->role === 'buyer' && ($alreadyPurchased ?? false)): ?>
                <script>
                    // Reading Progress Tracking
                    const noteId = '<?php echo e($note->id); ?>';
                    let progressUpdateTimeout;
                    let currentProgress = 0;

                    // Load existing progress
                    fetch(`/reading-progress/note/${noteId}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.progress) {
                                currentProgress = data.progress.progress_percentage || 0;
                                updateProgressBar(currentProgress);
                            }
                        })
                        .catch(error => console.error('Error loading progress:', error));

                    // Track scroll position
                    const noteContent = document.getElementById('note-content');
                    if (noteContent) {
                        const totalHeight = noteContent.scrollHeight;
                        const viewportHeight = window.innerHeight;
                        const totalScrollable = totalHeight - viewportHeight;

                        window.addEventListener('scroll', () => {
                            clearTimeout(progressUpdateTimeout);

                            progressUpdateTimeout = setTimeout(() => {
                                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                                const contentTop = noteContent.offsetTop;
                                const scrollPosition = Math.max(0, scrollTop - contentTop);

                                const progress = totalScrollable > 0 ?
                                    Math.min(100, Math.round((scrollPosition / totalScrollable) * 100)) :
                                    0;

                                if (progress !== currentProgress) {
                                    currentProgress = progress;
                                    updateProgressBar(progress);
                                    saveProgress(progress, scrollPosition, noteContent.textContent.length);
                                }
                            }, 500); // Debounce: update every 500ms
                        });
                    }

                    function updateProgressBar(percentage) {
                        const progressBar = document.getElementById('progress-bar');
                        const progressPercentage = document.getElementById('progress-percentage');
                        if (progressBar) progressBar.style.width = percentage + '%';
                        if (progressPercentage) progressPercentage.textContent = percentage + '%';
                    }

                    function saveProgress(percentage, position, totalChars) {
                        fetch(`/reading-progress/note/${noteId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    progress_percentage: percentage,
                                    last_position: position,
                                    read_characters: Math.round((percentage / 100) * totalChars),
                                    total_characters: totalChars
                                })
                            })
                            .catch(error => console.error('Error saving progress:', error));
                    }

                    const bookmarkTranslations = <?php echo \Illuminate\Support\Js::from([
    'empty' => __('messages.bookmarks_empty', ['action' => __('messages.add_bookmark')]),
    'default_title' => __('messages.bookmark_default_title'),
    'go_to' => __('messages.bookmark_go_to'),
    'delete' => __('messages.bookmark_delete'),
    'modal_title' => __('messages.bookmark_modal_title'),
    'modal_title_placeholder' => __('messages.bookmark_modal_title_placeholder'),
    'modal_note_placeholder' => __('messages.bookmark_modal_note_placeholder'),
    'modal_confirm' => __('messages.bookmark_modal_confirm'),
    'cancel' => __('messages.cancel'),
    'success_title' => __('messages.bookmark_success_title'),
    'success_message' => __('messages.bookmark_success_message'),
    'error_title' => __('messages.bookmark_error_title'),
    'error_message' => __('messages.bookmark_error_message'),
    'delete_confirm_title' => __('messages.bookmark_delete_confirm_title'),
    'delete_confirm_text' => __('messages.bookmark_delete_confirm_text'),
    'delete_confirm_button' => __('messages.bookmark_delete_confirm_button'),
    'delete_success_title' => __('messages.bookmark_delete_success_title'),
    'delete_success_message' => __('messages.bookmark_delete_success_message'),
    'delete_error_message' => __('messages.bookmark_delete_error_message'),
    'show' => __('messages.bookmarks_toggle_show'),
    'hide' => __('messages.bookmarks_toggle_hide'),
])->toHtml() ?>;

                    // Bookmarks functionality
                    let bookmarks = [];
                    let bookmarksVisible = false;

                    // Load bookmarks
                    function loadBookmarks() {
                        fetch(`/bookmarks/note/${noteId}`, {
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    bookmarks = data.bookmarks || [];
                                    renderBookmarks();
                                }
                            })
                            .catch(error => console.error('Error loading bookmarks:', error));
                    }

                    function renderBookmarks() {
                        const bookmarksList = document.getElementById('bookmarks-list');
                        if (!bookmarksList) return;

                        if (bookmarks.length === 0) {
                            bookmarksList.innerHTML = `<p class="text-sm text-gray-600">${bookmarkTranslations.empty}</p>`;
                            return;
                        }

                        bookmarksList.innerHTML = bookmarks.map(bookmark => `
                <div
                    class="flex items-start justify-between p-2 bg-white rounded border border-purple-200 hover:bg-purple-50 transition-colors">
                    <div class="flex-1">
                        <h5 class="text-sm font-medium text-gray-900">${bookmark.title || bookmarkTranslations.default_title}</h5>
                        ${bookmark.section_text ? `<p class="text-xs text-gray-600 mt-1 line-clamp-2">
                                                                                                                                                                                                                                                                            ${bookmark.section_text.substring(0, 100)}...</p>` : ''}
                        ${bookmark.note_text ? `<p class="text-xs text-purple-700 mt-1">${bookmark.note_text}</p>` : ''}
                    </div>
                    <div class="flex items-center space-x-2 ml-3">
                        <button onclick="scrollToBookmark(${bookmark.position})" class="text-xs text-blue-600 hover:text-blue-800"
                            title="${bookmarkTranslations.go_to}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </button>
                        <button onclick="deleteBookmark('${bookmark.id}')" class="text-xs text-red-600 hover:text-red-800"
                            title="${bookmarkTranslations.delete}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>
                `).join('');
                    }

                    function toggleBookmarks() {
                        const section = document.getElementById('bookmarks-section');
                        const toggleText = document.getElementById('bookmarks-toggle-text');
                        if (section) {
                            bookmarksVisible = !bookmarksVisible;
                            section.classList.toggle('hidden', !bookmarksVisible);
                            if (toggleText) {
                                toggleText.textContent = bookmarksVisible ? bookmarkTranslations.hide : bookmarkTranslations.show;
                            }
                        }
                    }

                    function showAddBookmarkModal() {
                        const noteContent = document.getElementById('note-content');
                        const selection = window.getSelection();
                        let selectedText = '';
                        let position = 0;

                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0);
                            selectedText = range.toString();
                            position = range.startOffset;
                        } else {
                            // Use scroll position as fallback
                            position = window.pageYOffset || document.documentElement.scrollTop;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: bookmarkTranslations.modal_title,
                                html: `
                <input id="bookmark-title" class="swal2-input" placeholder="${bookmarkTranslations.modal_title_placeholder.replace(/"/g, '&quot;')}"
                    value="${selectedText.substring(0, 50) || ''}">
                <textarea id="bookmark-note" class="swal2-textarea" placeholder="${bookmarkTranslations.modal_note_placeholder.replace(/"/g, '&quot;')}"></textarea>
                `,
                                showCancelButton: true,
                                confirmButtonText: bookmarkTranslations.modal_confirm,
                                cancelButtonText: bookmarkTranslations.cancel,
                                preConfirm: () => {
                                    return {
                                        title: document.getElementById('bookmark-title').value || bookmarkTranslations
                                            .default_title,
                                        note_text: document.getElementById('bookmark-note').value || null,
                                        section_text: selectedText || null,
                                        position: position
                                    };
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    createBookmark(result.value);
                                }
                            });
                        }
                    }

                    function createBookmark(data) {
                        fetch(`/bookmarks/note/${noteId}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(data)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    loadBookmarks();
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire(bookmarkTranslations.success_title, bookmarkTranslations.success_message,
                                            'success');
                                    }
                                } else {
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire(bookmarkTranslations.error_title, data.message || bookmarkTranslations
                                            .error_message, 'error');
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Error creating bookmark:', error);
                                if (typeof Swal !== 'undefined') {
                                    Swal.fire(bookmarkTranslations.error_title, bookmarkTranslations.error_message, 'error');
                                }
                            });
                    }

                    function deleteBookmark(bookmarkId) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: bookmarkTranslations.delete_confirm_title,
                                text: bookmarkTranslations.delete_confirm_text,
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: bookmarkTranslations.delete_confirm_button,
                                cancelButtonText: bookmarkTranslations.cancel
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch(`/bookmarks/${bookmarkId}`, {
                                            method: 'DELETE',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                                'Accept': 'application/json'
                                            }
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.success) {
                                                loadBookmarks();
                                                Swal.fire(bookmarkTranslations.delete_success_title, bookmarkTranslations
                                                    .delete_success_message, 'success');
                                            } else {
                                                Swal.fire(bookmarkTranslations.error_title, data.message ||
                                                    bookmarkTranslations.delete_error_message, 'error');
                                            }
                                        })
                                        .catch(error => {
                                            console.error('Error deleting bookmark:', error);
                                            Swal.fire(bookmarkTranslations.error_title, bookmarkTranslations
                                                .delete_error_message, 'error');
                                        });
                                }
                            });
                        }
                    }

                    function scrollToBookmark(position) {
                        window.scrollTo({
                            top: position,
                            behavior: 'smooth'
                        });
                    }

                    // Load bookmarks on page load
                    loadBookmarks();
                </script>
            <?php endif; ?>
        <?php endif; ?>
        <script>
            // Reactions functionality
            function toggleReaction(type) {
                const btn = document.querySelector(`[data-reaction="${type}"]`);
                if (!btn) return;

                // Disable button during request
                const originalDisabled = btn.disabled;
                btn.disabled = true;
                btn.style.opacity = '0.6';

                fetch(`/notes/<?php echo e($note->id); ?>/reactions/toggle`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            reaction_type: type
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update reaction counts
                            if (data.reactions) {
                                Object.keys(data.reactions).forEach(reactionType => {
                                    const countEl = document.getElementById(`reaction-count-${reactionType}`);
                                    if (countEl) {
                                        countEl.textContent = data.reactions[reactionType] || 0;
                                    }
                                });
                            }

                            // Update button states - reset all
                            document.querySelectorAll('.reaction-btn').forEach(button => {
                                button.classList.remove('bg-blue-50', 'border-blue-300', 'bg-red-50',
                                    'border-red-300', 'bg-green-50', 'border-green-300', 'bg-purple-50',
                                    'border-purple-300', 'bg-yellow-50', 'border-yellow-300');
                                button.classList.add('border-gray-300');
                            });

                            // Highlight active reaction
                            if (data.user_reaction) {
                                const activeBtn = document.querySelector(`[data-reaction="${data.user_reaction}"]`);
                                if (activeBtn) {
                                    activeBtn.classList.remove('border-gray-300');
                                    const colorMap = {
                                        'like': ['bg-blue-50', 'border-blue-300'],
                                        'love': ['bg-red-50', 'border-red-300'],
                                        'helpful': ['bg-green-50', 'border-green-300'],
                                        'insightful': ['bg-purple-50', 'border-purple-300'],
                                        'thanks': ['bg-yellow-50', 'border-yellow-300']
                                    };
                                    const colors = colorMap[data.user_reaction];
                                    if (colors) {
                                        activeBtn.classList.add(...colors);
                                    }
                                }
                            }
                        } else {
                            throw new Error(data.message || 'Failed to update reaction');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message || 'Failed to update reaction. Please try again.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Failed to update reaction. Please try again.');
                        }
                    })
                    .finally(() => {
                        // Re-enable button
                        if (btn) {
                            btn.disabled = originalDisabled;
                            btn.style.opacity = '1';
                        }
                    });
            }

            // Comments reply functionality
            function showReplyForm(commentId) {
                const form = document.getElementById(`reply-form-${commentId}`);
                if (form) {
                    form.classList.remove('hidden');
                    const textarea = form.querySelector('textarea');
                    if (textarea) {
                        textarea.focus();
                    }
                }
            }

            function hideReplyForm(commentId) {
                const form = document.getElementById(`reply-form-${commentId}`);
                if (form) {
                    form.classList.add('hidden');
                    const textarea = form.querySelector('textarea');
                    if (textarea) {
                        textarea.value = '';
                    }
                }
            }

            // Q&A helpful functionality
            function markHelpful(questionId, element) {
                const btn = element || (window.event ? window.event.target : null);
                if (!btn) return;

                // Disable button during request
                const originalText = btn.textContent;
                btn.disabled = true;
                btn.style.opacity = '0.6';

                fetch(`/questions/${questionId}/helpful`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            btn.textContent = `<?php echo e(__('Helpful')); ?> (${data.helpful_count || 0})`;
                            btn.disabled = true;
                            btn.classList.add('opacity-50');
                            btn.style.opacity = '0.5';
                        } else {
                            throw new Error(data.message || 'Failed to mark as helpful');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        // Re-enable button on error
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.textContent = originalText;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: error.message || 'Failed to mark as helpful. Please try again.',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Failed to mark as helpful. Please try again.');
                        }
                    });
            }
        </script>

        <!-- Image Modal for Thumbnails -->
        <div id="imageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-75"
            onclick="closeImageModal()">
            <div class="relative max-w-7xl max-h-full p-4" onclick="event.stopPropagation()">
                <button onclick="closeImageModal()"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <img id="modalImage" src="" alt="Thumbnail"
                    class="max-w-full max-h-[90vh] mx-auto rounded-lg shadow-2xl">
            </div>
        </div>

        <script>
            function openImageModal(imageSrc) {
                const modal = document.getElementById('imageModal');
                const modalImage = document.getElementById('modalImage');
                modalImage.src = imageSrc;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }

            function closeImageModal() {
                const modal = document.getElementById('imageModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
            }

            // Close modal on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImageModal();
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\marketplace\show.blade.php ENDPATH**/ ?>