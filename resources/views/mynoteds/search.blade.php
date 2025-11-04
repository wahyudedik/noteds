@extends('layouts.app')

@section('title', __('messages.semantic_search_title'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ route('mynoteds.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.semantic_search') }}</h1>
            </div>
            <p class="text-gray-600">{{ __('messages.search_by_meaning_description') }}</p>
        </div>

        <!-- Search Interface -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
            <div class="flex gap-4 mb-4">
                <div class="flex-1">
                    <input 
                        type="text" 
                        id="search-query"
                        value="{{ $query }}"
                        :placeholder="__('messages.search_by_meaning_placeholder')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 px-4 py-3"
                        autofocus
                    >
                </div>
                <button 
                    type="button"
                    id="semantic-search-btn"
                    class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        {{ __('messages.ai_search') }}
                    </span>
                </button>
                <button 
                    type="button"
                    id="basic-search-btn"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('messages.basic_search') }}
                    </span>
                </button>
            </div>
            <div id="search-loading" class="hidden text-center py-4">
                <svg class="animate-spin h-6 w-6 text-blue-600 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm text-gray-600 mt-2">{{ __('messages.ai_is_searching') }}</p>
            </div>
        </div>

        <!-- Basic Search Results -->
        @if($query && $results->count() > 0)
            <div id="basic-results" class="bg-white rounded-lg border border-gray-200 shadow-sm mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">
                        {{ __('messages.found_results', ['total' => $results->total(), 'query' => $query]) }}
                    </h2>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @foreach($results as $note)
                            <div class="border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors duration-150">
                                <div class="flex items-start justify-between mb-3">
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <a href="{{ route('notes.show', $note) }}" class="hover:text-blue-600 transition-colors duration-200">
                                            {{ $note->title }}
                                        </a>
                                    </h3>
                                    <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                @if($note->summary)
                                    <p class="text-gray-600 mb-3">{{ $note->summary }}</p>
                                @endif
                                @if($note->tags->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($note->tags as $tag)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6">
                        {{ $results->links() }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Semantic Search Results Container -->
        <div id="semantic-results" class="hidden mb-6"></div>

        <!-- Info Section -->
        @if(!$query)
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">{{ __('messages.two_search_modes') }}</h3>
                        <ul class="text-blue-800 text-sm space-y-2">
                            <li><strong>{{ __('messages.ai_search') }}:</strong> {{ __('messages.ai_search_semantic') }}</li>
                            <li><strong>{{ __('messages.basic_search') }}:</strong> {{ __('messages.basic_search_traditional') }}</li>
                        </ul>
                        <p class="text-blue-700 text-sm mt-3">
                            {{ __('messages.try_searching_natural_language') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchQuery = document.getElementById('search-query');
    const semanticBtn = document.getElementById('semantic-search-btn');
    const basicBtn = document.getElementById('basic-search-btn');
    const loading = document.getElementById('search-loading');
    const semanticResults = document.getElementById('semantic-results');
    const basicResults = document.getElementById('basic-results');

    // Basic Search
    basicBtn.addEventListener('click', function() {
        const query = searchQuery.value.trim();
        if (!query) return;
        
        window.location.href = `{{ route('mynoteds.search') }}?q=${encodeURIComponent(query)}`;
    });

    // Semantic Search
    semanticBtn.addEventListener('click', async function() {
        const query = searchQuery.value.trim();
        if (!query) return;

        // Show loading, hide results
        loading.classList.remove('hidden');
        semanticResults.classList.add('hidden');
        if (basicResults) basicResults.classList.add('hidden');

        try {
            const response = await fetch('{{ route("ai-memory.search") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ query: query })
            });

            const data = await response.json();
            loading.classList.add('hidden');

            if (data.success && data.results && data.results.length > 0) {
                const foundResultsText = `{{ __('messages.found_results_semantic', ['total' => ':total', 'query' => ':query']) }}`
                    .replace(':total', data.total)
                    .replace(':query', data.query);
                let html = `
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-bold text-gray-900">
                                ${foundResultsText}
                            </h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                `;

                data.results.forEach(function(note) {
                    html += `
                        <div class="border border-gray-200 rounded-lg p-6 hover:bg-gray-50 transition-colors duration-150">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="/notes/${note.id}" class="hover:text-blue-600 transition-colors duration-200">
                                    ${note.title}
                                </a>
                            </h3>
                            <p class="text-gray-600">${note.preview}</p>
                        </div>
                    `;
                });

                html += `
                            </div>
                        </div>
                    </div>
                `;

                semanticResults.innerHTML = html;
                semanticResults.classList.remove('hidden');
            } else {
                semanticResults.innerHTML = `
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('messages.no_results_found') }}</h3>
                        <p class="text-gray-600">${data.message || '{{ __('messages.try_different_search_terms') }}'}</p>
                    </div>
                `;
                semanticResults.classList.remove('hidden');
            }
        } catch (error) {
            loading.classList.add('hidden');
            semanticResults.innerHTML = `
                <div class="bg-red-50 border-l-4 border-red-500 rounded-r-lg p-6">
                    <p class="text-red-800">{{ __('messages.error') }}: ${error.message || '{{ __('messages.network_error') }}'}</p>
                </div>
            `;
            semanticResults.classList.remove('hidden');
        }
    });

    // Enter key support
    searchQuery.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            semanticBtn.click();
        }
    });
});
</script>
@endpush
@endsection
