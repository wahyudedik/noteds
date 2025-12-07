<?php $__env->startSection('title', __('messages.admin_users')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.users_management')); ?></h2>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_dashboard')); ?></a>
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

        <!-- Filter -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" action="<?php echo e(route('admin.users.index')); ?>" class="flex gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" :placeholder="__('messages.search_name_or_email')"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="role" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_roles')); ?></option>
                    <option value="admin" <?php echo e(request('role') === 'admin' ? 'selected' : ''); ?>><?php echo e(__('messages.admin')); ?></option>
                    <option value="seller" <?php echo e(request('role') === 'seller' ? 'selected' : ''); ?>><?php echo e(__('messages.seller')); ?></option>
                    <option value="buyer" <?php echo e(request('role') === 'buyer' ? 'selected' : ''); ?>><?php echo e(__('messages.buyer')); ?></option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.filter')); ?>

                </button>
                <?php if(request()->hasAny(['search', 'role'])): ?>
                    <a href="<?php echo e(route('admin.users.index')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                <?php endif; ?>
            </form>
        </div>

        <?php if($users->count() > 0): ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.name')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.email')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.role')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.status')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.balance')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.joined')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.action')); ?></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo e($user->name); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($user->email); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if($user->role === 'admin'): ?>
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.admin')); ?></span>
                                        <?php elseif($user->role === 'seller'): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.seller')); ?></span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.buyer')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($user->suspended_at): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                                <?php echo e(__('messages.user_status_suspended')); ?>

                                            </span>
                                        <?php elseif(! $user->is_active): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                                <?php echo e(__('messages.user_status_inactive')); ?>

                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                <?php echo e(__('messages.user_status_active')); ?>

                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo e(currency($user->wallet_balance ?? 0)); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo e($user->created_at->format('d M Y')); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a href="<?php echo e(route('admin.users.show', $user)); ?>" class="text-blue-600 hover:text-blue-800"><?php echo e(__('messages.view')); ?></a>
                                            <a href="<?php echo e(route('admin.users.edit', $user)); ?>" class="text-green-600 hover:text-green-800"><?php echo e(__('messages.edit')); ?></a>

                                            <?php if($user->id !== auth()->id()): ?>
                                                <?php if($user->isAccessible()): ?>
                                                    <form method="POST" action="<?php echo e(route('admin.users.deactivate', $user)); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('<?php echo e(__('messages.confirm_deactivate_user')); ?>');">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-yellow-600 hover:text-yellow-800"><?php echo e(__('messages.deactivate')); ?></button>
                                                    </form>
                                                    <form method="POST" action="<?php echo e(route('admin.users.suspend', $user)); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('<?php echo e(__('messages.confirm_suspend_user')); ?>');">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-red-600 hover:text-red-800"><?php echo e(__('messages.suspend')); ?></button>
                                                    </form>
                                                <?php elseif($user->suspended_at): ?>
                                                    <form method="POST" action="<?php echo e(route('admin.users.release', $user)); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('<?php echo e(__('messages.confirm_release_user')); ?>');">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-green-600 hover:text-green-800"><?php echo e(__('messages.release_suspend')); ?></button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" action="<?php echo e(route('admin.users.activate', $user)); ?>" class="inline-flex items-center gap-2" onsubmit="return confirm('<?php echo e(__('messages.confirm_activate_user')); ?>');">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="text-green-600 hover:text-green-800"><?php echo e(__('messages.activate')); ?></button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 px-6 pb-6">
                    <?php echo e($users->links()); ?>

                </div>
            </div>
        <?php else: ?>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
                <p class="text-gray-600"><?php echo e(__('messages.no_users_found')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\users\index.blade.php ENDPATH**/ ?>