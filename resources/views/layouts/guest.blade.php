<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Fix Vite URLs in development - Client-side fix -->
        @if(config('app.env') === 'local')
        <script>
            (function() {
                // Fix Vite URLs from HTTPS to HTTP
                const host = window.location.hostname;
                const port = ':5173';
                
                // Fix all Vite asset URLs
                document.addEventListener('DOMContentLoaded', function() {
                    // Fix link tags
                    document.querySelectorAll('link[href*=":5173"]').forEach(function(link) {
                        link.href = link.href.replace('https://' + host + port, 'http://' + host + port);
                    });
                    
                    // Fix script tags
                    document.querySelectorAll('script[src*=":5173"]').forEach(function(script) {
                        script.src = script.src.replace('https://' + host + port, 'http://' + host + port);
                    });
                });
                
                // Also fix dynamically added scripts
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) {
                                if (node.tagName === 'LINK' && node.href && node.href.includes(':5173')) {
                                    node.href = node.href.replace('https://' + host + port, 'http://' + host + port);
                                }
                                if (node.tagName === 'SCRIPT' && node.src && node.src.includes(':5173')) {
                                    node.src = node.src.replace('https://' + host + port, 'http://' + host + port);
                                }
                            }
                        });
                    });
                });
                
                observer.observe(document.head, { childList: true, subtree: true });
            })();
        </script>
        @endif
    </head>
    <body class="font-sans antialiased bg-white text-slate-900">
        <div class="min-h-screen flex flex-col">
            <!-- Navigation -->
            <nav class="bg-white border-b border-slate-200 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <a href="{{ url('/') }}" class="flex items-center gap-2 text-xl font-bold text-slate-900 hover:text-blue-600 transition-colors">
                            <x-application-logo class="w-8 h-8 text-blue-600" />
                            <span>{{ config('app.name', 'Noteds') }}</span>
                        </a>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-blue-600 transition-colors">
                                {{ __('messages.login') }}
                            </a>
                            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-all shadow-sm hover:shadow-md">
                                {{ __('messages.register') }}
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <div class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
                <div class="w-full max-w-md">
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-xl shadow-blue-100/50 p-8 sm:p-10">
                        @yield('content')
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p class="text-center text-sm text-slate-500">
                        © {{ date('Y') }} {{ config('app.name', 'Noteds') }}. {{ __('messages.all_rights_reserved') }}
                    </p>
                </div>
            </footer>
        </div>
        @stack('scripts')
    </body>
</html>
