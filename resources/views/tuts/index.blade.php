@extends('layouts.app')

@section('title', __('messages.tuts') . ' — ' . __('messages.education_creative_coding'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('messages.tuts') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('messages.tuts_description') ?? 'Platform edukasi berisi tutorial & kursus tentang desain, coding, fotografi, dan kreativitas digital.' }}</p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('messages.categories') }}</h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li>{{ __('messages.tuts_category_design') ?? 'Desain grafis, UI/UX' }}</li>
                <li>{{ __('messages.tuts_category_web') ?? 'Web dev & backend (PHP, JS, Laravel, dsb.)' }}</li>
                <li>{{ __('messages.tuts_category_photo') ?? 'Fotografi & video editing' }}</li>
                <li>{{ __('messages.tuts_category_business') ?? 'Productivity & creative business' }}</li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('subscription.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">
                    {{ __('messages.access_with_premium') ?? 'Akses dengan Premium' }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection


