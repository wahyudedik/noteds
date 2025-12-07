<?php $__env->startSection('title', $contest->title . ' - Contest'); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="<?php echo e(route('contests.index')); ?>" class="text-blue-600 hover:text-blue-800">
                ← Back to Contests
            </a>
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

        <!-- Contest Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            <?php if($contest->banner_image): ?>
                <img src="<?php echo e(Storage::url($contest->banner_image)); ?>" alt="<?php echo e($contest->title); ?>" class="w-full h-64 object-cover">
            <?php else: ?>
                <div class="w-full h-64 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                    <span class="text-9xl">🏆</span>
                </div>
            <?php endif; ?>

            <div class="p-8">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e($contest->title); ?></h1>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                <?php if($contest->status === 'open'): ?> bg-green-100 text-green-800
                                <?php elseif($contest->status === 'voting'): ?> bg-blue-100 text-blue-800
                                <?php elseif($contest->status === 'closed'): ?> bg-gray-100 text-gray-800
                                <?php else: ?> bg-yellow-100 text-yellow-800
                                <?php endif; ?>">
                                <?php echo e(ucfirst($contest->status)); ?>

                            </span>
                            <?php if($contest->type === 'monthly'): ?>
                                <span>📅 Monthly Challenge</span>
                            <?php elseif($contest->type === 'themed'): ?>
                                <span>🎨 Theme: <?php echo e($contest->theme); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="prose max-w-none mb-6">
                    <p class="text-gray-700 text-lg"><?php echo e($contest->description); ?></p>
                </div>

                <?php if($contest->rules): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Rules</h3>
                        <ul class="list-disc list-inside space-y-1 text-gray-700">
                            <?php $__currentLoopData = $contest->rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($rule); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if($contest->prizes): ?>
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Prizes</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <?php $__currentLoopData = $contest->prizes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $prize): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="text-2xl font-bold text-yellow-600 mb-1">
                                        <?php if($index === 0): ?> 🥇
                                        <?php elseif($index === 1): ?> 🥈
                                        <?php elseif($index === 2): ?> 🥉
                                        <?php else: ?> #<?php echo e($index + 1); ?>

                                        <?php endif; ?>
                                    </div>
                                    <?php if($prize['type'] === 'cash'): ?>
                                        <div class="font-semibold text-gray-900">$<?php echo e(number_format($prize['value'], 2)); ?></div>
                                    <?php elseif($prize['type'] === 'credits'): ?>
                                        <div class="font-semibold text-gray-900"><?php echo e($prize['value']); ?> Credits</div>
                                    <?php elseif($prize['type'] === 'badge'): ?>
                                        <div class="font-semibold text-gray-900">Badge</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600 mb-6">
                    <div>
                        <div class="font-semibold text-gray-900">Start Date</div>
                        <div><?php echo e($contest->start_date ? $contest->start_date->format('M d, Y') : 'TBD'); ?></div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">End Date</div>
                        <div><?php echo e($contest->end_date ? $contest->end_date->format('M d, Y') : 'TBD'); ?></div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Entries</div>
                        <div><?php echo e($contest->entries()->count()); ?></div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-900">Votes</div>
                        <div><?php echo e($contest->votes()->count()); ?></div>
                    </div>
                </div>

                <?php if($contest->status === 'open' && auth()->check() && $canSubmit['can_submit'] && !$userEntry): ?>
                    <a href="<?php echo e(route('contests.submit', $contest)); ?>" 
                       class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors">
                        Submit Your Entry
                    </a>
                <?php elseif($userEntry): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-blue-800 font-medium">✓ You have submitted an entry</p>
                        <p class="text-sm text-blue-600 mt-1">Status: <?php echo e(ucfirst($userEntry->status)); ?></p>
                    </div>
                <?php elseif(!$canSubmit['can_submit']): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <p class="text-yellow-800"><?php echo e(implode(' ', $canSubmit['reasons'])); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Winners Section -->
        <?php if($contest->winners->count() > 0): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">🏆 Winners</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <?php $__currentLoopData = $contest->winners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $winner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="text-center">
                                <div class="text-4xl mb-2">
                                    <?php if($winner->position === 1): ?> 🥇
                                    <?php elseif($winner->position === 2): ?> 🥈
                                    <?php elseif($winner->position === 3): ?> 🥉
                                    <?php else: ?> #<?php echo e($winner->position); ?>

                                    <?php endif; ?>
                                </div>
                                <div class="font-semibold text-gray-900"><?php echo e($winner->user->name); ?></div>
                                <div class="text-sm text-gray-600"><?php echo e($winner->entry->note->title); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Top Entries -->
        <?php if($contest->status === 'voting' || $contest->status === 'closed'): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Top Entries</h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <?php $__empty_1 = true; $__currentLoopData = $topEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div class="flex items-center gap-4 flex-1">
                                    <div class="text-2xl font-bold text-gray-400">#<?php echo e($loop->iteration); ?></div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-gray-900"><?php echo e($entry->note->title); ?></div>
                                        <div class="text-sm text-gray-600">by <?php echo e($entry->user->name); ?></div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-blue-600"><?php echo e($entry->vote_count); ?></div>
                                        <div class="text-xs text-gray-500">votes</div>
                                    </div>
                                </div>
                                <?php if($contest->isVotingOpen() && auth()->check() && !$userVote): ?>
                                    <form action="<?php echo e(route('contests.vote', $contest)); ?>" method="POST" class="ml-4">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="entry_id" value="<?php echo e($entry->id); ?>">
                                        <button type="submit" 
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-md">
                                            Vote
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <p class="text-gray-500 text-center py-8">No entries yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\contests\show.blade.php ENDPATH**/ ?>