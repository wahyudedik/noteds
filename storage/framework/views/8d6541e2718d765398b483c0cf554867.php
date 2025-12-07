<?php $__env->startSection('title', __('messages.product_conversations')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-10 bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-4">
            <a href="<?php echo e(route('note-conversations.index')); ?>"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 inline-flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <?php echo e(__('messages.back_to_conversations')); ?>

            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                            <?php echo e($conversation->note->title ?? __('messages.product_not_available')); ?>

                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <?php echo e(__('messages.conversation_between')); ?>

                            <strong><?php echo e($conversation->buyer->name); ?></strong> (<?php echo e(__('messages.buyer')); ?>)
                            <?php echo e(__('messages.and')); ?>

                            <strong><?php echo e($conversation->seller->name); ?></strong> (<?php echo e(__('messages.seller')); ?>)
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 text-sm">
                        <?php if($conversation->note): ?>
                            <a href="<?php echo e(route('marketplace.show', $conversation->note)); ?>"
                                class="px-3 py-2 bg-blue-50 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors text-xs font-medium">
                                <?php echo e(__('messages.view_product')); ?>

                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto bg-gray-50 dark:bg-gray-900" id="message-container">
                <?php $__empty_1 = true; $__currentLoopData = $conversation->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="flex <?php echo e($message->sender_id === $user->id ? 'justify-end' : 'justify-start'); ?>" x-data="{ showTranslation<?php echo e($message->id); ?>: false, translatedText<?php echo e($message->id); ?>: null, translating<?php echo e($message->id); ?>: false }">
                        <div class="max-w-xs sm:max-w-md">
                            <div class="rounded-2xl px-4 py-3 shadow-sm
                                <?php echo e($message->sender_id === $user->id ? 'bg-blue-600 text-white rounded-tr-sm' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white rounded-tl-sm border border-gray-200 dark:border-gray-700'); ?>">
                                <p class="text-sm leading-relaxed whitespace-pre-line" x-show="!showTranslation<?php echo e($message->id); ?>">
                                    <?php echo e($message->message); ?>

                                </p>
                                <p class="text-sm leading-relaxed whitespace-pre-line" x-show="showTranslation<?php echo e($message->id); ?>" x-cloak>
                                    <span x-text="translatedText<?php echo e($message->id); ?> || '<?php echo e(__('chat.translating')); ?>...'"></span>
                                </p>
                                <div class="mt-2 flex items-center justify-between gap-2">
                                    <div class="text-[11px] <?php echo e($message->sender_id === $user->id ? 'text-blue-100' : 'text-gray-400 dark:text-gray-500'); ?>">
                                        <?php echo e($message->created_at->format('d M Y, H:i')); ?>

                                        <?php if($message->sender_id === $user->id): ?>
                                            • <?php echo e($message->read_at ? __('messages.read') : __('messages.sent')); ?>

                                        <?php endif; ?>
                                    </div>
                                    <?php if($message->sender_id !== $user->id): ?>
                                        <div class="flex items-center gap-1">
                                            <?php
                                                $userLocale = app()->getLocale();
                                                $availableLanguages = ['en', 'id', 'ar'];
                                                $messageLang = $message->original_language ?? 'en';
                                            ?>
                                            <?php $__currentLoopData = $availableLanguages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($lang !== $messageLang): ?>
                                                    <button type="button" 
                                                        @click="
                                                            if (!translatedText<?php echo e($message->id); ?>) {
                                                                translating<?php echo e($message->id); ?> = true;
                                                                fetch('<?php echo e(route('note-conversations.translate', $message)); ?>', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/json',
                                                                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                                                    },
                                                                    body: JSON.stringify({ target_language: '<?php echo e($lang); ?>' })
                                                                })
                                                                .then(response => response.json())
                                                                .then(data => {
                                                                    translatedText<?php echo e($message->id); ?> = data.translated_message;
                                                                    showTranslation<?php echo e($message->id); ?> = true;
                                                                    translating<?php echo e($message->id); ?> = false;
                                                                })
                                                                .catch(error => {
                                                                    console.error('Translation error:', error);
                                                                    translating<?php echo e($message->id); ?> = false;
                                                                });
                                                            } else {
                                                                showTranslation<?php echo e($message->id); ?> = !showTranslation<?php echo e($message->id); ?>;
                                                            }
                                                        "
                                                        class="text-[10px] px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                                        :disabled="translating<?php echo e($message->id); ?>">
                                                        <?php echo e(strtoupper($lang)); ?>

                                                    </button>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                        <?php echo e(__('messages.start_conversation')); ?>

                    </div>
                <?php endif; ?>
            </div>

            <!-- Chat Rating Section (if conversation has messages and not rated yet) -->
            <?php if($conversation->messages->count() > 0 && !$hasRated): ?>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                    <form action="<?php echo e(route('chat-ratings.store', $conversation)); ?>" method="POST" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <div>
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                <?php echo e(__('chat.rate_conversation')); ?>

                            </label>
                            <div class="flex items-center gap-2 mb-2">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="<?php echo e($i); ?>" class="hidden peer" required>
                                        <svg class="w-6 h-6 text-gray-300 dark:text-gray-600 peer-checked:text-yellow-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </label>
                                <?php endfor; ?>
                            </div>
                            <textarea name="comment" rows="2" placeholder="<?php echo e(__('chat.rating_comment_placeholder')); ?>"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                maxlength="500"></textarea>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded-lg hover:bg-yellow-600 transition-colors">
                            <?php echo e(__('chat.submit_rating')); ?>

                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- User Rating Display (if rated) -->
            <?php if($hasRated && $userRating): ?>
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-yellow-50 dark:bg-yellow-900/20">
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                        <strong><?php echo e(__('chat.you_rated')); ?>:</strong>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <svg class="w-4 h-4 inline <?php echo e($i <= $userRating->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'); ?>" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        <?php endfor; ?>
                        <?php if($userRating->comment): ?>
                            <br><span class="text-gray-600 dark:text-gray-400 mt-1 block"><?php echo e($userRating->comment); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="px-6 py-5 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                <?php if(session('success')): ?>
                    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-3 py-2 rounded-lg mb-3 text-sm">
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <!-- Quick Replies -->
                <?php if($quickReplies->count() > 0): ?>
                    <div class="mb-3">
                        <details class="group">
                            <summary class="cursor-pointer text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                </svg>
                                <?php echo e(__('chat.quick_replies')); ?>

                            </summary>
                            <div class="mt-2 space-y-2">
                                <?php $__currentLoopData = $quickReplies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $quickReply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <button type="button" 
                                        onclick="document.getElementById('message').value = '<?php echo e(addslashes($quickReply->message)); ?>'"
                                        class="block w-full text-left px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <strong><?php echo e($quickReply->title); ?>:</strong>
                                        <span class="text-gray-600 dark:text-gray-400"><?php echo e(\Illuminate\Support\Str::limit($quickReply->message, 50)); ?></span>
                                    </button>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </details>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('note-conversations.store', $conversation)); ?>" method="POST" class="space-y-3" id="message-form">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label for="message" class="sr-only"><?php echo e(__('messages.message_label')); ?></label>
                        <textarea name="message" id="message" rows="3" required maxlength="2000"
                            placeholder="<?php echo e(__('messages.write_message_placeholder')); ?>"
                            class="block w-full rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['message'];
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
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <?php echo e(__('messages.send_message')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
    <script>
        const container = document.getElementById('message-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        // Auto-scroll when new messages arrive
        const observer = new MutationObserver(function() {
            container.scrollTop = container.scrollHeight;
        });
        
        if (container) {
            observer.observe(container, { childList: true, subtree: true });
        }
    </script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\note-conversations\show.blade.php ENDPATH**/ ?>