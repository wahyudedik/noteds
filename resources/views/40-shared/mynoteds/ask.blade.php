@extends('40-shared/layouts/app')

@section('title', __('messages.ask_your_notes_title'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('mynoteds.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.ask_your_notes') }}</h1>
            </div>
            <p class="text-gray-600">{{ __('messages.ask_natural_language_questions_description') }}</p>
        </div>

        <!-- Q&A Interface -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
            <form id="ask-form" class="space-y-4">
                @csrf
                <div>
                    <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.your_question') }}
                    </label>
                    <textarea 
                        id="question" 
                        name="question" 
                        rows="3"
                        :placeholder="__('messages.question_placeholder')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500"
                        required
                    ></textarea>
                </div>
                <button 
                    type="submit" 
                    id="ask-button"
                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        {{ __('messages.ask_ai') }}
                    </span>
                </button>
            </form>

            <!-- Loading State -->
            <div id="loading" class="hidden mt-6 text-center py-8">
                <svg class="animate-spin h-8 w-8 text-purple-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600">{{ __('messages.ai_is_thinking') }}</p>
            </div>

            <!-- Answer Section -->
            <div id="answer-section" class="hidden mt-6">
                <div class="bg-purple-50 border-l-4 border-purple-500 rounded-r-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        {{ __('messages.answer') }}
                    </h3>
                    <div id="answer-content" class="text-gray-700 prose max-w-none mb-4"></div>
                    
                    <!-- Referenced Notes -->
                    <div id="referenced-notes" class="hidden mt-4 pt-4 border-t border-purple-200">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('messages.referenced_notes') }}
                        </h4>
                        <div id="referenced-notes-list" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>
            </div>

            <!-- Error Section -->
            <div id="error-section" class="hidden mt-6">
                <div class="bg-red-50 border-l-4 border-red-500 rounded-r-lg p-6">
                    <h3 class="text-lg font-semibold text-red-900 mb-2">{{ __('messages.error') }}</h3>
                    <p id="error-message" class="text-red-700"></p>
                </div>
            </div>
        </div>

        <!-- Tips Section -->
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-6 mt-6">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('messages.tips_for_better_answers') }}</h3>
                    <ul class="text-blue-800 text-sm space-y-1 list-disc list-inside">
                        <li>{{ __('messages.ask_specific_questions') }}</li>
                        <li>{{ __('messages.mention_names_dates_topics') }}</li>
                        <li>{{ __('messages.example_question') }}</li>
                        <li>{{ __('messages.ai_searches_last_100_notes') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('ask-form');
    const loading = document.getElementById('loading');
    const answerSection = document.getElementById('answer-section');
    const answerContent = document.getElementById('answer-content');
    const errorSection = document.getElementById('error-section');
    const errorMessage = document.getElementById('error-message');
    const askButton = document.getElementById('ask-button');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const question = document.getElementById('question').value.trim();
        
        if (!question) {
            return;
        }

        // Show loading, hide other sections
        loading.classList.remove('hidden');
        answerSection.classList.add('hidden');
        errorSection.classList.add('hidden');
        askButton.disabled = true;

        try {
            const response = await fetch('{{ route("ai-memory.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question: question })
            });

            const data = await response.json();

            loading.classList.add('hidden');
            askButton.disabled = false;

            if (data.success) {
                // Format answer with clickable note references
                let formattedAnswer = data.answer || '';
                
                // Replace "Note X" patterns with clickable links if referenced notes are available
                if (data.referenced_notes && data.referenced_notes.length > 0) {
                    // Create a map of note numbers to note objects
                    const noteMap = {};
                    data.referenced_notes.forEach((note) => {
                        if (note.number) {
                            noteMap[note.number] = note;
                        }
                    });
                    
                    // Replace all "Note X" patterns with clickable links
                    formattedAnswer = formattedAnswer.replace(/Note\s+(\d+)/gi, (match, noteNum) => {
                        const num = parseInt(noteNum);
                        if (noteMap[num]) {
                            const note = noteMap[num];
                            return `<a href="/notes/${note.id}" class="text-purple-600 hover:text-purple-800 font-semibold underline" target="_blank" title="${note.title}">${match}</a>`;
                        }
                        return match;
                    });
                }
                
                answerContent.innerHTML = formattedAnswer.replace(/\n/g, '<br>');
                answerSection.classList.remove('hidden');
                
                // Display referenced notes as clickable badges
                const referencedNotesDiv = document.getElementById('referenced-notes');
                const referencedNotesList = document.getElementById('referenced-notes-list');
                
                if (data.referenced_notes && data.referenced_notes.length > 0) {
                    referencedNotesList.innerHTML = '';
                    data.referenced_notes.forEach((note) => {
                        const badge = document.createElement('a');
                        badge.href = `/notes/${note.id}`;
                        badge.target = '_blank';
                        badge.className = 'inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 hover:border-purple-300 transition-colors text-sm font-medium';
                        badge.innerHTML = `
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            ${note.title || 'Note'}
                        `;
                        referencedNotesList.appendChild(badge);
                    });
                    referencedNotesDiv.classList.remove('hidden');
                } else {
                    referencedNotesDiv.classList.add('hidden');
                }
            } else {
                errorMessage.textContent = data.message || '{{ __('messages.an_error_occurred') }}';
                errorSection.classList.remove('hidden');
            }
        } catch (error) {
            loading.classList.add('hidden');
            askButton.disabled = false;
            errorMessage.textContent = '{{ __('messages.network_error') }}';
            errorSection.classList.remove('hidden');
        }
    });
});
</script>
@endpush
@endsection


