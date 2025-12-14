@extends('40-shared/layouts/app')

@section('title', '🎬 Videos — Stock Video, Video Tutorial, dan Video Content | Noteds')

@section('content')
    <div class="bg-gradient-to-b from-red-50 to-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12">
                <a href="{{ route('ecosystem.index') }}"
                    class="text-red-600 hover:text-red-700 font-semibold text-sm mb-4 inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Ecosystem
                </a>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">🎬 Videos</h1>
                <p class="text-xl text-gray-600">Koleksi video profesional: stock video, video tutorial, video marketing,
                    dan konten video berkualitas tinggi.</p>
            </div>

            <!-- Kategori Videos -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Video</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📹</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Stock Footage</h3>
                        <p class="text-gray-600 text-sm mb-4">Video stock berkualitas 4K/HD, B-roll footage, dan cinema
                            shot untuk video production.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'stock']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎓</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Tutorial & Education</h3>
                        <p class="text-gray-600 text-sm mb-4">Video tutorial, how-to guide, course content, dan educational
                            video untuk pembelajaran.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'tutorial']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">✨</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Motion Graphics & Effects</h3>
                        <p class="text-gray-600 text-sm mb-4">Animated intro, transition, motion graphics, dan visual
                            effects untuk video production.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'motion']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📢</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Promo & Commercial</h3>
                        <p class="text-gray-600 text-sm mb-4">Video iklan, commercial template, marketing video, dan promo
                            content untuk advertising.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'promo']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎵</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Music Videos & Performance</h3>
                        <p class="text-gray-600 text-sm mb-4">Music video template, performance footage, dan concert
                            recording untuk audio visual content.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'music']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🌍</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Background & Ambient</h3>
                        <p class="text-gray-600 text-sm mb-4">Background video, ambient footage, looping video, dan scene
                            untuk website background.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'videos', 'category' => 'background']) }}"
                            class="text-red-600 hover:text-red-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Content Creation</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>YouTube video
                                    production</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Social media
                                    content (TikTok, Reels)</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Blog video
                                    supplement</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Vlogging &
                                    streaming</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Marketing & Advertising</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Iklan & komersial
                                    video</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Promo campaign
                                    video</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Product showcase &
                                    demo</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Email marketing
                                    video</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Professional Production</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Film & short
                                    movie</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Documentary &
                                    reportase</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Corporate video &
                                    event</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Wedding & special
                                    event</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Learning & Development</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Online course &
                                    e-learning</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Training & tutorial
                                    video</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Webinar &
                                    presentation</span></li>
                            <li class="flex items-start"><span class="text-red-500 mr-2">•</span> <span>Educational
                                    content</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-lg p-6 border border-red-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Video</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-red-500 font-bold mr-2">✓</span> <span>4K & HD
                                quality</span></li>
                        <li class="flex items-start"><span class="text-red-500 font-bold mr-2">✓</span> <span>Multiple
                                format support</span></li>
                        <li class="flex items-start"><span class="text-red-500 font-bold mr-2">✓</span> <span>Royalty-free
                                license</span></li>
                        <li class="flex items-start"><span class="text-red-500 font-bold mr-2">✓</span> <span>Subtitle &
                                CC included</span></li>
                        <li class="flex items-start"><span class="text-red-500 font-bold mr-2">✓</span> <span>Ready to
                                edit</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Hemat
                                waktu
                                production</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>Professional quality content</span>
                        </li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Affordable
                                pricing</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Instant
                                download</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Lifetime
                                access & usage</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">🚀 Mulai Berkreasi dengan Video Berkualitas!</h2>
                <p class="text-gray-700 mb-4">Dapatkan akses ke ribuan video stock, tutorial, dan media assets untuk
                    kebutuhan proyek kreatif Anda.</p>
                @if (auth()->check() && auth()->user()->role === 'seller')
                    <a href="{{ route('notes.create', ['type' => 'videos']) }}"
                        class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                        Upload Video Anda
                    </a>
                @elseif (!auth()->check())
                    <a href="{{ route('register') }}"
                        class="bg-red-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-red-700 transition">
                        Daftar Gratis Sekarang
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection

