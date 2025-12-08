@extends('layouts.guest')

@section('title', $link->name ?: __('affiliate.affiliate_landing_page'))

@section('content')
<style>
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    
    .animate-slide-down {
        animation: slideInDown 0.8s ease-out;
    }
    
    .animate-slide-up {
        animation: slideInUp 0.8s ease-out;
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .feature-card {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="absolute top-20 left-10 w-72 h-72 bg-blue-300 dark:bg-blue-900 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float"></div>
    <div class="absolute top-40 right-10 w-72 h-72 bg-purple-300 dark:bg-purple-900 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 2s;"></div>
    <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-300 dark:bg-pink-900 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-float" style="animation-delay: 4s;"></div>
    
    <div class="container mx-auto px-4 py-12 relative z-10">
        @if($link->landing_page_content)
            <!-- Custom Content -->
            <div class="max-w-4xl mx-auto animate-slide-down">
                <!-- Header Badge -->
                <div class="text-center mb-8">
                    <span class="inline-block px-4 py-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm font-bold uppercase tracking-wide">
                        ✨ {{ $link->name ?: 'Exclusive Offer' }}
                    </span>
                </div>
                
                <!-- Main Content Card -->
                <div class="glass-effect rounded-2xl shadow-2xl p-8 md:p-12 mb-8">
                    <div class="prose dark:prose-invert max-w-none">
                        {!! $link->landing_page_content !!}
                    </div>
                </div>
                
                <!-- CTA Button -->
                <div class="text-center">
                    <a href="{{ route('marketplace.index', ['ref' => $link->code]) }}" 
                        class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold px-10 py-4 rounded-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl shadow-lg uppercase tracking-wide">
                        🚀 {{ __('affiliate.visit_marketplace') }}
                    </a>
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-12 flex justify-center gap-8 flex-wrap">
                    <div class="text-center">
                        <div class="text-2xl mb-2">🔒</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ __('messages.secure') ?? 'Secure' }}</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-2">⚡</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ __('messages.instant') ?? 'Instant Access' }}</p>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl mb-2">✓</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">{{ __('messages.verified') ?? '100% Verified' }}</p>
                    </div>
                </div>
            </div>
        @else
            <!-- Default Content -->
            <div class="max-w-4xl mx-auto animate-slide-down">
                <!-- Header Badge -->
                <div class="text-center mb-12">
                    <span class="inline-block px-4 py-2 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white text-sm font-bold uppercase tracking-wide">
                        🎉 Limited Time Offer
                    </span>
                </div>
                
                <!-- Main Title -->
                <div class="text-center mb-12">
                    <h1 class="text-5xl md:text-6xl font-black gradient-text mb-6 leading-tight">
                        Welcome to Our Platform!
                    </h1>
                    <p class="text-xl text-slate-600 dark:text-slate-300 max-w-2xl mx-auto leading-relaxed">
                        Discover exclusive deals, premium content, and amazing opportunities crafted just for you.
                    </p>
                </div>
                
                <!-- Features Grid -->
                <div class="grid md:grid-cols-3 gap-6 mb-12 animate-slide-up">
                    @foreach([
                        ['icon' => '📚', 'title' => 'Rich Content', 'desc' => 'Curated premium notes and templates'],
                        ['icon' => '💰', 'title' => 'Best Prices', 'desc' => 'Exclusive affiliate discounts'],
                        ['icon' => '⚡', 'title' => 'Instant Access', 'desc' => 'Start using immediately']
                    ] as $feature)
                    <div class="feature-card glass-effect rounded-xl p-6 text-center">
                        <div class="text-4xl mb-4">{{ $feature['icon'] }}</div>
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-slate-600 dark:text-slate-400 text-sm">{{ $feature['desc'] }}</p>
                    </div>
                    @endforeach
                </div>
                
                <!-- CTA Section -->
                <div class="text-center mb-12 animate-slide-up" style="animation-delay: 0.2s;">
                    <a href="{{ route('marketplace.index', ['ref' => $link->code]) }}" 
                        class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold px-12 py-5 rounded-xl transition-all duration-300 transform hover:-translate-y-1 hover:shadow-2xl shadow-lg uppercase tracking-wide text-lg">
                        🚀 Start Exploring Now
                    </a>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-6">
                        No credit card required • Free to join • Cancel anytime
                    </p>
                </div>
                
                <!-- Stats -->
                <div class="grid md:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.4s;">
                    @foreach([
                        ['num' => '10K+', 'label' => 'Active Users'],
                        ['num' => '50K+', 'label' => 'Premium Notes'],
                        ['num' => '4.9★', 'label' => 'Rating']
                    ] as $stat)
                    <div class="glass-effect rounded-xl p-6 text-center">
                        <div class="text-3xl font-black gradient-text mb-2">{{ $stat['num'] }}</div>
                        <p class="text-slate-600 dark:text-slate-400 font-medium">{{ $stat['label'] }}</p>
                    </div>
                    @endforeach
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-12 flex justify-center gap-8 flex-wrap text-center">
                    <div>
                        <div class="text-3xl mb-2">🔒</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Secure Payment</p>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">⚡</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">Instant Access</p>
                    </div>
                    <div>
                        <div class="text-3xl mb-2">✓</div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">100% Verified</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@endsection

