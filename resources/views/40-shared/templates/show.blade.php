@extends('40-shared/layouts/app')

@section('title', $template->name)

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('templates.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Templates') }}
            </a>
        </div>

        <!-- Template Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $template->name }}</h1>
            
            @if($template->description)
                <p class="text-gray-700 mb-4">{{ $template->description }}</p>
            @endif
            
            <div class="flex items-center gap-4 mb-4">
                @if($template->category)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $template->category }}
                    </span>
                @endif
                <span class="text-sm text-gray-600">
                    {{ __('By') }} <a href="{{ route('public.profile.show', $template->user->username) }}" class="text-blue-600 hover:text-blue-800">
                        {{ $template->user->name }}
                    </a>
                </span>
                @if($template->is_public)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ __('Public') }}
                    </span>
                @endif
            </div>

            <div class="prose max-w-none">
                <div class="ql-editor text-gray-900 leading-relaxed">{!! $template->content_template !!}</div>
            </div>
        </div>

        <!-- Actions -->
        @auth
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form action="{{ route('templates.use', $template) }}" method="POST">
                    @csrf
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ __('Use This Template') }}</h3>
                            <p class="text-sm text-gray-600">{{ __('This will create a new note with the template content.') }}</p>
                        </div>
                        <button type="submit"
                            class="px-6 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">
                            {{ __('Use Template') }}
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                <p class="text-sm text-blue-800 mb-4">{{ __('Please log in to use this template.') }}</p>
                <a href="{{ route('login') }}"
                    class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    {{ __('Log In') }}
                </a>
            </div>
        @endauth
    </div>
</div>
@endsection


