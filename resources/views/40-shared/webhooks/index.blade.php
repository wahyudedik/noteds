@extends('40-shared.layouts.app')

@section('title', __('messages.webhooks'))

@section('content')
    <div class="max-w-5xl mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">{{ __('messages.webhooks') }}</h1>
        <p class="text-gray-600 mb-6">{{ __('messages.coming_soon') }}</p>

        <div class="bg-white shadow rounded p-4 space-y-3">
            <p class="text-sm text-gray-500">Webhook endpoints, secret management, and delivery logs will be configured here.
            </p>
            <ul class="list-disc ml-5 text-sm text-gray-600">
                <li>Create/manage endpoints</li>
                <li>Rotate secrets</li>
                <li>View retry and delivery status</li>
            </ul>
        </div>
    </div>
    @endsection@extends('40-shared/layouts/app')

@section('title', __('Webhooks'))

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('Webhooks') }}</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ __('Manage your webhooks for real-time notifications') }}</p>
                </div>
                <a href="{{ route('webhooks.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('Create Webhook') }}
                </a>
            </div>

            <!-- Webhooks List -->
            @if ($webhooks->count() > 0)
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Name') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Event') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('URL') }}
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Status') }}
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($webhooks as $webhook)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $webhook->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ str_replace('.', ' ', $webhook->event) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 truncate max-w-xs">{{ $webhook->url }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($webhook->is_active)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ __('Active') }}
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ __('Inactive') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('webhooks.show', $webhook) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-4">
                                            {{ __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $webhooks->links() }}
                </div>
            @else
                <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('No webhooks yet') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        {{ __('Create your first webhook to receive real-time notifications.') }}</p>
                    <a href="{{ route('webhooks.create') }}"
                        class="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800">
                        {{ __('Create Webhook') }} →
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
