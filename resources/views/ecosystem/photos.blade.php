@extends('layouts.app')

@section('title', __('messages.photodune') . ' — ' . __('messages.stock_photography'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('messages.photodune') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('messages.photodune_description') }}</p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('messages.use_cases') }}</h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li>{{ __('messages.photos_use_website') }}</li>
                <li>{{ __('messages.photos_use_promo') }}</li>
                <li>{{ __('messages.photos_use_presentation') }}</li>
            </ul>
            <div class="mt-6">
                {{-- Subscription removed - all features are now free --}}
                {{-- <a href="{{ route('subscription.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">{{ __('messages.subscribe_photos_access') }}</a> --}}
            </div>
        </div>
    </div>
</div>
@endsection


