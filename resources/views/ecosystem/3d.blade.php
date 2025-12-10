@extends('layouts.app')

@section('title', '🎪 3D Assets — Model, Texture, dan Rendering Asset | Noteds')

@section('content')
    <div class="bg-gradient-to-b from-indigo-50 to-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-12">
                <a href="{{ route('ecosystem.index') }}"
                    class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm mb-4 inline-flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Ecosystem
                </a>
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">🎪 3D Assets</h1>
                <p class="text-xl text-gray-600">Model 3D, texture, rendering asset profesional untuk game development,
                    animasi, visualisasi, dan project 3D lainnya.</p>
            </div>

            <!-- Kategori 3D -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori 3D</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏗️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">3D Models</h3>
                        <p class="text-gray-600 text-sm mb-4">Model 3D berkualitas tinggi: character, props, environment,
                            dan object untuk berbagai aplikasi.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'models']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎨</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Textures & Materials</h3>
                        <p class="text-gray-600 text-sm mb-4">PBR texture, material preset, dan seamless pattern untuk
                            rendering realistis.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'textures']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🤖</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Rigged & Animated</h3>
                        <p class="text-gray-600 text-sm mb-4">Character rigged, animation pack, dan motion capture untuk
                            animasi profesional.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'rigged']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎮</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Game Assets</h3>
                        <p class="text-gray-600 text-sm mb-4">Game-ready model, optimized mesh, dan asset untuk game
                            engine (Unity, Unreal).</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'game-assets']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🌟</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Render Assets</h3>
                        <p class="text-gray-600 text-sm mb-4">Lighting setup, particle effect, shader, dan render scene
                            untuk production.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'render']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🏛️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Architecture & Environment</h3>
                        <p class="text-gray-600 text-sm mb-4">Building model, landscape, interior design, dan
                            environment asset untuk visualisasi.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => '3d', 'category' => 'architecture']) }}"
                            class="text-indigo-600 hover:text-indigo-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Game Development</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Game character &
                                    NPC</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Level design &
                                    environment</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Weapon & prop
                                    model</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Game UI &
                                    HUD</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Animation & Film</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>3D animation &
                                    motion</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>VFX & visual
                                    effect</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Cinematic &
                                    trailer</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Motion graphics
                                    & animation</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Visualization & Design</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Architectural
                                    rendering</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Product
                                    visualization</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Interior design
                                    rendering</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Technical
                                    visualization</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Learning & Portfolio</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>3D modeling
                                    practice</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Animation
                                    learning</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Portfolio
                                    project</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">•</span> <span>Reference &
                                    study</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Software Support -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">💻 Software Support</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">📦</div>
                        <h3 class="text-sm font-semibold text-gray-900">Blender</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🎨</div>
                        <h3 class="text-sm font-semibold text-gray-900">Maya</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🏗️</div>
                        <h3 class="text-sm font-semibold text-gray-900">3ds Max</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🎬</div>
                        <h3 class="text-sm font-semibold text-gray-900">Cinema 4D</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🎮</div>
                        <h3 class="text-sm font-semibold text-gray-900">Unity</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🚀</div>
                        <h3 class="text-sm font-semibold text-gray-900">Unreal Engine</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">🖌️</div>
                        <h3 class="text-sm font-semibold text-gray-900">Substance</h3>
                    </div>
                    <div
                        class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 text-center hover:shadow-md transition">
                        <div class="text-3xl mb-2">✨</div>
                        <h3 class="text-sm font-semibold text-gray-900">Marmoset</h3>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg p-6 border border-indigo-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur 3D Asset</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-indigo-500 font-bold mr-2">✓</span>
                            <span>Multiple format (FBX, OBJ, MAX, BLEND)</span>
                        </li>
                        <li class="flex items-start"><span class="text-indigo-500 font-bold mr-2">✓</span>
                            <span>Game-ready
                                & optimized</span></li>
                        <li class="flex items-start"><span class="text-indigo-500 font-bold mr-2">✓</span> <span>Full
                                source
                                file included</span></li>
                        <li class="flex items-start"><span class="text-indigo-500 font-bold mr-2">✓</span>
                            <span>Commercial
                                license</span></li>
                        <li class="flex items-start"><span class="text-indigo-500 font-bold mr-2">✓</span>
                            <span>Documentation
                                & tutorial</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Percepat
                                project timeline</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>Professional quality asset</span>
                        </li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Cost
                                effective solution</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Instant
                                download</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Creator
                                support & help</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Ciptakan Project 3D Menakjubkan!</h2>
                <p class="text-lg text-gray-600 mb-6">Temukan ribuan 3D asset profesional dari creator berpengalaman.</p>
                <a href="{{ route('marketplace.index', ['ecosystem' => '3d']) }}"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white font-semibold rounded-lg hover:bg-indigo-700 transition">
                    Jelajahi 3D Assets Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection
