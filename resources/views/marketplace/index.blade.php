@extends('layouts.app')

@section('title', __('messages.marketplace'))

@section('content')
    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.marketplace') }}</h1>
                    <p class="mt-2 text-base text-gray-600">{{ __('messages.discover_purchase_notes') }}</p>
                </div>

                <!-- Share Marketplace Button -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600">{{ __('messages.share_marketplace') }}</span>
                    @php
                        $marketplaceUrl = route('marketplace.index');
                        $marketplaceTitle = urlencode(
                            __('messages.marketplace_share_title', ['app' => config('app.name')]),
                        );
                    @endphp

                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($marketplaceUrl) }}" target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                        title="{{ __('messages.share_on_facebook') }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                        </svg>
                    </a>

                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($marketplaceUrl) }}&text={{ $marketplaceTitle }}"
                        target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors duration-200"
                        title="{{ __('messages.share_on_twitter') }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z" />
                        </svg>
                    </a>

                    <a href="https://wa.me/?text={{ $marketplaceTitle }}%20{{ urlencode($marketplaceUrl) }}" target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors duration-200"
                        title="{{ __('messages.share_on_whatsapp') }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                        </svg>
                    </a>

                    <button onclick="copyToClipboard('{{ $marketplaceUrl }}')"
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors duration-200"
                        title="{{ __('messages.copy_link') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Search and Filter Form -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8" x-data="{
                showAdvancedFilters: false,
                selectedTags: @js(request('tags', [])),
                selectedLanguages: @js(request('languages', request('language') ? [request('language')] : [])),
                selectedFileTypes: @js(request('file_type', [])),
                priceRange: [{{ request('min_price', 0) }}, {{ request('max_price', 999999) }}],
                minPrice: {{ request('min_price', 0) }},
                maxPrice: {{ request('max_price', 999999) }},
                allTags: @js($tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name, 'count' => $tag->notes_count])->toArray()),
                filterTags: '',
                get filteredTags() {
                    if (!this.filterTags) return this.allTags;
                    return this.allTags.filter(tag => tag.name.toLowerCase().includes(this.filterTags));
                }
            }">
                <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-4" id="filterForm">
                    <!-- Basic Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <div class="relative">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                {{ __('messages.search_title') }}
                                <span class="text-xs text-gray-500 ml-1">(Supports AND, OR, NOT)</span>
                            </label>
                            <div class="relative">
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    placeholder="e.g., Laravel AND authentication OR 'user login' NOT admin"
                                    autocomplete="off"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                <div id="search-autocomplete"
                                    class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-96 overflow-y-auto">
                                    <!-- Autocomplete results will be inserted here -->
                                </div>
                                @auth
                                    <!-- Search History & Saved Searches -->
                                    <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1">
                                        @if (isset($searchHistory) && $searchHistory->count() > 0)
                                            <div class="relative" x-data="{ open: false }">
                                                <button type="button" @click="open = !open"
                                                    class="p-1 text-gray-400 hover:text-gray-600" title="Search History">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-cloak
                                                    class="absolute right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
                                                    <div class="p-2">
                                                        <div class="text-xs font-semibold text-gray-700 mb-2 px-2">Recent
                                                            Searches</div>
                                                        @foreach ($searchHistory as $history)
                                                            <a href="{{ route('marketplace.index', array_merge($history->filters ?? [], ['search' => $history->query])) }}"
                                                                class="block px-2 py-1.5 text-sm text-gray-700 hover:bg-gray-50 rounded">
                                                                <div class="font-medium truncate">
                                                                    {{ $history->query ?: 'Filters only' }}</div>
                                                                <div class="text-xs text-gray-500">{{ $history->result_count }}
                                                                    results • {{ $history->searched_at->diffForHumans() }}
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        @if (isset($savedSearches) && $savedSearches->count() > 0)
                                            <div class="relative" x-data="{ open: false }">
                                                <button type="button" @click="open = !open"
                                                    class="p-1 text-gray-400 hover:text-gray-600" title="Saved Searches">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                                    </svg>
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-cloak
                                                    class="absolute right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto">
                                                    <div class="p-2">
                                                        <div class="text-xs font-semibold text-gray-700 mb-2 px-2">Saved
                                                            Searches</div>
                                                        @foreach ($savedSearches as $saved)
                                                            <div
                                                                class="flex items-center justify-between px-2 py-1.5 hover:bg-gray-50 rounded group">
                                                                <a href="{{ route('marketplace.index', array_merge($saved->filters ?? [], ['search' => $saved->query])) }}"
                                                                    class="flex-1 text-sm text-gray-700">
                                                                    <div class="font-medium truncate">{{ $saved->name }}
                                                                    </div>
                                                                    <div class="text-xs text-gray-500 truncate">
                                                                        {{ $saved->query ?: 'Filters only' }}</div>
                                                                </a>
                                                                <button type="button"
                                                                    onclick="deleteSavedSearch('{{ $saved->id }}')"
                                                                    class="ml-2 p-1 text-gray-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition">
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                                        viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endauth
                            </div>
                        </div>
                        <!-- Tags Multi-select -->
                        <div>
                            <label for="tags"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.tag') }}
                                (Multi-select)</label>
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" @click="open = !open"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 bg-white text-left px-3 py-2 flex items-center justify-between">
                                    <span class="text-sm text-gray-700"
                                        x-text="selectedTags.length > 0 ? selectedTags.length + ' tags selected' : 'All tags'"></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 space-y-1">
                                        <input type="text" placeholder="Search tags..."
                                            @input="filterTags = $event.target.value.toLowerCase()"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                        <template x-for="tag in filteredTags" :key="tag.id">
                                            <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                                <input type="checkbox" :value="tag.id" x-model="selectedTags"
                                                    :name="'tags[]'"
                                                    class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <span class="text-sm text-gray-700"
                                                    x-text="tag.name + ' (' + tag.count + ')'"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                                <!-- Hidden inputs for selected tags -->
                                <template x-for="tagId in selectedTags" :key="tagId">
                                    <input type="hidden" name="tags[]" :value="tagId">
                                </template>
                            </div>
                        </div>
                        <div>
                            <label for="ecosystem"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.ecosystem') }}</label>
                            @php
                                $ecosystems = [
                                    '' => __('messages.all_ecosystem'),
                                    'design' => __('messages.design'),
                                    'code' => __('messages.code'),
                                    'photo' => __('messages.photo'),
                                    'audio' => __('messages.audio'),
                                    'video' => __('messages.video'),
                                    'theme' => __('messages.theme'),
                                    '3d' => '3D',
                                    'elements' => __('messages.elements'),
                                ];
                            @endphp
                            <select name="ecosystem" id="ecosystem"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                @foreach ($ecosystems as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ request('ecosystem') === $value ? 'selected' : '' }}>
                                        {{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Language Multi-select -->
                        <div>
                            <label for="languages" class="block text-sm font-medium text-gray-700 mb-2">Bahasa
                                (Multi-select)</label>
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" @click="open = !open"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 bg-white text-left px-3 py-2 flex items-center justify-between">
                                    <span class="text-sm text-gray-700"
                                        x-text="selectedLanguages.length > 0 ? selectedLanguages.length + ' languages' : 'All languages'"></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg">
                                    <div class="p-2 space-y-1">
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="en" x-model="selectedLanguages"
                                                name="languages[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">English</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="id" x-model="selectedLanguages"
                                                name="languages[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Bahasa Indonesia</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="ar" x-model="selectedLanguages"
                                                name="languages[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">العربية</span>
                                        </label>
                                    </div>
                                </div>
                                <template x-for="lang in selectedLanguages" :key="lang">
                                    <input type="hidden" name="languages[]" :value="lang">
                                </template>
                                <!-- Keep single language input for backward compatibility -->
                                <input type="hidden" name="language"
                                    x-bind:value="selectedLanguages.length === 1 ? selectedLanguages[0] : ''">
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Filters Row -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Author Search -->
                        <div>
                            <label for="author" class="block text-sm font-medium text-gray-700 mb-2">Search by
                                Author</label>
                            <input type="text" name="author" id="author" value="{{ request('author') }}"
                                placeholder="Author name or username"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        </div>
                        <div>
                            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                        </div>

                        <!-- Price Range Slider -->
                        <div class="col-span-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price Range</label>
                            <div class="flex items-center gap-4">
                                <input type="number" name="min_price" x-model="minPrice"
                                    @input="priceRange[0] = parseInt($event.target.value) || 0" placeholder="Min"
                                    min="0" step="0.01"
                                    class="w-32 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <input type="range" x-model="priceRange[0]" @input="minPrice = $event.target.value"
                                    min="0" max="1000000" step="1000" class="flex-1">
                                <span class="text-sm text-gray-600"
                                    x-text="'Rp ' + (minPrice || 0).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="flex items-center gap-4 mt-2">
                                <input type="number" name="max_price" x-model="maxPrice"
                                    @input="priceRange[1] = parseInt($event.target.value) || 999999" placeholder="Max"
                                    min="0" step="0.01"
                                    class="w-32 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                                <input type="range" x-model="priceRange[1]" @input="maxPrice = $event.target.value"
                                    min="0" max="1000000" step="1000" class="flex-1">
                                <span class="text-sm text-gray-600"
                                    x-text="'Rp ' + (maxPrice || 999999).toLocaleString('id-ID')"></span>
                            </div>
                        </div>

                        <!-- Rating Filter -->
                        <div>
                            <label for="min_rating" class="block text-sm font-medium text-gray-700 mb-2">Minimum
                                Rating</label>
                            <select name="min_rating" id="min_rating"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                <option value="">All ratings</option>
                                <option value="4.5" {{ request('min_rating') == '4.5' ? 'selected' : '' }}>4.5+ stars
                                </option>
                                <option value="4" {{ request('min_rating') == '4' ? 'selected' : '' }}>4+ stars
                                </option>
                                <option value="3" {{ request('min_rating') == '3' ? 'selected' : '' }}>3+ stars
                                </option>
                                <option value="2" {{ request('min_rating') == '2' ? 'selected' : '' }}>2+ stars
                                </option>
                                <option value="1" {{ request('min_rating') == '1' ? 'selected' : '' }}>1+ stars
                                </option>
                            </select>
                        </div>

                        <!-- Seller Verified Filter -->
                        <div>
                            <label for="seller_verified" class="block text-sm font-medium text-gray-700 mb-2">Seller
                                Status</label>
                            <select name="seller_verified" id="seller_verified"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                <option value="">All sellers</option>
                                <option value="1" {{ request('seller_verified') == '1' ? 'selected' : '' }}>Verified
                                    sellers only</option>
                            </select>
                        </div>

                        <!-- Seller Type Filter -->
                        <div>
                            <label for="seller_type" class="block text-sm font-medium text-gray-700 mb-2">Seller
                                Type</label>
                            <select name="seller_type" id="seller_type"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                <option value="">All sellers</option>
                                <option value="top_rated" {{ request('seller_type') == 'top_rated' ? 'selected' : '' }}>
                                    Top-rated sellers (4.5+ stars)</option>
                            </select>
                        </div>

                        <!-- File Type Filter -->
                        <div>
                            <label for="file_type" class="block text-sm font-medium text-gray-700 mb-2">File Type</label>
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" @click="open = !open"
                                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200 bg-white text-left px-3 py-2 flex items-center justify-between">
                                    <span class="text-sm text-gray-700"
                                        x-text="selectedFileTypes.length > 0 ? selectedFileTypes.length + ' types' : 'All file types'"></span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" @click.away="open = false" x-cloak
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    <div class="p-2 space-y-1">
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="pdf" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">PDF</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="doc" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">DOC / DOCX</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="zip" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">ZIP / RAR</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="image" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Images</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="video" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Videos</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="audio" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Audio</span>
                                        </label>
                                        <label class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" value="code" x-model="selectedFileTypes"
                                                name="file_type[]"
                                                class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                            <span class="text-sm text-gray-700">Code</span>
                                        </label>
                                    </div>
                                </div>
                                <template x-for="fileType in selectedFileTypes" :key="fileType">
                                    <input type="hidden" name="file_type[]" :value="fileType">
                                </template>
                            </div>
                        </div>
                        <div>
                            <label for="sort"
                                class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.sort_by') }}</label>
                            <select name="sort" id="sort"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>
                                    {{ __('messages.sort_newest') }}</option>
                                <option value="trending" {{ request('sort') == 'trending' ? 'selected' : '' }}>🔥 Trending
                                    (7
                                    hari terakhir)</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>⭐ Popular
                                    (Terlaris)</option>
                                <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>
                                    {{ __('messages.sort_highest_rated') }}</option>
                                <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>
                                    {{ __('messages.sort_price_low_high') }}</option>
                                <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>
                                    {{ __('messages.sort_price_high_low') }}</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>
                                    {{ __('messages.sort_oldest') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            @auth
                                <button type="button" onclick="saveCurrentSearch()"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                    </svg>
                                    Save Search
                                </button>
                            @endauth
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                {{ __('messages.filter') }}
                            </button>
                            @if (request()->hasAny([
                                    'search',
                                    'tag',
                                    'tags',
                                    'min_price',
                                    'max_price',
                                    'sort',
                                    'ecosystem',
                                    'language',
                                    'languages',
                                    'min_rating',
                                    'seller_verified',
                                    'seller_type',
                                    'file_type',
                                    'author',
                                    'date_from',
                                    'date_to',
                                ]))
                                <a href="{{ route('marketplace.index') }}"
                                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                    {{ __('messages.clear') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <!-- Featured Banner -->
            @if (isset($featuredBanner) && $featuredBanner)
                <div class="mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <span
                                class="inline-block px-3 py-1 bg-white rounded-full text-xs font-semibold text-orange-600 mb-2">⭐
                                {{ __('messages.featured_note') }}</span>
                            <h3 class="text-2xl font-bold text-white mb-2">
                                <a href="{{ route('marketplace.show', $featuredBanner->note) }}"
                                    class="hover:underline featured-click-tracking"
                                    data-featured-id="{{ $featuredBanner->id }}">
                                    {{ $featuredBanner->note->title }}
                                </a>
                            </h3>
                            <p class="text-white/90 mb-3">
                                {{ Str::limit($featuredBanner->note->summary ?? strip_tags($featuredBanner->note->content), 100) }}
                            </p>
                            <div class="flex items-center gap-4">
                                @if ($featuredBanner->note->price > 0)
                                    <span
                                        class="text-white font-semibold">{{ currency($featuredBanner->note->price) }}</span>
                                @else
                                    <span class="text-white font-semibold">{{ __('messages.free') }}</span>
                                @endif
                                <a href="{{ route('marketplace.show', $featuredBanner->note) }}"
                                    class="px-4 py-2 bg-white text-orange-600 font-semibold rounded-lg hover:bg-orange-50 transition featured-click-tracking"
                                    data-featured-id="{{ $featuredBanner->id }}">
                                    {{ __('messages.view_note') }} →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Featured Notes Grid -->
            @if (isset($featuredNotes) && $featuredNotes->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">⭐ {{ __('messages.featured_notes') }}</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($featuredNotes as $featured)
                            @php
                                $note = $featured->note;
                            @endphp
                            <div
                                class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-yellow-400 hover:shadow-xl hover:border-yellow-500 transition-all duration-200 group relative">
                                <!-- Featured Badge -->
                                <div class="absolute top-2 right-2 z-10 flex flex-col gap-1">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-400 text-yellow-900">
                                        ⭐ {{ __('messages.featured_note') }}
                                    </span>
                                    <!-- Viral/Hot Badge -->
                                    @if ($note->isViral() || $note->isHot())
                                        @if ($note->isViral())
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-red-500 to-pink-600 text-white shadow-lg animate-pulse">
                                                🔥 VIRAL
                                            </span>
                                        @endif
                                        @if ($note->isHot())
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                                                🔥 HOT
                                            </span>
                                        @endif
                                    @endif
                                </div>
                                <!-- Thumbnail -->
                                @if ($note->hasThumbnails())
                                    <div class="h-48 overflow-hidden bg-gray-100">
                                        <img src="{{ Storage::url($note->thumbnails[0]) }}" alt="{{ $note->title }}"
                                            loading="lazy"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                @endif
                                <div class="p-6">
                                    <!-- Title and Content -->
                                    <div class="mb-4">
                                        <h3
                                            class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                            <a href="{{ route('marketplace.show', $note) }}"
                                                class="featured-click-tracking" data-featured-id="{{ $featured->id }}">
                                                {{ $note->title }}
                                            </a>
                                        </h3>
                                        <p class="text-sm text-gray-600 line-clamp-3">
                                            {!! Str::limit(strip_tags($note->content), 120) !!}</p>
                                    </div>

                                    <!-- Tags -->
                                    @if ($note->tags->count() > 0)
                                        <div class="flex flex-wrap gap-2 mb-4">
                                            @foreach ($note->tags->take(3) as $tag)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $tag->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Price and Author -->
                                    <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                        <div>
                                            @if ($note->price > 0)
                                                @if ($note->hasDiscount())
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex flex-col">
                                                            <span
                                                                class="text-xs text-gray-500 line-through">{{ currency($note->price) }}</span>
                                                            <span
                                                                class="text-lg font-bold text-green-600">{{ currency($note->discount_price) }}</span>
                                                        </div>
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-500 text-white">
                                                            -{{ $note->discount_percent }}%
                                                        </span>
                                                    </div>
                                                @else
                                                    <span
                                                        class="text-lg font-bold text-green-600">{{ currency($note->price) }}</span>
                                                @endif
                                            @else
                                                <span
                                                    class="text-lg font-bold text-gray-600">{{ __('messages.free') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex flex-col">
                                            <a href="{{ route('public.profile.show', $note->user->username) }}"
                                                class="text-sm text-gray-600 hover:text-blue-600">
                                                {{ $note->user->name }}
                                            </a>
                                            @if ($note->user->badges->count() > 0)
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach ($note->user->badges->take(3) as $badge)
                                                        @php
                                                            $badgeColorClass = match ($badge->color) {
                                                                'gold', 'yellow' => 'text-yellow-600',
                                                                'green' => 'text-green-600',
                                                                'blue' => 'text-blue-600',
                                                                'purple' => 'text-purple-600',
                                                                'orange' => 'text-orange-600',
                                                                default => 'text-gray-600',
                                                            };
                                                        @endphp
                                                        <span
                                                            class="inline-flex items-center text-[10px] font-medium {{ $badgeColorClass }}"
                                                            title="{{ $badge->name }}">
                                                            @if ($badge->icon)
                                                                {{ $badge->icon }}
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if ($note->user->current_seller_level)
                                                @php
                                                    $sellerLevelClass = match (
                                                        $note->user->current_seller_level->color
                                                    ) {
                                                        'bronze'
                                                            => 'bg-gradient-to-r from-orange-600 to-amber-700 text-white',
                                                        'silver'
                                                            => 'bg-gradient-to-r from-gray-300 to-gray-400 text-gray-900',
                                                        'gold'
                                                            => 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white',
                                                        'platinum'
                                                            => 'bg-gradient-to-r from-gray-400 to-gray-600 text-white',
                                                        'diamond'
                                                            => 'bg-gradient-to-r from-cyan-400 to-blue-500 text-white',
                                                        default => 'bg-gray-100 text-gray-800',
                                                    };
                                                @endphp
                                                <div class="relative inline-block group mt-1">
                                                    <span
                                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $sellerLevelClass }}"
                                                        title="{{ $note->user->current_seller_level->name }}">
                                                        @if ($note->user->current_seller_level->icon)
                                                            <span
                                                                class="mr-0.5">{{ $note->user->current_seller_level->icon }}</span>
                                                        @endif
                                                        {{ $note->user->current_seller_level->name }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Regular Notes Section -->
            <div class="mb-4">
                <h2 class="text-xl font-bold text-gray-900">{{ __('messages.all_notes') }}</h2>
            </div>

            @if ($notes->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($notes as $note)
                        <div
                            class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group relative">
                            <!-- Viral/Hot Badge -->
                            @if ($note->isViral() || $note->isHot())
                                <div class="absolute top-2 left-2 z-10">
                                    @if ($note->isViral())
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-red-500 to-pink-600 text-white shadow-lg animate-pulse">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            🔥 VIRAL
                                        </span>
                                    @elseif($note->isHot())
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-orange-500 to-red-500 text-white shadow-lg">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.16-.85-.434-1.675-.82-2.45a5.549 5.549 0 00-5.8-2.13A4.5 4.5 0 001 6.477v6c0 1.968.785 3.747 2.05 5.043a4.5 4.5 0 006.95-1.95c0-.64-.13-1.25-.36-1.81a5.389 5.389 0 01-.22-3.68 4.5 4.5 0 00-1.88-2.547 2.5 2.5 0 01-1.32-2.88 1.5 1.5 0 00-1.14-1.86 1.5 1.5 0 00-1.12.12c-1.24.82-2.27 1.9-3.01 3.18-.75 1.3-1.23 2.78-1.23 4.38 0 1.56.48 3.03 1.23 4.33.74 1.28 1.77 2.36 3.01 3.18a1.5 1.5 0 001.12.12c.5-.07.93-.46 1.14-1.86.2-1.4.6-2.88 1.32-2.88.72 0 1.12 1.48 1.32 2.88.21 1.4.64 1.79 1.14 1.86a1.5 1.5 0 001.12-.12c1.24-.82 2.27-1.9 3.01-3.18.75-1.3 1.23-2.78 1.23-4.33 0-1.6-.48-3.08-1.23-4.38z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            🔥 HOT
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Thumbnail -->
                            @if ($note->hasThumbnails())
                                <div class="h-48 overflow-hidden bg-gray-100 relative">
                                    <img src="{{ Storage::url($note->thumbnails[0]) }}" alt="{{ $note->title }}"
                                        loading="lazy"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @endif
                            <div class="p-6">
                                <!-- Title and Content -->
                                <div class="mb-4">
                                    <h3
                                        class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                        <a href="{{ route('marketplace.show', $note) }}">{{ $note->title }}</a>
                                    </h3>
                                    <p class="text-sm text-gray-600 line-clamp-3">
                                        {!! Str::limit(strip_tags($note->content), 120) !!}</p>
                                </div>

                                <!-- Tags -->
                                @if ($note->tags->count() > 0)
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach ($note->tags->take(3) as $tag)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Badges and Meta -->
                                <div class="flex flex-wrap items-center gap-2 mb-4">
                                    @if ($note->sale_mode)
                                        @if ($note->isScarcityMode())
                                            <div class="relative inline-block group">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 cursor-help">
                                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Scarcity
                                                </span>
                                                <div
                                                    class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-56 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50">
                                                    <div class="font-semibold mb-1">Scarcity Mode</div>
                                                    <div class="text-gray-300 space-y-0.5">
                                                        <div>• One-time purchase</div>
                                                        <div>• Buyer bisa resell</div>
                                                        <div>• Creator dapat komisi</div>
                                                    </div>
                                                    <div
                                                        class="absolute left-2 top-full w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900">
                                                    </div>
                                                </div>
                                            </div>
                                        @elseif($note->isStandardMode())
                                            <div class="relative inline-block group">
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 cursor-help">
                                                    <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor"
                                                        viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Standard
                                                </span>
                                                <div
                                                    class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-56 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50">
                                                    <div class="font-semibold mb-1">Standard Mode</div>
                                                    <div class="text-gray-300 space-y-0.5">
                                                        <div>• Multiple sales</div>
                                                        <div>• Buyer tidak bisa resell</div>
                                                        <div>• Cocok untuk akses ulang</div>
                                                    </div>
                                                    <div
                                                        class="absolute left-2 top-full w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    @if ($note->average_rating > 0)
                                        <div class="flex items-center gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg class="w-3 h-3 {{ $i <= $note->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"
                                                    fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                            <span class="text-xs text-gray-600 ml-1">{{ $note->average_rating }}</span>
                                        </div>
                                    @endif
                                    @if ($note->price > 0)
                                        @if ($note->hasDiscount())
                                            <div
                                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100">
                                                <div class="flex flex-col items-end">
                                                    <span
                                                        class="text-gray-500 line-through text-[10px]">{{ currency($note->price) }}</span>
                                                    <span
                                                        class="text-yellow-800 font-semibold">{{ currency($note->discount_price) }}</span>
                                                </div>
                                                <span
                                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-500 text-white">
                                                    -{{ $note->discount_percent }}%
                                                </span>
                                            </div>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                {{ currency($note->price) }}
                                            </span>
                                        @endif
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ __('messages.free') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Author and Date -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <a href="{{ route('public.profile.show', $note->user->username) }}"
                                        class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200 group"
                                        title="{{ __('messages.view_all_notes_from', ['name' => $note->user->name]) }}">
                                        <div
                                            class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-2 group-hover:ring-2 group-hover:ring-blue-500 transition-all duration-200">
                                            @if ($note->user->avatar)
                                                @if (str_starts_with($note->user->avatar, 'http'))
                                                    <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}"
                                                        loading="lazy" class="w-6 h-6 rounded-full object-cover">
                                                @else
                                                    <img src="{{ Storage::url($note->user->avatar) }}"
                                                        alt="{{ $note->user->name }}" loading="lazy"
                                                        class="w-6 h-6 rounded-full object-cover">
                                                @endif
                                            @else
                                                <span
                                                    class="text-xs font-semibold text-gray-600">{{ substr($note->user->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="flex flex-col">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span
                                                        class="group-hover:text-blue-600">{{ $note->user->name }}</span>
                                                </div>
                                                @if ($note->user->badges && $note->user->badges->count() > 0)
                                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                                        @foreach ($note->user->badges->take(3) as $badge)
                                                            @php
                                                                $badgeColorClass = match ($badge->color) {
                                                                    'gold', 'yellow' => 'text-yellow-600',
                                                                    'green' => 'text-green-600',
                                                                    'blue' => 'text-blue-600',
                                                                    'purple' => 'text-purple-600',
                                                                    'orange' => 'text-orange-600',
                                                                    default => 'text-gray-600',
                                                                };
                                                            @endphp
                                                            <span
                                                                class="inline-flex items-center text-[10px] font-medium {{ $badgeColorClass }}"
                                                                title="{{ $badge->name }}">
                                                                @if ($badge->icon)
                                                                    {{ $badge->icon }}
                                                                @endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                @if ($note->user->current_seller_level)
                                                    @php
                                                        $sellerLevelClass = match (
                                                            $note->user->current_seller_level->color
                                                        ) {
                                                            'bronze'
                                                                => 'bg-gradient-to-r from-orange-600 to-amber-700 text-white',
                                                            'silver'
                                                                => 'bg-gradient-to-r from-gray-300 to-gray-400 text-gray-900',
                                                            'gold'
                                                                => 'bg-gradient-to-r from-yellow-400 to-orange-500 text-white',
                                                            'platinum'
                                                                => 'bg-gradient-to-r from-gray-400 to-gray-600 text-white',
                                                            'diamond'
                                                                => 'bg-gradient-to-r from-cyan-400 to-blue-500 text-white',
                                                            default => 'bg-gray-100 text-gray-800',
                                                        };
                                                    @endphp
                                                    <div class="relative inline-block group mt-0.5">
                                                        <span
                                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $sellerLevelClass }}"
                                                            title="{{ $note->user->current_seller_level->name }}">
                                                            @if ($note->user->current_seller_level->icon)
                                                                <span
                                                                    class="mr-0.5">{{ $note->user->current_seller_level->icon }}</span>
                                                            @endif
                                                            {{ $note->user->current_seller_level->name }}
                                                        </span>
                                                    </div>
                                                @endif
                                            </div>
                                            @if ($note->user->role === 'seller')
                                                <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24" title="{{ __('messages.seller') }}">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            @endif
                                        </div>
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
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_notes_found') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('messages.try_adjusting_criteria') }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Copy to clipboard function
        function copyToClipboard(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Link Copied!',
                            text: 'The link has been copied to your clipboard.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('{{ __('messages.link_copied') }}');
                    }
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                    fallbackCopyToClipboard(text);
                });
            } else {
                fallbackCopyToClipboard(text);
            }
        }

        function fallbackCopyToClipboard(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Link Copied!',
                        text: 'The link has been copied to your clipboard.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    alert('{{ __('messages.link_copied') }}');
                }
            } catch (err) {
                console.error('Fallback copy failed:', err);
                alert('Failed to copy link. Please copy manually.');
            }
            document.body.removeChild(textArea);
        }

        // Save current search
        function saveCurrentSearch() {
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const filters = {};

            // Collect all form data
            for (const [key, value] of formData.entries()) {
                if (filters[key]) {
                    if (Array.isArray(filters[key])) {
                        filters[key].push(value);
                    } else {
                        filters[key] = [filters[key], value];
                    }
                } else {
                    filters[key] = value;
                }
            }

            const searchQuery = formData.get('search') || '';
            const name = prompt('Enter a name for this saved search (optional):');

            if (name === null) return; // User cancelled

            fetch('{{ route('marketplace.save-search') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: name || 'Saved Search ' + new Date().toLocaleString(),
                        query: searchQuery,
                        ...filters
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Search Saved!',
                                text: 'Your search has been saved successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            alert('Search saved successfully!');
                        }
                        // Reload page to show updated saved searches
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    console.error('Error saving search:', error);
                    alert('Failed to save search. Please try again.');
                });
        }

        // Delete saved search
        function deleteSavedSearch(savedSearchId) {
            if (!confirm('Are you sure you want to delete this saved search?')) {
                return;
            }

            fetch(`/marketplace/saved-search/${savedSearchId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Saved search has been deleted.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        // Reload page to update saved searches list
                        setTimeout(() => location.reload(), 2000);
                    }
                })
                .catch(error => {
                    console.error('Error deleting saved search:', error);
                    alert('Failed to delete saved search. Please try again.');
                });
        }

        // Track clicks on featured notes
        document.addEventListener('DOMContentLoaded', function() {
            const featuredLinks = document.querySelectorAll('.featured-click-tracking');
            featuredLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const featuredId = this.getAttribute('data-featured-id');
                    if (featuredId) {
                        // Track click via AJAX
                        fetch(`/api/featured-notes/${featuredId}/click`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            }
                        }).catch(err => console.error('Failed to track click:', err));
                    }
                });
            });
        });
    </script>
@endpush
