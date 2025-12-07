<?php $__env->startSection('title', __('messages.create_quote') . ' — ' . __('messages.studio')); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-slate-900 mb-6"><?php echo e(__('messages.create_quote')); ?></h1>
                <form action="<?php echo e(route('studio.orders.quotes.store', $order)); ?>" method="POST" class="space-y-6">
                    <?php echo csrf_field(); ?>
                    <?php $user = auth()->user(); ?>
                    <?php if($user->hasRole('admin')): ?>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.vendor')); ?></label>
                            <input type="text" list="vendors" name="vendor_id" class="w-full rounded-lg border-gray-300"
                                placeholder="<?php echo e(__('messages.enter_vendor_id')); ?>" required>
                            <datalist id="vendors">
                                <?php
                                    $vendors = \App\Models\User::role('vendor')->limit(100)->orderBy('name')->get();
                                ?>
                                <?php $__currentLoopData = $vendors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($v->id); ?>"><?php echo e($v->name); ?> (<?php echo e($v->email); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($vendors->count() >= 100): ?>
                                    <option disabled>--- <?php echo e(__('messages.type_to_search_more')); ?> ---</option>
                                <?php endif; ?>
                            </datalist>
                            <?php $__errorArgs = ['vendor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 rounded-md bg-gray-50 border text-sm text-gray-700">
                            <?php echo e(__('messages.quote_as_vendor')); ?>: <strong><?php echo e($user->name); ?></strong>
                        </div>
                    <?php endif; ?>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.quote_total_amount')); ?></label>
                        <input type="number" step="0.01" min="1" name="total_amount"
                            value="<?php echo e(old('total_amount', 0)); ?>" class="w-full rounded-lg border-gray-300" required>
                        <?php $__errorArgs = ['total_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div x-data="{ rows: 1 }">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700 mb-1"><?php echo e(__('messages.milestones')); ?>

                                (<?php echo e(__('messages.optional')); ?>)</label>
                            <button type="button" class="text-sm text-blue-600" @click="rows++">+
                                <?php echo e(__('messages.add_milestone')); ?></button>
                        </div>
                        <template x-for="i in rows" :key="i">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
                                <input type="text" name="milestones[][title]"
                                    placeholder="<?php echo e(__('messages.milestone_title')); ?>"
                                    class="rounded-lg border-gray-300 md:col-span-1">
                                <input type="number" name="milestones[][amount]" step="0.01" min="0"
                                    placeholder="<?php echo e(__('messages.amount')); ?>" class="rounded-lg border-gray-300">
                                <input type="text" name="milestones[][description]"
                                    placeholder="<?php echo e(__('messages.order_description')); ?>"
                                    class="rounded-lg border-gray-300 md:col-span-1">
                            </div>
                        </template>
                        <?php $__errorArgs = ['milestones.*.title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        <?php $__errorArgs = ['milestones.*.amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-sm text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="<?php echo e(route('studio.orders.show', $order)); ?>"
                            class="px-4 py-2 rounded-md border"><?php echo e(__('messages.cancel')); ?></a>
                        <button type="submit"
                            class="px-4 py-2 rounded-md bg-blue-600 text-white"><?php echo e(__('messages.send')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\studio\quotes\create.blade.php ENDPATH**/ ?>