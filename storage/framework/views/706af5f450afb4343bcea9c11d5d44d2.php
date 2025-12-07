<?php $__env->startSection('title', __('featured.admin.page_title')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('admin.featured-notes.index')); ?>" class="text-blue-600 hover:text-blue-800">← <?php echo e(__('messages.back_to_featured_notes')); ?></a>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 mb-6"><?php echo e(__('messages.featured_note_detail')); ?></h2>

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

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('featured.admin.note_information')); ?></h3>
                    <div class="space-y-2">
                        <p><strong><?php echo e(__('featured.admin.title_label')); ?>:</strong> 
                            <a href="<?php echo e(route('marketplace.show', $featuredNote->note)); ?>" class="text-blue-600 hover:text-blue-800">
                                <?php echo e($featuredNote->note->title); ?>

                            </a>
                        </p>
                        <p><strong><?php echo e(__('featured.admin.summary_label')); ?>:</strong> <?php echo e(Str::limit($featuredNote->note->summary ?? __('featured.admin.no_summary'), 100)); ?></p>
                        <p><strong><?php echo e(__('messages.price')); ?>:</strong> <?php echo e(currency($featuredNote->note->price)); ?></p>
                        <p><strong><?php echo e(__('featured.admin.status_label')); ?>:</strong> 
                            <?php if($featuredNote->note->is_public): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.admin.public')); ?></span>
                            <?php else: ?>
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.admin.private')); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('featured.admin.seller_information')); ?></h3>
                    <div class="space-y-2">
                        <p><strong><?php echo e(__('featured.admin.name_label')); ?>:</strong> <?php echo e($featuredNote->user->name); ?></p>
                        <p><strong><?php echo e(__('featured.admin.email_label')); ?>:</strong> <?php echo e($featuredNote->user->email); ?></p>
                        <p><strong><?php echo e(__('messages.wallet_balance_title')); ?>:</strong> <?php echo e(currency($featuredNote->user->wallet_balance ?? 0)); ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('featured.admin.featured_details')); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p><strong><?php echo e(__('featured.admin.location_label')); ?>:</strong> <?php echo e(__('featured.locations.' . $featuredNote->location)); ?></p>
                        <p><strong><?php echo e(__('featured.admin.duration_label')); ?>:</strong> <?php echo e(__('messages.day_count', ['count' => $featuredNote->duration_days])); ?></p>
                        <p><strong><?php echo e(__('messages.price')); ?>:</strong> <span class="text-lg font-bold text-green-600"><?php echo e(currency($featuredNote->price)); ?></span></p>
                    </div>
                    <div>
                        <p><strong><?php echo e(__('featured.admin.status_label')); ?>:</strong> 
                            <?php if($featuredNote->status === 'pending'): ?>
                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_pending')); ?></span>
                            <?php elseif($featuredNote->status === 'active'): ?>
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_active')); ?></span>
                            <?php elseif($featuredNote->status === 'expired'): ?>
                                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_expired')); ?></span>
                            <?php else: ?>
                                <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs"><?php echo e(__('featured.status_cancelled')); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php if($featuredNote->start_date && $featuredNote->end_date): ?>
                            <p><strong><?php echo e(__('featured.admin.start_date')); ?>:</strong> <?php echo e($featuredNote->start_date->format('d M Y, H:i')); ?></p>
                            <p><strong><?php echo e(__('featured.admin.end_date')); ?>:</strong> <?php echo e($featuredNote->end_date->format('d M Y, H:i')); ?></p>
                            <?php if($featuredNote->isActive()): ?>
                                <p class="text-sm text-green-600 mt-2"><?php echo e(__('featured.admin.currently_active')); ?></p>
                            <?php elseif($featuredNote->end_date < now() && $featuredNote->status === 'active'): ?>
                                <p class="text-sm text-gray-600 mt-2"><?php echo e(__('featured.admin.expired_cron')); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-gray-400"><?php echo e(__('featured.admin.dates_not_set')); ?></p>
                        <?php endif; ?>
                        <p><strong><?php echo e(__('featured.admin.requested_at')); ?>:</strong> <?php echo e($featuredNote->created_at->format('d M Y, H:i')); ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('featured.admin.analytics')); ?></h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><strong><?php echo e(__('featured.admin.impressions_label')); ?>:</strong> <?php echo e(number_format($featuredNote->impressions, 0, ',', '.')); ?></p>
                        <p><strong><?php echo e(__('featured.admin.clicks_label')); ?>:</strong> <?php echo e(number_format($featuredNote->clicks, 0, ',', '.')); ?></p>
                    </div>
                    <div>
                        <?php if($featuredNote->impressions > 0): ?>
                            <p><strong><?php echo e(__('featured.admin.ctr_label')); ?>:</strong> <?php echo e(number_format(($featuredNote->clicks / $featuredNote->impressions) * 100, 2)); ?>%</p>
                        <?php else: ?>
                            <p><strong><?php echo e(__('featured.admin.ctr_label')); ?>:</strong> 0%</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if($featuredNote->admin_notes): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-2"><?php echo e(__('featured.admin.admin_notes')); ?></h4>
                    <p class="text-gray-700"><?php echo e($featuredNote->admin_notes); ?></p>
                </div>
            <?php endif; ?>

            <?php if($featuredNote->status === 'pending'): ?>
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold text-gray-900 mb-4"><?php echo e(__('featured.admin.process_request')); ?></h4>
                    
                    <!-- Approve Form -->
                    <form action="<?php echo e(route('admin.featured-notes.approve', $featuredNote)); ?>" method="POST" class="mb-4">
                        <?php echo csrf_field(); ?>
                        <div class="mb-4">
                            <label for="approve_notes" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('featured.admin.admin_notes_optional')); ?></label>
                            <textarea name="admin_notes" id="approve_notes" rows="3" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="<?php echo e(__('featured.admin.admin_notes_optional_placeholder')); ?>"></textarea>
                        </div>
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                            <?php echo e(__('featured.admin.approve_button')); ?>

                        </button>
                    </form>

                    <!-- Reject Form -->
                    <form action="<?php echo e(route('admin.featured-notes.reject', $featuredNote)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-4">
                            <label for="reject_notes" class="block text-sm font-medium text-gray-700 mb-2"><?php echo e(__('featured.admin.rejection_reason')); ?> <span class="text-red-500">*</span></label>
                            <textarea name="admin_notes" id="reject_notes" rows="3" required
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                                placeholder="<?php echo e(__('featured.admin.rejection_placeholder')); ?>"></textarea>
                            <p class="mt-1 text-sm text-gray-500"><?php echo e(__('featured.admin.refund_notice')); ?></p>
                        </div>
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-6 rounded">
                            <?php echo e(__('featured.admin.reject_button')); ?>

                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\featured-notes\show.blade.php ENDPATH**/ ?>