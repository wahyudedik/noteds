<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root">
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
        
        <!-- PWA Manifest -->
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#2563eb">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="Noteds">
        <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>[x-cloak]{display:none !important;}</style>
        
        <!-- Dark Mode Styles -->
        <style>
            :root {
                --bg-primary: #ffffff;
                --bg-secondary: #f9fafb;
                --text-primary: #111827;
                --text-secondary: #6b7280;
                --border-color: #e5e7eb;
            }
            
            .dark {
                --bg-primary: #111827;
                --bg-secondary: #1f2937;
                --text-primary: #f9fafb;
                --text-secondary: #d1d5db;
                --border-color: #374151;
            }
            
            body {
                background-color: var(--bg-primary);
                color: var(--text-primary);
                transition: background-color 0.3s ease, color 0.3s ease;
            }
            
            /* Dark mode transitions for common elements */
            .bg-white {
                transition: background-color 0.3s ease;
            }
            
            .dark .bg-white {
                background-color: var(--bg-secondary) !important;
            }
            
            .text-gray-700, .text-gray-900 {
                transition: color 0.3s ease;
            }
            
            .border-gray-200 {
                transition: border-color 0.3s ease;
            }
        </style>
        
        <!-- Dark Mode Script -->
        <script>
            // Initialize dark mode from localStorage
            (function() {
                const darkMode = localStorage.getItem('darkMode') === 'true';
                const html = document.documentElement;
                
                if (darkMode) {
                    html.classList.add('dark');
                    updateDarkModeIcons(true);
                } else {
                    html.classList.remove('dark');
                    updateDarkModeIcons(false);
                }
            })();
            
            function toggleDarkMode() {
                const html = document.documentElement;
                const isDark = html.classList.toggle('dark');
                localStorage.setItem('darkMode', isDark);
                updateDarkModeIcons(isDark);
            }
            
            function updateDarkModeIcons(isDark) {
                const darkIcon = document.getElementById('dark-mode-icon');
                const lightIcon = document.getElementById('light-mode-icon');
                const darkText = document.getElementById('dark-mode-text');
                
                if (darkIcon && lightIcon && darkText) {
                    if (isDark) {
                        darkIcon.classList.remove('hidden');
                        lightIcon.classList.add('hidden');
                        darkText.textContent = 'Light Mode';
                    } else {
                        darkIcon.classList.add('hidden');
                        lightIcon.classList.remove('hidden');
                        darkText.textContent = 'Dark Mode';
                    }
                }
            }
        </script>
        
        <!-- Content Protection Styles -->
        @php
            $disableTextSelection = \App\Models\Setting::getSetting('protection_disable_text_selection', 'content_protection', false);
            $disableDragDrop = \App\Models\Setting::getSetting('protection_disable_drag_drop', 'content_protection', false);
        @endphp
        @if($disableTextSelection || $disableDragDrop)
        <style>
            @if($disableTextSelection)
            /* Disable text selection */
            body {
                -webkit-user-select: none !important;
                -moz-user-select: none !important;
                -ms-user-select: none !important;
                user-select: none !important;
                -webkit-touch-callout: none !important;
            }
            
            /* Allow text selection in input fields, textareas, and rich text editors */
            input, textarea, [contenteditable="true"], 
            .ql-editor, .ql-container, .ql-toolbar,
            .ql-editor *, .ql-container *,
            #content-editor, #content-editor *,
            [class*="ql-"], [class*="ql-"] * {
                -webkit-user-select: text !important;
                -moz-user-select: text !important;
                -ms-user-select: text !important;
                user-select: text !important;
            }
            
            /* Exclude form pages from text selection protection */
            body.create-note-page, body.edit-note-page {
                -webkit-user-select: text !important;
                -moz-user-select: text !important;
                -ms-user-select: text !important;
                user-select: text !important;
            }
            @endif
            
            @if($disableDragDrop)
            /* Disable drag and drop */
            img, a {
                -webkit-user-drag: none !important;
                -khtml-user-drag: none !important;
                -moz-user-drag: none !important;
                -o-user-drag: none !important;
                user-drag: none !important;
                pointer-events: none !important;
            }
            
            /* Re-enable pointer events for interactive elements */
            button, a[href], input, textarea, select, [onclick], [role="button"] {
                pointer-events: auto !important;
            }
            @endif
            
        </style>
        @endif
        
        @stack('styles')
        
        <!-- Iconify Icons -->
        <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js" defer></script>
        
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
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 overflow-x-hidden transition-colors duration-200 {{ request()->routeIs('notes.create') ? 'create-note-page' : '' }} {{ request()->routeIs('notes.edit') ? 'edit-note-page' : '' }}">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 transition-colors duration-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden">
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
                                    <a href="{{ route('cms.show', ['cmsPage' => 'privacy-policy']) }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.privacy_policy') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('cms.show', ['cmsPage' => 'terms-and-conditions']) }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                        {{ __('messages.terms_and_conditions') }}
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
        
        <!-- Global SweetAlert2 Toast Helper -->
        @push('scripts')
        <script>
            (function initNotedsToast() {
                if (typeof Swal === 'undefined') {
                    return setTimeout(initNotedsToast, 100);
                }

                if (window.NotedsToast) {
                    return;
                }

                const toastMixin = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3200,
                    timerProgressBar: true,
                });

                window.NotedsToast = function(icon, title, options = {}) {
                    if (!title) {
                        return;
                    }

                    const navigation = (performance.getEntriesByType && performance.getEntriesByType('navigation')[0]) || null;
                    const isBackForward = navigation
                        ? navigation.type === 'back_forward'
                        : (performance.navigation && performance.navigation.type === 2);

                    if (isBackForward && options.skipBackForward !== false) {
                        return;
                    }

                    const { skipBackForward, ...restOptions } = options || {};

                    if (isBackForward && skipBackForward !== false) {
                        return;
                    }

                    const fireOptions = Object.assign({
                        icon: icon || 'info',
                        title,
                    }, restOptions);

                    toastMixin.fire(fireOptions);
                };
            })();
        </script>
        @endpush

        <!-- Flash Messages with SweetAlert2 Toast -->
        @if (session('success'))
            @push('scripts')
            <script>
                (function showFlashSuccess() {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('success', @json(session('success')), { skipBackForward: true });
                    } else {
                        setTimeout(showFlashSuccess, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        @if (session('error'))
            @push('scripts')
            <script>
                (function showFlashError() {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('error', @json(session('error')), { skipBackForward: true });
                    } else {
                        setTimeout(showFlashError, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        @if (session('warning'))
            @push('scripts')
            <script>
                (function showFlashWarning() {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('warning', @json(session('warning')), { skipBackForward: true });
                    } else {
                        setTimeout(showFlashWarning, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        @if (session('info'))
            @push('scripts')
            <script>
                (function showFlashInfo() {
                    if (typeof window.NotedsToast === 'function') {
                        window.NotedsToast('info', @json(session('info')), { skipBackForward: true });
                    } else {
                        setTimeout(showFlashInfo, 100);
                    }
                })();
            </script>
            @endpush
        @endif

        @stack('scripts')
        
        <!-- Content Protection Scripts -->
        @php
            $protectionSettings = [
                'disable_right_click' => \App\Models\Setting::getSetting('protection_disable_right_click', 'content_protection', false),
                'disable_keyboard_shortcuts' => \App\Models\Setting::getSetting('protection_disable_keyboard_shortcuts', 'content_protection', false),
                'disable_copy_paste' => \App\Models\Setting::getSetting('protection_disable_copy_paste', 'content_protection', false),
                'disable_drag_drop' => \App\Models\Setting::getSetting('protection_disable_drag_drop', 'content_protection', false),
                'disable_print' => \App\Models\Setting::getSetting('protection_disable_print', 'content_protection', false),
                'disable_view_source' => \App\Models\Setting::getSetting('protection_disable_view_source', 'content_protection', false),
                'detect_devtools' => \App\Models\Setting::getSetting('protection_detect_devtools', 'content_protection', false),
                'disable_screenshot' => \App\Models\Setting::getSetting('protection_disable_screenshot', 'content_protection', false),
                'disable_image_saving' => \App\Models\Setting::getSetting('protection_disable_image_saving', 'content_protection', false),
                'disable_console' => \App\Models\Setting::getSetting('protection_disable_console', 'content_protection', false),
                'monitor_clipboard' => \App\Models\Setting::getSetting('protection_monitor_clipboard', 'content_protection', false),
                'disable_print_screen' => \App\Models\Setting::getSetting('protection_disable_print_screen', 'content_protection', false),
                'disable_snipping_tool' => \App\Models\Setting::getSetting('protection_disable_snipping_tool', 'content_protection', false),
                'detect_window_blur' => \App\Models\Setting::getSetting('protection_detect_window_blur', 'content_protection', false),
                'detect_visibility_change' => \App\Models\Setting::getSetting('protection_detect_visibility_change', 'content_protection', false),
                'clear_clipboard_periodic' => \App\Models\Setting::getSetting('protection_clear_clipboard_periodic', 'content_protection', false),
                'blur_overlay' => \App\Models\Setting::getSetting('protection_blur_overlay', 'content_protection', false),
                'disable_f12' => \App\Models\Setting::getSetting('protection_disable_f12', 'content_protection', false),
                'disable_devtools_shortcuts' => \App\Models\Setting::getSetting('protection_disable_devtools_shortcuts', 'content_protection', false),
                'detect_ai_bots' => \App\Models\Setting::getSetting('protection_detect_ai_bots', 'content_protection', false),
                'detect_headless' => \App\Models\Setting::getSetting('protection_detect_headless', 'content_protection', false),
                'detect_mouse_movement' => \App\Models\Setting::getSetting('protection_detect_mouse_movement', 'content_protection', false),
                'detect_click_pattern' => \App\Models\Setting::getSetting('protection_detect_click_pattern', 'content_protection', false),
                'detect_screen_recording' => \App\Models\Setting::getSetting('protection_detect_screen_recording', 'content_protection', false),
            ];
        @endphp
        <script>
            (function() {
                'use strict';
                
                // Protection settings from server
                const protectionSettings = @json($protectionSettings);
                
                @if($protectionSettings['blur_overlay'])
                // Blur overlay untuk mencegah screenshot
                const blurOverlay = document.createElement('div');
                blurOverlay.id = 'screenshot-protection-overlay';
                blurOverlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.9);z-index:9999999;display:none;backdrop-filter:blur(10px);';
                document.body.appendChild(blurOverlay);
                @endif
                
                @if($protectionSettings['disable_right_click'])
                // Disable right-click context menu
                document.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }, true);
                @endif
                
                // Helper function untuk show blur overlay
                function showBlurOverlay() {
                    if (protectionSettings.blur_overlay && typeof blurOverlay !== 'undefined') {
                        blurOverlay.style.display = 'block';
                    }
                }
                
                function hideBlurOverlay(delay = 0) {
                    if (protectionSettings.blur_overlay && typeof blurOverlay !== 'undefined') {
                        setTimeout(function() {
                            blurOverlay.style.display = 'none';
                        }, delay);
                    }
                }
                
                // Deteksi Print Screen dan keyboard shortcuts
                let printScreenPressed = false;
                
                document.addEventListener('keydown', function(e) {
                    @if($protectionSettings['disable_print_screen'])
                    // Disable Print Screen
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        e.preventDefault();
                        e.stopPropagation();
                        printScreenPressed = true;
                        
                        // Show blur overlay
                        if (typeof blurOverlay !== 'undefined') {
                            blurOverlay.style.display = 'block';
                        }
                        
                        // Clear clipboard immediately
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText('').catch(function() {});
                        }
                        
                        // Try to clear clipboard using fallback method
                        try {
                            const textArea = document.createElement('textarea');
                            textArea.value = '';
                            document.body.appendChild(textArea);
                            textArea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textArea);
                        } catch(err) {}
                        
                        // Hide overlay after delay
                        setTimeout(function() {
                            if (typeof blurOverlay !== 'undefined') {
                                blurOverlay.style.display = 'none';
                            }
                            printScreenPressed = false;
                        }, 2000);
                        
                        return false;
                    }
                    
                    // Deteksi kombinasi Windows + Print Screen
                    if (e.key === 'PrintScreen' && (e.metaKey || e.ctrlKey)) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof blurOverlay !== 'undefined') {
                            blurOverlay.style.display = 'block';
                            setTimeout(function() {
                                blurOverlay.style.display = 'none';
                            }, 2000);
                        }
                        return false;
                    }
                    
                    // Disable F12 (DevTools)
                    if (e.key === 'F12' || (e.keyCode === 123)) {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+Shift+I (DevTools)
                    if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+Shift+J (Console)
                    if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+Shift+C (Inspect Element)
                    if (e.ctrlKey && e.shiftKey && e.key === 'C') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+S (Save)
                    if (e.ctrlKey && e.key === 's') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+P (Print)
                    if (e.ctrlKey && e.key === 'p') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+A (Select All) - except in input fields and rich text editors
                    if (e.ctrlKey && e.key === 'a') {
                        const target = e.target;
                        const isElement = target && target.nodeType === 1;
                        const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                         (target.closest && target.closest('.ql-editor')) ||
                                         (target.closest && target.closest('[contenteditable="true"]')) ||
                                         (target.closest && target.closest('#content-editor')) ||
                                         (target.closest && target.closest('[class*="ql-"]'))) ||
                                         document.body.classList.contains('create-note-page') ||
                                         document.body.classList.contains('edit-note-page');
                        
                        if (!isEditable) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    }
                    
                    // Disable Ctrl+C (Copy) - except in input fields and rich text editors
                    if (e.ctrlKey && e.key === 'c') {
                        const target = e.target;
                        const isElement = target && target.nodeType === 1;
                        const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                         (target.closest && target.closest('.ql-editor')) ||
                                         (target.closest && target.closest('[contenteditable="true"]')) ||
                                         (target.closest && target.closest('#content-editor')) ||
                                         (target.closest && target.closest('[class*="ql-"]'))) ||
                                         document.body.classList.contains('create-note-page') ||
                                         document.body.classList.contains('edit-note-page');
                        
                        if (!isEditable) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    }
                    
                    // Disable Ctrl+V (Paste) - except in input fields and rich text editors
                    if (e.ctrlKey && e.key === 'v') {
                        const target = e.target;
                        const isElement = target && target.nodeType === 1;
                        const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                         (target.closest && target.closest('.ql-editor')) ||
                                         (target.closest && target.closest('[contenteditable="true"]')) ||
                                         (target.closest && target.closest('#content-editor')) ||
                                         (target.closest && target.closest('[class*="ql-"]'))) ||
                                         document.body.classList.contains('create-note-page') ||
                                         document.body.classList.contains('edit-note-page');
                        
                        if (!isEditable) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    }
                    
                    // Disable Ctrl+X (Cut) - except in input fields and rich text editors
                    if (e.ctrlKey && e.key === 'x') {
                        const target = e.target;
                        const isElement = target && target.nodeType === 1;
                        const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                         (target.closest && target.closest('.ql-editor')) ||
                                         (target.closest && target.closest('[contenteditable="true"]')) ||
                                         (target.closest && target.closest('#content-editor')) ||
                                         (target.closest && target.closest('[class*="ql-"]'))) ||
                                         document.body.classList.contains('create-note-page') ||
                                         document.body.classList.contains('edit-note-page');
                        
                        if (!isEditable) {
                            e.preventDefault();
                            e.stopPropagation();
                            return false;
                        }
                    }
                    
                    // Disable Ctrl+U (View Source)
                    if (e.ctrlKey && e.key === 'u') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    
                    // Disable Ctrl+Shift+P (Command Palette)
                    if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    @endif
                }, true);
                
                // Disable copy (allow in input, textarea, and rich text editors)
                document.addEventListener('copy', function(e) {
                    const target = e.target;
                    const isElement = target && target.nodeType === 1;
                    const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                     (target.closest && target.closest('.ql-editor')) ||
                                     (target.closest && target.closest('[contenteditable="true"]')) ||
                                     (target.closest && target.closest('#content-editor')) ||
                                     (target.closest && target.closest('[class*="ql-"]'))) ||
                                     document.body.classList.contains('create-note-page') ||
                                     document.body.classList.contains('edit-note-page');
                    
                    if (!isEditable) {
                        e.preventDefault();
                        if (e.clipboardData) {
                            e.clipboardData.setData('text/plain', '');
                        }
                        return false;
                    }
                }, true);
                
                // Disable cut (allow in input, textarea, and rich text editors)
                document.addEventListener('cut', function(e) {
                    const target = e.target;
                    const isElement = target && target.nodeType === 1;
                    const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                     (target.closest && target.closest('.ql-editor')) ||
                                     (target.closest && target.closest('[contenteditable="true"]')) ||
                                     (target.closest && target.closest('#content-editor')) ||
                                     (target.closest && target.closest('[class*="ql-"]'))) ||
                                     document.body.classList.contains('create-note-page') ||
                                     document.body.classList.contains('edit-note-page');
                    
                    if (!isEditable) {
                        e.preventDefault();
                        return false;
                    }
                }, true);
                
                // Disable paste (allow in input, textarea, and rich text editors)
                document.addEventListener('paste', function(e) {
                    const target = e.target;
                    const isElement = target && target.nodeType === 1;
                    const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                     (target.closest && target.closest('.ql-editor')) ||
                                     (target.closest && target.closest('[contenteditable="true"]')) ||
                                     (target.closest && target.closest('#content-editor')) ||
                                     (target.closest && target.closest('[class*="ql-"]'))) ||
                                     document.body.classList.contains('create-note-page') ||
                                     document.body.classList.contains('edit-note-page');
                    
                    if (!isEditable) {
                        e.preventDefault();
                        return false;
                    }
                }, true);
                
                // Disable drag start
                document.addEventListener('dragstart', function(e) {
                    e.preventDefault();
                    return false;
                }, true);
                
                // Disable select start (allow in input, textarea, and rich text editors)
                document.addEventListener('selectstart', function(e) {
                    const target = e.target;
                    const isElement = target && target.nodeType === 1;
                    const isEditable = isElement && (['INPUT', 'TEXTAREA'].includes(target.tagName) ||
                                     (target.closest && target.closest('.ql-editor')) ||
                                     (target.closest && target.closest('[contenteditable="true"]')) ||
                                     (target.closest && target.closest('#content-editor')) ||
                                     (target.closest && target.closest('[class*="ql-"]'))) ||
                                     document.body.classList.contains('create-note-page') ||
                                     document.body.classList.contains('edit-note-page');
                    
                    if (!isEditable) {
                        e.preventDefault();
                        return false;
                    }
                }, true);
                
                // Detect screen recording attempts
                let canvasFingerprint = '';
                try {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    ctx.textBaseline = 'top';
                    ctx.font = '14px Arial';
                    ctx.textBaseline = 'alphabetic';
                    ctx.fillStyle = '#f60';
                    ctx.fillRect(125, 1, 62, 20);
                    ctx.fillStyle = '#069';
                    ctx.fillText('Screen recording detection', 2, 15);
                    canvasFingerprint = canvas.toDataURL();
                } catch(e) {}
                
                // Monitor for DevTools
                let devtools = {open: false, orientation: null};
                const threshold = 160;
                
                // Monitor for DevTools (less aggressive - just warn)
                setInterval(function() {
                    if (window.outerHeight - window.innerHeight > threshold || 
                        window.outerWidth - window.innerWidth > threshold) {
                        if (!devtools.open) {
                            devtools.open = true;
                            // Show warning but don't break the page
                            console.warn('Developer Tools Detected. Content protection is active.');
                        }
                    } else {
                        devtools.open = false;
                    }
                }, 1000);
                
                // Disable print
                window.addEventListener('beforeprint', function(e) {
                    e.preventDefault();
                    return false;
                });
                
                // Disable screenshot on mobile (iOS/Android)
                if (navigator.userAgent.match(/iPhone|iPad|iPod|Android/i)) {
                    document.addEventListener('touchstart', function(e) {
                        if (e.touches.length > 1) {
                            e.preventDefault();
                            return false;
                        }
                    }, {passive: false});
                }
                
                // Console warning
                const originalLog = console.log;
                console.log = function() {
                    // Silently block console logs
                };
                
                // Disable image saving
                document.addEventListener('DOMContentLoaded', function() {
                    const images = document.querySelectorAll('img');
                    images.forEach(function(img) {
                        img.addEventListener('dragstart', function(e) {
                            e.preventDefault();
                            return false;
                        });
                        img.setAttribute('draggable', 'false');
                    });
                });
                
                // Monitor clipboard changes (detect screenshot)
                let lastClipboardCheck = '';
                setInterval(function() {
                    if (navigator.clipboard && navigator.clipboard.readText) {
                        navigator.clipboard.readText().then(function(text) {
                            // Jika clipboard berubah dan bukan dari input field, clear
                            if (text && text !== lastClipboardCheck && !printScreenPressed) {
                                navigator.clipboard.writeText('').catch(function() {});
                                if (typeof blurOverlay !== 'undefined') {
                                    blurOverlay.style.display = 'block';
                                    setTimeout(function() {
                                        blurOverlay.style.display = 'none';
                                    }, 1500);
                                }
                            }
                            lastClipboardCheck = text;
                        }).catch(function() {});
                    }
                }, 500);
                
                // Clear clipboard periodically
                setInterval(function() {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText('').catch(function() {});
                    }
                }, 800);
                
                // Deteksi window blur (mungkin screenshot)
                let windowBlurred = false;
                window.addEventListener('blur', function() {
                    windowBlurred = true;
                    if (typeof blurOverlay !== 'undefined') {
                        blurOverlay.style.display = 'block';
                    }
                });
                
                window.addEventListener('focus', function() {
                    if (windowBlurred) {
                        setTimeout(function() {
                            if (typeof blurOverlay !== 'undefined') {
                                blurOverlay.style.display = 'none';
                            }
                            windowBlurred = false;
                            // Clear clipboard saat window focus kembali
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText('').catch(function() {});
                            }
                        }, 1000);
                    }
                });
                
                // Deteksi Print Screen dengan keyup (additional method)
                document.addEventListener('keyup', function(e) {
                    if (e.key === 'PrintScreen' || e.keyCode === 44) {
                        printScreenPressed = true;
                        if (typeof blurOverlay !== 'undefined') {
                            blurOverlay.style.display = 'block';
                        }
                        
                        // Clear clipboard multiple times
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText('').catch(function() {});
                            setTimeout(function() {
                                navigator.clipboard.writeText('').catch(function() {});
                            }, 100);
                            setTimeout(function() {
                                navigator.clipboard.writeText('').catch(function() {});
                            }, 300);
                        }
                        
                        // Try fallback method
                        try {
                            const textArea = document.createElement('textarea');
                            textArea.value = '';
                            document.body.appendChild(textArea);
                            textArea.select();
                            document.execCommand('copy');
                            document.body.removeChild(textArea);
                        } catch(err) {}
                        
                        setTimeout(function() {
                            if (typeof blurOverlay !== 'undefined') {
                                blurOverlay.style.display = 'none';
                            }
                            printScreenPressed = false;
                        }, 2000);
                    }
                });
                
                // Deteksi Snipping Tool (Windows + Shift + S)
                document.addEventListener('keydown', function(e) {
                    if (e.key === 's' && e.shiftKey && (e.metaKey || e.ctrlKey)) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (typeof blurOverlay !== 'undefined') {
                            blurOverlay.style.display = 'block';
                            setTimeout(function() {
                                blurOverlay.style.display = 'none';
                            }, 3000);
                        }
                        return false;
                    }
                }, true);
                
                // Deteksi perubahan visibility (tab switch untuk screenshot)
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        if (typeof blurOverlay !== 'undefined') {
                            blurOverlay.style.display = 'block';
                        }
                        // Clear clipboard saat tab hidden
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText('').catch(function() {});
                        }
                    } else {
                        setTimeout(function() {
                            if (typeof blurOverlay !== 'undefined') {
                                blurOverlay.style.display = 'none';
                            }
                            // Clear clipboard saat tab visible kembali
                            if (navigator.clipboard && navigator.clipboard.writeText) {
                                navigator.clipboard.writeText('').catch(function() {});
                            }
                        }, 500);
                    }
                });
                
                // ========== AI DETECTION & PROTECTION ==========
                // Only run if AI detection is enabled in admin settings
                if (protectionSettings.detect_ai_bots) {
                
                // Deteksi AI Bots dari User-Agent
                const userAgent = navigator.userAgent.toLowerCase();
                const aiBotPatterns = [
                    'chatgpt', 'openai', 'gpt-', 'claude', 'anthropic', 'bard', 'gemini',
                    'perplexity', 'copilot', 'bingbot', 'googlebot', 'crawler', 'spider',
                    'scraper', 'bot', 'automated', 'ai-agent', 'llm', 'language-model',
                    'crawler', 'scraper', 'headless', 'phantom', 'selenium', 'puppeteer',
                    'playwright', 'webdriver', 'automation', 'testcafe', 'cypress'
                ];
                
                let aiDetected = false;
                aiBotPatterns.forEach(function(pattern) {
                    if (userAgent.includes(pattern)) {
                        aiDetected = true;
                        console.warn('AI Bot detected:', pattern);
                    }
                });
                
                // Deteksi headless browser
                const headlessIndicators = {
                    webdriver: navigator.webdriver === true,
                    chrome: !window.chrome,
                    permissions: navigator.permissions === undefined,
                    plugins: navigator.plugins.length === 0,
                    languages: navigator.languages.length === 0,
                    platform: navigator.platform === '',
                    vendor: navigator.vendor === ''
                };
                
                let headlessScore = 0;
                Object.keys(headlessIndicators).forEach(function(key) {
                    if (headlessIndicators[key]) headlessScore++;
                });
                
                if (headlessScore >= 3) {
                    aiDetected = true;
                    console.warn('Headless browser detected');
                }
                
                // Deteksi automated mouse movement (AI biasanya tidak punya human-like movement)
                let mouseMovements = [];
                let lastMouseTime = Date.now();
                let suspiciousMovement = false;
                
                document.addEventListener('mousemove', function(e) {
                    const now = Date.now();
                    const timeDiff = now - lastMouseTime;
                    const distance = Math.sqrt(
                        Math.pow(e.movementX || 0, 2) + Math.pow(e.movementY || 0, 2)
                    );
                    
                    mouseMovements.push({
                        time: timeDiff,
                        distance: distance,
                        speed: distance / (timeDiff || 1)
                    });
                    
                    // Keep only last 50 movements
                    if (mouseMovements.length > 50) {
                        mouseMovements.shift();
                    }
                    
                    // Deteksi movement yang terlalu konsisten (AI pattern)
                    if (mouseMovements.length >= 20) {
                        const speeds = mouseMovements.map(m => m.speed);
                        const avgSpeed = speeds.reduce((a, b) => a + b, 0) / speeds.length;
                        const variance = speeds.reduce((sum, speed) => sum + Math.pow(speed - avgSpeed, 2), 0) / speeds.length;
                        
                        // AI biasanya punya variance sangat rendah (terlalu konsisten)
                        // Threshold dinaikkan untuk mengurangi false positive
                        if (variance < 0.0001 && avgSpeed > 0) {
                            suspiciousMovement = true;
                        }
                        
                        // Deteksi movement terlalu cepat atau terlalu lambat
                        // Threshold dinaikkan untuk mengurangi false positive
                        if (avgSpeed > 100 || (avgSpeed < 0.01 && avgSpeed > 0)) {
                            suspiciousMovement = true;
                        }
                    }
                    
                    lastMouseTime = now;
                });
                
                // Deteksi click pattern (AI biasanya terlalu cepat atau terlalu konsisten)
                let clickTimes = [];
                let lastClickTime = 0;
                
                document.addEventListener('click', function(e) {
                    const now = Date.now();
                    if (lastClickTime > 0) {
                        const timeDiff = now - lastClickTime;
                        clickTimes.push(timeDiff);
                        
                        if (clickTimes.length > 20) {
                            clickTimes.shift();
                        }
                        
                        // Deteksi click yang terlalu konsisten (AI pattern)
                        if (clickTimes.length >= 10) {
                            const avgTime = clickTimes.reduce((a, b) => a + b, 0) / clickTimes.length;
                            const variance = clickTimes.reduce((sum, time) => sum + Math.pow(time - avgTime, 2), 0) / clickTimes.length;
                            
                            // AI biasanya punya variance sangat rendah
                            // Threshold dinaikkan untuk mengurangi false positive
                            if (variance < 10 && avgTime < 200) {
                                suspiciousMovement = true;
                            }
                        }
                    }
                    lastClickTime = now;
                });
                
                // Deteksi scroll pattern (AI biasanya scroll terlalu smooth atau terlalu cepat)
                let scrollEvents = [];
                let lastScrollTime = Date.now();
                
                window.addEventListener('scroll', function() {
                    const now = Date.now();
                    const timeDiff = now - lastScrollTime;
                    
                    scrollEvents.push({
                        time: timeDiff,
                        position: window.pageYOffset
                    });
                    
                    if (scrollEvents.length > 30) {
                        scrollEvents.shift();
                    }
                    
                    // Deteksi scroll yang terlalu konsisten
                    if (scrollEvents.length >= 15) {
                        const times = scrollEvents.map(s => s.time);
                        const avgTime = times.reduce((a, b) => a + b, 0) / times.length;
                        const variance = times.reduce((sum, time) => sum + Math.pow(time - avgTime, 2), 0) / times.length;
                        
                        // Threshold dinaikkan untuk mengurangi false positive
                        if (variance < 5 && avgTime < 50) {
                            suspiciousMovement = true;
                        }
                    }
                    
                    lastScrollTime = now;
                });
                
                // Deteksi kecepatan membaca (AI biasanya terlalu cepat)
                let pageLoadTime = Date.now();
                let readingTime = 0;
                
                setInterval(function() {
                    readingTime = Date.now() - pageLoadTime;
                    const scrollPercentage = (window.pageYOffset / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
                    
                    // Jika scroll > 80% dalam waktu < 2 detik, kemungkinan AI (threshold lebih ketat)
                    if (readingTime < 2000 && scrollPercentage > 90) {
                        suspiciousMovement = true;
                    }
                }, 1000);
                
                // Deteksi missing human behavior
                let humanActivity = {
                    mouseMove: false,
                    click: false,
                    scroll: false,
                    keypress: false,
                    touch: false
                };
                
                document.addEventListener('mousemove', function() {
                    humanActivity.mouseMove = true;
                });
                
                document.addEventListener('click', function() {
                    humanActivity.click = true;
                });
                
                window.addEventListener('scroll', function() {
                    humanActivity.scroll = true;
                });
                
                document.addEventListener('keypress', function() {
                    humanActivity.keypress = true;
                });
                
                document.addEventListener('touchstart', function() {
                    humanActivity.touch = true;
                });
                
                // Check setelah 30 detik apakah ada human activity (threshold lebih longgar)
                setTimeout(function() {
                    const hasActivity = Object.values(humanActivity).some(function(active) {
                        return active === true;
                    });
                    
                    if (!hasActivity && !document.hidden) {
                        suspiciousMovement = true;
                    }
                }, 30000);
                
                // Deteksi automation tools
                if (window.navigator.webdriver || 
                    window.document.documentElement.getAttribute('webdriver') ||
                    window.navigator.plugins.length === 0 ||
                    !window.chrome ||
                    window.outerHeight === window.innerHeight) {
                    aiDetected = true;
                }
                
                // Deteksi jika browser tidak punya proper window properties
                try {
                    if (window.outerWidth === 0 || window.outerHeight === 0) {
                        aiDetected = true;
                    }
                } catch(e) {
                    aiDetected = true;
                }
                
                // Action jika AI terdeteksi
                if (aiDetected || suspiciousMovement) {
                    // Blur seluruh konten
                    if (typeof blurOverlay !== 'undefined') {
                        blurOverlay.style.display = 'block';
                        blurOverlay.style.background = 'rgba(255, 0, 0, 0.8)';
                        blurOverlay.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:white;font-size:24px;font-weight:bold;text-align:center;">AI Tool Detected<br><span style="font-size:16px;">Automated access is not allowed</span></div>';
                    }
                    
                    // Clear semua konten setelah delay
                    setTimeout(function() {
                        document.body.style.display = 'none';
                        document.body.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100vh;font-size:24px;color:red;">AI Tool Detected. Automated access is not allowed on this website.</div>';
                    }, 3000);
                    
                    // Log ke server (optional)
                    try {
                        fetch('/api/ai-detection', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({
                                userAgent: navigator.userAgent,
                                detected: true,
                                reason: aiDetected ? 'AI Bot User-Agent' : 'Suspicious Behavior',
                                timestamp: new Date().toISOString()
                            })
                        }).catch(function() {});
                    } catch(e) {}
                }
                
                // Periodic check untuk suspicious behavior - DISABLED untuk menghindari false positive
                // Overlay orange terlalu mengganggu user experience normal
                // Hanya aktifkan jika benar-benar terdeteksi AI bot, bukan hanya suspicious movement
                /*
                setInterval(function() {
                    if (suspiciousMovement && !aiDetected) {
                        // Show warning overlay
                        blurOverlay.style.display = 'block';
                        blurOverlay.style.background = 'rgba(255, 165, 0, 0.7)';
                        setTimeout(function() {
                            blurOverlay.style.display = 'none';
                            blurOverlay.style.background = 'rgba(0,0,0,0.9)';
                        }, 2000);
                    }
                }, 5000);
                */
                
                } // End of AI detection check
                
            })();
        </script>
        
        <!-- PWA Install Banner -->
        <div id="pwa-install-banner" class="hidden fixed bottom-4 left-4 right-4 md:left-auto md:right-4 md:w-96 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl p-4 z-50">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Install Noteds App</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mb-3">Install aplikasi untuk akses lebih cepat dan pengalaman yang lebih baik!</p>
                    <div class="flex gap-2">
                        <button onclick="installPWA()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded-md transition-colors">
                            Install
                        </button>
                        <button onclick="document.getElementById('pwa-install-banner').classList.add('hidden')" class="px-3 py-2 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors">
                            Nanti
                        </button>
                    </div>
                </div>
                <button onclick="document.getElementById('pwa-install-banner').classList.add('hidden')" class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Featured Notes Popups -->
        @include('components.featured-popups')

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('[SW] Service Worker registered:', registration.scope);
                            
                            // Check for updates
                            registration.addEventListener('updatefound', function() {
                                const newWorker = registration.installing;
                                newWorker.addEventListener('statechange', function() {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // New service worker available
                                        if (confirm('Update tersedia! Muat ulang halaman untuk mendapatkan versi terbaru?')) {
                                            window.location.reload();
                                        }
                                    }
                                });
                            });
                        })
                        .catch(function(error) {
                            console.log('[SW] Service Worker registration failed:', error);
                        });
                });
            }

            // PWA Install Prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                
                // Show install button
                const installBanner = document.getElementById('pwa-install-banner');
                if (installBanner) {
                    installBanner.classList.remove('hidden');
                }
            });

            // Handle install button click
            window.installPWA = function() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((choiceResult) => {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('User accepted the install prompt');
                        }
                        deferredPrompt = null;
                        const installBanner = document.getElementById('pwa-install-banner');
                        if (installBanner) {
                            installBanner.classList.add('hidden');
                        }
                    });
                }
            };

            // Hide install banner if already installed
            window.addEventListener('appinstalled', () => {
                console.log('PWA installed');
                const installBanner = document.getElementById('pwa-install-banner');
                if (installBanner) {
                    installBanner.classList.add('hidden');
                }
                deferredPrompt = null;
            });
        </script>
    </body>
</html>
