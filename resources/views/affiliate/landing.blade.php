@extends('layouts.guest')

@section('title', __('affiliate.landing_page'))

@section('content')
    <div class="min-h-screen bg-white dark:bg-gray-950 flex items-center justify-center px-4 py-8">
        <div class="max-w-md w-full">
            <!-- Profile Section -->
            <div class="text-center mb-12">
                @if ($landingPage->user && $landingPage->user->profile_photo_url)
                    <div class="mb-6 flex justify-center">
                        <img src="{{ $landingPage->user->profile_photo_url }}" alt="{{ $landingPage->user->name }}"
                            class="w-24 h-24 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                    </div>
                @endif

                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ $landingPage->user->name ?? 'Welcome' }}
                </h1>

                @if ($landingPage->content)
                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed">
                        {{ $landingPage->content }}
                    </p>
                @endif
            </div>

            <!-- Links Section -->
            <div class="space-y-3 mb-12">
                @forelse ($landingPage->affiliateLinks as $link)
                    <a href="{{ route('marketplace.index', ['ref' => $link->code]) }}"
                        class="block w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-6 py-4 text-center text-gray-800 dark:text-white font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 flex items-center justify-center gap-3">
                        <span class="text-xl">{{ $link->icon ?? '🔗' }}</span>
                        <span>{{ $link->name ?? 'Affiliate Link' }}</span>
                    </a>
                @empty
                    @if ($promotionalMaterials->count() > 0)
                        <a href="{{ route('marketplace.index') }}"
                            class="block w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl px-6 py-4 text-center font-semibold transition-colors duration-200">
                            🚀 Explore Now
                        </a>
                    @endif
                @endforelse

                @if ($promotionalMaterials->count() > 0 && $landingPage->affiliateLinks->count() > 0)
                    <a href="{{ route('marketplace.index') }}"
                        class="block w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-6 py-4 text-center text-gray-800 dark:text-white font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                        📦 View All Products
                    </a>
                @endif
            </div>

            <!-- Promotional Materials (Compact) -->
            @if ($promotionalMaterials->count() > 0)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-8">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 text-center">
                        {{ __('affiliate.promotional_materials') ?? 'Promotional Materials' }}
                    </h3>

                    <!-- Image Materials -->
                    @php
                        $imageCount = $promotionalMaterials->where('type', 'image')->count();
                    @endphp

                    @if ($imageCount > 0)
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            @foreach ($promotionalMaterials->where('type', 'image')->take(4) as $material)
                                @if ($material->image_path)
                                    <div class="rounded-lg overflow-hidden bg-gray-200 dark:bg-gray-700 h-24">
                                        <img src="{{ asset('storage/' . $material->image_path) }}"
                                            alt="{{ $material->name }}" class="w-full h-full object-cover" loading="lazy">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <!-- HTML Materials -->
                    @php
                        $htmlCount = $promotionalMaterials->where('type', 'html')->count();
                    @endphp

                    @if ($htmlCount > 0)
                        <div class="space-y-2">
                            @foreach ($promotionalMaterials->where('type', 'html')->take(3) as $material)
                                <div
                                    class="text-xs bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <p class="font-semibold text-gray-900 dark:text-white mb-1">{{ $material->name }}</p>
                                    <p class="text-gray-600 dark:text-gray-400">
                                        {{ Str::limit($material->description, 80) }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Footer -->
            {{-- <div
                class="text-center text-xs text-gray-500 dark:text-gray-400 mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p>✨ Powered by Noteds</p>
            </div> --}}
        </div>
    </div>

@endsection
