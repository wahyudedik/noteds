@extends('layouts.app')

@section('title', __('Create Bundle'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('bundles.index') }}"
                class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                {{ __('Back to Bundles') }}
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ __('Create Bundle') }}</h1>
        </div>

        <form action="{{ route('bundles.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Bundle Title') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="title" id="title" required value="{{ old('title') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Description') }}
                </label>
                <textarea name="description" id="description" rows="4"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Price -->
            <div class="mb-6">
                <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Bundle Price') }} <span class="text-red-500">*</span>
                </label>
                <input type="number" name="price" id="price" required min="0" step="0.01" value="{{ old('price') }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('price') border-red-500 @enderror">
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Discount Percentage -->
            <div class="mb-6">
                <label for="discount_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Discount Percentage') }} (%)
                </label>
                <input type="number" name="discount_percentage" id="discount_percentage" min="0" max="100" step="0.01" value="{{ old('discount_percentage', 0) }}"
                    class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-xs text-gray-500">{{ __('Discount percentage compared to total original price') }}</p>
            </div>

            <!-- Notes Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ __('Select Notes') }} <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-3">{{ __('Select at least 2 notes to create a bundle') }}</p>
                
                @if($userNotes->count() > 0)
                    <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                        <div class="space-y-2">
                            @foreach($userNotes as $note)
                                <label class="flex items-start p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="checkbox" name="note_ids[]" value="{{ $note->id }}"
                                        class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <div class="ml-3 flex-1">
                                        <div class="font-medium text-gray-900">{{ $note->title }}</div>
                                        <div class="text-sm text-gray-500 mt-1">
                                        {{ currency($note->price) }}
                                        @if($note->discount_price && $note->discount_price < $note->price)
                                            <span class="text-red-600">({{ currency($note->discount_price) }})</span>
                                        @endif
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @error('note_ids')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                @else
                    <div class="border border-gray-300 rounded-lg p-8 text-center">
                        <p class="text-sm text-gray-500">{{ __('You do not have any public notes yet.') }}</p>
                        <a href="{{ route('notes.create') }}" class="mt-2 inline-block text-sm text-blue-600 hover:text-blue-800">
                            {{ __('Create a note first') }}
                        </a>
                    </div>
                @endif
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('bundles.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
                <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    {{ __('Create Bundle') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

