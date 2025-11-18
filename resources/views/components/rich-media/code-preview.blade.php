@props(['code' => null, 'language' => 'javascript', 'codeUrl' => null, 'demoLink' => null])

<div class="mb-6">
    <div class="bg-gradient-to-r from-blue-50 via-indigo-50 to-purple-50 rounded-xl border-2 border-blue-200 p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">💻 Code Preview</h3>
                    @if($language)
                        <p class="text-xs text-gray-600">Language: <span class="font-semibold">{{ ucfirst($language) }}</span></p>
                    @endif
                </div>
            </div>
            @if($demoLink)
                <a href="{{ $demoLink }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:from-green-700 hover:to-emerald-700 transition-all duration-200 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    View Demo
                </a>
            @endif
        </div>
        
        <div x-data="{ 
            code: @js($code),
            language: @js($language),
            loading: {{ $codeUrl ? 'true' : 'false' }},
            async loadCode() {
                if (!this.code && '{{ $codeUrl }}') {
                    try {
                        const response = await fetch('{{ $codeUrl }}');
                        this.code = await response.text();
                    } catch (error) {
                        this.code = 'Error loading code preview.';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }" 
        x-init="loadCode()"
        class="relative">
            <div class="bg-gray-900 rounded-lg overflow-hidden shadow-xl">
                <div class="flex items-center justify-between px-4 py-2 bg-gray-800 border-b border-gray-700">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-red-500"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                        <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ $language ?? 'code' }}</span>
                </div>
                <div class="p-4 overflow-x-auto max-h-96">
                    <pre x-show="!loading" class="text-sm text-gray-100 font-mono"><code x-text="code || 'No code preview available.'"></code></pre>
                    <div x-show="loading" class="flex items-center justify-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
                    </div>
                </div>
            </div>
            <button 
                @click="navigator.clipboard.writeText(code || ''); $dispatch('notify', { type: 'success', message: 'Code copied to clipboard!' })"
                class="absolute top-16 right-4 p-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200"
                title="Copy code">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
            </button>
        </div>
    </div>
</div>

