<?php $__env->startSection('title', __('messages.admin_notes')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.notes_management')); ?></h2>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_dashboard')); ?></a>
        </div>

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.notes.index')); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" :placeholder="__('messages.search_title_or_content')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="is_public" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_visibility')); ?></option>
                    <option value="1" <?php echo e(request('is_public') === '1' ? 'selected' : ''); ?>><?php echo e(__('messages.public')); ?></option>
                    <option value="0" <?php echo e(request('is_public') === '0' ? 'selected' : ''); ?>><?php echo e(__('messages.private')); ?></option>
                </select>
                <select name="status" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_status')); ?></option>
                    <option value="active" <?php echo e(request('status') === 'active' ? 'selected' : ''); ?>><?php echo e(__('messages.active')); ?></option>
                    <option value="sold" <?php echo e(request('status') === 'sold' ? 'selected' : ''); ?>><?php echo e(__('messages.sold')); ?></option>
                    <option value="inactive" <?php echo e(request('status') === 'inactive' ? 'selected' : ''); ?>><?php echo e(__('messages.inactive')); ?></option>
                </select>
                <select name="sale_mode" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_sale_mode')); ?></option>
                    <option value="scarcity" <?php echo e(request('sale_mode') === 'scarcity' ? 'selected' : ''); ?>><?php echo e(__('messages.scarcity_mode')); ?></option>
                    <option value="standard" <?php echo e(request('sale_mode') === 'standard' ? 'selected' : ''); ?>><?php echo e(__('messages.standard_mode')); ?></option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.filter')); ?>

                    </button>
                    <?php if(request()->hasAny(['search', 'is_public', 'status', 'sale_mode'])): ?>
                        <a href="<?php echo e(route('admin.notes.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                            <?php echo e(__('messages.clear')); ?>

                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if($notes->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.title')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.owner')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.price')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.sale_mode')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.visibility')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.monetization')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.created')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <?php echo e(Str::limit($note->title, 50)); ?>

                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <?php echo e($note->user->name); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if($note->price > 0): ?>
                                            <?php echo e(currency($note->price)); ?>

                                        <?php else: ?>
                                            <span class="text-gray-400"><?php echo e(__('messages.free')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($note->sale_mode): ?>
                                            <?php if($note->isScarcityMode()): ?>
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.scarcity')); ?></span>
                                            <?php elseif($note->isStandardMode()): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.standard')); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($note->is_public): ?>
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.public')); ?></span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.private')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo e($note->status); ?></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($note->price == 0): ?>
                                            <?php if($note->monetization_approved || $note->monetization_auto_approved): ?>
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                    <?php echo e(__('messages.approved')); ?>

                                                    <?php if($note->monetization_auto_approved): ?>
                                                        (<?php echo e(__('messages.auto')); ?>)
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.pending')); ?></span>
                                                <div class="mt-1 flex gap-1">
                                                    <form method="POST" action="<?php echo e(route('admin.notes.approve-monetization', $note)); ?>" class="inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-xs bg-green-500 hover:bg-green-700 text-white px-2 py-1 rounded"><?php echo e(__('messages.approve')); ?></button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('admin.notes.reject-monetization', $note)); ?>" class="inline" onsubmit="return confirm('<?php echo e(__('messages.confirm_reject_monetization')); ?>')">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="admin_notes" value="<?php echo e(__('messages.rejected_by_admin')); ?>">
                                                        <button type="submit" class="text-xs bg-red-500 hover:bg-red-700 text-white px-2 py-1 rounded"><?php echo e(__('messages.reject')); ?></button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($note->created_at->format('d M Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="<?php echo e(route('marketplace.show', $note)); ?>" class="text-blue-600 hover:text-blue-800"><?php echo e(__('messages.view')); ?></a>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    <?php echo e($notes->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600"><?php echo e(__('messages.no_notes_found')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\notes\index.blade.php ENDPATH**/ ?>