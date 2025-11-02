@extends('layouts.app')

@section('title', 'Welcome to ' . config('app.name'))

@section('content')
<div class="py-12 sm:py-16 lg:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @forelse($sections as $section)
            @if(!$section->isValid())
                @continue
            @endif

            @php
                $content = $section->content ?? [];
                $bgColor = $section->background_color ? (str_starts_with($section->background_color, '#') ? 'style="background-color: ' . $section->background_color . ';"' : 'class="' . $section->background_color . '"') : '';
                $textColor = $section->text_color ? (str_starts_with($section->text_color, '#') ? 'style="color: ' . $section->text_color . ';"' : 'class="' . $section->text_color . '"') : '';
                $alignment = $section->alignment ?? 'center';
            @endphp

            @if($section->section_type === 'hero')
                <!-- Hero Section -->
                <div {!! $bgColor !!} {!! $textColor !!} class="text-{{ $alignment }} mb-16">
                    @if($section->image_url)
                        <div class="flex justify-center mb-6">
                            <img src="{{ $section->image_url }}" alt="{{ $section->title }}" class="h-20 w-20 sm:h-24 sm:w-24">
                        </div>
                    @endif
                    @if($section->title)
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-4">{!! $section->title !!}</h1>
                    @endif
                    @if($section->subtitle)
                        <p class="text-xl sm:text-2xl mb-8 max-w-3xl mx-auto">{!! $section->subtitle !!}</p>
                    @endif
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                        @if(isset($content['primary_button_text']))
                            <a href="{{ $content['primary_button_link'] ?? '#' }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-lg hover:shadow-xl transition-all duration-200">
                                {{ $content['primary_button_text'] }}
                            </a>
                        @endif
                        @if(isset($content['secondary_button_text']))
                            <a href="{{ $content['secondary_button_link'] ?? '#' }}" class="inline-flex items-center px-8 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 shadow-sm hover:shadow-md transition-all duration-200">
                                {{ $content['secondary_button_text'] }}
                            </a>
                        @endif
                    </div>
                </div>

            @elseif($section->section_type === 'features')
                <!-- Features Grid -->
                <div {!! $bgColor !!} {!! $textColor !!} class="mb-16">
                    @if($section->title)
                        <h2 class="text-3xl font-bold mb-4 text-{{ $alignment }}">{{ $section->title }}</h2>
                    @endif
                    @if($section->subtitle)
                        <p class="text-lg mb-8 text-{{ $alignment }} max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">{{ $section->subtitle }}</p>
                    @endif
                    @if(isset($content['features']) && is_array($content['features']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($content['features'] as $feature)
                                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 p-8 hover:shadow-lg transition-shadow duration-200">
                                    @if(isset($feature['icon']))
                                        <div class="flex-shrink-0 bg-{{ $feature['color'] ?? 'blue' }}-100 rounded-lg p-4 w-16 h-16 mb-4 flex items-center justify-center">
                                            {!! $feature['icon'] !!}
                                        </div>
                                    @endif
                                    @if(isset($feature['title']))
                                        <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $feature['title'] }}</h3>
                                    @endif
                                    @if(isset($feature['description']))
                                        <p class="text-gray-600 leading-relaxed">{{ $feature['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif($section->section_type === 'how_it_works')
                <!-- How It Works -->
                <div {!! $bgColor !!} {!! $textColor !!} class="rounded-2xl border border-gray-200 p-8 sm:p-12 mb-16">
                    @if($section->title)
                        <div class="text-{{ $alignment }} mb-12">
                            <h2 class="text-3xl font-bold mb-4">{{ $section->title }}</h2>
                            @if($section->subtitle)
                                <p class="text-lg max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">{{ $section->subtitle }}</p>
                            @endif
                        </div>
                    @endif
                    @if(isset($content['steps']) && is_array($content['steps']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($content['steps'] as $step)
                                <div class="flex flex-col items-center text-center">
                                    @if(isset($step['number']))
                                        <div class="flex-shrink-0 bg-white rounded-full p-4 w-20 h-20 mb-4 flex items-center justify-center shadow-md">
                                            <span class="text-2xl font-bold text-blue-600">{{ $step['number'] }}</span>
                                        </div>
                                    @endif
                                    @if(isset($step['title']))
                                        <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $step['title'] }}</h3>
                                    @endif
                                    @if(isset($step['description']))
                                        <p class="text-gray-600 leading-relaxed">{{ $step['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif($section->section_type === 'premium_benefits')
                <!-- Premium Benefits -->
                <div {!! $bgColor !!} {!! $textColor !!} class="overflow-hidden shadow-lg rounded-2xl border border-gray-200 p-8 sm:p-12 mb-16">
                    @if($section->title)
                        <div class="text-{{ $alignment }} mb-12">
                            <h2 class="text-3xl font-bold mb-4">{{ $section->title }}</h2>
                            @if($section->subtitle)
                                <p class="text-lg max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">{{ $section->subtitle }}</p>
                            @endif
                        </div>
                    @endif
                    @if(isset($content['benefits']) && is_array($content['benefits']))
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            @foreach($content['benefits'] as $benefit)
                                <div class="text-center">
                                    @if(isset($benefit['icon']))
                                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3 w-12 h-12 mb-3 mx-auto flex items-center justify-center">
                                            {!! $benefit['icon'] !!}
                                        </div>
                                    @endif
                                    @if(isset($benefit['title']))
                                        <h4 class="text-base font-semibold text-gray-900 mb-2">{{ $benefit['title'] }}</h4>
                                    @endif
                                    @if(isset($benefit['description']))
                                        <p class="text-sm text-gray-600">{{ $benefit['description'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @if(isset($content['cta_text']))
                        <div class="text-center mt-8">
                            <a href="{{ $content['cta_link'] ?? route('subscription.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-md hover:shadow-lg transition-all duration-200">
                                {{ $content['cta_text'] }}
                            </a>
                        </div>
                    @endif
                </div>

            @elseif($section->section_type === 'trust_indicators')
                <!-- Trust Indicators -->
                <div {!! $bgColor !!} {!! $textColor !!} class="text-{{ $alignment }} mb-16">
                    @if($section->subtitle)
                        <p class="text-sm mb-8">{{ $section->subtitle }}</p>
                    @endif
                    @if(isset($content['indicators']) && is_array($content['indicators']))
                        <div class="flex flex-wrap justify-center items-center gap-8">
                            @foreach($content['indicators'] as $indicator)
                                <div class="flex items-center gap-2">
                                    @if(isset($indicator['icon']))
                                        <span>{!! $indicator['icon'] !!}</span>
                                    @endif
                                    @if(isset($indicator['text']))
                                        <span class="text-sm font-medium">{{ $indicator['text'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif($section->section_type === 'testimonials')
                <!-- Testimonials -->
                <div {!! $bgColor !!} {!! $textColor !!} class="mb-16">
                    @if($section->title)
                        <h2 class="text-3xl font-bold mb-4 text-{{ $alignment }}">{{ $section->title }}</h2>
                    @endif
                    @if($section->subtitle)
                        <p class="text-lg mb-12 text-{{ $alignment }} max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">{{ $section->subtitle }}</p>
                    @endif
                    @if(isset($content['testimonials']) && is_array($content['testimonials']))
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach($content['testimonials'] as $testimonial)
                                <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                                    @if(isset($testimonial['rating']))
                                        <div class="flex items-center gap-1 mb-4">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= ($testimonial['rating'] ?? 5) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                        </div>
                                    @endif
                                    @if(isset($testimonial['content']))
                                        <p class="text-gray-600 mb-4">{{ $testimonial['content'] }}</p>
                                    @endif
                                    <div class="flex items-center gap-3">
                                        @if(isset($testimonial['avatar']))
                                            <img src="{{ $testimonial['avatar'] }}" alt="{{ $testimonial['name'] ?? 'User' }}" class="w-10 h-10 rounded-full">
                                        @endif
                                        <div>
                                            @if(isset($testimonial['name']))
                                                <p class="font-semibold text-gray-900">{{ $testimonial['name'] }}</p>
                                            @endif
                                            @if(isset($testimonial['role']))
                                                <p class="text-sm text-gray-600">{{ $testimonial['role'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            @elseif($section->section_type === 'promo')
                <!-- Promotional Section -->
                <div {!! $bgColor !!} {!! $textColor !!} class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200 rounded-2xl p-8 sm:p-12 mb-16">
                    <div class="text-{{ $alignment }}">
                        @if($section->title)
                            <h2 class="text-3xl font-bold mb-4">{{ $section->title }}</h2>
                        @endif
                        @if(isset($content['promo_text']))
                            <p class="text-lg mb-6">{{ $content['promo_text'] }}</p>
                        @endif
                        @if(isset($content['discount_code']))
                            <div class="mb-6">
                                <span class="inline-block bg-yellow-400 text-yellow-900 px-4 py-2 rounded-lg font-bold text-lg">
                                    Code: {{ $content['discount_code'] }}
                                </span>
                            </div>
                        @endif
                        @if(isset($content['cta_text']))
                            <a href="{{ $content['cta_link'] ?? '#' }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 shadow-lg hover:shadow-xl transition-all duration-200">
                                {{ $content['cta_text'] }}
                            </a>
                        @endif
                    </div>
                </div>

            @else
                <!-- Custom Section -->
                <div {!! $bgColor !!} {!! $textColor !!} class="mb-16">
                    @if($section->title)
                        <h2 class="text-3xl font-bold mb-4 text-{{ $alignment }}">{{ $section->title }}</h2>
                    @endif
                    @if($section->subtitle)
                        <p class="text-lg mb-8 text-{{ $alignment }} max-w-2xl {{ $alignment === 'center' ? 'mx-auto' : '' }}">{{ $section->subtitle }}</p>
                    @endif
                    @if($section->image_url)
                        <div class="mb-6">
                            <img src="{{ $section->image_url }}" alt="{{ $section->title }}" class="w-full rounded-lg">
                        </div>
                    @endif
                    <!-- Custom content can be rendered based on JSON structure -->
                    <div class="prose max-w-none">
                        {!! json_encode($content, JSON_PRETTY_PRINT) !!}
                    </div>
                </div>
            @endif
        @empty
            <!-- Default Content (if no sections exist) -->
            <div class="text-center mb-16">
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Noteds') }}" class="h-20 w-20 sm:h-24 sm:w-24">
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-4">
                    Welcome to {{ config('app.name') }}
                </h1>
                <p class="text-xl sm:text-2xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    The digital marketplace where knowledge meets opportunity. Buy and sell quality notes, discover insights, and share your expertise.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            Go to Dashboard
                        </a>
                        <a href="{{ route('marketplace.index') }}" class="inline-flex items-center px-8 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            Explore Marketplace
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex items-center px-8 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700">
                            Get Started Free
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-3 border border-gray-300 text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50">
                            Sign In
                        </a>
                    @endauth
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
