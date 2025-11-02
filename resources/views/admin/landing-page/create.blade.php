@extends('layouts.app')

@section('title', 'Create Landing Page Section')

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Create Landing Page Section</h1>
                    <p class="mt-2 text-base text-gray-600">Add a new section to the homepage</p>
                </div>
                <a href="{{ route('admin.landing-page.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    ← Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('admin.landing-page.store') }}" method="POST" class="space-y-6" id="section-form">
            @csrf

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                <!-- Section Type -->
                <div>
                    <label for="section_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Section Type <span class="text-red-500">*</span>
                    </label>
                    <select name="section_type" id="section_type" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('section_type') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Select Section Type</option>
                        @foreach($sectionTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('section_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose the type of section you want to create</p>
                    @error('section_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title
                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        placeholder="e.g., Welcome to Noteds"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subtitle -->
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                        Subtitle / Description
                    </label>
                    <textarea name="subtitle" id="subtitle" rows="2"
                        placeholder="Brief description or subtitle..."
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('subtitle') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('subtitle') }}</textarea>
                    @error('subtitle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content Builder (Dynamic based on section type) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Content <span class="text-red-500">*</span>
                    </label>
                    <div id="content-builder" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <p class="text-sm text-gray-500">Select a section type to configure content</p>
                    </div>
                    <input type="hidden" name="content" id="content-input" value="{{ old('content', '{}') }}" required>
                    @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Image URL -->
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            Image URL
                        </label>
                        <input type="url" 
                            id="image_url"
                            name="image_url"
                            value="{{ old('image_url') }}"
                            placeholder="https://example.com/image.jpg"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            Display Order
                        </label>
                        <input type="number" 
                            id="order"
                            name="order"
                            value="{{ old('order', 0) }}"
                            min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                    </div>

                    <!-- Background Color -->
                    <div>
                        <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Background Color
                        </label>
                        <input type="text" 
                            id="background_color"
                            name="background_color"
                            value="{{ old('background_color') }}"
                            placeholder="#ffffff or bg-blue-50"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">
                            Text Color
                        </label>
                        <input type="text" 
                            id="text_color"
                            name="text_color"
                            value="{{ old('text_color') }}"
                            placeholder="#000000 or text-gray-900"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Alignment -->
                <div>
                    <label for="alignment" class="block text-sm font-medium text-gray-700 mb-2">
                        Alignment
                    </label>
                    <select name="alignment" id="alignment"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="left" {{ old('alignment', 'center') === 'left' ? 'selected' : '' }}>Left</option>
                        <option value="center" {{ old('alignment', 'center') === 'center' ? 'selected' : '' }}>Center</option>
                        <option value="right" {{ old('alignment', 'center') === 'right' ? 'selected' : '' }}>Right</option>
                    </select>
                </div>

                <!-- Valid Period (for promo sections) -->
                <div id="promo-dates" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">
                            Valid From
                        </label>
                        <input type="date" 
                            id="valid_from"
                            name="valid_from"
                            value="{{ old('valid_from') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-2">
                            Valid Until
                        </label>
                        <input type="date" 
                            id="valid_until"
                            name="valid_until"
                            value="{{ old('valid_until') }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" 
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', true) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Active (visible on homepage)
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.landing-page.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Create Section
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sectionTypeSelect = document.getElementById('section_type');
    const contentBuilder = document.getElementById('content-builder');
    const contentInput = document.getElementById('content-input');
    const promoDates = document.getElementById('promo-dates');
    
    let contentData = {};

    // Content builder templates for different section types
    const templates = {
        hero: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Hero Section Content:</p>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Primary Button Text</label>
                        <input type="text" name="primary_button_text" class="w-full rounded border-gray-300 text-sm" placeholder="Get Started Free">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Primary Button Link</label>
                        <input type="text" name="primary_button_link" class="w-full rounded border-gray-300 text-sm" placeholder="/register">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Secondary Button Text (optional)</label>
                        <input type="text" name="secondary_button_text" class="w-full rounded border-gray-300 text-sm" placeholder="Sign In">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Secondary Button Link (optional)</label>
                        <input type="text" name="secondary_button_link" class="w-full rounded border-gray-300 text-sm" placeholder="/login">
                    </div>
                </div>
            `,
            getData: function() {
                return {
                    primary_button_text: document.querySelector('[name="primary_button_text"]')?.value || '',
                    primary_button_link: document.querySelector('[name="primary_button_link"]')?.value || '',
                    secondary_button_text: document.querySelector('[name="secondary_button_text"]')?.value || '',
                    secondary_button_link: document.querySelector('[name="secondary_button_link"]')?.value || '',
                };
            }
        },
        features: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Features Grid:</p>
                    <div id="features-list" class="space-y-3"></div>
                    <button type="button" id="add-feature" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Feature
                    </button>
                </div>
            `,
            getData: function() {
                const features = [];
                document.querySelectorAll('.feature-item').forEach(item => {
                    features.push({
                        icon: item.querySelector('[name*="icon"]')?.value || '',
                        title: item.querySelector('[name*="title"]')?.value || '',
                        description: item.querySelector('[name*="description"]')?.value || '',
                        color: item.querySelector('[name*="color"]')?.value || 'blue',
                    });
                });
                return { features };
            }
        },
        how_it_works: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">How It Works Steps:</p>
                    <div id="steps-list" class="space-y-3"></div>
                    <button type="button" id="add-step" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Step
                    </button>
                </div>
            `,
            getData: function() {
                const steps = [];
                document.querySelectorAll('.step-item').forEach(item => {
                    steps.push({
                        number: item.querySelector('[name*="number"]')?.value || '',
                        title: item.querySelector('[name*="title"]')?.value || '',
                        description: item.querySelector('[name*="description"]')?.value || '',
                    });
                });
                return { steps };
            }
        },
        premium_benefits: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Premium Benefits:</p>
                    <div id="benefits-list" class="space-y-3"></div>
                    <button type="button" id="add-benefit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Benefit
                    </button>
                    <div class="mt-4">
                        <label class="block text-xs text-gray-600 mb-1">CTA Button Text</label>
                        <input type="text" name="cta_text" class="w-full rounded border-gray-300 text-sm" placeholder="Upgrade to Premium">
                    </div>
                </div>
            `,
            getData: function() {
                const benefits = [];
                document.querySelectorAll('.benefit-item').forEach(item => {
                    benefits.push({
                        icon: item.querySelector('[name*="icon"]')?.value || '',
                        title: item.querySelector('[name*="title"]')?.value || '',
                        description: item.querySelector('[name*="description"]')?.value || '',
                    });
                });
                return {
                    benefits,
                    cta_text: document.querySelector('[name="cta_text"]')?.value || '',
                };
            }
        },
        trust_indicators: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Trust Indicators:</p>
                    <div id="indicators-list" class="space-y-3"></div>
                    <button type="button" id="add-indicator" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Indicator
                    </button>
                </div>
            `,
            getData: function() {
                const indicators = [];
                document.querySelectorAll('.indicator-item').forEach(item => {
                    indicators.push({
                        icon: item.querySelector('[name*="icon"]')?.value || '',
                        text: item.querySelector('[name*="text"]')?.value || '',
                    });
                });
                return { indicators };
            }
        },
        testimonials: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Testimonials:</p>
                    <div id="testimonials-list" class="space-y-3"></div>
                    <button type="button" id="add-testimonial" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        + Add Testimonial
                    </button>
                </div>
            `,
            getData: function() {
                const testimonials = [];
                document.querySelectorAll('.testimonial-item').forEach(item => {
                    testimonials.push({
                        name: item.querySelector('[name*="name"]')?.value || '',
                        role: item.querySelector('[name*="role"]')?.value || '',
                        content: item.querySelector('[name*="content"]')?.value || '',
                        avatar: item.querySelector('[name*="avatar"]')?.value || '',
                        rating: item.querySelector('[name*="rating"]')?.value || '5',
                    });
                });
                return { testimonials };
            }
        },
        promo: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Promotional Section:</p>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Promo Text</label>
                        <textarea name="promo_text" rows="3" class="w-full rounded border-gray-300 text-sm" placeholder="Limited time offer..."></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">CTA Button Text</label>
                        <input type="text" name="cta_text" class="w-full rounded border-gray-300 text-sm" placeholder="Claim Now">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">CTA Button Link</label>
                        <input type="text" name="cta_link" class="w-full rounded border-gray-300 text-sm" placeholder="/promo">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Discount Code (optional)</label>
                        <input type="text" name="discount_code" class="w-full rounded border-gray-300 text-sm" placeholder="SAVE50">
                    </div>
                </div>
            `,
            getData: function() {
                return {
                    promo_text: document.querySelector('[name="promo_text"]')?.value || '',
                    cta_text: document.querySelector('[name="cta_text"]')?.value || '',
                    cta_link: document.querySelector('[name="cta_link"]')?.value || '',
                    discount_code: document.querySelector('[name="discount_code"]')?.value || '',
                };
            }
        },
        custom: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">Custom Section (JSON):</p>
                    <textarea id="custom-json" rows="10" class="w-full rounded border-gray-300 text-sm font-mono" placeholder='{"key": "value"}'></textarea>
                    <p class="text-xs text-gray-500">Enter JSON content for custom section</p>
                </div>
            `,
            getData: function() {
                try {
                    return JSON.parse(document.getElementById('custom-json').value || '{}');
                } catch (e) {
                    return {};
                }
            }
        }
    };

    // Show/hide promo dates based on section type
    function updatePromoDates() {
        if (sectionTypeSelect.value === 'promo') {
            promoDates.classList.remove('hidden');
        } else {
            promoDates.classList.add('hidden');
        }
    }

    // Update content builder when section type changes
    sectionTypeSelect.addEventListener('change', function() {
        const type = this.value;
        updatePromoDates();
        
        if (type && templates[type]) {
            contentBuilder.innerHTML = templates[type].html;
            
            // Add event listeners for dynamic content
            setupDynamicContent(type);
        } else {
            contentBuilder.innerHTML = '<p class="text-sm text-gray-500">Select a section type to configure content</p>';
        }
    });

    // Setup dynamic content handlers
    function setupDynamicContent(type) {
        if (type === 'features') {
            setupFeaturesBuilder();
        } else if (type === 'how_it_works') {
            setupStepsBuilder();
        } else if (type === 'premium_benefits') {
            setupBenefitsBuilder();
        } else if (type === 'trust_indicators') {
            setupIndicatorsBuilder();
        } else if (type === 'testimonials') {
            setupTestimonialsBuilder();
        }
    }

    // Features builder
    function setupFeaturesBuilder() {
        let featureIndex = 0;
        const featuresList = document.getElementById('features-list');
        const addBtn = document.getElementById('add-feature');
        
        function addFeature() {
            const div = document.createElement('div');
            div.className = 'feature-item border border-gray-300 rounded p-3 bg-white';
            div.innerHTML = `
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="text" name="features[${featureIndex}][title]" placeholder="Feature title" class="text-sm rounded border-gray-300">
                    <input type="text" name="features[${featureIndex}][icon]" placeholder="Icon/SVG" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="features[${featureIndex}][description]" rows="2" placeholder="Description" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <select name="features[${featureIndex}][color]" class="text-sm rounded border-gray-300">
                    <option value="blue">Blue</option>
                    <option value="green">Green</option>
                    <option value="purple">Purple</option>
                    <option value="yellow">Yellow</option>
                </select>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">Remove</button>
            `;
            featuresList.appendChild(div);
            featureIndex++;
            div.querySelector('.remove-item').addEventListener('click', () => div.remove());
        }
        
        addBtn.addEventListener('click', addFeature);
        addFeature(); // Add one default
    }

    // Steps builder
    function setupStepsBuilder() {
        let stepIndex = 0;
        const stepsList = document.getElementById('steps-list');
        const addBtn = document.getElementById('add-step');
        
        function addStep() {
            const div = document.createElement('div');
            div.className = 'step-item border border-gray-300 rounded p-3 bg-white';
            div.innerHTML = `
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <input type="text" name="steps[${stepIndex}][number]" placeholder="Step number" class="text-sm rounded border-gray-300">
                    <input type="text" name="steps[${stepIndex}][title]" placeholder="Step title" class="text-sm rounded border-gray-300">
                    <textarea name="steps[${stepIndex}][description]" rows="2" placeholder="Description" class="text-sm rounded border-gray-300"></textarea>
                </div>
                <button type="button" class="remove-item text-red-600 text-xs">Remove</button>
            `;
            stepsList.appendChild(div);
            stepIndex++;
            div.querySelector('.remove-item').addEventListener('click', () => div.remove());
        }
        
        addBtn.addEventListener('click', addStep);
        addStep(); // Add one default
    }

    // Benefits builder
    function setupBenefitsBuilder() {
        let benefitIndex = 0;
        const benefitsList = document.getElementById('benefits-list');
        const addBtn = document.getElementById('add-benefit');
        
        function addBenefit() {
            const div = document.createElement('div');
            div.className = 'benefit-item border border-gray-300 rounded p-3 bg-white';
            div.innerHTML = `
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="text" name="benefits[${benefitIndex}][title]" placeholder="Benefit title" class="text-sm rounded border-gray-300">
                    <input type="text" name="benefits[${benefitIndex}][icon]" placeholder="Icon/SVG" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="benefits[${benefitIndex}][description]" rows="2" placeholder="Description" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <button type="button" class="remove-item text-red-600 text-xs">Remove</button>
            `;
            benefitsList.appendChild(div);
            benefitIndex++;
            div.querySelector('.remove-item').addEventListener('click', () => div.remove());
        }
        
        addBtn.addEventListener('click', addBenefit);
        addBenefit(); // Add one default
    }

    // Indicators builder
    function setupIndicatorsBuilder() {
        let indicatorIndex = 0;
        const indicatorsList = document.getElementById('indicators-list');
        const addBtn = document.getElementById('add-indicator');
        
        function addIndicator() {
            const div = document.createElement('div');
            div.className = 'indicator-item border border-gray-300 rounded p-3 bg-white';
            div.innerHTML = `
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="indicators[${indicatorIndex}][icon]" placeholder="Icon/SVG" class="text-sm rounded border-gray-300">
                    <input type="text" name="indicators[${indicatorIndex}][text]" placeholder="Text" class="text-sm rounded border-gray-300">
                </div>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">Remove</button>
            `;
            indicatorsList.appendChild(div);
            indicatorIndex++;
            div.querySelector('.remove-item').addEventListener('click', () => div.remove());
        }
        
        addBtn.addEventListener('click', addIndicator);
        addIndicator(); // Add one default
    }

    // Testimonials builder
    function setupTestimonialsBuilder() {
        let testimonialIndex = 0;
        const testimonialsList = document.getElementById('testimonials-list');
        const addBtn = document.getElementById('add-testimonial');
        
        function addTestimonial() {
            const div = document.createElement('div');
            div.className = 'testimonial-item border border-gray-300 rounded p-3 bg-white';
            div.innerHTML = `
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="text" name="testimonials[${testimonialIndex}][name]" placeholder="Name" class="text-sm rounded border-gray-300">
                    <input type="text" name="testimonials[${testimonialIndex}][role]" placeholder="Role" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="testimonials[${testimonialIndex}][content]" rows="3" placeholder="Testimonial content" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <input type="url" name="testimonials[${testimonialIndex}][avatar]" placeholder="Avatar URL" class="text-sm rounded border-gray-300">
                    <input type="number" name="testimonials[${testimonialIndex}][rating]" placeholder="Rating (1-5)" min="1" max="5" value="5" class="text-sm rounded border-gray-300">
                </div>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">Remove</button>
            `;
            testimonialsList.appendChild(div);
            testimonialIndex++;
            div.querySelector('.remove-item').addEventListener('click', () => div.remove());
        }
        
        addBtn.addEventListener('click', addTestimonial);
        addTestimonial(); // Add one default
    }

    // Update content input before form submit
    document.getElementById('section-form').addEventListener('submit', function(e) {
        const type = sectionTypeSelect.value;
        if (type && templates[type]) {
            contentData = templates[type].getData();
            contentInput.value = JSON.stringify(contentData);
        } else {
            contentInput.value = '{}';
        }
    });

    // Initialize if section type is already selected
    if (sectionTypeSelect.value) {
        sectionTypeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection

