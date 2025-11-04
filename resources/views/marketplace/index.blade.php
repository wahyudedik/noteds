@extends('layouts.app')

@section('title', __('messages.marketplace'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.marketplace') }}</h1>
            <p class="mt-2 text-base text-gray-600">{{ __('messages.discover_purchase_notes') }}</p>
        </div>

        <!-- Search and Filter Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('marketplace.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search_title') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" 
                        :placeholder="__('messages.search_notes')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                </div>
                <div>
                    <label for="tag" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.tag') }}</label>
                    <select name="tag" id="tag" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        <option value="">{{ __('messages.all_tags') }}</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                                {{ $tag->name }} ({{ $tag->notes_count }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="min_price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.min_price') }}</label>
                    <input type="number" name="min_price" id="min_price" value="{{ request('min_price') }}" 
                        placeholder="0" min="0" step="0.01"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                </div>
                <div>
                    <label for="max_price" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.max_price') }}</label>
                    <input type="number" name="max_price" id="max_price" value="{{ request('max_price') }}" 
                        placeholder="999999999" min="0" step="0.01"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('messages.filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'tag', 'min_price', 'max_price']))
                        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            {{ __('messages.clear') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($notes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($notes as $note)
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                        <div class="p-6">
                            <!-- Title and Content -->
                            <div class="mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                    <a href="{{ route('marketplace.show', $note) }}">{{ $note->title }}</a>
                                </h3>
                                <p class="text-sm text-gray-600 line-clamp-3">{!! Str::limit(strip_tags($note->content), 120) !!}</p>
                            </div>

                            <!-- Tags -->
                            @if($note->tags->count() > 0)
                                <div class="flex flex-wrap gap-2 mb-4">
                                    @foreach($note->tags->take(3) as $tag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Badges and Meta -->
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                @if($note->average_rating > 0)
                                    <div class="flex items-center gap-0.5">
                                        @for($i = 1; $i <= 5; $i++)
                                            <svg class="w-3 h-3 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        @endfor
                                        <span class="text-xs text-gray-600 ml-1">{{ $note->average_rating }}</span>
                                    </div>
                                @endif
                                @if($note->price > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 font-semibold">
                                        Rp {{ number_format($note->price, 0, ',', '.') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('messages.free') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Author and Date -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <a href="{{ route('public.profile.show', $note->user->username) }}" class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-2">
                                        @if($note->user->avatar)
                                            <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}" class="w-6 h-6 rounded-full object-cover">
                                        @else
                                            <span class="text-xs font-semibold text-gray-600">{{ substr($note->user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <span>{{ $note->user->name }}</span>
                                </a>
                                <span class="text-xs text-gray-500">{{ $note->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $notes->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_notes_found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('messages.try_adjusting_criteria') }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

