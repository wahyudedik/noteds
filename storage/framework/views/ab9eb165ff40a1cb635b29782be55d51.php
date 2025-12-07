<?php $__env->startSection('title', __('messages.ai_chat') . ' - ' . $seller->name . ' ' . __('messages.profile')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('public.profile.show', $seller->username)); ?>" 
               class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors duration-200 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to <?php echo e($seller->name); ?>'s Profile
            </a>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center gap-4">
                    <!-- Seller Avatar -->
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center shadow-lg overflow-hidden">
                        <?php if($seller->avatar): ?>
                            <?php if(str_starts_with($seller->avatar, 'http')): ?>
                                <img src="<?php echo e($seller->avatar); ?>" alt="<?php echo e($seller->name); ?>" class="w-16 h-16 rounded-full object-cover">
                            <?php else: ?>
                                <img src="<?php echo e(Storage::url($seller->avatar)); ?>" alt="<?php echo e($seller->name); ?>" class="w-16 h-16 rounded-full object-cover">
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-2xl font-bold text-white"><?php echo e(strtoupper(substr($seller->name, 0, 1))); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-gray-900 mb-1">AI Chat: <?php echo e($seller->name); ?></h1>
                        <p class="text-sm text-gray-600">
                            Ask questions about <?php echo e($seller->name); ?>'s notes. AI will answer based on <?php echo e($publicNotes->count()); ?> public notes.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Chat Interface -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200" id="ai-chat-container">
            <!-- Chat Messages -->
            <div class="h-96 overflow-y-auto p-6 space-y-4" id="chat-messages">
                <?php if(!$aiAvailable): ?>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">AI Service Unavailable</p>
                                <p class="text-xs text-yellow-700 mt-1">AI service is currently unavailable. Please try again later.</p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        </div>
                        <div class="flex-1 bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-700">
                                👋 Hi! I'm an AI assistant that can answer questions about <strong><?php echo e($seller->name); ?></strong>'s notes. 
                                I have access to <?php echo e($publicNotes->count()); ?> public notes from this seller.
                            </p>
                            <p class="text-xs text-gray-500 mt-2">
                                Try asking: "What topics does <?php echo e($seller->name); ?> write about?" or "Tell me about the latest notes"
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 p-4">
                <form id="chat-form" class="flex gap-2">
                    <?php echo csrf_field(); ?>
                    <input type="text" 
                           id="question-input" 
                           placeholder="Ask a question about <?php echo e($seller->name); ?>'s notes..." 
                           class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200"
                           <?php if(!$aiAvailable): ?> disabled <?php endif; ?>>
                    <button type="submit" 
                            id="send-button"
                            class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-medium rounded-lg hover:from-purple-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            <?php if(!$aiAvailable): ?> disabled <?php endif; ?>>
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send
                    </button>
                </form>
            </div>
        </div>

        <!-- Notes Info -->
        <?php if($publicNotes->count() > 0): ?>
            <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <p class="text-sm text-gray-600">
                    <strong><?php echo e($publicNotes->count()); ?></strong> public notes available for AI context.
                    <?php if($publicNotes->count() > 10): ?>
                        Showing first 10 notes, AI can access all <?php echo e($publicNotes->count()); ?> notes.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chat-form');
    const questionInput = document.getElementById('question-input');
    const sendButton = document.getElementById('send-button');
    const chatMessages = document.getElementById('chat-messages');
    const aiAvailable = <?php echo json_encode($aiAvailable, 15, 512) ?>;

    if (!aiAvailable) {
        return;
    }

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const question = questionInput.value.trim();
        if (!question) {
            return;
        }

        // Disable input
        questionInput.disabled = true;
        sendButton.disabled = true;
        sendButton.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Processing...';

        // Add user message
        addMessage('user', question);
        questionInput.value = '';

        // Add loading message
        const loadingId = addMessage('ai', 'Thinking...', true);

        try {
            const response = await fetch('<?php echo e(route("public.profile.ai-chat.ask", $seller->username)); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '<?php echo e(csrf_token()); ?>',
                },
                body: JSON.stringify({ question: question })
            });

            // Check HTTP status before parsing JSON
            if (!response.ok) {
                let errorMessage = 'Terjadi kesalahan. ';
                if (response.status === 503) {
                    errorMessage = 'AI service sedang tidak tersedia. Silakan coba lagi nanti.';
                } else if (response.status === 404) {
                    errorMessage = 'Tidak ada notes ditemukan. Pastikan seller memiliki notes public.';
                } else if (response.status >= 500) {
                    errorMessage = 'Server error. Silakan coba lagi nanti.';
                } else {
                    errorMessage += 'Silakan coba lagi.';
                }
                
                removeMessage(loadingId);
                addMessage('ai', errorMessage, false);
                
                // Show retry option for server errors
                if (response.status >= 500 || response.status === 503) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Coba Lagi',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            questionInput.value = question;
                            chatForm.dispatchEvent(new Event('submit'));
                        }
                    });
                }
                return;
            }

            const data = await response.json();

            // Remove loading message
            removeMessage(loadingId);

            if (data.success) {
                // Validate answer is not empty
                if (data.answer && data.answer.trim()) {
                    addMessage('ai', data.answer, false, data.referenced_notes);
                } else {
                    addMessage('ai', 'Maaf, saya tidak dapat memproses pertanyaan Anda. Silakan coba dengan pertanyaan yang berbeda.', false);
                }
            } else {
                // Determine error message
                let errorMessage = data.message || 'Maaf, saya tidak dapat memproses pertanyaan Anda. Silakan coba lagi.';
                
                addMessage('ai', errorMessage, false);
                
                // Show retry option if it's a service error
                if (errorMessage.includes('tidak tersedia') || errorMessage.includes('error') || errorMessage.includes('unavailable')) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Coba Lagi',
                        cancelButtonText: 'Tutup',
                        confirmButtonColor: '#3b82f6',
                        cancelButtonColor: '#6b7280'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            questionInput.value = question;
                            chatForm.dispatchEvent(new Event('submit'));
                        }
                    });
                }
            }
        } catch (error) {
            removeMessage(loadingId);
            
            // Determine error message
            let errorMessage = 'Terjadi kesalahan. ';
            
            if (error.message) {
                errorMessage += error.message;
            } else {
                errorMessage += 'Pastikan koneksi internet Anda stabil dan coba lagi.';
            }
            
            addMessage('ai', errorMessage, false);
            console.error('Error:', error);
            
            // Show retry option
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                showConfirmButton: true,
                showCancelButton: true,
                confirmButtonText: 'Coba Lagi',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280'
            }).then((result) => {
                if (result.isConfirmed) {
                    questionInput.value = question;
                    chatForm.dispatchEvent(new Event('submit'));
                }
            });
        } finally {
            // Re-enable input
            questionInput.disabled = false;
            sendButton.disabled = false;
            sendButton.innerHTML = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" /></svg>Send';
            questionInput.focus();
        }
    });

    function addMessage(type, content, isLoading = false, referencedNotes = []) {
        const messageId = 'msg-' + Date.now();
        const messageDiv = document.createElement('div');
        messageDiv.id = messageId;
        messageDiv.className = `flex items-start gap-3 ${type === 'user' ? 'flex-row-reverse' : ''}`;

        if (type === 'user') {
            messageDiv.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <div class="flex-1 bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-800">${escapeHtml(content)}</p>
                </div>
            `;
        } else {
            const aiIcon = isLoading 
                ? '<svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>'
                : '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>';
            
            let notesHtml = '';
            if (referencedNotes && referencedNotes.length > 0) {
                notesHtml = '<div class="mt-3 pt-3 border-t border-gray-200"><p class="text-xs font-semibold text-gray-600 mb-2">Referenced Notes:</p><div class="flex flex-wrap gap-2">';
                referencedNotes.forEach(note => {
                    notesHtml += `<a href="<?php echo e(route('marketplace.show', ':id')); ?>" class="inline-flex items-center px-2 py-1 rounded text-xs bg-blue-100 text-blue-700 hover:bg-blue-200 transition-colors">${escapeHtml(note.title)}</a>`.replace(':id', note.id);
                });
                notesHtml += '</div></div>';
            }

            messageDiv.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-blue-500 flex items-center justify-center flex-shrink-0">
                    ${aiIcon}
                </div>
                <div class="flex-1 bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">${escapeHtml(content)}</p>
                    ${notesHtml}
                </div>
            `;
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        return messageId;
    }

    function removeMessage(messageId) {
        const message = document.getElementById(messageId);
        if (message) {
            message.remove();
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\public\profile\ai-chat.blade.php ENDPATH**/ ?>