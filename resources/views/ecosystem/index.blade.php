@extends('layouts.app')

@section('title', 'Creative Ecosystem — Noteds Marketplace')

@section('content')
    <div class="bg-gradient-to-b from-blue-50 to-white py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Hero Section -->
            <div class="text-center mb-16">
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-4">
                    🎨 Creative Marketplace
                </h1>
                <p class="text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Noteds adalah platform digital marketplace yang menghubungkan creator dengan learner.
                    Jual dan beli berbagai jenis konten digital: catatan, kode, audio, video, 3D, dan banyak lagi.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('marketplace.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Jelajahi Marketplace
                    </a>
                    @if (auth()->check() && auth()->user()->role === 'seller')
                        <a href="{{ route('notes.create') }}"
                            class="inline-flex items-center px-6 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Mulai Jual
                        </a>
                    @endif
                </div>
            </div>

            <!-- Platform Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                    <div class="text-3xl mb-3">💡</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Untuk Seller</h3>
                    <p class="text-gray-600 text-sm">Monetize kreativitas Anda dengan menjual catatan, kode, desain, dan
                        konten digital lainnya. Dapatkan komisi dari setiap penjualan.</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                    <div class="text-3xl mb-3">📚</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Untuk Buyer</h3>
                    <p class="text-gray-600 text-sm">Temukan ribuan catatan dan konten berkualitas dari creator terbaik.
                        Beli, pelajari, dan tingkatkan skill Anda dengan mudah.</p>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                    <div class="text-3xl mb-3">🌐</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Ekosistem Terpadu</h3>
                    <p class="text-gray-600 text-sm">Satu platform untuk semua kebutuhan konten digital Anda. Dari catatan
                        sampai 3D models, semuanya ada di Noteds.</p>
                </div>
            </div>

            <!-- Fitur Utama Platform -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">✨ Fitur Utama Platform</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">📖 Manajemen Konten</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Rich Text Editor
                                    dengan formatting lengkap</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Versioning &
                                    tracking perubahan otomatis</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Draft, Schedule,
                                    dan Publish otomatis</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Upload file hingga
                                    20MB per catatan</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Tagging & kategori
                                    hierarki lengkap</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🛡️ Perlindungan Konten</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Anti-Copy: Disable
                                    text selection & copy</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Anti-Screenshot
                                    dengan blur overlay</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Bot & automation
                                    detection</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>DevTools blocking
                                    & clipboard monitoring</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Watermarking
                                    support</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">💰 Sistem Monetisasi</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Scarcity Mode:
                                    One-time purchase dengan resale</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Standard Mode:
                                    Multiple unlimited sales</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Dynamic pricing
                                    dengan komisi per penjualan</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Bundle notes
                                    dengan discount otomatis</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Wallet & withdraw
                                    system</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">📊 Analytics & Insights</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Revenue tracking &
                                    sales trends</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Buyer demographics
                                    & behavior</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Featured notes
                                    performance metrics</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Share leaderboard
                                    & commission tracking</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-2">✓</span> <span>Affiliate link
                                    tracking dengan conversion</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content Types / Ecosystem -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">🎯 Jenis Konten di Noteds</h2>
                <p class="text-gray-600 mb-8">Noteds mendukung 8 kategori ekosistem berbeda. Pilih kategori yang sesuai saat
                    membuat konten:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Notes/Catatan -->
                    <a href="{{ route('marketplace.index', ['ecosystem' => 'notes']) }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📝</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Catatan & Tutorial</h3>
                        <p class="text-sm text-gray-600">Catatan kuliah, study guide, tutorial lengkap, dan panduan
                            pembelajaran berbagai topik.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- AudioJungle -->
                    <a href="{{ route('ecosystem.audio') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎵</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Audio</h3>
                        <p class="text-sm text-gray-600">Musik, sound effects, intro/outro, jingle, dan audio asset untuk
                            video & podcast.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- Code/CodeCanyon -->
                    <a href="{{ route('ecosystem.code') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">💻</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Code & Scripts</h3>
                        <p class="text-sm text-gray-600">Plugin, script, snippet, library. Support: Laravel, PHP,
                            WordPress, JavaScript, Python, dll.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- GraphicRiver -->
                    <a href="{{ route('ecosystem.graphics') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎨</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Graphic</h3>
                        <p class="text-sm text-gray-600">Logo template, desain grafis, font, Photoshop action, dan aset
                            desain lainnya.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- PhotoDune -->
                    <a href="{{ route('ecosystem.photos') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">📸</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Photo</h3>
                        <p class="text-sm text-gray-600">Stock photography, royalty-free images, foto profesional untuk
                            berbagai kebutuhan.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- ThemeForest -->
                    <a href="{{ route('ecosystem.themes') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎭</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Theme</h3>
                        <p class="text-sm text-gray-600">WordPress theme, HTML template, landing page, responsive design
                            siap pakai.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- VideoHive -->
                    <a href="{{ route('ecosystem.videos') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎬</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">Video</h3>
                        <p class="text-sm text-gray-600">Video template, motion graphics, intro/outro, efek visual untuk
                            video project.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>

                    <!-- 3DOcean -->
                    <a href="{{ route('ecosystem.3d') }}"
                        class="group bg-white rounded-lg p-6 shadow-sm border border-gray-200 hover:border-blue-400 hover:shadow-md transition">
                        <div class="text-4xl mb-3">🎪</div>
                        <h3 class="font-bold text-gray-900 mb-2 group-hover:text-blue-600">3D</h3>
                        <p class="text-sm text-gray-600">3D model, texture, animation, architecture asset untuk berbagai
                            software 3D.</p>
                        <div class="mt-3 text-xs text-blue-600 font-semibold">Jelajahi →</div>
                    </a>
                </div>
            </div>

            <!-- Fitur Buyer -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">🛍️ Pengalaman Buyer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📚 Koleksi & Organisasi</h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">•</span> <span>Buat
                                    collection untuk mengorganisir catatan yang dibeli</span></li>
                            <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">•</span> <span>Folder
                                    organization dengan custom colors</span></li>
                            <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">•</span>
                                <span>Wishlist & bookmark untuk catatan favorit</span>
                            </li>
                            <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">•</span> <span>Reading
                                    history dengan progress tracking</span></li>
                            <li class="flex items-start"><span class="text-blue-500 font-bold mr-2">•</span> <span>Batch
                                    download multiple notes sekaligus</span></li>
                        </ul>
                    </div>

                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Analytics & Insights</h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start"><span class="text-green-500 font-bold mr-2">•</span>
                                <span>Purchase history & spending analytics</span>
                            </li>
                            <li class="flex items-start"><span class="text-green-500 font-bold mr-2">•</span>
                                <span>Category breakdown & learning trends</span>
                            </li>
                            <li class="flex items-start"><span class="text-green-500 font-bold mr-2">•</span>
                                <span>Download statistics & completion rate</span>
                            </li>
                            <li class="flex items-start"><span class="text-green-500 font-bold mr-2">•</span> <span>Export
                                    note ke PDF, DOCX, Markdown</span></li>
                            <li class="flex items-start"><span class="text-green-500 font-bold mr-2">•</span>
                                <span>Bookmark pada catatan untuk notes penting</span>
                            </li>
                        </ul>
                    </div>

                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 border border-purple-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">💬 Community & Engagement</h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start"><span class="text-purple-500 font-bold mr-2">•</span>
                                <span>Rating & review untuk catatan yang dibeli</span>
                            </li>
                            <li class="flex items-start"><span class="text-purple-500 font-bold mr-2">•</span>
                                <span>Comment & Q&A dengan seller</span>
                            </li>
                            <li class="flex items-start"><span class="text-purple-500 font-bold mr-2">•</span>
                                <span>Reaction system: Like, Love, Helpful, Insightful</span>
                            </li>
                            <li class="flex items-start"><span class="text-purple-500 font-bold mr-2">•</span>
                                <span>Follow seller & get notified on new content</span>
                            </li>
                            <li class="flex items-start"><span class="text-purple-500 font-bold mr-2">•</span> <span>Forum
                                    discussions & community tips</span></li>
                        </ul>
                    </div>

                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg p-6 border border-orange-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">🎁 Program Referral & Rewards</h3>
                        <ul class="space-y-3 text-gray-700">
                            <li class="flex items-start"><span class="text-orange-500 font-bold mr-2">•</span>
                                <span>Dapatkan komisi dari referral link Anda</span>
                            </li>
                            <li class="flex items-start"><span class="text-orange-500 font-bold mr-2">•</span> <span>Share
                                    notes & earn commission per purchase</span></li>
                            <li class="flex items-start"><span class="text-orange-500 font-bold mr-2">•</span>
                                <span>Points system & gamification (badges, levels)</span>
                            </li>
                            <li class="flex items-start"><span class="text-orange-500 font-bold mr-2">•</span>
                                <span>Leaderboard ranking & status badges</span>
                            </li>
                            <li class="flex items-start"><span class="text-orange-500 font-bold mr-2">•</span>
                                <span>Premium membership dengan exclusive benefits</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Fitur Seller -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">💼 Solusi Lengkap untuk Seller</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">📈 Featured Notes Advertising</h3>
                        <p class="text-gray-600 text-sm mb-4">Tingkatkan penjualan dengan menampilkan catatan di lokasi
                            premium marketplace.</p>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Hero Section,
                                    Carousel, Banner, Grid, Popup</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Duration: 7, 14,
                                    atau 30 hari</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Real-time
                                    analytics: Impressions, Clicks, CTR, ROI</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Auto-approve
                                    untuk verified sellers</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🔗 Affiliate Marketing System</h3>
                        <p class="text-gray-600 text-sm mb-4">Buat dan kelola affiliate link untuk monetize konten Anda
                            lebih maksimal.</p>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Unique tracking
                                    links dengan custom slug</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Landing page
                                    builder dengan live preview</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Commission tier
                                    system & leaderboard</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Real-time
                                    conversion tracking & analytics</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">📤 Share & Referral Program</h3>
                        <p class="text-gray-600 text-sm mb-4">Share note Anda & dapatkan komisi dari setiap pembelian yang
                            dihasilkan.</p>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Generate share
                                    referral links per note</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Share leaderboard
                                    by earnings</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Monthly
                                    commission accumulation & payout</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Fraud prevention
                                    dengan share limits</span></li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">👥 Buyer Management</h3>
                        <p class="text-gray-600 text-sm mb-4">Kelola hubungan dengan buyer dan tingkatkan customer loyalty.
                        </p>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Buyer history &
                                    purchase tracking</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Direct messaging
                                    dengan buyer</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Customer support
                                    & ticket system</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">→</span> <span>Seller analytics
                                    & performance metrics</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Advanced Features -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">⚡ Fitur Advanced</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🎬 Multimedia Support</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li>✓ Video preview & streaming</li>
                            <li>✓ Audio player integration</li>
                            <li>✓ 3D model viewer (GLB/GLTF)</li>
                            <li>✓ PDF viewer dengan navigation</li>
                            <li>✓ Advanced image gallery dengan zoom</li>
                            <li>✓ Code syntax highlighting</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">🌍 Internationalization</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li>✓ 3 bahasa: English, Bahasa Indonesia, Arabic</li>
                            <li>✓ Multi-currency: USD & IDR</li>
                            <li>✓ Dynamic tax by country & category</li>
                            <li>✓ RTL support untuk Arabic</li>
                            <li>✓ Regional content filtering</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-3">⚙️ Technical Stack</h3>
                        <ul class="space-y-2 text-gray-600 text-sm">
                            <li>✓ Laravel 12 framework</li>
                            <li>✓ Redis caching & optimization</li>
                            <li>✓ Midtrans payment integration</li>
                            <li>✓ Real-time updates dengan Pusher</li>
                            <li>✓ PWA-ready dengan offline support</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Security & Trust -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-8 mb-16 border border-blue-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">🔒 Keamanan & Kepercayaan</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Perlindungan Pembeli</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-blue-500 mr-2">✓</span> <span>Secure payment
                                    gateway (Midtrans)</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">✓</span> <span>Refund system
                                    dengan approval workflow</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">✓</span> <span>KYC verification
                                    untuk semua user</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">✓</span> <span>Content
                                    moderation & quality control</span></li>
                            <li class="flex items-start"><span class="text-blue-500 mr-2">✓</span> <span>User verification
                                    & badges</span></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Perlindungan Seller</h3>
                        <ul class="space-y-2 text-gray-700">
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">✓</span> <span>DRM & download
                                    tracking</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">✓</span> <span>Duplicate
                                    content detection</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">✓</span> <span>Unauthorized
                                    access logging</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">✓</span> <span>Seller badge &
                                    reputation system</span></li>
                            <li class="flex items-start"><span class="text-indigo-500 mr-2">✓</span> <span>Commission tier
                                    protection</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            <div class="text-center bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Siap Memulai?</h2>
                <p class="text-lg text-gray-600 mb-8">Bergabunglah dengan ribuan creator dan learner di ekosistem Noteds.
                </p>
                <div class="flex flex-wrap gap-4 justify-center">
                    <a href="{{ route('marketplace.index') }}"
                        class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                        Jelajahi Marketplace
                    </a>
                    @if (!auth()->check())
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center px-6 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition">
                            Daftar Sekarang
                        </a>
                    @elseif(auth()->user()->role === 'seller')
                        <a href="{{ route('notes.create') }}"
                            class="inline-flex items-center px-6 py-3 border-2 border-blue-600 text-blue-600 font-semibold rounded-lg hover:bg-blue-50 transition">
                            Mulai Upload Catatan
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
