@extends('layouts.guest')

@section('title', $link->name ?: __('affiliate.affiliate_landing_page'))

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-12">
        @if($link->landing_page_content)
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8">
                <div class="prose dark:prose-invert max-w-none">
                    {!! $link->landing_page_content !!}
                </div>
                
                <div class="mt-8 text-center">
                    <a href="{{ route('marketplace.index', ['ref' => $link->code]) }}" 
                        class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors">
                        {{ __('affiliate.visit_marketplace') }}
                    </a>
                </div>
            </div>
        @else
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ __('affiliate.welcome') }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300 mb-8">
                    {{ __('affiliate.landing_page_default_message') }}
                </p>
                <a href="{{ route('marketplace.index', ['ref' => $link->code]) }}" 
                    class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-lg transition-colors">
                    {{ __('affiliate.visit_marketplace') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

