<?php $__env->startSection('title', __('messages.order_detail') . ' — ' . __('messages.studio')); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900"><?php echo e($order->title); ?></h1>
                        <div class="mt-1 text-sm text-slate-600"><?php echo e(__('messages.order_status')); ?>: <span
                                class="font-semibold"><?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?></span></div>
                    </div>
                    <div class="text-right">
                        <?php if($order->budget > 0): ?>
                            <div class="text-sm text-slate-600"><?php echo e(__('messages.order_budget')); ?></div>
                            <div class="text-lg font-semibold"><?php echo e(currency($order->budget)); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="mt-6">
                    <h2 class="text-sm font-semibold text-slate-900 mb-2"><?php echo e(__('messages.order_description')); ?></h2>
                    <div class="prose max-w-none">
                        <p class="text-slate-700 whitespace-pre-wrap"><?php echo e($order->description); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e(__('messages.milestones')); ?></h2>
                <?php if(empty($order->milestones)): ?>
                    <p class="text-slate-600 text-sm"><?php echo e(__('messages.no_milestones')); ?></p>
                <?php else: ?>
                    <ol class="list-decimal pl-5 space-y-2 text-slate-700">
                        <?php $__currentLoopData = $order->milestones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li>
                                <div class="font-semibold"><?php echo e($m['title'] ?? __('messages.milestone')); ?></div>
                                <div class="text-sm"><?php echo e($m['description'] ?? ''); ?></div>
                                <?php if(isset($m['amount'])): ?>
                                    <div class="text-xs text-slate-500"><?php echo e(__('messages.milestone_amount')); ?>:
                                        <?php echo e(currency($m['amount'])); ?></div>
                                <?php endif; ?>
                                <?php if(isset($m['status'])): ?>
                                    <div class="text-xs text-slate-500"><?php echo e(__('messages.order_status')); ?>:
                                        <?php echo e(ucfirst($m['status'])); ?></div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ol>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e(__('messages.escrow_amount')); ?></h2>
                <p class="text-sm text-slate-600"><?php echo e(__('messages.current_escrow')); ?>:
                    <strong><?php echo e(currency($order->escrow_amount)); ?></strong></p>
                <p class="text-xs text-slate-500 mt-1"><?php echo e(__('messages.escrow_note')); ?></p>
                <div class="mt-3">
                    <details class="text-sm">
                        <summary class="cursor-pointer text-slate-700"><?php echo e(__('messages.escrow_history')); ?></summary>
                        <?php if($ledger->isEmpty()): ?>
                            <p class="text-slate-500 mt-2"><?php echo e(__('messages.no_escrow_history')); ?></p>
                        <?php else: ?>
                            <div class="mt-2 space-y-1">
                                <?php $__currentLoopData = $ledger; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="flex items-center justify-between p-2 rounded border">
                                        <div>
                                            <span class="font-medium"><?php echo e(ucfirst($row->type)); ?></span>
                                            <?php if(!is_null($row->milestone_index)): ?>
                                                <span class="text-xs text-slate-500"> (<?php echo e(__('messages.milestone')); ?>

                                                    #<?php echo e($row->milestone_index + 1); ?>)</span>
                                            <?php endif; ?>
                                            <div class="text-xs text-slate-500"><?php echo e($row->created_at->format('d M Y H:i')); ?>

                                            </div>
                                        </div>
                                        <div class="font-semibold"><?php echo e(currency($row->amount)); ?></div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </details>
                </div>

                <div class="mt-4">
                    <details class="text-sm">
                        <summary class="cursor-pointer text-slate-700"><?php echo e(__('messages.activity_timeline')); ?></summary>
                        <?php if($activities->isEmpty()): ?>
                            <p class="text-slate-500 mt-2"><?php echo e(__('messages.no_activities')); ?></p>
                        <?php else: ?>
                            <div class="mt-2 space-y-2">
                                <?php $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="p-2 rounded border">
                                        <div class="flex items-center justify-between">
                                            <div class="font-medium"><?php echo e(str_replace('_', ' ', ucfirst($act->action))); ?>

                                            </div>
                                            <div class="text-xs text-slate-500"><?php echo e($act->created_at->format('d M Y H:i')); ?>

                                            </div>
                                        </div>
                                        <?php if($act->description): ?>
                                            <div class="text-xs text-slate-600 mt-1"><?php echo e($act->description); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </details>
                </div>
                <?php if(auth()->guard()->check()): ?>
                    <?php if(isset($vendors) && count($vendors) > 0 && empty($order->assigned_user_id)): ?>
                        <div class="mt-4 p-4 border border-yellow-200 bg-yellow-50 rounded-md">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-yellow-800"><?php echo e(__('messages.assign_vendor')); ?>

                                        (<?php echo e(__('messages.admin')); ?>)</p>
                                    <p class="text-xs text-yellow-700"><?php echo e(__('messages.assign_vendor_description')); ?></p>
                                </div>
                            </div>
                            <form method="POST" action="<?php echo e(route('studio.orders.assign-vendor', $order)); ?>"
                                class="mt-3 flex gap-2 items-center">
                                <?php echo csrf_field(); ?>
                                <select name="vendor_id" class="rounded-lg border-gray-300">
                                    <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <button type="submit"
                                    class="px-3 py-2 rounded-md bg-yellow-600 text-white text-sm"><?php echo e(__('messages.assign')); ?></button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <?php if(auth()->id() === $order->user_id): ?>
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <form method="POST" action="<?php echo e(route('studio.orders.fund-escrow', $order)); ?>"
                                class="flex items-center gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="number" name="amount" step="0.01" min="1"
                                    class="w-full rounded-lg border-gray-300" placeholder="<?php echo e(__('messages.amount')); ?>">
                                <button type="submit"
                                    class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm"><?php echo e(__('messages.fund_escrow')); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('studio.orders.release-escrow', $order)); ?>"
                                class="flex items-center gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="number" name="amount" step="0.01" min="1"
                                    class="w-full rounded-lg border-gray-300" placeholder="<?php echo e(__('messages.amount')); ?>">
                                <?php if(!empty($order->milestones)): ?>
                                    <select name="milestone_index" class="rounded-lg border-gray-300">
                                        <option value="">— <?php echo e(__('messages.milestone')); ?> —</option>
                                        <?php $__currentLoopData = $order->milestones ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($i); ?>">#<?php echo e($i + 1); ?> -
                                                <?php echo e($m['title'] ?? __('messages.milestone')); ?>

                                                (<?php echo e(isset($m['amount']) ? currency($m['amount']) : '—'); ?>)</option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                <?php endif; ?>
                                <button type="submit"
                                    class="px-3 py-2 rounded-md bg-green-600 text-white text-sm"><?php echo e(__('messages.release_escrow')); ?></button>
                            </form>
                            <form method="POST" action="<?php echo e(route('studio.orders.refund-escrow', $order)); ?>"
                                class="flex items-center gap-2">
                                <?php echo csrf_field(); ?>
                                <input type="number" name="amount" step="0.01" min="1"
                                    class="w-full rounded-lg border-gray-300" placeholder="<?php echo e(__('messages.amount')); ?>">
                                <button type="submit"
                                    class="px-3 py-2 rounded-md bg-red-600 text-white text-sm"><?php echo e(__('messages.refund_escrow')); ?></button>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-slate-900"><?php echo e(__('messages.quotes')); ?></h2>
                    <?php if(auth()->user()?->hasRole('admin')): ?>
                        <a href="<?php echo e(route('studio.orders.quotes.create', $order)); ?>"
                            class="px-3 py-2 rounded-md bg-blue-600 text-white text-sm"><?php echo e(__('messages.create_quote')); ?></a>
                    <?php endif; ?>
                </div>
                <?php if($quotes->isEmpty()): ?>
                    <p class="text-slate-600 text-sm"><?php echo e(__('messages.no_quotes_found')); ?></p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $quotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4 rounded-lg border border-slate-200">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="text-sm text-slate-600"><?php echo e(__('messages.vendor')); ?></div>
                                        <div class="font-semibold"><?php echo e($quote->vendor?->name ?? 'N/A'); ?></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-slate-600"><?php echo e(__('messages.quote_total_amount')); ?></div>
                                        <div class="font-semibold"><?php echo e(currency($quote->total_amount)); ?></div>
                                        <div class="text-xs text-slate-500 mt-1"><?php echo e(__('messages.order_status')); ?>:
                                            <?php echo e(ucfirst($quote->status)); ?></div>
                                    </div>
                                </div>
                                <?php if(!empty($quote->milestones)): ?>
                                    <div class="mt-3">
                                        <div class="text-sm font-semibold text-slate-900 mb-1">
                                            <?php echo e(__('messages.milestones')); ?></div>
                                        <ol class="list-decimal pl-5 space-y-1 text-slate-700">
                                            <?php $__currentLoopData = $quote->milestones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <li>
                                                    <span
                                                        class="font-medium"><?php echo e($m['title'] ?? __('messages.milestone')); ?></span>
                                                    <?php if(isset($m['amount'])): ?>
                                                        <span class="text-xs text-slate-500"> —
                                                            <?php echo e(currency($m['amount'])); ?></span>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ol>
                                    </div>
                                <?php endif; ?>
                                <?php if(auth()->guard()->check()): ?>
                                    <?php if(auth()->id() === $order->user_id && $quote->status === 'pending'): ?>
                                        <div class="mt-3 flex items-center gap-2">
                                            <form method="POST" action="<?php echo e(route('studio.quotes.accept', $quote)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="px-3 py-1.5 rounded-md bg-green-600 text-white text-sm"><?php echo e(__('messages.accept_quote')); ?></button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('studio.quotes.reject', $quote)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="px-3 py-1.5 rounded-md bg-red-600 text-white text-sm"><?php echo e(__('messages.reject_quote')); ?></button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\studio\orders\show.blade.php ENDPATH**/ ?>