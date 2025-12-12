@extends('layouts.app')

@section('title', __('messages.view_revenue_details'))

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.view_revenue_details') }}</h2>
                <a href="{{ route('admin.view-history.index') }}" class="text-blue-600 hover:text-blue-800">←
                    {{ __('messages.back_to_view_history') }}</a>
            </div>

            <!-- View Revenue Info -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.view_information') }}</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.note') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            <a href="{{ route('marketplace.show', $viewRevenue->note) }}"
                                class="text-blue-600 hover:text-blue-800">
                                {{ $viewRevenue->note->title ?? 'N/A' }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.note_owner') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $viewRevenue->note->user->name ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.viewer') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($viewRevenue->user)
                                {{ $viewRevenue->user->name }} ({{ $viewRevenue->user->email }})
                            @else
                                <span class="text-gray-500">{{ __('messages.guest_user') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.amount') }}</dt>
                        <dd class="mt-1 text-sm font-semibold text-green-600">
                            {{ currency($viewRevenue->amount, 'IDR', 'IDR') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.ip_address') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $viewRevenue->ip_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.fingerprint') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono text-xs">
                            {{ Str::limit($viewRevenue->fingerprint, 40) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.validation_status') }}</dt>
                        <dd class="mt-1">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $viewRevenue->validation_status === 'approved'
                                ? 'bg-green-100 text-green-800'
                                : ($viewRevenue->validation_status === 'rejected'
                                    ? 'bg-red-100 text-red-800'
                                    : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($viewRevenue->validation_status) }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.is_valid') }}</dt>
                        <dd class="mt-1">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $viewRevenue->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $viewRevenue->is_valid ? __('messages.yes') : __('messages.no') }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.user_agent') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 font-mono text-xs">
                            {{ Str::limit($viewRevenue->user_agent, 100) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">{{ __('messages.viewed_at') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $viewRevenue->viewed_at->format('M d, Y H:i:s') }}</dd>
                    </div>
                    @if ($viewRevenue->rejection_reason)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('messages.rejection_reason') }}</dt>
                            <dd class="mt-1 text-sm text-red-600">{{ $viewRevenue->rejection_reason }}</dd>
                        </div>
                    @endif
                    @if ($viewRevenue->bot_detection_data)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">{{ __('messages.bot_detection_data') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto">{{ json_encode($viewRevenue->bot_detection_data, JSON_PRETTY_PRINT) }}</pre>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Related Views -->
            @if ($relatedViews->count() > 0)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('messages.related_views') }}
                        ({{ __('messages.same_ip_fingerprint') }})</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.note') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.user') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.amount') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.status') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ __('messages.viewed_at') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($relatedViews as $relatedView)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $relatedView->note->title ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $relatedView->user->name ?? __('messages.guest') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rp {{ number_format($relatedView->amount, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $relatedView->validation_status === 'approved'
                                                ? 'bg-green-100 text-green-800'
                                                : ($relatedView->validation_status === 'rejected'
                                                    ? 'bg-red-100 text-red-800'
                                                    : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($relatedView->validation_status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $relatedView->viewed_at->format('M d, Y H:i') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
