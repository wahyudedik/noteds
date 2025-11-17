@extends('layouts.app')

@section('title', __('messages.studio_title'))

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('messages.studio') }}</h1>
            <p class="mt-3 text-slate-600">{{ __('messages.studio_description') }}</p>
        </div>
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h2 class="text-xl font-semibold text-slate-900">{{ __('messages.example_services') }}</h2>
            <ul class="mt-3 list-disc list-inside text-slate-700 space-y-2">
                <li>{{ __('messages.service_logo_design') }}</li>
                <li>{{ __('messages.service_video_editing') }}</li>
                <li>{{ __('messages.service_web_development') }}</li>
                <li>{{ __('messages.service_voice_over') }}</li>
            </ul>
            <div class="mt-6">
                <a href="{{ route('studio.orders.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md">{{ __('messages.create_order') }}</a>
                <a href="{{ route('studio.orders.index') }}" class="inline-flex items-center px-4 py-2 ml-2 border rounded-md">{{ __('messages.my_orders') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection


