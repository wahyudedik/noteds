@extends('40-shared/layouts/app')

@section('title', '🎨 Graphic Design — Template, Asset, dan Resource Design | Noteds')

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
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">🎨 Graphic Design</h1>
                <p class="text-xl text-gray-600">Template, asset, dan resource design profesional untuk berbagai kebutuhan
                    grafis dan branding.</p>
            </div>

            <!-- Kategori Graphic -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Design</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏷️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Logo & Branding</h3>
                        <p class="text-gray-600 text-sm mb-4">Template logo, brand guideline, dan branding asset untuk
                            company identity.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'logo']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎭</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">UI/UX Design</h3>
                        <p class="text-gray-600 text-sm mb-4">UI kit, wireframe template, dan design system untuk app &
                            website.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'ui']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📱</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Social Media Template</h3>
                        <p class="text-gray-600 text-sm mb-4">Post template, story template, dan social media asset untuk
                            Instagram, Facebook, TikTok.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'social']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📄</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Print Materials</h3>
                        <p class="text-gray-600 text-sm mb-4">Flyer, brochure, poster, business card template untuk print
                            material.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'print']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎯</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Design Tools & Resource</h3>
                        <p class="text-gray-600 text-sm mb-4">Plugin, brush, font, texture untuk Photoshop, Figma, Adobe
                            Creative Suite.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'tools']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🔤</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Icon & Typography</h3>
                        <p class="text-gray-600 text-sm mb-4">Icon set, font bundle, typography asset untuk design project.
                        </p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic', 'category' => 'icon']) }}"
                            class="text-pink-600 hover:text-pink-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Brand & Marketing</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Create professional
                                    logo</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Develop brand
                                    identity</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Design marketing
                                    material</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Campaign creative
                                    asset</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Digital & Web</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>UI/UX design
                                    template</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Web design
                                    mockup</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>App screen
                                    design</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Interactive
                                    prototype</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Social Media</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Instagram post
                                    design</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Story
                                    template</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Banner & header
                                    design</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Infographic
                                    template</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Print & Merchandise</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Flyer & poster
                                    design</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Business card
                                    template</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Brochure
                                    layout</span></li>
                            <li class="flex items-start"><span class="text-pink-500 mr-2">•</span> <span>Packaging
                                    design</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-lg p-6 border border-pink-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Design</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span>
                            <span>Professional quality design</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>Fully
                                editable template</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>High
                                resolution files</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>Commercial
                                license</span></li>
                        <li class="flex items-start"><span class="text-pink-500 font-bold mr-2">✓</span> <span>Free font
                                included</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Hemat
                                waktu design</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>Professional result</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>No design
                                skill needed</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Support &
                                tutorial</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Lifetime
                                access</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Ciptakan Design Menakjubkan!</h2>
                <p class="text-lg text-gray-600 mb-6">Temukan ribuan template design profesional dari designer terbaik.</p>
                <a href="{{ route('marketplace.index', ['ecosystem' => 'graphic']) }}"
                    class="inline-flex items-center px-6 py-3 bg-pink-600 text-white font-semibold rounded-lg hover:bg-pink-700 transition">
                    Jelajahi Design Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection

