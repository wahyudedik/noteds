<?php $__env->startSection('title', __('messages.admin_support_tickets')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="<?php echo e(route('admin.dashboard')); ?>" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.support_tickets')); ?></h1>
            </div>
            <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.manage_and_respond_to_user_support_requests')); ?></p>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <form method="GET" action="<?php echo e(route('admin.tickets.index')); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.search')); ?></label>
                    <input type="text" name="search" id="search" value="<?php echo e(request('search')); ?>" 
                        :placeholder="__('messages.search_tickets')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                </div>
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
                        <option value="urgent" <?php echo e(request('priority') === 'urgent' ? 'selected' : ''); ?>><?php echo e(__('messages.urgent')); ?></option>
                        <option value="high" <?php echo e(request('priority') === 'high' ? 'selected' : ''); ?>><?php echo e(__('messages.high')); ?></option>
                        <option value="medium" <?php echo e(request('priority') === 'medium' ? 'selected' : ''); ?>><?php echo e(__('messages.medium')); ?></option>
                        <option value="low" <?php echo e(request('priority') === 'low' ? 'selected' : ''); ?>><?php echo e(__('messages.low')); ?></option>
                    </select>
                </div>
                <div>
                    <label for="premium_only" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('messages.premium_only')); ?></label>
                    <select name="premium_only" id="premium_only" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value=""><?php echo e(__('messages.all_users')); ?></option>
                        <option value="1" <?php echo e(request('premium_only') === '1' ? 'selected' : ''); ?>><?php echo e(__('messages.premium_only')); ?></option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                        <?php echo e(__('messages.filter')); ?>

                    </button>
                    <a href="<?php echo e(route('admin.tickets.index')); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                        <?php echo e(__('messages.clear')); ?>

                    </a>
                </div>
            </form>
        </div>

        <!-- Tickets Table -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.title')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.user')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.status')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.priority')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.assigned_to')); ?></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.created')); ?></th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"><?php echo e(__('messages.actions')); ?></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="text-sm font-medium text-gray-900 hover:text-blue-600">
                                        <?php echo e(Str::limit($ticket->title, 40)); ?>

                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if($ticket->user->avatar): ?>
                                            <img src="<?php echo e($ticket->user->avatar); ?>" alt="<?php echo e($ticket->user->name); ?>" class="w-8 h-8 rounded-full mr-2">
                                        <?php else: ?>
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center mr-2">
                                                <span class="text-xs font-semibold text-white"><?php echo e(strtoupper(substr($ticket->user->name, 0, 1))); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900"><?php echo e($ticket->user->name); ?></span>
                                                <?php if($ticket->user->hasPremium()): ?>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gradient-to-r from-yellow-400 to-orange-500 text-white" title="Premium User - Priority Support">
                                                        <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                        </svg>
                                                        Premium
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-xs text-gray-500"><?php echo e($ticket->user->email); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($ticket->assignedAdmin ? $ticket->assignedAdmin->name : __('messages.unassigned')); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo e($ticket->created_at->diffForHumans()); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="<?php echo e(route('admin.tickets.show', $ticket)); ?>" class="text-blue-600 hover:text-blue-900">
                                        <?php echo e(__('messages.view')); ?>

                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="mt-4 text-lg font-semibold text-gray-900"><?php echo e(__('messages.no_support_tickets')); ?></h3>
                                    <p class="mt-2 text-gray-600"><?php echo e(__('messages.no_tickets_match_filters')); ?></p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if($tickets->hasPages()): ?>
                <div class="px-6 py-4 border-t border-gray-200">
                    <?php echo e($tickets->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\tickets\index.blade.php ENDPATH**/ ?>