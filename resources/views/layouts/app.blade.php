<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Meta Tags -->
        @stack('meta')

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        
        <!-- Expose Laravel translation helper to Alpine.js -->
        <script>
            // Load all translations from messages file
            window.__translations = @json(trans('messages'));
            
            // Make Laravel's __() function available to Alpine.js
            window.__ = function(key, replace = {}) {
                let translation = window.__translations[key] || key;
                
                // Replace placeholders like :name, :count, etc.
                if (replace && typeof replace === 'object') {
                    Object.keys(replace).forEach(replaceKey => {
                        translation = translation.replace(`:${replaceKey}`, replace[replaceKey]);
                    });
                }
                
                return translation;
            };
        </script>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow-sm border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1">
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot }}
                @endif
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                        <!-- About -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                                {{ __('messages.about') }}
                            </h3>
                            <p class="text-sm text-gray-600 leading-relaxed">
                                {{ __('messages.about_description') }}
                            </p>
                        </div>

                        <!-- Quick Links -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                                {{ __('messages.quick_links') }}
                            </h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('marketplace.index') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.marketplace') }}
                                    </a>
                                </li>
                                @auth
                                    <li>
                                        <a href="{{ route('notes.index') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                            {{ __('messages.my_notes') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                            {{ __('messages.dashboard') }}
                                        </a>
                                    </li>
                                @else
                                    <li>
                                        <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                            {{ __('messages.sign_in') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                            {{ __('messages.register') }}
                                        </a>
                                    </li>
                                @endauth
                            </ul>
                        </div>

                        <!-- Support -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                                {{ __('messages.support') }}
                            </h3>
                            <ul class="space-y-2">
                                <li>
                                    <a href="{{ route('docs.index') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.documentation') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('faq') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.faq') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('contact.index') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.contact_us') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="mailto:info@noteds.com" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        info@noteds.com
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Connect -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">
                                {{ __('messages.connect') }}
                            </h3>
                            <div class="flex space-x-4">
                                @php
                                    $socialMediaLinks = \App\Models\SocialMediaLink::active()->ordered()->get();
                                @endphp
                                @forelse($socialMediaLinks as $link)
                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-gray-600 hover:text-blue-600 transition-colors duration-200" title="{{ $link->name }}">
                                        @if($link->icon)
                                            {!! $link->icon !!}
                                        @else
                                            {!! $link->icon_html !!}
                                        @endif
                                    </a>
                                @empty
                                    <!-- Default fallback icons if no social media links -->
                                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                        </svg>
                                    </a>
                                    <a href="#" class="text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                    </a>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-8">
                        <p class="text-center text-sm text-gray-500">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Noteds') }}. {{ __('messages.all_rights_reserved') }}.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
        
        <!-- Flash Messages with SweetAlert2 -->
        @if(session('success'))
            @push('scripts')
            <script>
                (function() {
                    function showSuccess() {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ session('success') }}',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            // Fallback: wait for Swal to load
                            setTimeout(showSuccess, 100);
                        }
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', showSuccess);
                    } else {
                        showSuccess();
                    }
                })();
            </script>
            @endpush
        @endif

        @if(session('error'))
            @push('scripts')
            <script>
                (function() {
                    function showError() {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ session('error') }}',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            // Fallback: wait for Swal to load
                            setTimeout(showError, 100);
                        }
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', showError);
                    } else {
                        showError();
                    }
                })();
            </script>
            @endpush
        @endif

        @if(session('warning'))
            @push('scripts')
            <script>
                (function() {
                    function showWarning() {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'warning',
                                title: '{{ session('warning') }}',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            // Fallback: wait for Swal to load
                            setTimeout(showWarning, 100);
                        }
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', showWarning);
                    } else {
                        showWarning();
                    }
                })();
            </script>
            @endpush
        @endif

        @if(session('info'))
            @push('scripts')
            <script>
                (function() {
                    function showInfo() {
                        if (typeof Swal !== 'undefined' && Swal.fire) {
                            Swal.fire({
                                icon: 'info',
                                title: '{{ session('info') }}',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            // Fallback: wait for Swal to load
                            setTimeout(showInfo, 100);
                        }
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', showInfo);
                    } else {
                        showInfo();
                    }
                })();
            </script>
            @endpush
        @endif

        @stack('scripts')
        
        <!-- Featured Notes Popups -->
        @include('components.featured-popups')
    </body>
</html>
