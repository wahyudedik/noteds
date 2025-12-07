<?php $__env->startSection('title', __('messages.admin_user_detail')); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="<?php echo e(route('admin.users.index')); ?>"
                    class="text-blue-600 hover:text-blue-800"><?php echo e(__('messages.back_to_users')); ?></a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.identity_verification_title')); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <p>
                            <strong><?php echo e(__('messages.status_label')); ?></strong>
                            <?php if($user->verification_status === 'approved'): ?>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700"><?php echo e(__('messages.approved')); ?></span>
                            <?php elseif($user->verification_status === 'rejected'): ?>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700"><?php echo e(__('messages.rejected')); ?></span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700"><?php echo e(__('messages.pending')); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php if($user->agreement_accepted_at): ?>
                            <p><strong><?php echo e(__('messages.agreement_label')); ?></strong> <?php echo e(__('messages.accepted')); ?>

                                <?php echo e($user->agreement_accepted_at->format('d M Y H:i')); ?>

                                (<?php echo e($user->agreement_version ?? 'v1'); ?>)</p>
                        <?php endif; ?>
                        <?php if($user->verification_reviewed_at): ?>
                            <p><strong><?php echo e(__('messages.reviewed_label')); ?></strong>
                                <?php echo e($user->verification_reviewed_at->format('d M Y H:i')); ?></p>
                        <?php endif; ?>
                        <?php if($user->verification_notes): ?>
                            <p class="text-sm"><strong><?php echo e(__('messages.notes_label')); ?></strong>
                                <?php echo e($user->verification_notes); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="space-y-3">
                        <div class="flex gap-3">
                            <a href="<?php echo e($user->ktp_path ? route('admin.users.download-doc', ['user' => $user->id, 'type' => 'ktp']) : '#'); ?>"
                                class="px-3 py-2 text-xs rounded-md <?php echo e($user->ktp_path ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-400 cursor-not-allowed'); ?>"
                                <?php if(!$user->ktp_path): ?> aria-disabled="true" <?php endif; ?>><?php echo e(__('messages.download_ktp')); ?></a>
                            <a href="<?php echo e($user->selfie_path ? route('admin.users.download-doc', ['user' => $user->id, 'type' => 'selfie']) : '#'); ?>"
                                class="px-3 py-2 text-xs rounded-md <?php echo e($user->selfie_path ? 'bg-slate-100 hover:bg-slate-200 text-slate-800' : 'bg-slate-50 text-slate-400 cursor-not-allowed'); ?>"
                                <?php if(!$user->selfie_path): ?> aria-disabled="true" <?php endif; ?>><?php echo e(__('messages.download_selfie')); ?></a>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <form method="POST" action="<?php echo e(route('admin.users.verify.approve', $user)); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="notes" value="">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md"
                                    <?php if($user->verification_status === 'approved'): ?> disabled <?php endif; ?>><?php echo e(__('messages.approve')); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('admin.users.verify.reject', $user)); ?>"
                                onsubmit="return confirmRejectIdentity(this);">
                                <?php echo csrf_field(); ?>
                                <input type="text" name="reason"
                                    placeholder="<?php echo e(__('messages.rejection_reason_required')); ?>"
                                    class="rounded-md border-gray-300 text-sm">
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md"><?php echo e(__('messages.reject')); ?></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900"><?php echo e(__('messages.user_detail', ['name' => $user->name])); ?></h2>
                <a href="<?php echo e(route('admin.users.edit', $user)); ?>"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <?php echo e(__('messages.edit_user')); ?>

                </a>
            </div>

            <?php if(session('success')): ?>
                <?php $__env->startPush('scripts'); ?>
                    <script>
                        (function() {
                            if (typeof window.showSuccess === 'function') {
                                window.showSuccess(<?php echo json_encode(session('success'), 15, 512) ?>);
                            } else {
                                setTimeout(arguments.callee, 100);
                            }
                        })();
                    </script>
                <?php $__env->stopPush(); ?>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <?php $__env->startPush('scripts'); ?>
                    <script>
                        (function() {
                            if (typeof window.showError === 'function') {
                                window.showError(<?php echo json_encode(session('error'), 15, 512) ?>);
                            } else {
                                setTimeout(arguments.callee, 100);
                            }
                        })();
                    </script>
                <?php $__env->stopPush(); ?>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.user_information')); ?></h3>
                        <div class="space-y-2">
                            <p><strong><?php echo e(__('messages.name')); ?>:</strong> <?php echo e($user->name); ?></p>
                            <p><strong><?php echo e(__('messages.email')); ?>:</strong> <?php echo e($user->email); ?></p>
                            <p><strong><?php echo e(__('messages.role')); ?>:</strong>
                                <?php if($user->role === 'admin'): ?>
                                    <span
                                        class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.admin')); ?></span>
                                <?php elseif($user->role === 'seller'): ?>
                                    <span
                                        class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.seller')); ?></span>
                                <?php else: ?>
                                    <span
                                        class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.buyer')); ?></span>
                                <?php endif; ?>
                            </p>
                            <p><strong><?php echo e(__('messages.wallet_balance_label')); ?>:</strong>
                                <?php echo e(currency($user->wallet_balance ?? 0)); ?></p>
                            <p><strong><?php echo e(__('messages.joined')); ?>:</strong> <?php echo e($user->created_at->format('d M Y, H:i')); ?>

                            </p>
                            <p>
                                <strong><?php echo e(__('messages.status')); ?>:</strong>
                                <?php if($user->suspended_at): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        <?php echo e(__('messages.user_status_suspended')); ?>

                                    </span>
                                    <span class="block text-xs text-gray-500 mt-1">
                                        <?php echo e(__('messages.suspended_at_label')); ?>:
                                        <?php echo e($user->suspended_at->format('d M Y H:i')); ?>

                                    </span>
                                <?php elseif(!$user->is_active): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                                        <?php echo e(__('messages.user_status_inactive')); ?>

                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                        <?php echo e(__('messages.user_status_active')); ?>

                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.statistics')); ?></h3>
                        <div class="space-y-2">
                            <p><strong><?php echo e(__('messages.total_notes_label')); ?>:</strong> <?php echo e($user->notes->count()); ?></p>
                            <p><strong><?php echo e(__('messages.public_notes_label')); ?>:</strong>
                                <?php echo e($user->notes->where('is_public', true)->count()); ?></p>
                            <p><strong><?php echo e(__('messages.total_withdraws')); ?>:</strong> <?php echo e($user->withdraws->count()); ?></p>
                            <p><strong><?php echo e(__('messages.pending_withdraws')); ?>:</strong>
                                <?php echo e($user->withdraws->where('status', 'pending')->count()); ?></p>
                            <p><strong><?php echo e(__('messages.transactions_buyer')); ?>:</strong>
                                <?php echo e($user->transactionsAsBuyer->count()); ?></p>
                            <p><strong><?php echo e(__('messages.transactions_seller')); ?>:</strong>
                                <?php echo e($user->transactionsAsSeller->count()); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.account_actions')); ?></h3>

                <?php if($user->id === auth()->id()): ?>
                    <p class="text-sm text-gray-600"><?php echo e(__('messages.cannot_modify_self_status')); ?></p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <form method="POST" action="<?php echo e(route('admin.users.deactivate', $user)); ?>"
                            class="border rounded-lg p-4 shadow-sm space-y-3" onsubmit="return confirmDeactivate(this);">
                            <?php echo csrf_field(); ?>
                            <h4 class="text-sm font-semibold text-gray-800"><?php echo e(__('messages.deactivate_account')); ?></h4>
                            <p class="text-xs text-gray-600"><?php echo e(__('messages.deactivate_account_help')); ?></p>
                            <input type="text" name="reason"
                                placeholder="<?php echo e(__('messages.optional_reason_placeholder')); ?>"
                                class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                            <button type="submit"
                                class="mt-2 inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-xs font-semibold rounded-md">
                                <?php echo e(__('messages.deactivate')); ?>

                            </button>
                        </form>

                        <form method="POST"
                            action="<?php echo e($user->suspended_at ? route('admin.users.release', $user) : route('admin.users.suspend', $user)); ?>"
                            class="border rounded-lg p-4 shadow-sm space-y-3"
                            onsubmit="return confirmSuspend(this, <?php echo e($user->suspended_at ? 'true' : 'false'); ?>);">
                            <?php echo csrf_field(); ?>
                            <?php if($user->suspended_at): ?>
                                <h4 class="text-sm font-semibold text-gray-800"><?php echo e(__('messages.release_suspend')); ?></h4>
                                <p class="text-xs text-gray-600"><?php echo e(__('messages.release_suspend_help')); ?></p>
                                <button type="submit"
                                    class="mt-2 inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-md">
                                    <?php echo e(__('messages.release_suspend')); ?>

                                </button>
                            <?php else: ?>
                                <h4 class="text-sm font-semibold text-gray-800"><?php echo e(__('messages.suspend_account')); ?></h4>
                                <p class="text-xs text-gray-600"><?php echo e(__('messages.suspend_account_help')); ?></p>
                                <input type="text" name="reason"
                                    placeholder="<?php echo e(__('messages.optional_reason_placeholder')); ?>"
                                    class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500">
                                <button type="submit"
                                    class="mt-2 inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-md">
                                    <?php echo e(__('messages.suspend')); ?>

                                </button>
                            <?php endif; ?>
                        </form>
                    </div>

                    <?php if(!$user->isAccessible()): ?>
                        <form method="POST" action="<?php echo e(route('admin.users.activate', $user)); ?>" class="mt-4"
                            onsubmit="return confirmActivate(this);">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                                <?php echo e(__('messages.activate_account')); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if($user->withdraws->count() > 0): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.recent_withdraws')); ?></h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('messages.date')); ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('messages.amount')); ?></th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        <?php echo e(__('messages.status')); ?></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php $__currentLoopData = $user->withdraws->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $withdraw): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <?php echo e($withdraw->created_at->format('d M Y')); ?></td>
                                        <td class="px-4 py-3 text-sm font-medium"><?php echo e(currency($withdraw->amount)); ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <?php if($withdraw->status === 'approved'): ?>
                                                <span
                                                    class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.approved')); ?></span>
                                            <?php elseif($withdraw->status === 'rejected'): ?>
                                                <span
                                                    class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.rejected')); ?></span>
                                            <?php else: ?>
                                                <span
                                                    class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs"><?php echo e(__('messages.pending')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.admin_action_logs')); ?></h3>

                <?php if($actionLogs->isEmpty()): ?>
                    <p class="text-sm text-gray-600"><?php echo e(__('messages.no_admin_actions_found')); ?></p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $actionLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-sm font-semibold text-gray-800">
                                        <?php echo e(__('messages.admin_action_label')); ?>:
                                        <?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?>

                                    </span>
                                    <span class="text-xs text-gray-500">
                                        <?php echo e(__('messages.performed_at')); ?>: <?php echo e($log->created_at->format('d M Y H:i')); ?>

                                    </span>
                                </div>
                                <p class="text-xs text-gray-600 mt-1">
                                    <?php echo e(__('messages.performed_by')); ?>: <?php echo e($log->admin?->name ?? __('messages.admin')); ?>

                                </p>
                                <?php if($log->reason): ?>
                                    <p class="text-xs text-red-600 mt-1">
                                        <?php echo e(__('messages.admin_action_reason')); ?>: <?php echo e($log->reason); ?>

                                    </p>
                                <?php endif; ?>
                                <?php
                                    $previous = $log->metadata['previous_status'] ?? [];
                                    $current = $log->metadata['current_status'] ?? [];
                                ?>
                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 text-xs text-gray-600">
                                    <div class="bg-white border rounded p-3">
                                        <p class="font-semibold text-gray-700 mb-1"><?php echo e(__('messages.previous_status')); ?>

                                        </p>
                                        <p><?php echo e(__('messages.user_status_active')); ?>:
                                            <?php echo e($previous['is_active'] ?? false ? __('messages.yes') : __('messages.no')); ?>

                                        </p>
                                        <p><?php echo e(__('messages.user_status_suspended')); ?>:
                                            <?php echo e($previous['suspended_at'] ?? null ? __('messages.yes') : __('messages.no')); ?>

                                        </p>
                                    </div>
                                    <div class="bg-white border rounded p-3">
                                        <p class="font-semibold text-gray-700 mb-1"><?php echo e(__('messages.current_status')); ?>

                                        </p>
                                        <p><?php echo e(__('messages.user_status_active')); ?>:
                                            <?php echo e($current['is_active'] ?? false ? __('messages.yes') : __('messages.no')); ?>

                                        </p>
                                        <p><?php echo e(__('messages.user_status_suspended')); ?>:
                                            <?php echo e($current['suspended_at'] ?? null ? __('messages.yes') : __('messages.no')); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function confirmDeactivate(form) {
            if (typeof Swal === 'undefined') {
                return confirm(<?php echo json_encode(__('messages.confirm_deactivate_user'), 15, 512) ?>);
            }

            Swal.fire({
                icon: 'warning',
                title: <?php echo json_encode(__('messages.deactivate_account'), 15, 512) ?>,
                text: <?php echo json_encode(__('messages.confirm_deactivate_user'), 15, 512) ?>,
                showCancelButton: true,
                confirmButtonColor: '#d97706',
                cancelButtonColor: '#6b7280',
                confirmButtonText: <?php echo json_encode(__('messages.deactivate'), 15, 512) ?>,
                cancelButtonText: <?php echo json_encode(__('messages.cancel'), 15, 512) ?>,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

            return false;
        }

        function confirmSuspend(form, isRelease) {
            if (typeof Swal === 'undefined') {
                return confirm(isRelease ? <?php echo json_encode(__('messages.confirm_release_user'), 15, 512) ?> : <?php echo json_encode(__('messages.confirm_suspend_user'), 15, 512) ?>);
            }

            const title = isRelease ? <?php echo json_encode(__('messages.release_suspend'), 15, 512) ?> : <?php echo json_encode(__('messages.suspend_account'), 15, 512) ?>;
            const text = isRelease ? <?php echo json_encode(__('messages.confirm_release_user'), 15, 512) ?> : <?php echo json_encode(__('messages.confirm_suspend_user'), 15, 512) ?>;
            const confirmText = isRelease ? <?php echo json_encode(__('messages.release_suspend'), 15, 512) ?> : <?php echo json_encode(__('messages.suspend'), 15, 512) ?>;
            const confirmColor = isRelease ? '#2563eb' : '#dc2626';

            Swal.fire({
                icon: 'warning',
                title,
                text,
                showCancelButton: true,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#6b7280',
                confirmButtonText: confirmText,
                cancelButtonText: <?php echo json_encode(__('messages.cancel'), 15, 512) ?>,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

            return false;
        }

        function confirmActivate(form) {
            if (typeof Swal === 'undefined') {
                return confirm(<?php echo json_encode(__('messages.confirm_activate_user'), 15, 512) ?>);
            }

            Swal.fire({
                icon: 'question',
                title: <?php echo json_encode(__('messages.activate_account'), 15, 512) ?>,
                text: <?php echo json_encode(__('messages.confirm_activate_user'), 15, 512) ?>,
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
                confirmButtonText: <?php echo json_encode(__('messages.activate_account'), 15, 512) ?>,
                cancelButtonText: <?php echo json_encode(__('messages.cancel'), 15, 512) ?>,
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });

            return false;
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\users\show.blade.php ENDPATH**/ ?>