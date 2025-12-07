<?php $__env->startSection('title', __('messages.view_revenue_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.view_revenue_details')); ?></h2>
            <a href="<?php echo e(route('admin.view-history.index')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_view_history')); ?></a>
        </div>

        <!-- View Revenue Info -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.view_information')); ?></h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.note')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <a href="<?php echo e(route('marketplace.show', $viewRevenue->note)); ?>" class="text-blue-600 hover:text-blue-800">
                            <?php echo e($viewRevenue->note->title ?? 'N/A'); ?>

                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.note_owner')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($viewRevenue->note->user->name ?? 'N/A'); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.viewer')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        <?php if($viewRevenue->user): ?>
                            <?php echo e($viewRevenue->user->name); ?> (<?php echo e($viewRevenue->user->email); ?>)
                        <?php else: ?>
                            <span class="text-gray-500"><?php echo e(__('messages.guest_user')); ?></span>
                        <?php endif; ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.amount')); ?></dt>
                    <dd class="mt-1 text-sm font-semibold text-green-600">Rp <?php echo e(number_format($viewRevenue->amount, 2, ',', '.')); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.ip_address')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono"><?php echo e($viewRevenue->ip_address); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.fingerprint')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono text-xs"><?php echo e(Str::limit($viewRevenue->fingerprint, 40)); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.validation_status')); ?></dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php echo e($viewRevenue->validation_status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($viewRevenue->validation_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                            <?php echo e(ucfirst($viewRevenue->validation_status)); ?>

                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.is_valid')); ?></dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            <?php echo e($viewRevenue->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                            <?php echo e($viewRevenue->is_valid ? __('messages.yes') : __('messages.no')); ?>

                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.user_agent')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900 font-mono text-xs"><?php echo e(Str::limit($viewRevenue->user_agent, 100)); ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.viewed_at')); ?></dt>
                    <dd class="mt-1 text-sm text-gray-900"><?php echo e($viewRevenue->viewed_at->format('M d, Y H:i:s')); ?></dd>
                </div>
                <?php if($viewRevenue->rejection_reason): ?>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.rejection_reason')); ?></dt>
                        <dd class="mt-1 text-sm text-red-600"><?php echo e($viewRevenue->rejection_reason); ?></dd>
                    </div>
                <?php endif; ?>
                <?php if($viewRevenue->bot_detection_data): ?>
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500"><?php echo e(__('messages.bot_detection_data')); ?></dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto"><?php echo e(json_encode($viewRevenue->bot_detection_data, JSON_PRETTY_PRINT)); ?></pre>
                        </dd>
                    </div>
                <?php endif; ?>
            </dl>
        </div>

        <!-- Related Views -->
        <?php if($relatedViews->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.related_views')); ?> (<?php echo e(__('messages.same_ip_fingerprint')); ?>)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.note')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.user')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.amount')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.viewed_at')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $relatedViews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $relatedView): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo e($relatedView->note->title ?? 'N/A'); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($relatedView->user->name ?? __('messages.guest')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp <?php echo e(number_format($relatedView->amount, 2, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            <?php echo e($relatedView->validation_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                               ($relatedView->validation_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                                            <?php echo e(ucfirst($relatedView->validation_status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($relatedView->viewed_at->format('M d, Y H:i')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\view-history\show.blade.php ENDPATH**/ ?>