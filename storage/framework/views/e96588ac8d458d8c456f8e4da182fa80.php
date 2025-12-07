<?php $__env->startSection('title', $note->title); ?>

<?php $__env->startSection('content'); ?>
    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <a href="<?php echo e(route('notes.index')); ?>"
                    class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <?php echo e(__('messages.back_to_my_notes')); ?>

                </a>
            </div>

            <!-- Note Details Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                <div class="p-6">
                    <!-- Header with Actions -->
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e($note->title); ?></h1>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?php echo e(__('messages.created')); ?> <?php echo e($note->created_at->format('d M Y')); ?>

                                <?php if($note->updated_at != $note->created_at): ?>
                                    <span class="text-gray-400">•</span>
                                    <span><?php echo e(__('messages.updated')); ?> <?php echo e($note->updated_at->format('d M Y')); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex gap-2 ml-4">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $note)): ?>
                                <a href="<?php echo e(route('notes.edit', $note)); ?>"
                                    class="inline-flex items-center px-3 py-2 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    <?php echo e(__('messages.edit')); ?>

                                </a>
                            <?php endif; ?>
                            <?php if($hasTransactions ?? false): ?>
                                <div class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-500 bg-gray-50 cursor-not-allowed"
                                    title="<?php echo e(__('messages.cannot_delete_sold_tooltip')); ?>">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <?php echo e(__('messages.cannot_delete_sold')); ?>

                                </div>
                            <?php else: ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $note)): ?>
                                    <form action="<?php echo e(route('notes.destroy', $note)); ?>" method="POST" class="delete-note-form">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-2 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <?php echo e(__('messages.delete')); ?>

                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Badges -->
                    <div class="flex flex-wrap items-center gap-3 mb-6 pb-6 border-b border-gray-200">
                        <?php if($note->is_public): ?>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                    <path fill-rule="evenodd"
                                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e(__('messages.public')); ?>

                            </span>
                        <?php else: ?>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                        clip-rule="evenodd" />
                                </svg>
                                <?php echo e(__('messages.private')); ?>

                            </span>
                        <?php endif; ?>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <?php echo e(ucfirst($note->status)); ?>

                        </span>
                        <?php if($note->price > 0): ?>
                            <span
                                class="inline-flex items-center px-3 py-1 rounded-lg text-base font-semibold bg-yellow-100 text-yellow-800">
                                <?php echo e(currency($note->price)); ?>

                            </span>
                        <?php else: ?>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                <?php echo e(__('messages.free')); ?>

                            </span>
                        <?php endif; ?>
                        <?php if($note->average_rating > 0): ?>
                            <div class="inline-flex items-center gap-0.5 ml-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-4 h-4 <?php echo e($i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300'); ?>"
                                        fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                <?php endfor; ?>
                                <span class="text-sm font-medium text-gray-700 ml-1"><?php echo e($note->average_rating); ?></span>
                                <span class="text-xs text-gray-500">(<?php echo e($note->total_reviews); ?>

                                    <?php echo e(Str::plural('review', $note->total_reviews)); ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tags -->
                    <?php if($note->tags->count() > 0): ?>
                        <div class="mb-6">
                            <div class="flex flex-wrap gap-2">
                                <?php $__currentLoopData = $note->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <?php echo e($tag->name); ?>

                                    </span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Author -->
                    <div class="mb-6 pb-6 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <?php if($note->user->avatar): ?>
                                    <img src="<?php echo e($note->user->avatar); ?>" alt="<?php echo e($note->user->name); ?>"
                                        class="w-10 h-10 rounded-full object-cover">
                                <?php else: ?>
                                    <span
                                        class="text-sm font-semibold text-gray-600"><?php echo e(strtoupper(substr($note->user->name, 0, 1))); ?></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Created by <?php echo e($note->user->name); ?></p>
                                <?php if($note->is_public): ?>
                                    <a href="<?php echo e(route('public.profile.show', $note->user->username)); ?>"
                                        class="text-xs text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                        View profile →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="prose max-w-none">
                        <div class="ql-editor text-gray-900 leading-relaxed"><?php echo $note->content; ?></div>
                    </div>

                    <!-- Attachments (if exists) -->
                    <?php if($note->hasAttachments()): ?>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Attachments (<?php echo e($note->file_count); ?>)
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <?php $__currentLoopData = $note->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php
                                        $isExternal = false;
                                        $url = null;
                                        $filename = 'Unknown';

                                        // Check if it's an external link
if (
    is_array($attachment) &&
    isset($attachment['type']) &&
    $attachment['type'] === 'external'
) {
    $isExternal = true;
    $url = $attachment['url'] ?? '';
    $filename = $attachment['filename'] ?? 'External Link';
} elseif (
    is_string($attachment) &&
    filter_var($attachment, FILTER_VALIDATE_URL)
) {
    $isExternal = true;
    $url = $attachment;
    $parsedUrl = parse_url($attachment);
    $filename = basename($parsedUrl['path'] ?? '') ?: 'External Link';
} else {
    $filename = is_array($attachment)
        ? $attachment['filename'] ?? 'Unknown'
        : basename($attachment);
    $url = route('notes.attachments.download', [
        'note' => $note->id,
        'filename' => $filename,
                                            ]);
                                        }
                                    ?>
                                    <a href="<?php echo e($url); ?>" target="<?php echo e($isExternal ? '_blank' : '_self'); ?>"
                                        rel="<?php echo e($isExternal ? 'noopener noreferrer' : ''); ?>"
                                        class="flex items-center p-3 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 hover:border-blue-300 transition-all duration-200">
                                        <?php if($isExternal): ?>
                                            <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        <?php endif; ?>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate"><?php echo e($filename); ?></p>
                                            <?php if($isExternal): ?>
                                                <p class="text-xs text-green-600 font-medium">🔗 External Link</p>
                                            <?php elseif(is_array($attachment) && isset($attachment['size'])): ?>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo e(number_format($attachment['size'] / 1024, 2)); ?> KB</p>
                                            <?php endif; ?>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Demo Link (Prominent Display) -->
                    <?php if($note->demo_link): ?>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div
                                class="bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 rounded-xl border-2 border-green-300 p-6 shadow-lg hover:shadow-xl transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4 flex-1">
                                        <div
                                            class="flex-shrink-0 w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-600 text-white uppercase tracking-wide">
                                                    🚀 Demo Live
                                                </span>
                                                <h3 class="text-lg font-bold text-gray-900">Coba Demo Sekarang!</h3>
                                            </div>
                                            <p class="text-sm text-gray-700 mb-2">Lihat dan coba produk ini secara langsung
                                                sebelum membeli</p>
                                            <p class="text-xs text-gray-500 truncate"><?php echo e($note->demo_link); ?></p>
                                        </div>
                                    </div>
                                    <a href="<?php echo e($note->demo_link); ?>" target="_blank" rel="noopener noreferrer"
                                        class="ml-4 flex-shrink-0 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold rounded-lg shadow-md hover:from-green-700 hover:to-emerald-700 hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                        Buka Demo
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Ecosystem Links (if exists) -->
                    <?php
                        $ecosystemLinks = [];
                        if ($note->ecosystem_category === 'video' && $note->video_link) {
                            $ecosystemLinks[] = [
                                'type' => 'video',
                                'label' => 'Video Link',
                                'url' => $note->video_link,
                                'icon' => 'video',
                            ];
                        }
                        if ($note->ecosystem_category === 'audio' && $note->audio_link) {
                            $ecosystemLinks[] = [
                                'type' => 'audio',
                                'label' => 'Audio Link',
                                'url' => $note->audio_link,
                                'icon' => 'music',
                            ];
                        }
                        if ($note->ecosystem_category === 'design' && $note->design_preview_link) {
                            $ecosystemLinks[] = [
                                'type' => 'design',
                                'label' => 'Design Preview',
                                'url' => $note->design_preview_link,
                                'icon' => 'eye',
                            ];
                        }
                        if ($note->ecosystem_category === 'photo' && $note->photo_gallery_link) {
                            $ecosystemLinks[] = [
                                'type' => 'photo',
                                'label' => 'Photo Gallery',
                                'url' => $note->photo_gallery_link,
                                'icon' => 'image',
                            ];
                        }
                        if ($note->ecosystem_category === 'code' && $note->code_demo_link) {
                            $ecosystemLinks[] = [
                                'type' => 'code',
                                'label' => 'Demo/Repository',
                                'url' => $note->code_demo_link,
                                'icon' => 'code',
                            ];
                        }
                        if ($note->ecosystem_category === 'theme' && $note->theme_preview_link) {
                            $ecosystemLinks[] = [
                                'type' => 'theme',
                                'label' => 'Live Demo',
                                'url' => $note->theme_preview_link,
                                'icon' => 'eye',
                            ];
                        }
                        if ($note->ecosystem_category === '3d' && $note->three_d_preview_link) {
                            $ecosystemLinks[] = [
                                'type' => '3d',
                                'label' => '3D Preview',
                                'url' => $note->three_d_preview_link,
                                'icon' => 'cube',
                            ];
                        }
                    ?>
                    <?php if(count($ecosystemLinks) > 0): ?>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                </svg>
                                External Links
                            </h3>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $ecosystemLinks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <a href="<?php echo e($link['url']); ?>" target="_blank" rel="noopener noreferrer"
                                        class="flex items-center p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:border-blue-400 hover:shadow-md transition-all duration-200 group">
                                        <div
                                            class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 group-hover:bg-blue-200 transition-colors">
                                            <?php if($link['icon'] === 'video'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            <?php elseif($link['icon'] === 'music'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                                </svg>
                                            <?php elseif($link['icon'] === 'eye'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            <?php elseif($link['icon'] === 'image'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            <?php elseif($link['icon'] === 'code'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                                </svg>
                                            <?php elseif($link['icon'] === 'cube'): ?>
                                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">
                                                <?php echo e($link['label']); ?></p>
                                            <p class="text-xs text-gray-500 truncate mt-1"><?php echo e($link['url']); ?></p>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600 transition-colors"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Rating/Review Summary (if public) -->
                    <?php if($note->is_public && $note->total_reviews > 0): ?>
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex items-center gap-2 mb-2">
                                <h3 class="text-base font-semibold text-gray-900"><?php echo e(__('messages.rating_summary')); ?></h3>
                                <div class="flex items-center gap-0.5">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-4 h-4 <?php echo e($i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300'); ?>"
                                            fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    <?php endfor; ?>
                                    <span
                                        class="text-sm font-medium text-gray-700 ml-1"><?php echo e($note->average_rating); ?></span>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600"><?php echo e($note->total_reviews); ?>

                                <?php echo e(Str::plural('review', $note->total_reviews)); ?> from buyers</p>
                            <a href="<?php echo e(route('marketplace.show', $note)); ?>"
                                class="mt-2 inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors duration-200">
                                View all reviews in marketplace →
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Buyer History (for sellers/original creators) -->
            <?php if(isset($buyerHistory) &&
                    $buyerHistory->count() > 0 &&
                    auth()->check() &&
                    ($note->original_creator_id === auth()->id() || $note->user_id === auth()->id())): ?>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Buyer History
                        </h2>
                        <p class="text-sm text-gray-600 mb-4">List of all buyers who have purchased this note (including
                            resells)</p>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $buyerHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <span class="text-sm font-bold text-blue-600">#<?php echo e($index + 1); ?></span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">
                                                Buyer: <?php echo e($transaction->buyer->name); ?>

                                            </p>
                                            <p class="text-xs text-gray-600">
                                                Sold by: <?php echo e($transaction->seller->name); ?>

                                                <?php if($transaction->original_creator_id && $transaction->original_creator_id !== $transaction->seller_id): ?>
                                                    <span class="text-blue-600">(Original creator:
                                                        <?php echo e($transaction->originalCreator->name ?? 'N/A'); ?>)</span>
                                                <?php endif; ?>
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                <?php echo e($transaction->created_at->format('d M Y, H:i')); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-green-600">
                                            <?php echo e(currency($transaction->amount)); ?>

                                        </p>
                                        <p class="text-xs text-gray-500">
                                            Platform: <?php echo e(currency($transaction->platform_fee)); ?>

                                            <?php if($transaction->creator_commission > 0): ?>
                                                <br>Creator: <?php echo e(currency($transaction->creator_commission)); ?>

                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Update History -->
            <?php if(isset($updateHistory) &&
                    $updateHistory->count() > 0 &&
                    auth()->check() &&
                    ($note->original_creator_id === auth()->id() || $note->user_id === auth()->id())): ?>
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 mb-8">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Update History
                        </h2>
                        <p class="text-sm text-gray-600 mb-4">History of all updates made to this note</p>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $updateHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $history): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium 
                                            <?php echo e($history->action === 'created' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'); ?>">
                                                <?php echo e(ucfirst($history->action)); ?>

                                            </span>
                                            <span class="text-xs text-gray-500">
                                                by <?php echo e($history->user->name); ?>

                                            </span>
                                        </div>
                                        <span class="text-xs text-gray-500">
                                            <?php echo e($history->created_at->format('d M Y, H:i')); ?>

                                        </span>
                                    </div>
                                    <?php if($history->changes): ?>
                                        <p class="text-sm text-gray-700 mb-2"><?php echo e($history->changes); ?></p>
                                    <?php endif; ?>
                                    <?php if($history->notes): ?>
                                        <p class="text-xs text-gray-500 italic"><?php echo e($history->notes); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Handle delete confirmation with SweetAlert2
                document.querySelectorAll('.delete-note-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formElement = this;

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: '<?php echo e(__('messages.are_you_sure')); ?>',
                                text: '<?php echo e(__('messages.delete_confirmation')); ?>',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#dc2626',
                                cancelButtonColor: '#6b7280',
                                confirmButtonText: '<?php echo e(__('messages.yes_delete')); ?>',
                                cancelButtonText: '<?php echo e(__('messages.no_cancel')); ?>'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    formElement.submit();
                                }
                            });
                        } else {
                            if (confirm('<?php echo e(__('messages.delete_confirmation')); ?>')) {
                                formElement.submit();
                            }
                        }
                    });
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\notes\show.blade.php ENDPATH**/ ?>