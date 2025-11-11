@extends('layouts.app')

@section('title', $webhook->name)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('webhooks.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Webhooks') }}
            </a>
        </div>

        <!-- Webhook Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $webhook->name }}</h1>
                    @if($webhook->is_active)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ __('Active') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                            {{ __('Inactive') }}
                        </span>
                    @endif
                </div>
            </div>

            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Event') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ str_replace('.', ' ', $webhook->event) }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($webhook->is_active)
                            <span class="text-green-600">{{ __('Active') }}</span>
                        @else
                            <span class="text-gray-600">{{ __('Inactive') }}</span>
                        @endif
                    </dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">{{ __('URL') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900 break-all">{{ $webhook->url }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-sm font-medium text-gray-500">{{ __('Secret') }}</dt>
                    <dd class="mt-1">
                        <div class="flex items-center gap-2">
                            <code class="text-sm bg-gray-100 px-2 py-1 rounded">{{ $webhook->secret }}</code>
                            <button onclick="copySecret()" class="text-xs text-blue-600 hover:text-blue-800">
                                {{ __('Copy') }}
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('Use this secret to verify webhook requests.') }}</p>
                    </dd>
                </div>
            </dl>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <form action="{{ route('webhooks.test', $webhook) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                        {{ __('Test Webhook') }}
                    </button>
                </form>
                <form action="{{ route('webhooks.destroy', $webhook) }}" method="POST" class="inline"
                    onsubmit="return confirm('{{ __('Are you sure you want to delete this webhook?') }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700">
                        {{ __('Delete') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copySecret() {
    const secret = '{{ $webhook->secret }}';
    navigator.clipboard.writeText(secret).then(() => {
        alert('{{ __('Secret copied to clipboard') }}');
    });
}
</script>
@endsection

