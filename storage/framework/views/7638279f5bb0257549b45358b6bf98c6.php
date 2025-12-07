<?php $__env->startSection('title', __('messages.vendor_list') . ' — ' . __('messages.admin')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-xl font-semibold text-slate-900"><?php echo e(__('messages.vendor_list')); ?></h1>
                    <p class="text-sm text-slate-600"><?php echo e(__('messages.manage_vendors')); ?></p>
                </div>
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:underline text-sm"><?php echo e(__('messages.back_to_dashboard')); ?></a>
            </div>

            <?php if(session('success')): ?>
                <div class="mb-4 p-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
                <div class="mb-4 p-3 rounded bg-red-50 border border-red-200 text-red-800 text-sm">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>

            <div class="mb-6 p-4 rounded border border-yellow-200 bg-yellow-50">
                <div class="text-sm font-medium text-yellow-800"><?php echo e(__('messages.quick_assign_order')); ?></div>
                <form method="POST" action="<?php echo e(route('admin.vendors.assign')); ?>" class="mt-3 flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                    <?php echo csrf_field(); ?>
                    <input type="text" name="order_id" placeholder="<?php echo e(__('messages.order_id')); ?>" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                    <select name="vendor_id" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                        <option value=""><?php echo e(__('messages.select_vendor')); ?></option>
                        <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->name); ?> (<?php echo e($vendor->email); ?>)</option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-md bg-yellow-600 text-white text-sm"><?php echo e(__('messages.assign')); ?></button>
                </form>
                <p class="mt-2 text-xs text-yellow-700"><?php echo e(__('messages.enter_order_id')); ?></p>
            </div>

            <div class="mb-6 p-4 rounded border border-blue-200 bg-blue-50">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-blue-800"><?php echo e(__('messages.bulk_assign_orders')); ?></div>
                        <div class="text-xs text-blue-700"><?php echo e(__('messages.bulk_assign_description')); ?></div>
                    </div>
                </div>
                <form method="POST" action="<?php echo e(route('admin.vendors.bulk-assign')); ?>" class="mt-3 space-y-3">
                    <?php echo csrf_field(); ?>
                    <div class="overflow-x-auto border rounded">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2"><input type="checkbox" onclick="document.querySelectorAll('input[name^=order_ids]').forEach(cb=>cb.checked=this.checked)"></th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.title')); ?></th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.buyer')); ?></th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.order_status')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__empty_1 = true; $__currentLoopData = $unassignedOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="px-4 py-2"><input type="checkbox" name="order_ids[]" value="<?php echo e($o->id); ?>"></td>
                                        <td class="px-4 py-2">
                                            <div class="font-medium text-slate-900"><a href="<?php echo e(route('studio.orders.show', $o)); ?>" class="text-blue-600 hover:underline"><?php echo e($o->title); ?></a></div>
                                        </td>
                                        <td class="px-4 py-2 text-slate-700"><?php echo e($o->user?->name); ?></td>
                                        <td class="px-4 py-2 text-slate-700"><?php echo e(ucfirst(str_replace('_',' ',$o->status))); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr><td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500"><?php echo e(__('messages.no_unassigned_orders')); ?></td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                        <select name="vendor_id" class="w-full sm:w-72 rounded-lg border-gray-300" required>
                            <option value=""><?php echo e(__('messages.select_vendor')); ?></option>
                            <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($vendor->id); ?>"><?php echo e($vendor->name); ?> (<?php echo e($vendor->email); ?>)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm"><?php echo e(__('messages.bulk_assign')); ?></button>
                        <div class="ml-auto"><?php echo e($unassignedOrders->links()); ?></div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.name')); ?></th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.email')); ?></th>
                            <th class="px-4 py-2 text-left font-medium text-gray-700"><?php echo e(__('messages.assigned_orders')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vendor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-2 font-medium text-slate-900"><?php echo e($vendor->name); ?></td>
                                <td class="px-4 py-2 text-slate-700"><?php echo e($vendor->email); ?></td>
                                <td class="px-4 py-2 text-slate-700">
                                    <?php
                                        $count = \App\Models\ServiceOrder::where('assigned_user_id', $vendor->id)->count();
                                    ?>
                                    <?php echo e($count); ?> <?php echo e($count === 1 ? __('messages.order_singular') : __('messages.orders')); ?>

                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <?php echo e($vendors->links()); ?>

            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\vendors\index.blade.php ENDPATH**/ ?>