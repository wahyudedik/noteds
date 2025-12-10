@extends('layouts.app')

@section('title', '💻 Code & Scripts — Plugin, Library, dan Code Snippet | Noteds')

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
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">💻 Code & Scripts</h1>
                <p class="text-xl text-gray-600">Plugin, script, snippet, library untuk berbagai bahasa pemrograman.
                    Percepat development dengan kode siap pakai.</p>
            </div>

            <!-- Kategori Code -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Code</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🔵</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Laravel & PHP</h3>
                        <p class="text-gray-600 text-sm mb-4">Plugin, package, dan snippet untuk Laravel framework dan PHP
                            development.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'laravel']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📘</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">WordPress</h3>
                        <p class="text-gray-600 text-sm mb-4">WordPress plugins, theme snippets, dan extension untuk extend
                            functionality.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'wordpress']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🟡</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">JavaScript & React</h3>
                        <p class="text-gray-600 text-sm mb-4">React component, Vue.js library, dan JavaScript plugin untuk
                            web development.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'javascript']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🟦</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Python & Backend</h3>
                        <p class="text-gray-600 text-sm mb-4">Python library, Django app, Flask extension untuk backend
                            development.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'python']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🔲</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Mobile Development</h3>
                        <p class="text-gray-600 text-sm mb-4">iOS, Android, React Native code snippet dan library untuk
                            mobile apps.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'mobile']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">⚙️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Utility & Tools</h3>
                        <p class="text-gray-600 text-sm mb-4">Build tools, CLI utilities, dan script automation untuk
                            development workflow.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'code', 'category' => 'tools']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Web Development</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Build website cepat
                                    dengan framework</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Add functionality
                                    dengan plugin</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>React component
                                    library</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>WordPress
                                    customization</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Mobile Apps</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>iOS & Android
                                    development</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>React Native
                                    app</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Cross-platform
                                    code</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Mobile library &
                                    SDK</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Backend & Database</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Server setup &
                                    configuration</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Database
                                    optimization</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>API
                                    development</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Authentication &
                                    security</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">DevOps & Tools</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>CI/CD pipeline
                                    setup</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Deployment
                                    automation</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Build tools &
                                    bundlers</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Testing
                                    framework</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Code</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span>
                            <span>Well-documented code</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Source code
                                included</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Easy
                                installation guide</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Commercial
                                license</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Regular
                                updates</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Hemat
                                waktu development</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span>
                            <span>Professional quality code</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Best
                                practices included</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Support &
                                documentation</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Affordable
                                pricing</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Percepat Development Anda!</h2>
                <p class="text-lg text-gray-600 mb-6">Temukan ribuan kode dan plugin berkualitas dari developer
                    profesional.</p>
                <a href="{{ route('marketplace.index', ['ecosystem' => 'code']) }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Cari Code Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection
