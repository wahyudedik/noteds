<?php $__env->startSection('title', __('featured.admin.index_page_title')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $currencyService = app(\App\Services\CurrencyService::class);
        $baseCurrency = $currencyService->getBaseCurrency();
        $currencySymbol = $currencyService->getCurrencySymbol($baseCurrency);
    ?>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.featured_notes_management')); ?></h2>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">←
                    <?php echo e(__('messages.back_to_dashboard')); ?></a>
            </div>

            <?php if(session('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <!-- Pricing Configuration Shortcut -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-yellow-200 mb-6">
                <div class="px-6 py-4 border-b border-yellow-100 bg-yellow-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                            </svg>
                            <?php echo e(__('featured.admin.pricing_panel_title')); ?>

                        </h3>
                        <p class="text-sm text-gray-600 mt-1"><?php echo e(__('featured.admin.pricing_panel_description')); ?></p>
                    </div>
                    <a href="<?php echo e(route('admin.settings.index')); ?>"
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"><?php echo e(__('featured.admin.pricing_panel_link')); ?></a>
                </div>
                <div class="p-6">
                    <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" class="space-y-6">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="redirect_to" value="<?php echo e(route('admin.featured-notes.index')); ?>">

                        <div class="space-y-4">
                            <?php $__currentLoopData = $featuredLocationLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $location => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <h4 class="text-md font-semibold text-gray-900"><?php echo e($label); ?></h4>
                                        <span
                                            class="text-xs text-gray-500"><?php echo e(__('featured.admin.location_key_label', ['key' => $location])); ?></span>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <?php $__currentLoopData = $featuredDurations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $duration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <label for="featured_price_<?php echo e($location); ?>_<?php echo e($duration); ?>"
                                                    class="block text-sm font-medium text-gray-700 mb-2">
                                                    <?php echo e(__('messages.day_count', ['count' => $duration])); ?>

                                                </label>
                                                <div class="relative">
                                                    <div
                                                        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 text-sm"><?php echo e($currencySymbol); ?></span>
                                                    </div>
                                                    <input type="number"
                                                        name="featured_price[<?php echo e($location); ?>][<?php echo e($duration); ?>]"
                                                        id="featured_price_<?php echo e($location); ?>_<?php echo e($duration); ?>"
                                                        value="<?php echo e(old('featured_price.' . $location . '.' . $duration, $featuredPricing[$location][$duration] ?? 0)); ?>"
                                                        min="0" step="1000" required
                                                        class="block w-full pl-10 pr-3 py-2 border-gray-300 rounded-lg shadow-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500 <?php $__errorArgs = ['featured_price.' . $location . '.' . $duration];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                                </div>
                                                <?php $__errorArgs = ['featured_price.' . $location . '.' . $duration];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                                    <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-sm text-yellow-800">
                            <p class="font-medium mb-1"><?php echo e(__('featured.admin.pricing_panel_note_title')); ?></p>
                            <ul class="space-y-1 text-xs">
                                <li>• <?php echo e(__('featured.admin.pricing_panel_note_items.new_requests')); ?></li>
                                <li>• <?php echo e(__('featured.admin.pricing_panel_note_items.existing_requests')); ?></li>
                                <li>• <?php echo e(__('featured.admin.pricing_panel_note_items.competitive')); ?></li>
                            </ul>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-200">
                            <button type="submit"
                                class="px-6 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors">
                                <?php echo e(__('featured.admin.update_pricing_button')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg p-4">
                    <div class="text-sm text-gray-600"><?php echo e(__('featured.admin.stats.total')); ?></div>
                    <div class="text-2xl font-bold text-gray-900"><?php echo e($stats['total']); ?></div>
                </div>
                <div class="bg-yellow-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-yellow-400">
                    <div class="text-sm text-gray-600"><?php echo e(__('featured.admin.stats.pending')); ?></div>
                    <div class="text-2xl font-bold text-yellow-600"><?php echo e($stats['pending']); ?></div>
                </div>
                <div class="bg-green-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-green-400">
                    <div class="text-sm text-gray-600"><?php echo e(__('featured.admin.stats.active')); ?></div>
                    <div class="text-2xl font-bold text-green-600"><?php echo e($stats['active']); ?></div>
                </div>
                <div class="bg-gray-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-gray-400">
                    <div class="text-sm text-gray-600"><?php echo e(__('featured.admin.stats.expired')); ?></div>
                    <div class="text-2xl font-bold text-gray-600"><?php echo e($stats['expired']); ?></div>
                </div>
                <div class="bg-blue-50 overflow-hidden shadow-sm rounded-lg p-4 border-l-4 border-blue-400">
                    <div class="text-sm text-gray-600"><?php echo e(__('featured.admin.stats.revenue')); ?></div>
                    <div class="text-2xl font-bold text-blue-600"><?php echo e(currency($stats['total_revenue'])); ?></div>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
                <form method="GET" action="<?php echo e(route('admin.featured-notes.index')); ?>" class="flex gap-4">
                    <select name="status" class="rounded-md border-gray-300 shadow-sm">
                        <option value=""><?php echo e(__('featured.admin.filter.status_all')); ?></option>
                        <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.status_pending')); ?></option>
                        <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.status_active')); ?></option>
                        <option value="expired" <?php echo e(request('status') === 'expired' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.status_expired')); ?></option>
                        <option value="cancelled" <?php echo e(request('status') === 'cancelled' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.status_cancelled')); ?></option>
                    </select>
                    <select name="location" class="rounded-md border-gray-300 shadow-sm">
                        <option value=""><?php echo e(__('featured.admin.filter.location_all')); ?></option>
                        <option value="marketplace_grid"
                            <?php echo e(request('location') === 'marketplace_grid' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.locations.marketplace_grid')); ?></option>
                        <option value="marketplace_banner"
                            <?php echo e(request('location') === 'marketplace_banner' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.locations.marketplace_banner')); ?></option>
                        <option value="landing_hero" <?php echo e(request('location') === 'landing_hero' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.locations.landing_hero')); ?></option>
                        <option value="landing_carousel"
                            <?php echo e(request('location') === 'landing_carousel' ? 'selected' : ''); ?>>
                            <?php echo e(__('featured.locations.landing_carousel')); ?></option>
                    </select>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('featured.admin.filter.button')); ?>

                    </button>
                    <?php if(request()->hasAny(['status', 'location'])): ?>
                        <a href="<?php echo e(route('admin.featured-notes.index')); ?>"
                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            <?php echo e(__('featured.admin.filter.clear')); ?>

                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if($featuredNotes->count() > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.note')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.seller')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.location')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.duration')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.price')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.status')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.dates')); ?></th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('featured.admin.table.action')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $featuredNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-6 py-4">
                                            <a href="<?php echo e(route('marketplace.show', $featured->note)); ?>"
                                                class="text-blue-600 hover:text-blue-800">
                                                <?php echo e(Str::limit($featured->note->title, 40)); ?>

                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo e($featured->user->name); ?><br>
                                            <span class="text-xs text-gray-500"><?php echo e($featured->user->email); ?></span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo e(__('featured.locations.' . $featured->location)); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo e(__('messages.day_count', ['count' => $featured->duration_days])); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php echo e(currency($featured->price)); ?>

                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if($featured->status === 'pending'): ?>
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_pending')); ?></span>
                                            <?php elseif($featured->status === 'active'): ?>
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_active')); ?></span>
                                            <?php elseif($featured->status === 'expired'): ?>
                                                <span
                                                    class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_expired')); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_cancelled')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php if($featured->start_date && $featured->end_date): ?>
                                                <span
                                                    class="text-sm text-gray-900"><?php echo e(__('featured.admin.date_range', [
                                                        'start' => $featured->start_date->format('d M Y'),
                                                        'end' => $featured->end_date->format('d M Y'),
                                                    ])); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="text-gray-400"><?php echo e(__('featured.admin.dates_not_set')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="<?php echo e(route('admin.featured-notes.show', $featured)); ?>"
                                                class="text-blue-600 hover:text-blue-800">
                                                <?php echo e(__('featured.admin.view_details')); ?>

                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-4">
                    <?php echo e($featuredNotes->links()); ?>

                </div>
            <?php else: ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-12 text-center">
                    <p class="text-gray-500"><?php echo e(__('messages.no_featured_notes_found')); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\featured-notes\index.blade.php ENDPATH**/ ?>