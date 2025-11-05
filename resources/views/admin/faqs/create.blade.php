@extends('layouts.app')

@section('title', __('messages.admin_create_faq'))

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.add_new_faq') }}</h2>
            <a href="{{ route('admin.faqs.index') }}" class="text-gray-600 hover:text-gray-800">← {{ __('messages.back_to_faqs') }}</a>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.faqs.store') }}" method="POST">
                @csrf

                <!-- Question -->
                <div class="mb-6">
                    <label for="question" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.question') }} <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                        id="question"
                        name="question"
                        value="{{ old('question') }}"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('question') border-red-500 @enderror">
                    @error('question')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Answer -->
                <div class="mb-6">
                    <label for="answer" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.answer') }} <span class="text-red-600">*</span>
                    </label>
                    <textarea 
                        id="answer"
                        name="answer"
                        rows="8"
                        required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('answer') border-red-500 @enderror">{{ old('answer') }}</textarea>
                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.use_new_lines_for_formatting') }}</p>
                    @error('answer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order -->
                <div class="mb-6">
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.display_order') }}
                    </label>
                    <input type="number" 
                        id="order"
                        name="order"
                        value="{{ old('order', 0) }}"
                        min="0"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-sm text-gray-500">{{ __('messages.lower_numbers_appear_first') }}</p>
                    @error('order')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Is Active -->
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                            name="is_active"
                            value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">{{ __('messages.active_visible_public_faq') }}</span>
                    </label>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.faqs.index') }}" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-md transition-colors duration-200">
                        {{ __('messages.add_new_faq') }}
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
@endsection

