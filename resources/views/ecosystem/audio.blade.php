@extends('layouts.app')

@section('title', '🎵 Audio — Musik, SFX, dan Sound Design | Noteds')

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
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">🎵 Audio</h1>
                <p class="text-xl text-gray-600">Musik, sound effects, jingle, dan audio asset profesional untuk video,
                    podcast, game, dan project lainnya.</p>
            </div>

            <!-- Kategori Audio -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">📂 Kategori Audio</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎵</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Musik Latar</h3>
                        <p class="text-gray-600 text-sm mb-4">Background music untuk video, film, iklan, dan project
                            multimedia. Tersedia berbagai genre dan mood.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'background-music']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🔊</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Sound Effects (SFX)</h3>
                        <p class="text-gray-600 text-sm mb-4">Efek suara realistis: suara alam, foley, UI sound, dan efek
                            spesial untuk berbagai kebutuhan.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'sfx']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📢</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Jingle & Logo</h3>
                        <p class="text-gray-600 text-sm mb-4">Audio branding: jingle iklan, logo animation audio, signature
                            sound untuk brand Anda.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'jingle']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎙️</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Intro & Outro</h3>
                        <p class="text-gray-600 text-sm mb-4">Audio intro/outro profesional untuk podcast, vlog, streaming,
                            dan presentasi multimedia.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'intro-outro']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎼</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Musik Instrumen</h3>
                        <p class="text-gray-600 text-sm mb-4">Musik instrumental: piano, gitar, orkestra, dan berbagai
                            instrumen untuk komposisi Anda.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'instrumental']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎶</div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Loops & Samples</h3>
                        <p class="text-gray-600 text-sm mb-4">Audio loops dan samples untuk music production, remix, dan
                            beat making.</p>
                        <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle', 'category' => 'loops']) }}"
                            class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Lihat Koleksi →</a>
                    </div>
                </div>
            </div>

            <!-- Use Cases -->
            <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">💡 Kasus Penggunaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Video & Multimedia</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Video YouTube &
                                    konten social media</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Iklan & komersial
                                    profesional</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Film pendek &
                                    dokumenter</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Presentasi &
                                    slideshow</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Audio & Streaming</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Podcast &
                                    audiobook</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Music streaming &
                                    playlist</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Live streaming &
                                    gaming</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Music production &
                                    beat making</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Game & Interactive</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Game background
                                    music</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Game SFX & UI
                                    sounds</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Interactive
                                    media</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>App notification
                                    sounds</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Brand & Marketing</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Audio branding &
                                    jingle</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Hold music &
                                    on-brand audio</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Podcast
                                    production</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">•</span> <span>Event & corporate
                                    audio</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur & Benefit -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-16">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">✨ Fitur Audio</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Kualitas
                                HD/HQ</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Multiple
                                format (MP3, WAV, AAC)</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Preview
                                sebelum beli</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span>
                            <span>Royalty-free license</span></li>
                        <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">✓</span> <span>Permanent
                                download</span></li>
                    </ul>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">🎯 Keuntungan Berbelanja</h3>
                    <ul class="space-y-2 text-gray-700">
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Harga
                                terjangkau & bersaing</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>One-time
                                payment, unlimited use</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Komersial
                                license included</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>24/7
                                instant download</span></li>
                        <li class="flex items-start"><span class="text-green-500 font-bold mr-2">✓</span> <span>Support
                                buyer & seller</span></li>
                    </ul>
                </div>
            </div>

            <!-- CTA -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Siap Temukan Audio Sempurna?</h2>
                <p class="text-lg text-gray-600 mb-6">Jelajahi ribuan audio berkualitas tinggi dari creator profesional.
                </p>
                <a href="{{ route('marketplace.index', ['ecosystem' => 'audiojungle']) }}"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    Cari Audio Sekarang
                </a>
            </div>
        </div>
    </div>
@endsection
