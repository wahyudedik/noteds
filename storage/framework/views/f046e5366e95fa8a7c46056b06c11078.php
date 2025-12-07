<?php $__env->startSection('title', __('messages.my_featured_notes')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.my_featured_notes')); ?></h1>
                <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.manage_featured_notes')); ?></p>
            </div>
            <a href="<?php echo e(route('featured-notes.create')); ?>" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                + <?php echo e(__('messages.request_featured')); ?>

            </a>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <p class="text-sm font-medium text-green-800"><?php echo e(session('success')); ?></p>
            </div>
        <?php endif; ?>

        <!-- Analytics Dashboard -->
        <?php if(isset($analytics)): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_total_impressions')); ?></div>
                    <div class="text-3xl font-bold text-blue-600"><?php echo e(number_format($analytics['total_impressions'], 0, ',', '.')); ?></div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_total_clicks')); ?></div>
                    <div class="text-3xl font-bold text-green-600"><?php echo e(number_format($analytics['total_clicks'], 0, ',', '.')); ?></div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_average_ctr')); ?></div>
                    <div class="text-3xl font-bold text-purple-600"><?php echo e($analytics['avg_ctr']); ?>%</div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_total_spent')); ?></div>
                    <div class="text-3xl font-bold text-red-600"><?php echo e(currency($analytics['total_spent'])); ?></div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_active_count')); ?></div>
                    <div class="text-2xl font-bold text-orange-600"><?php echo e($analytics['active_count']); ?></div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_revenue')); ?></div>
                    <div class="text-2xl font-bold text-green-600"><?php echo e(currency($analytics['revenue_from_featured'])); ?></div>
                </div>
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                    <div class="text-sm font-medium text-gray-600 mb-1"><?php echo e(__('featured.analytics_roi')); ?></div>
                    <div class="text-2xl font-bold <?php echo e($analytics['roi'] >= 100 ? 'text-green-600' : ($analytics['roi'] > 0 ? 'text-yellow-600' : 'text-red-600')); ?>">
                        <?php echo e($analytics['roi']); ?>%
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($featuredNotes->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_note')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_location')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_duration')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_price')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_status')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_analytics')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('featured.table_date')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $featuredNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?php echo e(route('marketplace.show', $featured->note)); ?>" class="text-blue-600 hover:text-blue-800">
                                        <?php echo e(Str::limit($featured->note->title, 40)); ?>

                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e(__('featured.locations.' . $featured->location)); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e(__('messages.day_count', ['count' => $featured->duration_days])); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e(currency($featured->price)); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($featured->status === 'pending'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full"><?php echo e(__('featured.status_pending')); ?></span>
                                    <?php elseif($featured->status === 'active'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full"><?php echo e(__('featured.status_active')); ?></span>
                                    <?php elseif($featured->status === 'expired'): ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full"><?php echo e(__('featured.status_expired')); ?></span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full"><?php echo e(__('featured.status_cancelled')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php if($featured->status === 'active' || $featured->status === 'expired'): ?>
                                        <div class="space-y-1">
                                        <div>👁️ <?php echo e(__('featured.impressions_label')); ?>: <?php echo e(number_format($featured->impressions, 0, ',', '.')); ?></div>
                                        <div>👆 <?php echo e(__('featured.clicks_label')); ?>: <?php echo e(number_format($featured->clicks, 0, ',', '.')); ?></div>
                                            <?php if($featured->impressions > 0): ?>
                                                <div class="text-xs text-gray-500"><?php echo e(__('featured.ctr_label')); ?>: <?php echo e(number_format(($featured->clicks / $featured->impressions) * 100, 2)); ?>%</div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($featured->created_at->format('d M Y')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <?php echo e($featuredNotes->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
                <p class="text-gray-500 mb-4"><?php echo e(__('featured.no_featured_notes_message')); ?></p>
                <a href="<?php echo e(route('featured-notes.create')); ?>" class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                    <?php echo e(__('featured.request_cta')); ?>

                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\featured-notes\index.blade.php ENDPATH**/ ?>