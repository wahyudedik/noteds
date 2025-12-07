<?php $__env->startSection('title', __('messages.my_support_tickets')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.my_support_tickets')); ?></h1>
                    <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.manage_support_requests')); ?></p>
                </div>
                <a href="<?php echo e(route('support-tickets.create')); ?>" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <?php echo e(__('messages.new_ticket')); ?>

                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <form method="GET" action="<?php echo e(route('support-tickets.index')); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.status')); ?></label>
                    <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php echo e(__('messages.all_status')); ?></option>
                        <option value="open" <?php echo e(request('status') === 'open' ? 'selected' : ''); ?>><?php echo e(__('messages.open')); ?></option>
                        <option value="in_progress" <?php echo e(request('status') === 'in_progress' ? 'selected' : ''); ?>><?php echo e(__('messages.in_progress')); ?></option>
                        <option value="resolved" <?php echo e(request('status') === 'resolved' ? 'selected' : ''); ?>><?php echo e(__('messages.resolved')); ?></option>
                        <option value="closed" <?php echo e(request('status') === 'closed' ? 'selected' : ''); ?>><?php echo e(__('messages.closed')); ?></option>
                    </select>
                </div>
                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.priority')); ?></label>
                    <select name="priority" id="priority" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php echo e(__('messages.all_priorities')); ?></option>
                        <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>><?php echo e(__('messages.low')); ?></option>
                        <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>><?php echo e(__('messages.medium')); ?></option>
                        <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>><?php echo e(__('messages.high')); ?></option>
                        <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>><?php echo e(__('messages.urgent')); ?></option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <?php echo e(__('messages.filter')); ?>

                    </button>
                    <a href="<?php echo e(route('support-tickets.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                </div>
            </form>
        </div>

        <!-- Tickets List -->
        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 mb-4 hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    <a href="<?php echo e(route('support-tickets.show', $ticket)); ?>" class="hover:text-blue-600">
                                        <?php echo e($ticket->title); ?>

                                    </a>
                                </h3>
                                <!-- Status Badge -->
                                <?php if($ticket->status === 'open'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?php echo e(__('messages.open')); ?>

                                    </span>
                                <?php elseif($ticket->status === 'in_progress'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e(__('messages.in_progress')); ?>

                                    </span>
                                <?php elseif($ticket->status === 'resolved'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <?php echo e(__('messages.resolved')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        <?php echo e(__('messages.closed')); ?>

                                    </span>
                                <?php endif; ?>
                                <!-- Priority Badge -->
                                <?php if($ticket->priority === 'urgent'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <?php echo e(__('messages.urgent')); ?>

                                    </span>
                                <?php elseif($ticket->priority === 'high'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        <?php echo e(__('messages.high')); ?>

                                    </span>
                                <?php elseif($ticket->priority === 'medium'): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?php echo e(__('messages.medium')); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo e(__('messages.low')); ?>

                                    </span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                                <?php echo e(Str::limit($ticket->description, 150)); ?>

                            </p>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span><?php echo e(__('messages.created')); ?> <?php echo e($ticket->created_at->diffForHumans()); ?></span>
                                <?php if($ticket->assignedAdmin): ?>
                                    <span>• <?php echo e(__('messages.assigned_to')); ?> <?php echo e($ticket->assignedAdmin->name); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <?php if($ticket->isOpen()): ?>
                                <a href="<?php echo e(route('support-tickets.edit', $ticket)); ?>" class="px-3 py-1.5 text-sm font-medium text-blue-600 hover:text-blue-800 border border-blue-300 rounded-lg hover:bg-blue-50 transition-all">
                                    <?php echo e(__('messages.edit')); ?>

                                </a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('support-tickets.show', $ticket)); ?>" class="px-3 py-1.5 text-sm font-medium text-gray-700 hover:text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-50 transition-all">
                                <?php echo e(__('messages.view')); ?>

                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900"><?php echo e(__('messages.no_tickets')); ?></h3>
                <p class="mt-2 text-gray-600"><?php echo e(__('messages.create_first_ticket')); ?></p>
                <a href="<?php echo e(route('support-tickets.create')); ?>" class="mt-6 inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <?php echo e(__('messages.new_ticket')); ?>

                </a>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if($tickets->hasPages()): ?>
            <div class="mt-6">
                <?php echo e($tickets->links()); ?>

            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\support-tickets\index.blade.php ENDPATH**/ ?>