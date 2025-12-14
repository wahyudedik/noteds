@extends('40-shared/layouts/app')

@section('title', __('System Health'))

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900">{{ __('System Health') }}</h1>
                <p class="text-lg text-gray-600 mt-2">{{ __('Monitor your application system status') }}</p>
            </div>

            <!-- Alerts Section -->
            @if (count($alerts ?? []) > 0)
                <div class="mb-8">
                    @foreach ($alerts ?? [] as $alert)
                        <div
                            class="mb-4 rounded-lg border-l-4 p-4 
                            @if ($alert['type'] === 'critical') bg-red-50 border-red-500 text-red-800
                            @else
                                bg-yellow-50 border-yellow-500 text-yellow-800 @endif">
                            <div class="flex items-start">
                                @if ($alert['type'] === 'critical')
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                                <div>
                                    <h3 class="font-semibold">{{ $alert['component'] }}</h3>
                                    <p class="text-sm mt-1">{{ $alert['message'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Health Checks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Database Health -->
                @if (isset($health['database']))
                    @php $db = $health['database']; @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('Database') }}</h2>
                            @if ($db['status'] === 'healthy')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Healthy') }}
                                </span>
                            @elseif ($db['status'] === 'warning')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Warning') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ __('Error') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $db['message'] }}</p>
                        @if (!empty($db['details']))
                            <div class="space-y-2">
                                @foreach ($db['details'] as $key => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="font-medium text-gray-900">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Queue Health -->
                @if (isset($health['queue']))
                    @php $queue = $health['queue']; @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('Queue') }}</h2>
                            @if ($queue['status'] === 'healthy')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Healthy') }}
                                </span>
                            @elseif ($queue['status'] === 'warning')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Warning') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ __('Error') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $queue['message'] }}</p>
                        @if (!empty($queue['details']))
                            <div class="space-y-2">
                                @foreach ($queue['details'] as $key => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="font-medium text-gray-900">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Cache Health -->
                @if (isset($health['cache']))
                    @php $cache = $health['cache']; @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('Cache') }}</h2>
                            @if ($cache['status'] === 'healthy')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Healthy') }}
                                </span>
                            @elseif ($cache['status'] === 'warning')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Warning') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ __('Error') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $cache['message'] }}</p>
                        @if (!empty($cache['details']))
                            <div class="space-y-2">
                                @foreach ($cache['details'] as $key => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="font-medium text-gray-900">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Scheduler Health -->
                @if (isset($health['scheduler']))
                    @php $scheduler = $health['scheduler']; @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('Scheduler') }}</h2>
                            @if ($scheduler['status'] === 'healthy')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Healthy') }}
                                </span>
                            @elseif ($scheduler['status'] === 'warning')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Warning') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ __('Error') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $scheduler['message'] }}</p>
                        @if (!empty($scheduler['details']))
                            <div class="space-y-2">
                                @foreach ($scheduler['details'] as $key => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="font-medium text-gray-900 break-words">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Broadcaster Health -->
                @if (isset($health['broadcaster']))
                    @php $broadcaster = $health['broadcaster']; @endphp
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900">{{ __('Broadcaster') }}</h2>
                            @if ($broadcaster['status'] === 'healthy')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ __('Healthy') }}
                                </span>
                            @elseif ($broadcaster['status'] === 'warning')
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Warning') }}
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    {{ __('Error') }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-600 mb-4">{{ $broadcaster['message'] }}</p>
                        @if (!empty($broadcaster['details']))
                            <div class="space-y-2">
                                @foreach ($broadcaster['details'] as $key => $value)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                        <span class="font-medium text-gray-900 break-words">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        @if (isset($broadcaster['details']['driver']) && $broadcaster['details']['driver'] === 'pusher')
                            <form action="{{ route('admin.system-health.test-broadcaster') }}" method="POST"
                                class="mt-4">
                                @csrf
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">
                                    {{ __('Test Broadcaster') }}
                                </button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Status Summary -->
            <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">{{ __('Summary') }}</h2>
                @php
                    $components = [
                        'database' => 'Database',
                        'queue' => 'Queue',
                        'cache' => 'Cache',
                        'scheduler' => 'Scheduler',
                        'broadcaster' => 'Broadcaster',
                    ];
                    $statusCounts = [
                        'healthy' => 0,
                        'warning' => 0,
                        'error' => 0,
                    ];
                    foreach ($components as $key => $label) {
                        if (isset($health[$key])) {
                            $statusCounts[$health[$key]['status']]++;
                        }
                    }
                @endphp
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center p-4 rounded-lg bg-green-50">
                        <p class="text-3xl font-bold text-green-600">{{ $statusCounts['healthy'] }}</p>
                        <p class="text-sm text-green-800">{{ __('Healthy') }}</p>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-yellow-50">
                        <p class="text-3xl font-bold text-yellow-600">{{ $statusCounts['warning'] }}</p>
                        <p class="text-sm text-yellow-800">{{ __('Warnings') }}</p>
                    </div>
                    <div class="text-center p-4 rounded-lg bg-red-50">
                        <p class="text-3xl font-bold text-red-600">{{ $statusCounts['error'] }}</p>
                        <p class="text-sm text-red-800">{{ __('Errors') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
