@extends('layouts.app')

@section('title', __('messages.create_new_note'))

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center mb-2">
                    <a href="{{ route('notes.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.create_new_note') }}</h1>
                </div>
                <p class="mt-2 text-base text-gray-600">{{ __('messages.share_knowledge') }}</p>
            </div>

            <!-- Flash Messages -->
            @if (session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            @if (!auth()->user()->hasPremium() || session('upgrade_message'))
                                <div class="mt-3">
                                    <a href="{{ route('subscription.create') }}"
                                        class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                        </svg>
                                        {{ __('messages.upgrade_to_premium') }} →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Subscription Limit Warning -->
            @if (!auth()->user()->hasPremium())
                @php
                    $noteCount = auth()->user()->notes()->count();
                    $limit = auth()->user()->getNoteCreationLimit();
                    $remaining = $limit - $noteCount;
                @endphp
                @if ($remaining <= 3 && $remaining > 0)
                    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="text-sm font-medium text-yellow-800">
                                    {{ __('messages.you_have') }} {{ $remaining }} {{ __('messages.notes_remaining') }}
                                </p>
                                <div class="mt-2">
                                    <a href="{{ route('subscription.create') }}"
                                        class="inline-flex items-center text-sm font-semibold text-yellow-700 hover:text-yellow-800">
                                        {{ __('messages.upgrade_to_premium_unlimited') }} →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Form Card -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.note_details') }}</h2>
                </div>
                <div class="p-6">
                    <form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.title') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                    :placeholder="__('messages.enter_note_title')"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                @error('title')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- AI Content Generator -->
                            <div
                                class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-200 p-4 mb-6 {{ !auth()->user()->hasPremium() ? 'opacity-75' : '' }}">
                                @if (!auth()->user()->hasPremium())
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-xs font-medium text-yellow-800">Fitur Premium</p>
                                        </div>
                                        <p class="text-xs text-yellow-700 mt-1">Fitur AI ini memerlukan subscription
                                            premium.</p>
                                        <a href="{{ route('subscription.create') }}"
                                            class="text-xs font-semibold text-yellow-800 hover:text-yellow-900 underline mt-1 inline-block">Upgrade
                                            Sekarang →</a>
                                    </div>
                                @endif
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">AI Content Generator</h4>
                                        <p class="text-xs text-gray-600 mb-3">Tulis prompt atau pertanyaan, AI akan membuat
                                            konten untuk Anda</p>
                                        <div class="flex gap-2">
                                            <input type="text" id="ai-prompt-input"
                                                placeholder="Contoh: Buatkan artikel tentang tips belajar efektif..."
                                                class="flex-1 text-sm rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                            <button type="button" id="ai-generate-btn"
                                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-all duration-200 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Generate
                                                <svg id="ai-generate-loading" class="hidden w-4 h-4 ml-2 animate-spin"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Search -->
                            <div
                                class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200 p-4 mb-6 {{ !auth()->user()->hasPremium() ? 'opacity-75' : '' }}">
                                @if (!auth()->user()->hasPremium())
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-xs font-medium text-yellow-800">Fitur Premium</p>
                                        </div>
                                        <p class="text-xs text-yellow-700 mt-1">Fitur AI ini memerlukan subscription
                                            premium.</p>
                                        <a href="{{ route('subscription.create') }}"
                                            class="text-xs font-semibold text-yellow-800 hover:text-yellow-900 underline mt-1 inline-block">Upgrade
                                            Sekarang →</a>
                                    </div>
                                @endif
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Cari Gambar Referensi</h4>
                                        <p class="text-xs text-gray-600 mb-3">Cari gambar dari Unsplash untuk melengkapi
                                            konten Anda</p>
                                        <div class="flex gap-2">
                                            <input type="text" id="image-search-input"
                                                placeholder="Contoh: belajar, teknologi, bisnis..."
                                                class="flex-1 text-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                            <button type="button" id="image-search-btn"
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                                Cari
                                                <svg id="image-search-loading" class="hidden w-4 h-4 ml-2 animate-spin"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Image Results -->
                                        <div id="image-results"
                                            class="hidden mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-2 bg-white rounded-lg border border-gray-200">
                                            <!-- Images will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image Generation -->
                            <div
                                class="bg-gradient-to-r from-pink-50 to-rose-50 rounded-lg border border-pink-200 p-4 mb-6 {{ !auth()->user()->hasPremium() ? 'opacity-75' : '' }}">
                                @if (!auth()->user()->hasPremium())
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-xs font-medium text-yellow-800">Fitur Premium</p>
                                        </div>
                                        <p class="text-xs text-yellow-700 mt-1">Fitur AI ini memerlukan subscription
                                            premium.</p>
                                        <a href="{{ route('subscription.create') }}"
                                            class="text-xs font-semibold text-yellow-800 hover:text-yellow-900 underline mt-1 inline-block">Upgrade
                                            Sekarang →</a>
                                    </div>
                                @endif
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-pink-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Generate Gambar dengan AI</h4>
                                        <p class="text-xs text-gray-600 mb-3">Buat gambar unik dari deskripsi teks
                                            menggunakan AI</p>
                                        <div class="flex gap-2 mb-2">
                                            <input type="text" id="image-generate-input"
                                                placeholder="Contoh: sunset over mountains, modern office..."
                                                class="flex-1 text-sm rounded-lg border-gray-300 shadow-sm focus:border-pink-500 focus:ring-pink-500 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                            <button type="button" id="image-generate-btn"
                                                class="inline-flex items-center px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white text-sm font-medium rounded-lg transition-all duration-200 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Generate
                                                <svg id="image-generate-loading" class="hidden w-4 h-4 ml-2 animate-spin"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex gap-2 text-xs">
                                            <select id="image-size-select" class="rounded border-gray-300 text-xs">
                                                <option value="1024x1024">1024x1024</option>
                                                <option value="512x512">512x512</option>
                                                <option value="1024x1792">1024x1792</option>
                                                <option value="1792x1024">1792x1024</option>
                                            </select>
                                            <select id="image-style-select" class="rounded border-gray-300 text-xs">
                                                <option value="vivid">Vivid</option>
                                                <option value="natural">Natural</option>
                                            </select>
                                        </div>
                                        <!-- Generated Image Result -->
                                        <div id="generated-image-result" class="hidden mt-4">
                                            <img id="generated-image" src="" alt="Generated"
                                                class="w-full max-w-md rounded-lg border border-gray-200">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Video Generation -->
                            <div
                                class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg border border-emerald-200 p-4 mb-6 {{ !auth()->user()->hasPremium() ? 'opacity-75' : '' }}">
                                @if (!auth()->user()->hasPremium())
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-xs font-medium text-yellow-800">Fitur Premium</p>
                                        </div>
                                        <p class="text-xs text-yellow-700 mt-1">Fitur AI ini memerlukan subscription
                                            premium.</p>
                                        <a href="{{ route('subscription.create') }}"
                                            class="text-xs font-semibold text-yellow-800 hover:text-yellow-900 underline mt-1 inline-block">Upgrade
                                            Sekarang →</a>
                                    </div>
                                @endif
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Generate Video dengan AI</h4>
                                        <p class="text-xs text-gray-600 mb-3">Buat video dari deskripsi teks menggunakan AI
                                        </p>
                                        <div class="flex gap-2 mb-2">
                                            <input type="text" id="video-generate-input"
                                                placeholder="Contoh: product showcase, tutorial intro..."
                                                class="flex-1 text-sm rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                            <button type="button" id="video-generate-btn"
                                                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-all duration-200 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Generate
                                                <svg id="video-generate-loading" class="hidden w-4 h-4 ml-2 animate-spin"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div class="flex gap-2 text-xs">
                                            <select id="video-duration-select" class="rounded border-gray-300 text-xs">
                                                <option value="5">5 detik</option>
                                                <option value="10">10 detik</option>
                                            </select>
                                            <select id="video-ratio-select" class="rounded border-gray-300 text-xs">
                                                <option value="16:9">16:9</option>
                                                <option value="9:16">9:16</option>
                                                <option value="1:1">1:1</option>
                                            </select>
                                        </div>
                                        <!-- Generated Video Result -->
                                        <div id="generated-video-result" class="hidden mt-4">
                                            <div id="video-status" class="text-sm text-gray-600"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Idea Generator -->
                            <div
                                class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-lg border border-amber-200 p-4 mb-6 {{ !auth()->user()->hasPremium() ? 'opacity-75' : '' }}">
                                @if (!auth()->user()->hasPremium())
                                    <div class="mb-3 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <p class="text-xs font-medium text-yellow-800">Fitur Premium</p>
                                        </div>
                                        <p class="text-xs text-yellow-700 mt-1">Fitur AI ini memerlukan subscription
                                            premium.</p>
                                        <a href="{{ route('subscription.create') }}"
                                            class="text-xs font-semibold text-yellow-800 hover:text-yellow-900 underline mt-1 inline-block">Upgrade
                                            Sekarang →</a>
                                    </div>
                                @endif
                                <div class="flex items-start gap-3 mb-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="text-sm font-semibold text-gray-900 mb-1">Idea Generator</h4>
                                        <p class="text-xs text-gray-600 mb-3">Dapatkan ide konten kreatif berdasarkan topik
                                        </p>
                                        <div class="flex gap-2 mb-2">
                                            <input type="text" id="idea-topic-input"
                                                placeholder="Contoh: teknologi, bisnis, pendidikan..."
                                                class="flex-1 text-sm rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                            <button type="button" id="idea-generate-btn"
                                                class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-all duration-200 {{ !auth()->user()->hasPremium() ? 'opacity-50 cursor-not-allowed' : '' }}"
                                                {{ !auth()->user()->hasPremium() ? 'disabled' : '' }}>
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                </svg>
                                                Generate Ideas
                                                <svg id="idea-generate-loading" class="hidden w-4 h-4 ml-2 animate-spin"
                                                    fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                                        stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor"
                                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- Generated Ideas Result -->
                                        <div id="generated-ideas-result"
                                            class="hidden mt-4 space-y-3 max-h-96 overflow-y-auto">
                                            <!-- Ideas will be inserted here -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Content (Rich Text Editor) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.content') }} <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1" id="editor-wrapper" style="min-height: 300px;">
                                    <div id="content-editor" style="min-height: 300px;"></div>
                                </div>
                                <textarea name="content" id="content" class="hidden" required>{{ old('content') }}</textarea>
                                @error('content')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- AI Assistant (for summary and tags) -->
                            <div
                                class="flex items-start gap-3 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg border border-purple-200">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">AI Assistant</h4>
                                    <p class="text-xs text-gray-600 mb-3">Let AI help you generate a summary and suggest
                                        tags</p>
                                    <button type="button" id="ai-analyze-btn"
                                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        <span id="ai-btn-text">Generate Summary & Tags</span>
                                        <svg id="ai-loading" class="hidden w-4 h-4 ml-2 animate-spin" fill="none"
                                            viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Summary (Auto-generated by AI) -->
                            <div>
                                <label for="summary" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.summary') }} <span
                                        class="text-xs text-gray-500">{{ __('messages.summary_optional_ai') }}</span>
                                </label>
                                <textarea name="summary" id="summary" rows="3"
                                    placeholder="{{ __('messages.auto_generated_summary_placeholder') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('summary') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('summary') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">{{ __('messages.brief_summary_note') }}</p>
                                @error('summary')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Preview Content (For paid notes - shown before purchase) -->
                            <div id="preview-content-wrapper"
                                style="display: {{ old('price', 0) > 0 ? 'block' : 'none' }};">
                                <label for="preview_content" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.preview_content') }} <span
                                        class="text-xs text-gray-500">{{ __('messages.preview_content_optional') }}</span>
                                </label>
                                <textarea name="preview_content" id="preview_content" rows="3" maxlength="300"
                                    placeholder="{{ __('messages.enter_preview_for_buyers') }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 resize-y @error('preview_content') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('preview_content') }}</textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    <span id="preview-char-count">0</span>/300 {{ __('messages.characters') }}.
                                    @if (empty(old('preview_content')))
                                        {{ __('messages.auto_generated_from_content') }}
                                    @endif
                                </p>
                                @error('preview_content')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror

                                <!-- Preview Percentage -->
                                <div class="mt-4">
                                    <label for="preview_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                        Persentase Preview Konten <span class="text-xs text-gray-500">(Opsional)</span>
                                    </label>
                                    <div class="flex items-center gap-4">
                                        <input type="range" name="preview_percentage" id="preview_percentage"
                                            min="0" max="100" step="5"
                                            value="{{ old('preview_percentage', 0) }}"
                                            class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                                        <div class="w-20 text-center">
                                            <span id="preview-percentage-value"
                                                class="text-lg font-semibold text-blue-600">{{ old('preview_percentage', 0) }}</span>
                                            <span class="text-sm text-gray-600">%</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-blue-600 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <div class="text-xs text-gray-600">
                                            <p><strong>0%</strong> = Konten terkunci sepenuhnya (hanya preview text di atas)
                                            </p>
                                            <p><strong>50%</strong> = Setengah baris konten terlihat (misal: 100 baris → 50
                                                baris terlihat)</p>
                                            <p><strong>100%</strong> = Konten terlihat penuh (tidak ada kunci)</p>
                                            <p class="mt-1 text-gray-500">Preview dihitung berdasarkan
                                                <strong>baris</strong>, bukan karakter. File attachments tetap terkunci
                                                sebelum pembelian.
                                            </p>
                                        </div>
                                    </div>
                                    @error('preview_percentage')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Thumbnail Images -->
                            <div id="thumbnail-wrapper" class="mt-6">
                                <label for="thumbnails" class="block text-sm font-medium text-gray-700 mb-2">
                                    Thumbnail Images <span class="text-xs text-gray-500">(Maksimal 5 gambar,
                                        opsional)</span>
                                </label>
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center w-full">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-4h-12m-6 4h.01M17 8h.01"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 justify-center items-center">
                                            <label for="thumbnails"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload gambar</span>
                                                <input type="file" name="thumbnails[]" id="thumbnails" multiple
                                                    accept="image/*" class="sr-only"
                                                    onchange="handleThumbnailUpload(this)">
                                            </label>
                                            <p class="pl-1">atau drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 text-center">PNG, JPG, GIF hingga 5MB per gambar
                                            (maks 5
                                            gambar)</p>
                                    </div>
                                </div>
                                <div id="thumbnail-preview" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <!-- Thumbnail previews will be inserted here -->
                                </div>
                                @error('thumbnails')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                @error('thumbnails.*')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- File Attachments -->
                            <div>
                                <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                                    {{ __('messages.file_attachments') }}
                                    <span class="text-xs text-gray-500 font-normal">
                                        {{ __('messages.file_attachments_optional') }}
                                    </span>
                                </label>
                                <div
                                    class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none"
                                            viewBox="0 0 48 48" aria-hidden="true">
                                            <path
                                                d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="attachments"
                                                class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>{{ __('messages.upload_files') }}</span>
                                                <input id="attachments" name="attachments[]" type="file" multiple
                                                    accept=".pdf,.doc,.docx,.txt,.zip,.rar,.jpg,.jpeg,.png,.gif,.xls,.xlsx,.ppt,.pptx"
                                                    class="sr-only">
                                            </label>
                                            <p class="pl-1">{{ __('messages.or_drag_and_drop') }}</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            @if (auth()->user()->hasPremium())
                                                {{ __('messages.max_50mb_per_file') }}
                                            @else
                                                {{ __('messages.max_5mb_per_file') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div id="file-list" class="mt-3 space-y-2"></div>
                                @error('attachments')
                                    <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-sm text-red-800">{!! $message !!}</p>
                                        @if (!auth()->user()->hasPremium())
                                            <a href="{{ route('subscription.create') }}"
                                                class="mt-2 inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-700 transition-colors duration-200">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                                </svg>
                                                {{ __('messages.upgrade_to_premium_arrow') }}
                                            </a>
                                        @endif
                                    </div>
                                @enderror
                                @error('attachments.*')
                                    <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                                        <p class="text-sm text-red-800">{!! $message !!}</p>
                                    </div>
                                @enderror
                                @if (!auth()->user()->hasPremium())
                                    <p class="mt-2 text-xs text-yellow-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ __('messages.file_size_limit_basic') }} <a
                                            href="{{ route('subscription.create') }}"
                                            class="font-semibold hover:underline">{{ __('messages.upgrade_to_premium') }}</a>
                                        {{ __('messages.file_size_limit_premium') }}
                                    </p>
                                @endif
                            </div>

                            <!-- Price and Public Toggle -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ __('messages.price') }} <span
                                            class="text-xs text-gray-500 font-normal">{{ __('messages.price_rp_0_for_free') }}</span>
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" name="price" id="price"
                                            value="{{ old('price', 0) }}" min="0" step="0.01" placeholder="0"
                                            class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 @error('price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                    </div>
                                    <div class="mt-2 flex items-start gap-2">
                                        <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-xs text-gray-600">
                                            <strong class="text-green-700">{{ __('messages.free') }}</strong>:
                                            {{ __('messages.share_knowledge_freely') }} •
                                            <strong class="text-blue-700">{{ __('messages.paid') }}</strong>:
                                            {{ __('messages.set_your_own_price') }}
                                        </p>
                                    </div>
                                    @error('price')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror

                                    <!-- Discount Price -->
                                    <div class="mt-4" id="discount-price-wrapper"
                                        style="display: {{ old('discount_price') || old('price', 0) > 0 ? 'block' : 'none' }};">
                                        <label for="discount_price" class="block text-sm font-medium text-gray-700 mb-2">
                                            Harga Diskon <span class="text-xs text-gray-500 font-normal">(Opsional)</span>
                                        </label>
                                        <div class="relative">
                                            <div
                                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input type="number" name="discount_price" id="discount_price"
                                                value="{{ old('discount_price') }}" min="0" step="0.01"
                                                placeholder="0"
                                                class="mt-1 block w-full pl-10 rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-opacity-50 transition-all duration-200 @error('discount_price') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                                        </div>
                                        <div class="mt-2 flex items-start gap-2">
                                            <svg class="w-4 h-4 text-green-600 mt-0.5" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            <p class="text-xs text-gray-600">
                                                Set harga diskon untuk menarik pembeli. Harga diskon harus lebih murah dari
                                                harga normal.
                                            </p>
                                        </div>
                                        <div id="discount-preview" class="mt-2 text-sm text-gray-600 hidden">
                                            <span class="font-medium text-green-600">Diskon: <span
                                                    id="discount-percent">0</span>%</span>
                                        </div>
                                        @error('discount_price')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="flex items-end">
                                    <label
                                        class="flex items-center p-4 rounded-lg border border-gray-200 hover:border-blue-300 cursor-pointer transition-colors duration-200 w-full">
                                        <input type="checkbox" name="is_public" value="1"
                                            {{ old('is_public') ? 'checked' : '' }}
                                            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded transition-colors duration-200">
                                        <div class="ml-3">
                                            <span
                                                class="text-sm font-medium text-gray-700">{{ __('messages.make_public') }}</span>
                                            <p class="text-xs text-gray-500 mt-0.5">
                                                {{ __('messages.available_in_marketplace') }}</p>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            @if (auth()->user()->hasPremium())
                                <!-- Organization (Premium Features) -->
                                <div
                                    class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-purple-50 rounded-lg border border-purple-200">
                                    <div>
                                        <label for="folder_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                            </svg>
                                            {{ __('messages.folder_optional') }}
                                        </label>
                                        <select name="folder_id" id="folder_id"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                            <option value="">{{ __('messages.none_root') }}</option>
                                            @foreach ($folders as $folder)
                                                <option value="{{ $folder->id }}"
                                                    data-workspace-id="{{ $folder->workspace_id ?? '' }}"
                                                    {{ old('folder_id', $selectedFolder?->id) == $folder->id ? 'selected' : '' }}>
                                                    {{ $folder->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('messages.organize_notes_in_folders') }}</p>
                                    </div>

                                    <div>
                                        <label for="workspace_id" class="block text-sm font-medium text-gray-700 mb-2">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ __('messages.workspace_optional') }}
                                        </label>
                                        <select name="workspace_id" id="workspace_id"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500">
                                            <option value="">{{ __('messages.personal') }}</option>
                                            @foreach ($workspaces as $workspace)
                                                <option value="{{ $workspace->id }}"
                                                    {{ old('workspace_id', $selectedWorkspace?->id) == $workspace->id ? 'selected' : '' }}>
                                                    {{ $workspace->name }} ({{ $workspace->type }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.assign_to_workspace') }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Tags -->
                            <div>
                                <label for="tags" class="block text-sm font-medium text-gray-700 mb-2">
                                    Tags
                                </label>
                                <div class="flex flex-wrap gap-2 mb-3 min-h-[2.5rem] p-2 border border-gray-200 rounded-lg bg-gray-50"
                                    id="tags-container">
                                    @if (old('tags'))
                                        @foreach (old('tags') as $tag)
                                            <span
                                                class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                <input type="hidden" name="tags[]" value="{{ $tag }}">
                                                {{ $tag }}
                                                <button type="button"
                                                    class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <input type="text" id="tag-input"
                                            placeholder="{{ __('messages.type_tag_and_press_enter') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                    </div>
                                    <div>
                                        <select id="tag-select"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                            <option value="">{{ __('messages.select_existing_tag') }}</option>
                                            @foreach ($tags as $tag)
                                                <option value="{{ $tag->name }}">{{ $tag->name }}
                                                    ({{ $tag->notes_count }} notes)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Add tags to categorize your note. Press Enter to add a new tag or select from existing
                                    tags.
                                </p>
                                @error('tags.*')
                                    <p class="mt-2 text-sm text-red-600 flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                                <a href="{{ route('notes.index') }}"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    Cancel
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                    Create Note
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Import Quill Delta for content insertion
                    const Delta = Quill.import('delta');

                    // Initialize Quill Rich Text Editor
                    const quill = new Quill('#content-editor', {
                        theme: 'snow',
                        placeholder: 'Write your note content here...',
                        modules: {
                            toolbar: [
                                [{
                                    'header': [1, 2, 3, false]
                                }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{
                                    'color': []
                                }, {
                                    'background': []
                                }],
                                [{
                                    'list': 'ordered'
                                }, {
                                    'list': 'bullet'
                                }],
                                ['blockquote', 'code-block'],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    // Get textarea element
                    const contentTextarea = document.getElementById('content');

                    // Sync Quill content to hidden textarea for form submission
                    quill.on('text-change', function() {
                        contentTextarea.value = quill.root.innerHTML;
                    });

                    // Set initial content if exists (from old input)
                    if (contentTextarea.value) {
                        quill.root.innerHTML = contentTextarea.value;
                    }

                    const tagInput = document.getElementById('tag-input');
                    const tagSelect = document.getElementById('tag-select');
                    const tagsContainer = document.getElementById('tags-container');

                    // Add tag from input
                    tagInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const tagValue = this.value.trim();
                            if (tagValue && !tagExists(tagValue)) {
                                addTag(tagValue);
                                this.value = '';
                            }
                        }
                    });

                    // Add tag from select
                    tagSelect.addEventListener('change', function() {
                        const tagValue = this.value.trim();
                        if (tagValue && !tagExists(tagValue)) {
                            addTag(tagValue);
                            this.value = '';
                        }
                    });

                    // Remove tag
                    tagsContainer.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-tag') || e.target.closest('.remove-tag')) {
                            const tagItem = e.target.closest('.tag-item');
                            if (tagItem) {
                                tagItem.remove();
                            }
                        }
                    });

                    function tagExists(tagName) {
                        return Array.from(tagsContainer.querySelectorAll('input[type="hidden"]'))
                            .some(input => input.value.toLowerCase() === tagName.toLowerCase());
                    }

                    function addTag(tagName) {
                        const tagItem = document.createElement('span');
                        tagItem.className =
                            'tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200';
                        tagItem.innerHTML = `
            <input type="hidden" name="tags[]" value="${tagName}">
            ${tagName}
            <button type="button" class="remove-tag ml-2 text-blue-600 hover:text-blue-800 focus:outline-none transition-colors duration-200">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        `;
                        tagsContainer.appendChild(tagItem);
                    }

                    // Preview content character counter
                    const previewContentTextarea = document.getElementById('preview_content');
                    const previewCharCount = document.getElementById('preview-char-count');
                    if (previewContentTextarea && previewCharCount) {
                        previewCharCount.textContent = (previewContentTextarea.value || '').length;
                        previewContentTextarea.addEventListener('input', function() {
                            previewCharCount.textContent = this.value.length;
                        });
                    }

                    // Show preview content field when price > 0
                    const priceInput = document.getElementById('price');
                    const previewContentWrapper = document.getElementById('preview-content-wrapper');
                    const discountPriceWrapper = document.getElementById('discount-price-wrapper');
                    const discountPriceInput = document.getElementById('discount_price');
                    const discountPreview = document.getElementById('discount-preview');
                    const discountPercent = document.getElementById('discount-percent');

                    function updateDiscountPreview() {
                        const price = parseFloat(priceInput.value) || 0;
                        const discountPrice = parseFloat(discountPriceInput.value) || 0;

                        if (price > 0) {
                            discountPriceWrapper.style.display = 'block';
                        } else {
                            discountPriceWrapper.style.display = 'none';
                            discountPriceInput.value = '';
                        }

                        if (discountPrice > 0 && discountPrice < price) {
                            const percent = Math.round(((price - discountPrice) / price) * 100);
                            discountPercent.textContent = percent;
                            discountPreview.classList.remove('hidden');
                        } else {
                            discountPreview.classList.add('hidden');
                        }
                    }

                    if (priceInput && previewContentWrapper) {
                        priceInput.addEventListener('input', function() {
                            if (parseFloat(this.value) > 0) {
                                previewContentWrapper.style.display = 'block';
                            } else {
                                previewContentWrapper.style.display = 'none';
                            }
                            updateDiscountPreview();
                        });
                        // Initial check
                        if (parseFloat(priceInput.value) > 0) {
                            previewContentWrapper.style.display = 'block';
                        } else {
                            previewContentWrapper.style.display = 'none';
                        }
                    }

                    if (discountPriceInput) {
                        discountPriceInput.addEventListener('input', updateDiscountPreview);
                    }

                    // Initial check
                    updateDiscountPreview();

                    // Filter folders based on selected workspace
                    const workspaceSelect = document.getElementById('workspace_id');
                    const folderSelect = document.getElementById('folder_id');

                    if (workspaceSelect && folderSelect) {
                        function filterFoldersByWorkspace() {
                            const selectedWorkspaceId = workspaceSelect.value;
                            const folderOptions = folderSelect.querySelectorAll('option[data-workspace-id]');

                            folderOptions.forEach(option => {
                                const folderWorkspaceId = option.getAttribute('data-workspace-id') || '';

                                // Show folder if:
                                // 1. No workspace selected (personal) AND folder has no workspace_id (personal folder)
                                // 2. Workspace selected AND folder's workspace_id matches selected workspace
                                if (selectedWorkspaceId === '') {
                                    // Personal mode: only show folders without workspace_id
                                    option.style.display = folderWorkspaceId === '' ? '' : 'none';
                                } else {
                                    // Workspace mode: only show folders that belong to selected workspace
                                    option.style.display = folderWorkspaceId === selectedWorkspaceId ? '' : 'none';
                                }
                            });

                            // Reset folder selection if current selection is hidden
                            const selectedOption = folderSelect.options[folderSelect.selectedIndex];
                            if (selectedOption && selectedOption.style.display === 'none' && selectedOption.value !== '') {
                                folderSelect.value = '';
                            }
                        }

                        // Filter on page load
                        filterFoldersByWorkspace();

                        // Filter when workspace changes
                        workspaceSelect.addEventListener('change', filterFoldersByWorkspace);
                    }

                    // Preview Percentage Slider
                    const previewPercentageInput = document.getElementById('preview_percentage');
                    const previewPercentageValue = document.getElementById('preview-percentage-value');
                    if (previewPercentageInput && previewPercentageValue) {
                        previewPercentageInput.addEventListener('input', function() {
                            previewPercentageValue.textContent = this.value;
                        });
                        // Initial value
                        previewPercentageValue.textContent = previewPercentageInput.value;
                    }

                    // Thumbnail Upload Handler
                    window.handleThumbnailUpload = function(input) {
                        const files = Array.from(input.files);
                        const previewContainer = document.getElementById('thumbnail-preview');
                        const maxFiles = 5;

                        // Limit to max 5 files
                        if (files.length > maxFiles) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Terlalu Banyak Gambar',
                                text: `Maksimal ${maxFiles} gambar. Hanya ${maxFiles} gambar pertama yang akan diupload.`,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                            // Keep only first 5 files
                            const dt = new DataTransfer();
                            files.slice(0, maxFiles).forEach(file => dt.items.add(file));
                            input.files = dt.files;
                        }

                        previewContainer.innerHTML = '';
                        const filesToShow = Array.from(input.files).slice(0, maxFiles);

                        filesToShow.forEach((file, index) => {
                            if (!file.type.startsWith('image/')) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'File Tidak Valid',
                                    text: `${file.name} bukan file gambar.`,
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const div = document.createElement('div');
                                div.className = 'relative group';
                                div.innerHTML = `
                                    <img src="${e.target.result}" alt="Thumbnail ${index + 1}" 
                                        class="w-full h-32 object-cover rounded-lg border-2 border-gray-200">
                                    <button type="button" onclick="removeThumbnail(${index})" 
                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                `;
                                previewContainer.appendChild(div);
                            };
                            reader.readAsDataURL(file);
                        });
                    };

                    window.removeThumbnail = function(index) {
                        const input = document.getElementById('thumbnails');
                        const dt = new DataTransfer();
                        const files = Array.from(input.files);
                        files.forEach((file, i) => {
                            if (i !== index) {
                                dt.items.add(file);
                            }
                        });
                        input.files = dt.files;
                        handleThumbnailUpload(input);
                    };

                    // File upload preview
                    const fileInput = document.getElementById('attachments');
                    const fileList = document.getElementById('file-list');
                    if (fileInput && fileList) {
                        fileInput.addEventListener('change', function() {
                            fileList.innerHTML = '';
                            Array.from(this.files).forEach((file, index) => {
                                const fileItem = document.createElement('div');
                                fileItem.className =
                                    'flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200';
                                fileItem.innerHTML = `
                    <div class="flex items-center flex-1 min-w-0">
                        <svg class="w-5 h-5 text-gray-400 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(2)} KB</p>
                        </div>
                    </div>
                `;
                                fileList.appendChild(fileItem);
                            });
                        });
                    }

                    // Helper function to check premium requirement
                    function checkPremiumRequirement(data) {
                        if (data.requires_premium) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Fitur Premium',
                                text: data.message || 'Fitur AI ini memerlukan subscription premium.',
                                showCancelButton: true,
                                confirmButtonText: 'Upgrade Sekarang',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#3085d6',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = '{{ route('subscription.create') }}';
                                }
                            });
                            return true;
                        }
                        return false;
                    }

                    // AI Assistant functionality
                    // AI Content Generator
                    const aiGenerateBtn = document.getElementById('ai-generate-btn');
                    const aiPromptInput = document.getElementById('ai-prompt-input');
                    const aiGenerateLoading = document.getElementById('ai-generate-loading');

                    if (aiGenerateBtn && aiPromptInput) {
                        // Allow Enter key to trigger generation
                        aiPromptInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                aiGenerateBtn.click();
                            }
                        });

                        aiGenerateBtn.addEventListener('click', async function() {
                            const prompt = aiPromptInput.value.trim();

                            if (!prompt) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Prompt Kosong',
                                    text: 'Silakan masukkan prompt atau pertanyaan untuk generate konten.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            // Show loading state
                            aiGenerateBtn.disabled = true;
                            aiGenerateLoading.classList.remove('hidden');

                            try {
                                const response = await fetch('{{ route('ai.generate-content') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        prompt: prompt,
                                        max_length: 2000
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    aiGenerateBtn.disabled = false;
                                    aiGenerateLoading.classList.add('hidden');
                                    return;
                                }

                                if (data.success && data.content) {
                                    // Insert generated content into Quill editor at current cursor position
                                    // Get current selection or end of content
                                    let range = quill.getSelection();
                                    if (!range) {
                                        range = {
                                            index: quill.getLength(),
                                            length: 0
                                        };
                                    }

                                    // Convert HTML to Quill Delta
                                    const delta = quill.clipboard.convert({
                                        html: data.content
                                    });

                                    // Insert at current position (append if no selection)
                                    quill.updateContents(
                                        new Delta()
                                        .retain(range.index)
                                        .delete(range.length)
                                        .concat(delta),
                                        'api'
                                    );

                                    // Move cursor to end of inserted content
                                    const newIndex = range.index + delta.length();
                                    quill.setSelection(newIndex);

                                    // Update hidden textarea
                                    contentTextarea.value = quill.root.innerHTML;

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Konten Berhasil Dibuat!',
                                        text: 'Konten telah dimasukkan ke editor. Anda dapat mengeditnya lebih lanjut.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                } else {
                                    throw new Error(data.message || 'Failed to generate content');
                                }
                            } catch (error) {
                                console.error('AI generation error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Gagal generate konten. Silakan coba lagi.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } finally {
                                aiGenerateBtn.disabled = false;
                                aiGenerateLoading.classList.add('hidden');
                            }
                        });
                    }

                    // Image Search
                    const imageSearchBtn = document.getElementById('image-search-btn');
                    const imageSearchInput = document.getElementById('image-search-input');
                    const imageSearchLoading = document.getElementById('image-search-loading');
                    const imageResults = document.getElementById('image-results');

                    if (imageSearchBtn && imageSearchInput) {
                        // Allow Enter key to trigger search
                        imageSearchInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                imageSearchBtn.click();
                            }
                        });

                        imageSearchBtn.addEventListener('click', async function() {
                            const query = imageSearchInput.value.trim();

                            if (!query) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Query Kosong',
                                    text: 'Silakan masukkan kata kunci untuk mencari gambar.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            // Show loading state
                            imageSearchBtn.disabled = true;
                            imageSearchLoading.classList.remove('hidden');
                            imageResults.classList.add('hidden');
                            imageResults.innerHTML = '';

                            try {
                                const response = await fetch('{{ route('ai.search-images') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        query: query,
                                        limit: 12
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    imageSearchBtn.disabled = false;
                                    imageSearchLoading.classList.add('hidden');
                                    return;
                                }

                                if (data.success && data.images && data.images.length > 0) {
                                    // Display images
                                    imageResults.innerHTML = '';
                                    data.images.forEach(image => {
                                        const imgDiv = document.createElement('div');
                                        imgDiv.className = 'relative group cursor-pointer';
                                        imgDiv.innerHTML = `
                            <img src="${image.thumbnail || image.url}" 
                                 alt="${image.description || query}" 
                                 class="w-full h-24 object-cover rounded-lg border border-gray-200 hover:border-blue-400 transition-all duration-200"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition-all duration-200 flex items-center justify-center">
                                <svg class="w-6 h-6 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                        `;

                                        // Insert image to editor on click
                                        imgDiv.addEventListener('click', function() {
                                            const imageUrl = image.url || image.full;
                                            if (imageUrl) {
                                                // Get current selection or end of content
                                                let range = quill.getSelection();
                                                if (!range) {
                                                    range = {
                                                        index: quill.getLength(),
                                                        length: 0
                                                    };
                                                }

                                                // Insert image into Quill editor
                                                quill.insertEmbed(range.index, 'image',
                                                    imageUrl, 'user');

                                                // Move cursor after image
                                                quill.setSelection(range.index + 1);

                                                // Update hidden textarea
                                                contentTextarea.value = quill.root.innerHTML;

                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Gambar Ditambahkan!',
                                                    text: 'Gambar telah dimasukkan ke editor.',
                                                    toast: true,
                                                    position: 'top-end',
                                                    showConfirmButton: false,
                                                    timer: 2000
                                                });
                                            }
                                        });

                                        imageResults.appendChild(imgDiv);
                                    });

                                    imageResults.classList.remove('hidden');
                                } else {
                                    Swal.fire({
                                        icon: 'info',
                                        title: 'Tidak Ada Hasil',
                                        text: data.message ||
                                            'Tidak ada gambar ditemukan. Coba kata kunci lain.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            } catch (error) {
                                console.error('Image search error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Gagal mencari gambar. Silakan coba lagi.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } finally {
                                imageSearchBtn.disabled = false;
                                imageSearchLoading.classList.add('hidden');
                            }
                        });
                    }

                    // Image Generation
                    const imageGenerateBtn = document.getElementById('image-generate-btn');
                    const imageGenerateInput = document.getElementById('image-generate-input');
                    const imageGenerateLoading = document.getElementById('image-generate-loading');
                    const imageSizeSelect = document.getElementById('image-size-select');
                    const imageStyleSelect = document.getElementById('image-style-select');
                    const generatedImageResult = document.getElementById('generated-image-result');
                    const generatedImage = document.getElementById('generated-image');

                    if (imageGenerateBtn && imageGenerateInput) {
                        imageGenerateInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                imageGenerateBtn.click();
                            }
                        });

                        imageGenerateBtn.addEventListener('click', async function() {
                            const prompt = imageGenerateInput.value.trim();

                            if (!prompt) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Prompt Kosong',
                                    text: 'Silakan masukkan deskripsi gambar yang ingin dibuat.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            imageGenerateBtn.disabled = true;
                            imageGenerateLoading.classList.remove('hidden');
                            generatedImageResult.classList.add('hidden');

                            try {
                                const response = await fetch('{{ route('ai.generate-image') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        prompt: prompt,
                                        size: imageSizeSelect.value,
                                        style: imageStyleSelect.value
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    imageGenerateBtn.disabled = false;
                                    imageGenerateLoading.classList.add('hidden');
                                    return;
                                }

                                if (data.success && data.image) {
                                    if (data.image.url) {
                                        generatedImage.src = data.image.url;
                                        generatedImageResult.classList.remove('hidden');

                                        // Add click to insert image
                                        generatedImage.onclick = function() {
                                            let range = quill.getSelection();
                                            if (!range) {
                                                range = {
                                                    index: quill.getLength(),
                                                    length: 0
                                                };
                                            }
                                            quill.insertEmbed(range.index, 'image', data.image.url, 'user');
                                            quill.setSelection(range.index + 1);
                                            contentTextarea.value = quill.root.innerHTML;

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Gambar Ditambahkan!',
                                                text: 'Gambar telah dimasukkan ke editor.',
                                                toast: true,
                                                position: 'top-end',
                                                showConfirmButton: false,
                                                timer: 2000
                                            });
                                        };

                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Gambar Berhasil Dibuat!',
                                            text: 'Klik gambar untuk memasukkannya ke editor.',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    } else if (data.image.base64) {
                                        generatedImage.src = 'data:image/png;base64,' + data.image.base64;
                                        generatedImageResult.classList.remove('hidden');
                                    }
                                } else {
                                    throw new Error(data.message || 'Failed to generate image');
                                }
                            } catch (error) {
                                console.error('Image generation error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message ||
                                        'Gagal generate gambar. Pastikan API key sudah dikonfigurasi.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } finally {
                                imageGenerateBtn.disabled = false;
                                imageGenerateLoading.classList.add('hidden');
                            }
                        });
                    }

                    // Video Generation
                    const videoGenerateBtn = document.getElementById('video-generate-btn');
                    const videoGenerateInput = document.getElementById('video-generate-input');
                    const videoGenerateLoading = document.getElementById('video-generate-loading');
                    const videoDurationSelect = document.getElementById('video-duration-select');
                    const videoRatioSelect = document.getElementById('video-ratio-select');
                    const generatedVideoResult = document.getElementById('generated-video-result');
                    const videoStatus = document.getElementById('video-status');

                    if (videoGenerateBtn && videoGenerateInput) {
                        videoGenerateInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                videoGenerateBtn.click();
                            }
                        });

                        videoGenerateBtn.addEventListener('click', async function() {
                            const prompt = videoGenerateInput.value.trim();

                            if (!prompt) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Prompt Kosong',
                                    text: 'Silakan masukkan deskripsi video yang ingin dibuat.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            videoGenerateBtn.disabled = true;
                            videoGenerateLoading.classList.remove('hidden');
                            generatedVideoResult.classList.remove('hidden');
                            videoStatus.textContent = 'Generating video... This may take a while.';

                            try {
                                const response = await fetch('{{ route('ai.generate-video') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        prompt: prompt,
                                        duration: parseInt(videoDurationSelect.value),
                                        ratio: videoRatioSelect.value
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    videoGenerateBtn.disabled = false;
                                    videoGenerateLoading.classList.add('hidden');
                                    return;
                                }

                                if (data.success && data.video) {
                                    if (data.video.type === 'script') {
                                        // Jika yang dihasilkan adalah script (dari Ollama)
                                        videoStatus.innerHTML = `
                                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                                <h5 class="font-semibold text-blue-900 mb-2">Video Script Generated</h5>
                                                <p class="text-sm text-blue-700 mb-2">${data.video.message || ''}</p>
                                                <div class="bg-white rounded p-3 max-h-64 overflow-y-auto">
                                                    <pre class="text-xs whitespace-pre-wrap">${data.video.script || ''}</pre>
                                                </div>
                                                <p class="text-xs text-blue-600 mt-2">Note: Untuk generate video actual, konfigurasi RunwayML API.</p>
                                            </div>
                                        `;
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Script Video Dibuat',
                                            text: 'Script video telah dibuat. Untuk video actual, gunakan RunwayML API.',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 4000
                                        });
                                    } else if (data.video.job_id) {
                                        videoStatus.textContent =
                                            `Video sedang diproses (Job ID: ${data.video.job_id}). Estimasi waktu: ${data.video.estimated_time || 60} detik.`;
                                        Swal.fire({
                                            icon: 'info',
                                            title: 'Video Sedang Diproses',
                                            text: 'Video generation sedang berjalan. Anda akan diberitahu ketika selesai.',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 5000
                                        });
                                    } else if (data.video.url) {
                                        videoStatus.innerHTML =
                                            `<video src="${data.video.url}" controls class="w-full max-w-md rounded-lg"></video>`;
                                    }
                                } else {
                                    throw new Error(data.message || 'Failed to generate video');
                                }
                            } catch (error) {
                                console.error('Video generation error:', error);
                                videoStatus.textContent = 'Error: ' + (error.message || 'Gagal generate video');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message ||
                                        'Gagal generate video. Pastikan API key sudah dikonfigurasi.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } finally {
                                videoGenerateBtn.disabled = false;
                                videoGenerateLoading.classList.add('hidden');
                            }
                        });
                    }

                    // Idea Generator
                    const ideaGenerateBtn = document.getElementById('idea-generate-btn');
                    const ideaTopicInput = document.getElementById('idea-topic-input');
                    const ideaGenerateLoading = document.getElementById('idea-generate-loading');
                    const generatedIdeasResult = document.getElementById('generated-ideas-result');

                    if (ideaGenerateBtn && ideaTopicInput) {
                        ideaTopicInput.addEventListener('keypress', function(e) {
                            if (e.key === 'Enter') {
                                ideaGenerateBtn.click();
                            }
                        });

                        ideaGenerateBtn.addEventListener('click', async function() {
                            const topic = ideaTopicInput.value.trim();

                            if (!topic) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Topik Kosong',
                                    text: 'Silakan masukkan topik untuk generate ide.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            ideaGenerateBtn.disabled = true;
                            ideaGenerateLoading.classList.remove('hidden');
                            generatedIdeasResult.classList.add('hidden');
                            generatedIdeasResult.innerHTML = '';

                            try {
                                const response = await fetch('{{ route('ai.generate-ideas') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        topic: topic,
                                        count: 5
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    ideaGenerateBtn.disabled = false;
                                    ideaGenerateLoading.classList.add('hidden');
                                    return;
                                }

                                if (data.success && data.ideas && data.ideas.length > 0) {
                                    generatedIdeasResult.innerHTML = '';
                                    data.ideas.forEach((idea, index) => {
                                        const ideaDiv = document.createElement('div');
                                        ideaDiv.className =
                                            'bg-white p-4 rounded-lg border border-gray-200 hover:border-amber-300 transition-all';
                                        ideaDiv.innerHTML = `
                            <h5 class="font-semibold text-gray-900 mb-2">${index + 1}. ${idea.title || 'Untitled Idea'}</h5>
                            ${idea.description ? `<p class="text-sm text-gray-600 mb-2">${idea.description}</p>` : ''}
                            ${idea.key_points && idea.key_points.length > 0 ? `
                                                                                                                                                                                                        <ul class="text-xs text-gray-500 list-disc list-inside">
                                                                                                                                                                                                            ${idea.key_points.map(point => `<li>${point}</li>`).join('')}
                                                                                                                                                                                                        </ul>
                                                                                                                                                                                                    ` : ''}
                            <button type="button" class="mt-2 text-xs text-amber-600 hover:text-amber-700 font-medium" onclick="useIdea('${idea.title || ''}', '${(idea.description || '').replace(/'/g, "\\'")}')">
                                Gunakan Ide Ini →
                            </button>
                        `;
                                        generatedIdeasResult.appendChild(ideaDiv);
                                    });

                                    generatedIdeasResult.classList.remove('hidden');

                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Ide Berhasil Dibuat!',
                                        text: `Ditemukan ${data.ideas.length} ide kreatif.`,
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                } else {
                                    throw new Error(data.message || 'Failed to generate ideas');
                                }
                            } catch (error) {
                                console.error('Idea generation error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: error.message || 'Gagal generate ide. Silakan coba lagi.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            } finally {
                                ideaGenerateBtn.disabled = false;
                                ideaGenerateLoading.classList.add('hidden');
                            }
                        });
                    }

                    // Function to use idea in editor
                    window.useIdea = function(title, description) {
                        let range = quill.getSelection();
                        if (!range) {
                            range = {
                                index: quill.getLength(),
                                length: 0
                            };
                        }

                        const content = `<h2>${title}</h2><p>${description}</p>`;
                        const delta = quill.clipboard.convert({
                            html: content
                        });
                        quill.updateContents(
                            new Delta()
                            .retain(range.index)
                            .delete(range.length)
                            .concat(delta),
                            'api'
                        );

                        quill.setSelection(range.index + delta.length());
                        contentTextarea.value = quill.root.innerHTML;

                        // Update title field if exists
                        const titleInput = document.getElementById('title');
                        if (titleInput && !titleInput.value) {
                            titleInput.value = title;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Ide Ditambahkan!',
                            text: 'Ide telah dimasukkan ke editor.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    };

                    // AI Analyze Button (for summary and tags)
                    const aiAnalyzeBtn = document.getElementById('ai-analyze-btn');
                    const aiBtnText = document.getElementById('ai-btn-text');
                    const aiLoading = document.getElementById('ai-loading');
                    const summaryTextarea = document.getElementById('summary');

                    if (aiAnalyzeBtn) {
                        aiAnalyzeBtn.addEventListener('click', async function() {
                            // Get plain text content from Quill for AI analysis
                            const content = quill.getText().trim();

                            if (!content) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'No Content',
                                    text: 'Please enter some content first.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                return;
                            }

                            // Show loading state
                            aiAnalyzeBtn.disabled = true;
                            aiBtnText.textContent = 'Analyzing...';
                            aiLoading.classList.remove('hidden');

                            try {
                                const response = await fetch('{{ route('ai.analyze') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        content: content
                                    })
                                });

                                const data = await response.json();

                                // Check premium requirement
                                if (checkPremiumRequirement(data)) {
                                    aiAnalyzeBtn.disabled = false;
                                    aiLoading.classList.add('hidden');
                                    aiBtnText.textContent = 'Generate Summary & Tags';
                                    return;
                                }

                                if (data.success) {
                                    // Fill summary
                                    if (data.data.summary) {
                                        summaryTextarea.value = data.data.summary;
                                    }

                                    // Add suggested tags
                                    if (data.data.tags && data.data.tags.length > 0) {
                                        data.data.tags.forEach(tag => {
                                            if (!tagExists(tag)) {
                                                addTag(tag);
                                            }
                                        });
                                    }

                                    // Show success message
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Generated!',
                                        text: 'AI has generated summary and suggested tags.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });

                                    aiBtnText.textContent = 'Success! ✓';
                                    setTimeout(() => {
                                        aiBtnText.textContent = 'Generate with AI';
                                    }, 2000);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'AI Unavailable',
                                        text: 'AI service is currently unavailable. Please try again later.',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    aiBtnText.textContent = 'Try Again';
                                }
                            } catch (error) {
                                console.error('AI analysis error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'An error occurred while analyzing your content. Please try again.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                                aiBtnText.textContent = 'Try Again';
                            } finally {
                                // Reset loading state
                                aiAnalyzeBtn.disabled = false;
                                aiLoading.classList.add('hidden');
                                setTimeout(() => {
                                    aiBtnText.textContent = 'Generate with AI';
                                }, 2000);
                            }
                        });
                    }
                });
            </script>
        @endpush
    @endsection
