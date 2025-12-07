<?php $__env->startSection('title', __('messages.view_history_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.view_history_title')); ?></h2>
            <div class="flex gap-2">
                <a href="<?php echo e(route('admin.view-history.export', request()->all())); ?>" 
                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.export_csv')); ?>

                </a>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_dashboard')); ?></a>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_views')); ?></div>
                <div class="text-2xl font-bold text-gray-900"><?php echo e(number_format($stats['total_views'])); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.valid_views')); ?></div>
                <div class="text-2xl font-bold text-green-600"><?php echo e(number_format($stats['valid_views'])); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_revenue')); ?></div>
                <div class="text-2xl font-bold text-blue-600">Rp <?php echo e(number_format($stats['total_revenue'], 2, ',', '.')); ?></div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="text-sm font-medium text-gray-500"><?php echo e(__('messages.today_revenue')); ?></div>
                <div class="text-2xl font-bold text-purple-600">Rp <?php echo e(number_format($stats['today_revenue'], 2, ',', '.')); ?></div>
                <div class="text-xs text-gray-500 mt-1"><?php echo e(number_format($stats['today_views'])); ?> <?php echo e(__('messages.views_today')); ?></div>
            </div>
        </div>

        <!-- Status Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-sm font-medium text-yellow-800"><?php echo e(__('messages.pending_validation')); ?></div>
                <div class="text-xl font-bold text-yellow-900"><?php echo e(number_format($stats['pending_views'])); ?></div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-sm font-medium text-red-800"><?php echo e(__('messages.rejected_views')); ?></div>
                <div class="text-xl font-bold text-red-900"><?php echo e(number_format($stats['rejected_views'])); ?></div>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.view-history.index')); ?>" class="flex gap-4 flex-wrap">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.search_by_note_user_ip')); ?>"
                    class="flex-1 min-w-[200px] rounded-md border-gray-300 shadow-sm">
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_status')); ?></option>
                    <option value="pending" <?php echo e(request('status') === 'pending' ? 'selected' : ''); ?>><?php echo e(__('messages.pending')); ?></option>
                    <option value="approved" <?php echo e(request('status') === 'approved' ? 'selected' : ''); ?>><?php echo e(__('messages.approved')); ?></option>
                    <option value="rejected" <?php echo e(request('status') === 'rejected' ? 'selected' : ''); ?>><?php echo e(__('messages.rejected')); ?></option>
                </select>
                <select name="valid" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all')); ?></option>
                    <option value="1" <?php echo e(request('valid') === '1' ? 'selected' : ''); ?>><?php echo e(__('messages.valid_only')); ?></option>
                    <option value="0" <?php echo e(request('valid') === '0' ? 'selected' : ''); ?>><?php echo e(__('messages.invalid_only')); ?></option>
                </select>
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="rounded-md border-gray-300 shadow-sm">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="rounded-md border-gray-300 shadow-sm">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.filter')); ?>

                </button>
                <?php if(request()->hasAny(['search', 'status', 'valid', 'date_from', 'date_to'])): ?>
                    <a href="<?php echo e(route('admin.view-history.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if($viewRevenues->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.note')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.user')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.amount')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.ip_address')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.viewed_at')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $viewRevenues; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $viewRevenue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo e($viewRevenue->note->title ?? 'N/A'); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo e(__('messages.owner')); ?>: <?php echo e($viewRevenue->note->user->name ?? 'N/A'); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($viewRevenue->user): ?>
                                            <div class="text-sm text-gray-900"><?php echo e($viewRevenue->user->name); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo e($viewRevenue->user->email); ?></div>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500"><?php echo e(__('messages.guest')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        Rp <?php echo e(number_format($viewRevenue->amount, 2, ',', '.')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($viewRevenue->ip_address); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col gap-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                <?php echo e($viewRevenue->validation_status === 'approved' ? 'bg-green-100 text-green-800' : 
                                                   ($viewRevenue->validation_status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')); ?>">
                                                <?php echo e(ucfirst($viewRevenue->validation_status)); ?>

                                            </span>
                                            <?php if(!$viewRevenue->is_valid): ?>
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    <?php echo e(__('messages.invalid')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($viewRevenue->viewed_at->format('M d, Y H:i')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="<?php echo e(route('admin.view-history.show', $viewRevenue)); ?>" class="text-blue-600 hover:text-blue-900"><?php echo e(__('messages.view_details')); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                <?php echo e($viewRevenues->links()); ?>

            </div>
        <?php else: ?>
            <div class="bg-white shadow-sm rounded-lg p-8 text-center">
                <p class="text-gray-500"><?php echo e(__('messages.no_view_history_found')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\view-history\index.blade.php ENDPATH**/ ?>