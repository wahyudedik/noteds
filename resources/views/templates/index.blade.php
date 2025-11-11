@extends('layouts.app')

@section('title', __('Note Templates'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Note Templates') }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ __('Create and use templates to speed up note creation') }}</p>
            </div>
            <a href="{{ route('templates.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('Create Template') }}
            </a>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <button onclick="showTab('my-templates')" id="tab-my-templates"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-blue-500 text-blue-600">
                    {{ __('My Templates') }} ({{ $myTemplates->total() }})
                </button>
                <button onclick="showTab('public-templates')" id="tab-public-templates"
                    class="tab-button px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    {{ __('Public Templates') }} ({{ $publicTemplates->total() }})
                </button>
            </nav>
        </div>

        <!-- My Templates -->
        <div id="tab-content-my-templates" class="tab-content">
            @if($myTemplates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($myTemplates as $template)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    {{ $template->name }}
                                </h3>
                                @if($template->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        {{ $template->category }}
                                    </span>
                                @endif
                                @if($template->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        {{ Str::limit($template->description, 150) }}
                                    </p>
                                @endif
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('templates.show', $template) }}"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                        {{ __('View') }}
                                    </a>
                                    <form action="{{ route('templates.use', $template) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            {{ __('Use Template') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $myTemplates->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No templates yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Create your first template to get started.') }}</p>
                    <a href="{{ route('templates.create') }}" class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                        {{ __('Create Template') }} →
                    </a>
                </div>
            @endif
        </div>

        <!-- Public Templates -->
        <div id="tab-content-public-templates" class="tab-content hidden">
            @if($publicTemplates->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($publicTemplates as $template)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow overflow-hidden">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
                                    {{ $template->name }}
                                </h3>
                                @if($template->category)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        {{ $template->category }}
                                    </span>
                                @endif
                                <div class="text-xs text-gray-500 mb-2">
                                    {{ __('By') }} {{ $template->user->name }}
                                </div>
                                @if($template->description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        {{ Str::limit($template->description, 150) }}
                                    </p>
                                @endif
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('templates.show', $template) }}"
                                        class="text-sm text-blue-600 hover:text-blue-800">
                                        {{ __('View') }}
                                    </a>
                                    <form action="{{ route('templates.use', $template) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="text-sm px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                            {{ __('Use Template') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $publicTemplates->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No public templates') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('No public templates are available at the moment.') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    document.getElementById('tab-content-' + tab).classList.remove('hidden');
    
    const button = document.getElementById('tab-' + tab);
    button.classList.remove('border-transparent', 'text-gray-500');
    button.classList.add('border-blue-500', 'text-blue-600');
}
</script>
@endsection

