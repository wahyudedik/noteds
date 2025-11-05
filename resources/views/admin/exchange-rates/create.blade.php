@extends('layouts.app')

@section('title', __('messages.admin_create_exchange_rate'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.admin_create_exchange_rate') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.add_new_exchange_rate') }}</p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-6">
            <form action="{{ route('admin.exchange-rates.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="from_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.from_currency') }}</label>
                        <select name="from_currency" id="from_currency" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('messages.select_currency') }}</option>
                            <option value="IDR" {{ old('from_currency') === 'IDR' ? 'selected' : '' }}>{{ __('messages.idr_indonesian_rupiah') }}</option>
                            <option value="USD" {{ old('from_currency') === 'USD' ? 'selected' : '' }}>{{ __('messages.usd_us_dollar') }}</option>
                        </select>
                        @error('from_currency')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="to_currency" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.to_currency') }}</label>
                        <select name="to_currency" id="to_currency" required class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                            <option value="">{{ __('messages.select_currency') }}</option>
                            <option value="IDR" {{ old('to_currency') === 'IDR' ? 'selected' : '' }}>{{ __('messages.idr_indonesian_rupiah') }}</option>
                            <option value="USD" {{ old('to_currency') === 'USD' ? 'selected' : '' }}>{{ __('messages.usd_us_dollar') }}</option>
                        </select>
                        @error('to_currency')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rate" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.exchange_rate') }}</label>
                        <input type="number" name="rate" id="rate" step="0.0001" min="0.0001" required value="{{ old('rate') }}" 
                            :placeholder="__('messages.rate_example')"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-2 text-xs text-gray-500">{{ __('messages.enter_rate_convert') }}</p>
                        @error('rate')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">{{ __('messages.active') }}</span>
                        </label>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.notes_optional') }}</label>
                        <textarea name="notes" id="notes" rows="3" 
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('admin.exchange-rates.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('messages.create_exchange_rate') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

