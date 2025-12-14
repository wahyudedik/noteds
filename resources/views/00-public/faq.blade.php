@extends('40-shared/layouts/app')

@section('title', __('messages.frequently_asked_questions'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">{{ __('messages.faq') }}</h1>
            <p class="text-xl text-gray-600">
                {{ __('messages.find_answers') }}
            </p>
        </div>

        <!-- FAQ Accordion -->
        @if($faqs->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-gray-900">{{ __('messages.no_faqs_yet') }}</h3>
                <p class="mt-2 text-gray-600">{{ __('messages.check_back_later') }}</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($faqs as $faq)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden" x-data="{ open: false }">
                        <button @click="open = !open" class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <h3 class="text-lg font-semibold text-gray-900 text-left">{{ $faq->question }}</h3>
                            <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div x-show="open" x-transition class="px-6 pb-4 border-t border-gray-200">
                            <div class="mt-4 text-gray-700 prose max-w-none">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Still Have Questions CTA -->
        <div class="mt-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-center text-white">
            <h2 class="text-2xl font-bold mb-4">{{ __('messages.still_have_questions') }}</h2>
            <p class="text-blue-100 mb-6">{{ __('messages.cant_find_what_youre_looking_for') }}</p>
            <a href="{{ route('contact.index') }}" 
                class="inline-flex items-center px-6 py-3 bg-white text-blue-600 font-bold rounded-lg hover:bg-gray-100 transition-all duration-200 shadow-lg">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ __('messages.contact_support') }}
            </a>
        </div>

    </div>
</div>
@endsection

