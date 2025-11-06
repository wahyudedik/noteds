@php
    $sectionType = $section->section_type;
    $content = $section->content ?? [];
    $bgColor = $section->background_color ?? 'transparent';
    $textColor = $section->text_color ?? '#000000';
    $alignment = $section->alignment ?? 'center';
@endphp

@if($section->is_active && $section->isValid())
    <section class="py-12" style="background-color: {{ $bgColor }}; color: {{ $textColor }};">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-{{ $alignment }}">
                @if($section->title)
                    <h2 class="text-3xl font-bold mb-4">{{ $section->title }}</h2>
                @endif
                
                @if($section->subtitle)
                    <p class="text-lg mb-6">{{ $section->subtitle }}</p>
                @endif

                @if($section->image_url)
                    <div class="mb-6">
                        <img src="{{ $section->image_url }}" alt="{{ $section->title }}" class="mx-auto rounded-lg shadow-lg max-w-full h-auto">
                    </div>
                @endif

                @if(is_array($content) && count($content) > 0)
                    <div class="mt-8">
                        @if($sectionType === 'features')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @foreach($content as $feature)
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        @if(isset($feature['icon']))
                                            <div class="text-4xl mb-4">{{ $feature['icon'] }}</div>
                                        @endif
                                        @if(isset($feature['title']))
                                            <h3 class="text-xl font-semibold mb-2">{{ $feature['title'] }}</h3>
                                        @endif
                                        @if(isset($feature['description']))
                                            <p>{{ $feature['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($sectionType === 'how_it_works')
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                @foreach($content as $step)
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        @if(isset($step['number']))
                                            <div class="text-3xl font-bold mb-4">{{ $step['number'] }}</div>
                                        @endif
                                        @if(isset($step['title']))
                                            <h3 class="text-xl font-semibold mb-2">{{ $step['title'] }}</h3>
                                        @endif
                                        @if(isset($step['description']))
                                            <p>{{ $step['description'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($sectionType === 'testimonials')
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($content as $testimonial)
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        @if(isset($testimonial['quote']))
                                            <p class="italic mb-4">"{{ $testimonial['quote'] }}"</p>
                                        @endif
                                        @if(isset($testimonial['author']))
                                            <p class="font-semibold">— {{ $testimonial['author'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="prose max-w-none">
                                @foreach($content as $item)
                                    @if(is_string($item))
                                        <p>{{ $item }}</p>
                                    @elseif(is_array($item))
                                        <div class="mb-4">
                                            @if(isset($item['title']))
                                                <h3 class="text-xl font-semibold mb-2">{{ $item['title'] }}</h3>
                                            @endif
                                            @if(isset($item['content']))
                                                <p>{{ $item['content'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif

