@extends('layouts.app')

@section('title', __('messages.create_service_order'))

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-2xl p-8">
            <h1 class="text-2xl font-bold text-slate-900 mb-6">{{ __('messages.create_service_order') }}</h1>
            <form action="{{ route('studio.orders.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.order_title') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-lg border-gray-300">
                    @error('title')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.order_description') }}</label>
                    <textarea name="description" rows="6" required class="w-full rounded-lg border-gray-300" placeholder="{{ __('messages.order_description_placeholder') }}">{{ old('description') }}</textarea>
                    @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.order_budget') }} ({{ __('messages.optional') }})</label>
                    <input type="number" name="budget" value="{{ old('budget', 0) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300">
                    @error('budget')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('studio.orders.index') }}" class="px-4 py-2 rounded-md border">{{ __('messages.cancel') }}</a>
                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white">{{ __('messages.submit_brief') }}</button>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection


