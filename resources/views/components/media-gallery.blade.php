@props(['images' => [], 'title' => 'Media Gallery'])

@php
    $images = is_array($images) ? $images : [];
    $imageCount = count($images);
@endphp

@if ($imageCount > 0)
    <div class="mb-6" x-data="window.mediaGallery()" x-init="init()">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ $title }} ({{ $imageCount }})
        </h3>

        <!-- Thumbnail Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
            @foreach ($images as $index => $image)
                @php
                    $imageUrl = is_string($image)
                        ? asset('storage/' . $image)
                        : (is_array($image)
                            ? $image['url'] ?? asset('storage/' . ($image['path'] ?? ''))
                            : $image);
                    $imageAlt = is_array($image) ? $image['alt'] ?? 'Image ' . ($index + 1) : 'Image ' . ($index + 1);
                @endphp
                <div class="relative group cursor-pointer overflow-hidden rounded-lg border-2 border-gray-200 hover:border-blue-400 transition-all duration-200 aspect-square"
                    @click="openLightbox({{ $index }})" role="button" tabindex="0"
                    @keydown.enter="openLightbox({{ $index }})"
                    @keydown.space.prevent="openLightbox({{ $index }})">
                    <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" loading="lazy"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div
                        class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-8 h-8 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                        </svg>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Lightbox Modal -->
        <div x-show="isOpen" x-cloak @keydown.escape.window="closeLightbox()"
            class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center" style="display: none;"
            @click.self="closeLightbox()">
            <!-- Close Button -->
            <button @click="closeLightbox()"
                class="absolute top-4 right-4 z-10 p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full text-white transition-all duration-200"
                aria-label="Close lightbox">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Navigation Buttons -->
            <button x-show="currentIndex > 0" @click="previousImage()"
                class="absolute left-4 z-10 p-3 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full text-white transition-all duration-200"
                aria-label="Previous image">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button x-show="currentIndex < imageCount - 1" @click="nextImage()"
                class="absolute right-4 z-10 p-3 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full text-white transition-all duration-200"
                aria-label="Next image">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Image Counter -->
            <div class="absolute top-4 left-4 z-10 px-4 py-2 bg-black bg-opacity-50 rounded-full text-white text-sm">
                <span x-text="currentIndex + 1"></span> / <span x-text="imageCount"></span>
            </div>

            <!-- Image Container with Zoom -->
            <div class="relative w-full h-full flex items-center justify-center p-4"
                @touchstart="handleTouchStart($event)" @touchmove.prevent="handleTouchMove($event)"
                @touchend="handleTouchEnd($event)">
                <div class="relative max-w-full max-h-full overflow-auto"
                    :style="{ transform: `scale(${zoomLevel}) translate(${translateX}px, ${translateY}px)` }"
                    style="transition: transform 0.3s ease-out;">
                    <img :src="currentImageUrl" :alt="'Image ' + (currentIndex + 1)"
                        class="max-w-full max-h-[90vh] object-contain" @load="imageLoaded = true"
                        @error="imageLoaded = false" draggable="false">
                </div>
            </div>

            <!-- Zoom Controls -->
            <div
                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 z-10 flex items-center gap-2 bg-black bg-opacity-50 rounded-full px-4 py-2">
                <button @click="zoomOut()" :disabled="zoomLevel <= 1"
                    :class="zoomLevel <= 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-opacity-30'"
                    class="p-2 text-white rounded-full transition-all duration-200" aria-label="Zoom out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                    </svg>
                </button>
                <span class="text-white text-sm px-2 min-w-[60px] text-center">
                    <span x-text="Math.round(zoomLevel * 100)"></span>%
                </span>
                <button @click="zoomIn()" :disabled="zoomLevel >= 3"
                    :class="zoomLevel >= 3 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-opacity-30'"
                    class="p-2 text-white rounded-full transition-all duration-200" aria-label="Zoom in">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                    </svg>
                </button>
                <button @click="resetZoom()" x-show="zoomLevel > 1 || translateX !== 0 || translateY !== 0"
                    class="p-2 text-white rounded-full hover:bg-opacity-30 transition-all duration-200 ml-2"
                    aria-label="Reset zoom">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
