<?php $__env->startSection('title', __('messages.user_verification_pending_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.verification_pending_title')); ?></h2>
                <p class="text-sm text-gray-600 mt-1"><?php echo e(__('messages.verification_pending_description')); ?></p>
            </div>
            <div class="flex gap-4 items-center">
                <?php if($pendingCount > 0): ?>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                        <?php echo e($pendingCount); ?> <?php echo e(__('messages.pending_count')); ?>

                    </span>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_users')); ?></a>
            </div>
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
            <form method="GET" action="<?php echo e(route('admin.users.pending-verification')); ?>" class="flex gap-4">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="<?php echo e(__('messages.search_name_email')); ?>"
                    class="rounded-md border-gray-300 shadow-sm">
                <select name="role" class="rounded-md border-gray-300 shadow-sm">
                    <option value=""><?php echo e(__('messages.all_roles')); ?></option>
                    <option value="seller" <?php echo e(request('role') === 'seller' ? 'selected' : ''); ?>><?php echo e(__('messages.seller')); ?></option>
                    <option value="buyer" <?php echo e(request('role') === 'buyer' ? 'selected' : ''); ?>><?php echo e(__('messages.buyer')); ?></option>
                </select>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.filter')); ?>

                </button>
                <?php if(request()->hasAny(['search', 'role'])): ?>
                    <a href="<?php echo e(route('admin.users.pending-verification')); ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.verification_status_label')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.identity_document_selfie')); ?></th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo e(__('messages.upload_date')); ?></th>
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
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">Admin</span>
                                        <?php elseif($user->role === 'seller'): ?>
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">Seller</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs">Buyer</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                            <?php echo e(__('messages.pending')); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-col gap-2">
                                            <?php if($user->ktp_path): ?>
                                                <div>
                                                    <span class="text-xs text-gray-600">
                                                        <?php echo e(__('messages.document_type_label_short')); ?>: <?php echo e($user->document_type === 'kartu_pelajar' ? __('messages.student_card_short') : __('messages.ktp_short')); ?>

                                                    </span>
                                                    <a href="<?php echo e(route('admin.users.download-doc', ['user' => $user->id, 'type' => 'ktp'])); ?>" 
                                                       class="block text-blue-600 hover:text-blue-800 text-xs mt-1">
                                                        <?php echo e(__('messages.download')); ?> <?php echo e($user->document_type === 'kartu_pelajar' ? __('messages.student_card_short') : __('messages.ktp_short')); ?>

                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs"><?php echo e(__('messages.document_not_available')); ?></span>
                                            <?php endif; ?>
                                            <?php if($user->selfie_path): ?>
                                                <a href="<?php echo e(route('admin.users.download-doc', ['user' => $user->id, 'type' => 'selfie'])); ?>" 
                                                   class="text-blue-600 hover:text-blue-800 text-xs"><?php echo e(__('messages.download')); ?> <?php echo e(__('messages.selfie')); ?></a>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs"><?php echo e(__('messages.selfie_not_available')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php
                                            $ktpDate = $user->ktp_path ? \Carbon\Carbon::parse($user->updated_at) : null;
                                            $selfieDate = $user->selfie_path ? \Carbon\Carbon::parse($user->updated_at) : null;
                                            $latestDate = $ktpDate && $selfieDate ? max($ktpDate, $selfieDate) : ($ktpDate ?? $selfieDate ?? $user->updated_at);
                                        ?>
                                        <?php echo e($latestDate ? (is_object($latestDate) ? $latestDate->format('d M Y H:i') : \Carbon\Carbon::parse($latestDate)->format('d M Y H:i')) : '-'); ?>

                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="<?php echo e(route('admin.users.show', $user)); ?>" 
                                               class="text-blue-600 hover:text-blue-800 text-xs"><?php echo e(__('messages.detail')); ?></a>
                                            <form method="POST" action="<?php echo e(route('admin.users.verify.approve', $user)); ?>" class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="notes" value="<?php echo e(__('messages.verified_by_admin', [], app()->getLocale())); ?>">
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-800 text-xs"
                                                        onclick="return confirm('<?php echo e(__('messages.approve_verification_confirm')); ?>');"><?php echo e(__('messages.approve')); ?></button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.users.verify.reject', $user)); ?>" 
                                                  class="inline"
                                                  onsubmit="return confirmReject(this);">
                                                <?php echo csrf_field(); ?>
                                                <input type="text" name="reason" placeholder="<?php echo e(__('messages.reject_reason_placeholder')); ?>" 
                                                       class="rounded border-gray-300 text-xs w-32" required>
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs"><?php echo e(__('messages.reject')); ?></button>
                                            </form>
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
                <p class="text-gray-600"><?php echo e(__('messages.no_users_need_verification')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function confirmReject(form) {
    const reason = form.querySelector('input[name="reason"]').value;
    if (!reason || reason.trim() === '') {
        alert('<?php echo e(__('messages.please_fill_reject_reason')); ?>');
        return false;
    }
    return confirm('<?php echo e(__('messages.confirm_reject_verification')); ?>');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\users\pending-verification.blade.php ENDPATH**/ ?>