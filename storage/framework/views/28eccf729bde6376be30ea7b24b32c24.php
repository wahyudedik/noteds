<?php $__env->startSection('title', __('messages.admin_ticket_details')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.ticket')); ?> #<?php echo e(substr($ticket->id, 0, 8)); ?></h1>
                </div>
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

        <!-- Ticket Details Header -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 flex-wrap">
                        <h2 class="text-xl font-bold text-gray-900"><?php echo e($ticket->title); ?></h2>
                        <?php if($ticket->status === 'open'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <?php echo e(__('messages.open')); ?>

                            </span>
                        <?php elseif($ticket->status === 'in_progress'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?php echo e(__('messages.in_progress')); ?>

                            </span>
                        <?php elseif($ticket->status === 'resolved'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                <?php echo e(__('messages.resolved')); ?>

                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <?php echo e(__('messages.closed')); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($ticket->priority === 'urgent'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <?php echo e(__('messages.urgent')); ?>

                            </span>
                        <?php elseif($ticket->priority === 'high'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                <?php echo e(__('messages.high')); ?>

                            </span>
                        <?php elseif($ticket->priority === 'medium'): ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <?php echo e(__('messages.medium')); ?>

                            </span>
                        <?php else: ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <?php echo e(__('messages.low')); ?>

                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.from')); ?></h3>
                    <div class="flex items-center gap-3">
                        <?php if($ticket->user->avatar): ?>
                            <img src="<?php echo e($ticket->user->avatar); ?>" alt="<?php echo e($ticket->user->name); ?>" class="w-10 h-10 rounded-full">
                        <?php else: ?>
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                <span class="text-sm font-semibold text-white"><?php echo e(strtoupper(substr($ticket->user->name, 0, 1))); ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900"><?php echo e($ticket->user->name); ?></span>
                                <?php if($ticket->user->hasPremium()): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white" title="Premium User - Priority Support">
                                        <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Premium - Priority Support
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="text-xs text-gray-500"><?php echo e($ticket->user->email); ?></div>
                        </div>
                    </div>
                </div>

                <?php if($ticket->links && count($ticket->links) > 0): ?>
                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-sm font-medium text-gray-700 mb-3"><?php echo e(__('messages.related_links')); ?></h3>
                        <div class="flex flex-wrap gap-2">
                            <?php $__currentLoopData = $ticket->links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($link); ?>" target="_blank" class="inline-flex items-center px-3 py-1 rounded-lg text-sm text-blue-600 bg-blue-50 hover:bg-blue-100">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                    </svg>
                                    <?php echo e(Str::limit($link, 30)); ?>

                                    <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="border-t border-gray-200 pt-6">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-medium text-gray-700"><?php echo e(__('messages.created')); ?>:</span>
                            <span class="text-gray-600 ml-2"><?php echo e($ticket->created_at->format('F d, Y H:i')); ?></span>
                        </div>
                        <div>
                            <span class="font-medium text-gray-700"><?php echo e(__('messages.last_updated')); ?>:</span>
                            <span class="text-gray-600 ml-2"><?php echo e($ticket->updated_at->format('F d, Y H:i')); ?></span>
                        </div>
                        <?php if($ticket->assignedAdmin): ?>
                            <div>
                                <span class="font-medium text-gray-700"><?php echo e(__('messages.assigned_to')); ?>:</span>
                                <span class="text-gray-600 ml-2"><?php echo e($ticket->assignedAdmin->name); ?></span>
                            </div>
                        <?php endif; ?>
                        <?php if($ticket->closedByUser): ?>
                            <div>
                                <span class="font-medium text-gray-700"><?php echo e(__('messages.closed_by')); ?>:</span>
                                <span class="text-gray-600 ml-2"><?php echo e($ticket->closedByUser->name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conversation Thread -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.conversation')); ?></h3>
            </div>
            <div class="divide-y divide-gray-200">
                <!-- Original Ticket Message -->
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <?php if($ticket->user->avatar): ?>
                                <img src="<?php echo e($ticket->user->avatar); ?>" alt="<?php echo e($ticket->user->name); ?>" class="w-10 h-10 rounded-full">
                            <?php else: ?>
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white"><?php echo e(strtoupper(substr($ticket->user->name, 0, 1))); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="font-semibold text-gray-900"><?php echo e($ticket->user->name); ?></span>
                                <span class="text-xs text-gray-500"><?php echo e($ticket->created_at->format('M d, Y H:i')); ?></span>
                                <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded"><?php echo e(__('messages.original_message')); ?></span>
                            </div>
                            <div class="prose max-w-none">
                                <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($ticket->description); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Replies -->
                <?php $__empty_1 = true; $__currentLoopData = $ticket->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-6 <?php echo e($reply->is_admin ? 'bg-green-50' : 'bg-white'); ?>">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <?php if($reply->user->avatar): ?>
                                    <img src="<?php echo e($reply->user->avatar); ?>" alt="<?php echo e($reply->user->name); ?>" class="w-10 h-10 rounded-full">
                                <?php else: ?>
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br <?php echo e($reply->is_admin ? 'from-green-400 to-blue-500' : 'from-blue-400 to-purple-500'); ?> flex items-center justify-center">
                                        <span class="text-sm font-semibold text-white"><?php echo e(strtoupper(substr($reply->user->name, 0, 1))); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-semibold text-gray-900"><?php echo e($reply->user->name); ?></span>
                                    <?php if($reply->is_admin): ?>
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded"><?php echo e(__('messages.admin')); ?></span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800 rounded"><?php echo e(__('messages.user')); ?></span>
                                    <?php endif; ?>
                                    <span class="text-xs text-gray-500"><?php echo e($reply->created_at->format('M d, Y H:i')); ?></span>
                                </div>
                                <div class="prose max-w-none">
                                    <p class="text-gray-700 whitespace-pre-wrap"><?php echo e($reply->message); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-6 text-center text-gray-500">
                        <?php echo e(__('messages.no_replies_yet')); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Admin Actions -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900"><?php echo e(__('messages.admin_actions')); ?></h3>
            </div>
            <div class="p-6 space-y-6">
                <!-- Assign Ticket -->
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3"><?php echo e(__('messages.assign_to_admin')); ?></h4>
                    <form action="<?php echo e(route('admin.tickets.assign', $ticket)); ?>" method="POST" class="flex items-end gap-2">
                        <?php echo csrf_field(); ?>
                        <div class="flex-1">
                            <select name="assigned_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value=""><?php echo e(__('messages.select_admin')); ?></option>
                                <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($admin->id); ?>" <?php echo e($ticket->assigned_to === $admin->id ? 'selected' : ''); ?>>
                                        <?php echo e($admin->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                            <?php echo e(__('messages.assign')); ?>

                        </button>
                    </form>
                </div>

                <!-- Update Status & Priority -->
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-medium text-gray-700 mb-3"><?php echo e(__('messages.update_status_priority')); ?></h4>
                    <form action="<?php echo e(route('admin.tickets.update', $ticket)); ?>" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PATCH'); ?>
                        <div>
                            <label for="status" class="block text-xs font-medium text-gray-700 mb-2"><?php echo e(__('messages.status')); ?></label>
                            <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="open" <?php echo e($ticket->status === 'open' ? 'selected' : ''); ?>><?php echo e(__('messages.open')); ?></option>
                                <option value="in_progress" <?php echo e($ticket->status === 'in_progress' ? 'selected' : ''); ?>><?php echo e(__('messages.in_progress')); ?></option>
                                <option value="resolved" <?php echo e($ticket->status === 'resolved' ? 'selected' : ''); ?>><?php echo e(__('messages.resolved')); ?></option>
                                <option value="closed" <?php echo e($ticket->status === 'closed' ? 'selected' : ''); ?>><?php echo e(__('messages.closed')); ?></option>
                            </select>
                        </div>
                        <div>
                            <label for="priority" class="block text-xs font-medium text-gray-700 mb-2"><?php echo e(__('messages.priority')); ?></label>
                            <select name="priority" id="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <option value="low" <?php echo e($ticket->priority === 'low' ? 'selected' : ''); ?>><?php echo e(__('messages.low')); ?></option>
                                <option value="medium" <?php echo e($ticket->priority === 'medium' ? 'selected' : ''); ?>><?php echo e(__('messages.medium')); ?></option>
                                <option value="high" <?php echo e($ticket->priority === 'high' ? 'selected' : ''); ?>><?php echo e(__('messages.high')); ?></option>
                                <option value="urgent" <?php echo e($ticket->priority === 'urgent' ? 'selected' : ''); ?>><?php echo e(__('messages.urgent')); ?></option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                                <?php echo e(__('messages.update')); ?>

                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reply Form -->
        <?php if($ticket->status !== 'closed'): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.send_reply')); ?></h3>
                <form action="<?php echo e(route('admin.tickets.reply', $ticket)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.your_message')); ?></label>
                        <textarea name="message" id="message" rows="5" required minlength="10"
                            :placeholder="__('messages.type_your_response')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('message')); ?></textarea>
                        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <p class="mt-1 text-xs text-gray-500"><?php echo e(__('messages.minimum_10_characters')); ?></p>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <?php echo e(__('messages.send_reply')); ?>

                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                <p class="text-gray-600"><?php echo e(__('messages.ticket_closed_no_replies')); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\tickets\show.blade.php ENDPATH**/ ?>