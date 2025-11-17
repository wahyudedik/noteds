@extends('layouts.app')

@section('title', __('messages.ecosystem_creative') . ' — ' . __('messages.elements_unlimited'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-10">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900">🌟 {{ __('messages.ecosystem_creative') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('messages.ecosystem_explore') }}</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-2xl font-semibold text-slate-900">{{ __('messages.elements_unlimited') }}</h2>
            <p class="mt-2 text-slate-600">{{ __('messages.elements_description') }}</p>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-900">{{ __('messages.elements_what_you_get') }}</h3>
                    <ul class="mt-3 space-y-2 text-slate-700 list-disc list-inside">
                        <li>{{ __('messages.elements_unlimited_access') }}</li>
                        <li>{{ __('messages.elements_commercial_license') }}</li>
                        <li>{{ __('messages.elements_regular_updates') }}</li>
                        <li>{{ __('messages.elements_curated_content') }}</li>
                    </ul>
                </div>
                <div class="rounded-xl border border-slate-200 p-5">
                    <h3 class="font-semibold text-slate-900">{{ __('messages.elements_popular_categories') }}</h3>
                    <ul class="mt-3 space-y-2 text-slate-700 list-disc list-inside">
                        <li>{{ __('messages.elements_doc_templates') }}</li>
                        <li>{{ __('messages.elements_graphics_assets') }}</li>
                        <li>{{ __('messages.elements_audio_assets') }}</li>
                        <li>{{ __('messages.elements_video_assets') }}</li>
                    </ul>
                </div>
            </div>

            {{-- Subscription removed - all features are now free --}}
            {{-- <div class="mt-6">
                <a href="{{ route('subscription.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md shadow-sm">
                    {{ __('messages.start_elements_subscription') }}
                </a>
            </div> --}}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.audiojungle') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.audiojungle_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_intro_jingles') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_sfx_ui') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.codecanyon') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.codecanyon_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'codecanyon']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_laravel_snippets') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'codecanyon']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_wordpress_plugins') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.graphicriver') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.graphicriver_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'graphicriver']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_logo_templates') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'graphicriver']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_social_media_packs') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.photodune') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.photodune_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'photodune']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_business_office') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'photodune']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_nature_landscape') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.themeforest') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.themeforest_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'themeforest']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_wordpress_themes') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'themeforest']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_landing_pages') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.videohive') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.videohive_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'videohive']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_logo_reveals') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'videohive']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_titles_lower_thirds') }}
                    </a>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl p-8 md:col-span-2">
                <h3 class="text-xl font-semibold text-slate-900">{{ __('messages.3docean') }}</h3>
                <p class="mt-2 text-slate-600">{{ __('messages.3docean_description') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('marketplace.index', ['ecosystem' => '3docean']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_product_mockups_3d') }}
                    </a>
                    <a href="{{ route('marketplace.index', ['ecosystem' => '3docean']) }}" class="block p-4 rounded-lg border border-slate-200 hover:border-blue-300 hover:shadow">
                        {{ __('messages.collection_architecture_assets') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


