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
                    $marketplaceTitle = urlencode(__('messages.marketplace_share_title', ['app' => config('app.name')]));
                @endphp
                
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($marketplaceUrl) }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors duration-200"
                   title="{{ __('messages.share_on_facebook') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($marketplaceUrl) }}&text={{ $marketplaceTitle }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-sky-500 text-white hover:bg-sky-600 transition-colors duration-200"
                   title="{{ __('messages.share_on_twitter') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                    </svg>
                </a>
                
                <a href="https://wa.me/?text={{ $marketplaceTitle }}%20{{ urlencode($marketplaceUrl) }}" 
                   target="_blank" 
                   rel="noopener noreferrer"
                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-500 text-white hover:bg-green-600 transition-colors duration-200"
                   title="{{ __('messages.share_on_whatsapp') }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                    </svg>
                </a>
                
                <button onclick="copyToClipboard('{{ $marketplaceUrl }}')" 
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-600 text-white hover:bg-gray-700 transition-colors duration-200"
                        title="{{ __('messages.copy_link') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Search and Filter Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('marketplace.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.search_title') }}</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                            placeholder="{{ __('messages.search_notes') }}"
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
                        <label for="ecosystem" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.ecosystem') }}</label>
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
                        <select name="ecosystem" id="ecosystem" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                            @foreach($ecosystems as $value => $label)
                                <option value="{{ $value }}" {{ request('ecosystem') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="language" class="block text-sm font-medium text-gray-700 mb-2">Bahasa</label>
                        <select name="language" id="language" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                            <option value="">{{ __('messages.all') }}</option>
                            <option value="en" {{ request('language')==='en' ? 'selected' : '' }}>English</option>
                            <option value="id" {{ request('language')==='id' ? 'selected' : '' }}>Bahasa Indonesia</option>
                            <option value="ar" {{ request('language')==='ar' ? 'selected' : '' }}>العربية</option>
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
                    <div>
                        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.sort_by') }}</label>
                        <select name="sort" id="sort" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 transition-all duration-200">
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>{{ __('messages.sort_newest') }}</option>
                            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
                            <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>{{ __('messages.sort_price_low_high') }}</option>
                            <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>{{ __('messages.sort_price_high_low') }}</option>
                            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>{{ __('messages.sort_highest_rated') }}</option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm hover:shadow-md transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        {{ __('messages.filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'tag', 'min_price', 'max_price', 'sort', 'ecosystem', 'language']))
                        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            {{ __('messages.clear') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Featured Banner -->
        @if(isset($featuredBanner) && $featuredBanner)
            <div class="mb-8 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-6 shadow-lg">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <span class="inline-block px-3 py-1 bg-white rounded-full text-xs font-semibold text-orange-600 mb-2">⭐ {{ __('messages.featured_note') }}</span>
                        <h3 class="text-2xl font-bold text-white mb-2">
                            <a href="{{ route('marketplace.show', $featuredBanner->note) }}" 
                               class="hover:underline featured-click-tracking" 
                               data-featured-id="{{ $featuredBanner->id }}">
                                {{ $featuredBanner->note->title }}
                            </a>
                        </h3>
                        <p class="text-white/90 mb-3">{{ Str::limit($featuredBanner->note->summary ?? strip_tags($featuredBanner->note->content), 100) }}</p>
                        <div class="flex items-center gap-4">
                            @if($featuredBanner->note->price > 0)
                                <span class="text-white font-semibold">{{ currency($featuredBanner->note->price) }}</span>
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
        @if(isset($featuredNotes) && $featuredNotes->count() > 0)
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-900">⭐ {{ __('messages.featured_notes') }}</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($featuredNotes as $featured)
                        @php($note = $featured->note)
                        <div class="bg-white overflow-hidden shadow-lg rounded-lg border-2 border-yellow-400 hover:shadow-xl hover:border-yellow-500 transition-all duration-200 group relative">
                            <!-- Featured Badge -->
                            <div class="absolute top-2 right-2 z-10">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-400 text-yellow-900">
                                    ⭐ {{ __('messages.featured_note') }}
                                </span>
                            </div>
                            <!-- Thumbnail -->
                            @if($note->hasThumbnails())
                                <div class="h-48 overflow-hidden">
                                    <img src="{{ Storage::url($note->thumbnails[0]) }}" alt="{{ $note->title }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </div>
                            @endif
                            <div class="p-6">
                                <!-- Title and Content -->
                                <div class="mb-4">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors duration-200">
                                        <a href="{{ route('marketplace.show', $note) }}" 
                                           class="featured-click-tracking" 
                                           data-featured-id="{{ $featured->id }}">{{ $note->title }}</a>
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

                                <!-- Price and Author -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                    <div>
                                        @if($note->price > 0)
                                            @if($note->hasDiscount())
                                                <div class="flex items-center gap-2">
                                                    <div class="flex flex-col">
                                                        <span class="text-xs text-gray-500 line-through">{{ currency($note->price) }}</span>
                                                        <span class="text-lg font-bold text-green-600">{{ currency($note->discount_price) }}</span>
                                                    </div>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-red-500 text-white">
                                                        -{{ $note->discount_percent }}%
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-lg font-bold text-green-600">{{ currency($note->price) }}</span>
                                            @endif
                                        @else
                                            <span class="text-lg font-bold text-gray-600">{{ __('messages.free') }}</span>
                                        @endif
                                    </div>
                                    <a href="{{ route('public.profile.show', $note->user->username) }}" class="text-sm text-gray-600 hover:text-blue-600">
                                        {{ $note->user->name }}
                                    </a>
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

        @if($notes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($notes as $note)
                    <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 group">
                        <!-- Thumbnail -->
                        @if($note->hasThumbnails())
                            <div class="h-48 overflow-hidden">
                                <img src="{{ Storage::url($note->thumbnails[0]) }}" alt="{{ $note->title }}" 
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @endif
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
                                @if($note->sale_mode)
                                    @if($note->isScarcityMode())
                                        <div class="relative inline-block group">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-blue-100 text-blue-800 cursor-help">
                                                <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                                </svg>
                                                Scarcity
                                            </span>
                                            <div class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-56 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50">
                                                <div class="font-semibold mb-1">Scarcity Mode</div>
                                                <div class="text-gray-300 space-y-0.5">
                                                    <div>• One-time purchase</div>
                                                    <div>• Buyer bisa resell</div>
                                                    <div>• Creator dapat komisi</div>
                                                </div>
                                                <div class="absolute left-2 top-full w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900"></div>
                                            </div>
                                        </div>
                                    @elseif($note->isStandardMode())
                                        <div class="relative inline-block group">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 cursor-help">
                                                <svg class="w-2.5 h-2.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                Standard
                                            </span>
                                            <div class="absolute left-0 bottom-full mb-2 hidden group-hover:block w-56 p-2 bg-gray-900 text-white text-[10px] rounded shadow-lg z-50">
                                                <div class="font-semibold mb-1">Standard Mode</div>
                                                <div class="text-gray-300 space-y-0.5">
                                                    <div>• Multiple sales</div>
                                                    <div>• Buyer tidak bisa resell</div>
                                                    <div>• Cocok untuk akses ulang</div>
                                                </div>
                                                <div class="absolute left-2 top-full w-0 h-0 border-l-2 border-r-2 border-t-2 border-transparent border-t-gray-900"></div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
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
                                    @if($note->hasDiscount())
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100">
                                            <div class="flex flex-col items-end">
                                                <span class="text-gray-500 line-through text-[10px]">{{ currency($note->price) }}</span>
                                                <span class="text-yellow-800 font-semibold">{{ currency($note->discount_price) }}</span>
                                            </div>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-500 text-white">
                                                -{{ $note->discount_percent }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800 font-semibold">
                                            {{ currency($note->price) }}
                                        </span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('messages.free') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Author and Date -->
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <a href="{{ route('public.profile.show', $note->user->username) }}" 
                                   class="flex items-center text-sm text-gray-600 hover:text-blue-600 transition-colors duration-200 group"
                                   title="{{ __('messages.view_all_notes_from', ['name' => $note->user->name]) }}">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center mr-2 group-hover:ring-2 group-hover:ring-blue-500 transition-all duration-200">
                                        @if($note->user->avatar)
                                            @if(str_starts_with($note->user->avatar, 'http'))
                                                <img src="{{ $note->user->avatar }}" alt="{{ $note->user->name }}" class="w-6 h-6 rounded-full object-cover">
                                            @else
                                                <img src="{{ Storage::url($note->user->avatar) }}" alt="{{ $note->user->name }}" class="w-6 h-6 rounded-full object-cover">
                                            @endif
                                        @else
                                            <span class="text-xs font-semibold text-gray-600">{{ substr($note->user->name, 0, 1) }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="group-hover:text-blue-600">{{ $note->user->name }}</span>
                                        </div>
                                        @if($note->user->role === 'seller')
                                            <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="{{ __('messages.seller') }}">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
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
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('messages.no_notes_found') }}</h3>
                <p class="mt-2 text-sm text-gray-500">{{ __('messages.try_adjusting_criteria') }}</p>
            </div>
        @endif
    </div>
</div>

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
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }).catch(err => console.error('Failed to track click:', err));
                }
            });
        });
    });
</script>
@endpush
@endsection

