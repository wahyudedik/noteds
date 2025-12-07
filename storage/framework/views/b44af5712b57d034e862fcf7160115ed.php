<?php $__env->startSection('title', __('messages.ai_memory_platform')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo e(__('messages.ai_memory_platform')); ?></h1>
            <p class="text-gray-600"><?php echo e(__('messages.ai_memory_description')); ?></p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.total_notes')); ?></p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($total_notes ?? 0); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Topik</p>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e(count($topics ?? [])); ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500"><?php echo e(__('messages.ai_status')); ?></p>
                        <p class="text-2xl font-bold text-green-600">Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Q&A Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4"><?php echo e(__('messages.ask_ai_about_notes')); ?></h2>
                    
                    <form id="aiMemoryForm" class="mb-6">
                        <?php echo csrf_field(); ?>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="questionInput"
                                name="question" 
                                placeholder="Contoh: Apa saja topik yang sering saya bahas? Atau: Jelaskan tentang project Laravel yang saya buat..."
                                class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <button 
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-lg transition-colors"
                            >
                                Tanya
                            </button>
                        </div>
                    </form>

                    <!-- Answer Display -->
                    <div id="answerContainer" class="hidden">
                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                    </svg>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h3 class="text-sm font-medium text-gray-900 mb-2"><?php echo e(__('messages.ai_answer')); ?></h3>
                                    <div id="answerText" class="text-gray-700 whitespace-pre-wrap"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Referenced Notes -->
                        <div id="referencedNotes" class="hidden">
                            <h4 class="text-sm font-semibold text-gray-900 mb-2">Catatan yang Direferensikan:</h4>
                            <div id="referencedNotesList" class="space-y-2"></div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div id="loadingState" class="hidden text-center py-8">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-sm text-gray-600"><?php echo e(__('messages.ai_processing')); ?></p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.quick_actions')); ?></h3>
                    <div class="space-y-2">
                        <button 
                            onclick="askQuestion('Apa saja topik utama dari catatan saya?')"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            📊 Topik Utama
                        </button>
                        <button 
                            onclick="askQuestion('Beri saya ringkasan dari semua catatan saya')"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            📝 Ringkasan Catatan
                        </button>
                        <button 
                            onclick="generateInsights()"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            🧠 Generate Insights
                        </button>
                        <button 
                            onclick="buildKnowledgeBase()"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            🔄 Rebuild Knowledge Base
                        </button>
                    </div>
                </div>

                <!-- Topics -->
                <?php if(!empty($topics)): ?>
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e(__('messages.popular_topics')); ?></h3>
                    <div class="flex flex-wrap gap-2">
                        <?php $__currentLoopData = array_slice($topics, 0, 15); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $topic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                <?php echo e($topic); ?>

                            </span>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('aiMemoryForm');
    const questionInput = document.getElementById('questionInput');
    const answerContainer = document.getElementById('answerContainer');
    const answerText = document.getElementById('answerText');
    const referencedNotes = document.getElementById('referencedNotes');
    const referencedNotesList = document.getElementById('referencedNotesList');
    const loadingState = document.getElementById('loadingState');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const question = questionInput.value.trim();
        
        if (!question) {
            return;
        }

        // Show loading
        loadingState.classList.remove('hidden');
        answerContainer.classList.add('hidden');
        referencedNotes.classList.add('hidden');

        // Ask question
        fetch('<?php echo e(route("ai-memory.ask")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ question: question })
        })
        .then(response => response.json())
        .then(data => {
            loadingState.classList.add('hidden');
            
            if (data.success) {
                answerText.textContent = data.answer;
                answerContainer.classList.remove('hidden');

                // Show referenced notes if any
                if (data.referenced_notes && data.referenced_notes.length > 0) {
                    referencedNotesList.innerHTML = '';
                    data.referenced_notes.forEach(noteId => {
                        const noteLink = document.createElement('a');
                        noteLink.href = `/notes/${noteId}`;
                        noteLink.className = 'block px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded-lg';
                        noteLink.textContent = `Catatan: ${noteId.substring(0, 8)}...`;
                        referencedNotesList.appendChild(noteLink);
                    });
                    referencedNotes.classList.remove('hidden');
                }
            } else {
                alert(data.message || 'Gagal mendapatkan jawaban dari AI.');
            }
        })
        .catch(error => {
            loadingState.classList.add('hidden');
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses pertanyaan.');
        });
    });
});

function askQuestion(question) {
    document.getElementById('questionInput').value = question;
    document.getElementById('aiMemoryForm').dispatchEvent(new Event('submit'));
}

function generateInsights() {
    if (!confirm('Generate insights dari semua catatan Anda? Ini mungkin memakan waktu beberapa saat.')) {
        return;
    }

    fetch('<?php echo e(route("ai-memory.insights")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('questionInput').value = 'Beri saya insights dari catatan saya';
            document.getElementById('answerText').textContent = data.insights;
            document.getElementById('answerContainer').classList.remove('hidden');
        } else {
            alert(data.message || 'Gagal menghasilkan insights.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghasilkan insights.');
    });
}

function buildKnowledgeBase() {
    if (!confirm('Rebuild knowledge base? Ini akan memperbarui data AI dengan catatan terbaru Anda.')) {
        return;
    }

    fetch('<?php echo e(route("ai-memory.build-knowledge-base")); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Knowledge base sedang dibangun. Silakan refresh halaman dalam beberapa saat.');
            setTimeout(() => location.reload(), 2000);
        } else {
            alert(data.message || 'Gagal membangun knowledge base.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat membangun knowledge base.');
    });
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\ai-memory\index.blade.php ENDPATH**/ ?>