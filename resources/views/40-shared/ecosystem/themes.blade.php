@extends('40-shared/layouts/app')

@section('title', '🎭 Themes — Website Template, WordPress Theme, dan Design Template | Noteds')

@section('content')
    <div class="bg-gradient-to-b from-pink-50 to-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12">
                <a href="{{ route('ecosystem.index') }}"
                    class="text-pink-600 hover:text-pink-700 font-semibold text-sm mb-4 inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Ecosystem
                </a>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">🎭 Themes</h1>
                <p class="text-xl text-gray-600">Template website, WordPress theme, dan design template profesional untuk
                    launching website Anda dengan cepat.</p>
            </div>

            <!-- Kategori Themes -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Theme</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏢</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Business & Corporate</h3>
                        <p class="text-gray-600 text-sm mb-4">Website template untuk perusahaan, startup, agency, dan
                            corporate website profesional.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'business']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🛍️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">E-commerce & Shop</h3>
                        <p class="text-gray-600 text-sm mb-4">WooCommerce theme, Shopify theme, dan e-commerce template
                            untuk online store.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'ecommerce']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📰</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Blog & Magazine</h3>
                        <p class="text-gray-600 text-sm mb-4">Blog template, magazine theme, dan publishing website untuk
                            content creator.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'blog']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎨</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Portfolio & Creative</h3>
                        <p class="text-gray-600 text-sm mb-4">Portfolio website, agency template, dan creative theme untuk
                            showcase karya Anda.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'portfolio']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">⚙️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">SaaS & Tech</h3>
                        <p class="text-gray-600 text-sm mb-4">SaaS template, tech startup theme, dan app landing page
                            untuk produk software.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'saas']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📱</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Mobile & Responsive</h3>
                        <p class="text-gray-600 text-sm mb-4">Mobile-first template, responsive design, dan optimized
                            theme untuk semua perangkat.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'themes', 'category' => 'mobile']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Launch</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Get online dalam
                                    hitungan jam</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Pre-built layout
                                    siap pakai</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>No coding
                                    knowledge needed</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Professional
                                    looking website</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Customization</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Drag & drop
                                    editor</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Full source code
                                    access</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Easy color & font
                                    customization</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Add custom
                                    plugin & extension</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Performance & SEO</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Optimized untuk
                                    kecepatan</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>SEO friendly
                                    structure</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Mobile responsive
                                    design</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Best practices
                                    included</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Support & Updates</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Documentation &
                                    tutorial</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Regular update &
                                    maintenance</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Creator support
                                    & help</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Lifetime access
                                    to updates</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg p-6 border border-pink-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Theme</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span>
                            <span>Fully responsive design</span>
                        </li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>High
                                performance & speed</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>SEO
                                optimized</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>Easy
                                customization</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>Commercial
                                license</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Hemat
                                waktu
                                & biaya development</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>Professional hasil</span>
                        </li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>No design
                                skill needed</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Instant
                                setup & deploy</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Long-term
                                support</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Launching Website Anda Sekarang!</h2>
                <p class="text-lg text-gray-600 mb-6">Pilih dari ratusan template website profesional dan segera go
                    live.</p>
                <a href="{{ route('marketplace.index', ['ecosystem' => 'themes']) }}"
                    class="inline-flex items-center px-6 py-3 bg-pink-600 text-white font-semibold rounded-lg hover:bg-pink-700 transition">
                    Jelajahi Theme Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection

