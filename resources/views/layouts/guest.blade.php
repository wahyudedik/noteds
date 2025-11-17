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
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100">
        <div class="min-h-screen flex flex-col lg:flex-row">
            <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-700 via-blue-900 to-slate-950 opacity-95"></div>
                <div class="absolute -top-24 -left-20 h-72 w-72 rounded-full bg-blue-400/30 blur-3xl"></div>
                <div class="absolute -bottom-32 right-24 h-80 w-80 rounded-full bg-indigo-500/30 blur-[200px]"></div>
                <div class="relative z-10 flex flex-col justify-between w-full px-16 py-12">
                    <div>
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-3 text-white/90">
                            <x-application-logo class="w-10 h-10 text-white" />
                            <span class="text-lg font-semibold tracking-wide">{{ config('app.name', 'Noteds') }}</span>
                        </a>
                    </div>
                    <div class="space-y-8">
                        <div class="space-y-4">
                            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-white/80 ring-1 ring-white/20">
                                {{ __('messages.welcome') }}
                            </span>
                            <h1 class="text-4xl xl:text-5xl font-semibold leading-tight text-white">
                                {{ __('messages.auth_hero_title') }}
                            </h1>
                            <p class="text-white/70 leading-relaxed max-w-lg">
                                {{ __('messages.auth_hero_subtitle') }}
                            </p>
                        </div>
                        <dl class="grid grid-cols-3 gap-4 max-w-xl text-white/80">
                            <div>
                                <dt class="text-sm font-semibold uppercase tracking-wide text-white/60">{{ __('messages.users') }}</dt>
                                <dd class="mt-1 text-2xl font-semibold">10K+</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold uppercase tracking-wide text-white/60">{{ __('messages.notes') }}</dt>
                                <dd class="mt-1 text-2xl font-semibold">50K+</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-semibold uppercase tracking-wide text-white/60">{{ __('messages.rating') }}</dt>
                                <dd class="mt-1 text-2xl font-semibold">4.9/5</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="text-white/60 text-sm">
                        © {{ date('Y') }} {{ config('app.name', 'Noteds') }}. {{ __('messages.all_rights_reserved') }}
                    </div>
                </div>
            </div>
            <div class="flex flex-1 items-center justify-center py-12 px-6 sm:px-8">
                <div class="w-full max-w-md">
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-slate-100">
                            <x-application-logo class="w-10 h-10 text-blue-600" />
                            <span class="text-lg font-semibold tracking-wide">{{ config('app.name', 'Noteds') }}</span>
                        </a>
                    </div>
                    <div class="rounded-3xl border border-slate-800/60 bg-white text-slate-900 shadow-2xl shadow-blue-500/10 p-8 sm:p-10">
                        {{ $slot }}
                    </div>
                    <div class="mt-10 text-center text-sm text-slate-400 lg:hidden">
                        © {{ date('Y') }} {{ config('app.name', 'Noteds') }}. {{ __('messages.all_rights_reserved') }}
                    </div>
                </div>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
