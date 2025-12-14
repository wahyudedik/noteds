@extends('40-shared/layouts/app')

@section('title', '📸 Photos — Stock Photo, Foto Profesional, dan Koleksi Foto | Noteds')

@section('content')
    <div class="bg-gradient-to-b from-blue-50 to-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12">
                <a href="{{ route('ecosystem.index') }}"
                    class="text-blue-600 hover:text-blue-700 font-semibold text-sm mb-4 inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Ecosystem
                </a>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">📸 Photos</h1>
                <p class="text-xl text-gray-600">Koleksi foto berkualitas tinggi: stock photo, portrait, landscape,
                    product, dan berbagai kategori foto profesional.</p>
            </div>

            <!-- Kategori Photos -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Foto</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏞️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Landscape & Nature</h3>
                        <p class="text-gray-600 text-sm mb-4">Foto pemandangan alam, landscape, outdoor photography, dan
                            fotografi alam yang memukau.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'landscape']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">👤</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Portrait & People</h3>
                        <p class="text-gray-600 text-sm mb-4">Potret profesional, business headshot, lifestyle
                            photography, dan foto orang untuk berbagai kebutuhan.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'portrait']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📦</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Product Photography</h3>
                        <p class="text-gray-600 text-sm mb-4">Foto produk profesional, e-commerce image, dan product shot
                            untuk katalog online.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'product']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🍽️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Food & Beverage</h3>
                        <p class="text-gray-600 text-sm mb-4">Food photography profesional, foto makanan, minuman, dan
                            kuliner untuk restaurant dan food business.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'food']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">✈️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Travel & Culture</h3>
                        <p class="text-gray-600 text-sm mb-4">Foto travel, destinasi wisata, budaya lokal, dan pengalaman
                            perjalanan dari seluruh dunia.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'travel']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏢</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Business & Corporate</h3>
                        <p class="text-gray-600 text-sm mb-4">Foto bisnis, office, meeting, teamwork, dan corporate image
                            untuk presentasi dan marketing.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'photos', 'category' => 'business']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Marketing & Advertising</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Social media
                                    content</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Advertisement
                                    campaign</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Email marketing
                                    image</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Website banner &
                                    hero</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">E-commerce & Retail</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Product listing
                                    image</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Katalog
                                    produk</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Online store
                                    photo</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Product display
                                    image</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Design & Content</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Blog post
                                    image</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Design project
                                    asset</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Presentation
                                    slide</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Brochure &
                                    poster</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Media & Entertainment</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Video background
                                    image</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Magazine cover &
                                    layout</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Newspaper &
                                    editorial</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Social media
                                    post</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Foto</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>High
                                resolution quality</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span>
                            <span>Royalty-free
                                license</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Commercial
                                use included</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Multiple
                                format (JPG, PNG, RAW)</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Instant
                                download</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Harga
                                terjangkau & kompetitif</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>One-time purchase lifetime use</span>
                        </li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Safe &
                                legal usage</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Premium
                                curated collection</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Global
                                photographer network</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Temukan Foto Sempurna Anda!</h2>
                <p class="text-lg text-gray-600 mb-6">Akses ribuan foto profesional dari fotografer berbakat di seluruh
                    dunia.</p>
                <a href="{{ route('marketplace.index', ['ecosystem' => 'photos']) }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Jelajahi Foto Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection

