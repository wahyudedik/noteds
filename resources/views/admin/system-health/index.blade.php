@extends('layouts.app')

@section('title', __('messages.system_health_title'))

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center mb-4">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('messages.back_to_dashboard') }}
            </a>
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.system_health_title') }}</h2>
            <p class="text-gray-600 mt-1">{{ __('messages.system_health_description') }}</p>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-yellow-800">{{ session('warning') }}</p>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg mb-6">
                <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
            </div>
        @endif

        @if (!empty($alerts))
            <div class="mb-6 space-y-3">
                @foreach ($alerts as $alert)
                    @if ($alert['type'] === 'critical')
                        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-red-800">
                                        Critical Alert: {{ $alert['component'] }}
                                    </p>
                                    <p class="text-sm text-red-700 mt-1">
                                        {{ $alert['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-semibold text-yellow-800">
                                        Warning: {{ $alert['component'] }}
                                    </p>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        {{ $alert['message'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Database -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.database') }}</h3>
                    @if($health['database']['status'] === 'healthy')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('messages.healthy') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ __('messages.error') }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ $health['database']['message'] }}</p>
                @if(!empty($health['database']['details']))
                    <div class="text-xs text-gray-500 space-y-1">
                        @foreach($health['database']['details'] as $key => $value)
                            <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Queue -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.queue') }}</h3>
                    @if($health['queue']['status'] === 'healthy')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('messages.healthy') }}
                        </span>
                    @elseif($health['queue']['status'] === 'warning')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ __('messages.warning') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ __('messages.error') }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ $health['queue']['message'] }}</p>
                @if(!empty($health['queue']['details']))
                    <div class="text-xs text-gray-500 space-y-1 mb-3">
                        @foreach($health['queue']['details'] as $key => $value)
                            @if($key === 'pending_jobs' && $value > 0)
                                <div class="flex items-center justify-between">
                                    <span><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</span>
                                    @if($value > 100)
                                        <span class="text-yellow-600 font-semibold">High Queue!</span>
                                    @endif
                                </div>
                            @elseif($key === 'failed_jobs' && $value > 0)
                                <div class="flex items-center justify-between">
                                    <span><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</span>
                                    <a href="{{ route('admin.system-health.index') }}?tab=failed-jobs" class="text-red-600 hover:text-red-800 underline">View Details</a>
                                </div>
                            @else
                                <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                            @endif
                        @endforeach
                    </div>
                @endif
                <div class="mt-3 text-xs text-gray-500">
                    <strong>Note:</strong> Run <code class="bg-gray-100 px-1 rounded">php artisan queue:work</code> to process jobs
                </div>
                @if(isset($health['queue']['details']['failed_jobs']) && $health['queue']['details']['failed_jobs'] > 0)
                    <div class="mt-2 text-xs text-red-600">
                        <strong>Action:</strong> Review failed jobs with <code class="bg-red-100 px-1 rounded">php artisan queue:failed</code>
                    </div>
                @endif
            </div>

            <!-- Cache -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Cache</h3>
                    @if($health['cache']['status'] === 'healthy')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Healthy
                        </span>
                    @elseif($health['cache']['status'] === 'warning')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Warning
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Error
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ $health['cache']['message'] }}</p>
                @if(!empty($health['cache']['details']))
                    <div class="text-xs text-gray-500 space-y-1">
                        @foreach($health['cache']['details'] as $key => $value)
                            <div><strong>{{ ucfirst($key) }}:</strong> {{ $value }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Scheduler -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Scheduler</h3>
                    @if($health['scheduler']['status'] === 'healthy')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Healthy
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Warning
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 mb-3">{{ $health['scheduler']['message'] }}</p>
                @if(!empty($health['scheduler']['details']))
                    <div class="text-xs text-gray-500 space-y-1">
                        @foreach($health['scheduler']['details'] as $key => $value)
                            <div><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</div>
                        @endforeach
                    </div>
                @endif
                <div class="mt-3 text-xs text-gray-500">
                    <strong>Cron setup:</strong> <code class="bg-gray-100 px-1 rounded">* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1</code>
                </div>
            </div>
        </div>

        <!-- Broadcaster -->
        <div class="mt-6 bg-white shadow-sm sm:rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Broadcaster (Echo/WebSocket)</h3>
                    <p class="text-sm text-gray-600 mt-1">Real-time notifications require proper broadcaster configuration</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($health['broadcaster']['status'] === 'healthy')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Configured
                        </span>
                    @elseif($health['broadcaster']['status'] === 'warning')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Warning
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Error
                        </span>
                    @endif
                    <form method="POST" action="{{ route('admin.system-health.test-broadcaster') }}" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-md transition">
                            Test Connection
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-sm text-gray-600 mb-3">{{ $health['broadcaster']['message'] }}</p>
            @if(!empty($health['broadcaster']['details']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    @foreach($health['broadcaster']['details'] as $key => $value)
                        <div class="text-xs">
                            <strong class="text-gray-700">{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <span class="text-gray-600 ml-1">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-4">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">Setup Instructions</h4>
                <div class="text-xs text-blue-800 space-y-2">
                    <p><strong>1. Pusher:</strong> Get credentials from <a href="https://pusher.com" target="_blank" class="underline">pusher.com</a>, add to <code class="bg-blue-100 px-1 rounded">.env</code>:</p>
                    <pre class="bg-blue-100 p-2 rounded text-xs overflow-x-auto">BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=ap1</pre>

                    <p><strong>2. Ably:</strong> Get credentials from <a href="https://ably.com" target="_blank" class="underline">ably.com</a>, add to <code class="bg-blue-100 px-1 rounded">.env</code>:</p>
                    <pre class="bg-blue-100 p-2 rounded text-xs overflow-x-auto">BROADCAST_DRIVER=ably
ABLY_KEY=your_ably_key</pre>

                    <p><strong>3. Frontend:</strong> Ensure Echo is initialized in <code class="bg-blue-100 px-1 rounded">resources/js/app.js</code> or your layout:</p>
                    <pre class="bg-blue-100 p-2 rounded text-xs overflow-x-auto">import Echo from 'laravel-echo';
window.Pusher = require('pusher-js');
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: process.env.MIX_PUSHER_APP_KEY,
    cluster: process.env.MIX_PUSHER_APP_CLUSTER,
    forceTLS: true
});</pre>

                    <p><strong>4. Install dependencies:</strong> <code class="bg-blue-100 px-1 rounded">npm install laravel-echo pusher-js</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

