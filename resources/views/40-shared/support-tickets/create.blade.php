@extends('40-shared/layouts/app')

@section('title', __('messages.create_support_ticket'))

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-center mb-2">
                <a href="{{ route('support-tickets.index') }}" class="text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.create_support_ticket') }}</h1>
            </div>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.report_issue_request_assistance') }}</p>
        </div>

        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
            <form action="{{ route('support-tickets.store') }}" method="POST" class="p-6">
                @csrf

                <!-- Title -->
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.title') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required
                        placeholder="{{ __('messages.brief_description_issue') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Priority -->
                <div class="mb-6" x-data="{ priority: '{{ old('priority', 'low') }}' }">
                    <label for="priority" class="flex items-center gap-2 text-sm font-semibold text-blue-700 mb-2">
                        <span class="inline-flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />
                                </svg>
                            </span>
                            <span>{{ __('messages.priority') }}</span>
                        </span>
                        <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide text-red-600">
                            Required
                        </span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label
                            class="relative flex cursor-pointer items-center justify-center rounded-xl border-2 p-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1"
                            :class="priority === 'low'
                                ? 'border-blue-400 bg-blue-50 text-blue-700 shadow-sm focus:ring-blue-200'
                                : 'border-gray-200 text-gray-600 hover:border-blue-200 hover:text-blue-600'">
                            <input type="radio" name="priority" value="low" x-model="priority" required class="sr-only">
                            <span>{{ __('messages.low') }}</span>
                        </label>
                        <label
                            class="relative flex cursor-pointer items-center justify-center rounded-xl border-2 p-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1"
                            :class="priority === 'medium'
                                ? 'border-amber-400 bg-amber-50 text-amber-700 shadow-sm focus:ring-amber-200'
                                : 'border-gray-200 text-gray-600 hover:border-amber-200 hover:text-amber-600'">
                            <input type="radio" name="priority" value="medium" x-model="priority" required class="sr-only">
                            <span>{{ __('messages.medium') }}</span>
                        </label>
                        <label
                            class="relative flex cursor-pointer items-center justify-center rounded-xl border-2 p-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1"
                            :class="priority === 'high'
                                ? 'border-orange-400 bg-orange-50 text-orange-700 shadow-sm focus:ring-orange-200'
                                : 'border-gray-200 text-gray-600 hover:border-orange-200 hover:text-orange-600'">
                            <input type="radio" name="priority" value="high" x-model="priority" required class="sr-only">
                            <span>{{ __('messages.high') }}</span>
                        </label>
                        <label
                            class="relative flex cursor-pointer items-center justify-center rounded-xl border-2 p-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-1"
                            :class="priority === 'urgent'
                                ? 'border-red-500 bg-red-50 text-red-700 shadow-sm focus:ring-red-200'
                                : 'border-gray-200 text-gray-600 hover:border-red-200 hover:text-red-600'">
                            <input type="radio" name="priority" value="urgent" x-model="priority" required class="sr-only">
                            <span>{{ __('messages.urgent') }}</span>
                        </label>
                    </div>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @if(auth()->user()->hasPremium())
                        <div class="mt-3 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-yellow-800 mb-1">⭐ Premium Priority Support</p>
                                    <p class="text-xs text-yellow-700">
                                        Your ticket priority will be automatically upgraded: Low → Medium, Medium → High, High → Urgent. 
                                        Premium tickets are prioritized in the admin queue for faster response.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.description') }} <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="8" required
                        placeholder="{{ __('messages.provide_detailed_information') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.minimum_characters_detailed') }}</p>
                </div>

                <!-- Links -->
                <div class="mb-6">
                    <label for="links" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.related_links_optional') }}
                    </label>
                    <input type="text" name="links" id="links" value="{{ old('links') }}"
                        placeholder="{{ __('messages.paste_urls_separated') }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 transition-all duration-200">
                    @error('links')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.add_relevant_links') }}</p>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('support-tickets.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        {{ __('messages.create_ticket') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


