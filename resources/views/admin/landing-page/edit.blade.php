@extends('layouts.app')

@section('title', __('messages.admin_edit_landing_section'))

@section('content')
<div class="py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.admin_edit_landing_section') }}</h1>
                    <p class="mt-2 text-base text-gray-600">{{ __('messages.update_section_content') }}</p>
                </div>
                <a href="{{ route('admin.landing-page.index') }}" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    ← {{ __('messages.back_to_list') }}
                </a>
            </div>
        </div>

        <form action="{{ route('admin.landing-page.update', $landingPage) }}" method="POST" class="space-y-6" id="section-form">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                <!-- Section Type -->
                <div>
                    <label for="section_type" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.section_type') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="section_type" id="section_type" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('section_type') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">{{ __('messages.select_section_type') }}</option>
                        @foreach($sectionTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('section_type', $landingPage->section_type) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.choose_section_type_create') }}</p>
                    @error('section_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.title') }}
                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="{{ old('title', $landingPage->title) }}"
                        placeholder="e.g., Welcome to Noteds"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Subtitle -->
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.subtitle_description') }}
                    </label>
                    <textarea name="subtitle" id="subtitle" rows="2"
                        :placeholder="__('messages.brief_description_subtitle')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 @error('subtitle') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('subtitle', $landingPage->subtitle) }}</textarea>
                    @error('subtitle')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Content Builder (Dynamic based on section type) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.content') }} <span class="text-red-500">*</span>
                    </label>
                    <div id="content-builder" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <p class="text-sm text-gray-500">{{ __('messages.select_section_type') }}</p>
                    </div>
                    <input type="hidden" name="content" id="content-input" value="{{ old('content', json_encode($landingPage->content ?? [])) }}" required>
                    @error('content')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Additional Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Image URL -->
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.image_url') }}
                        </label>
                        <input type="url" 
                            id="image_url"
                            name="image_url"
                            value="{{ old('image_url', $landingPage->image_url) }}"
                            placeholder="https://example.com/image.jpg"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.display_order') }}
                        </label>
                        <input type="number" 
                            id="order"
                            name="order"
                            value="{{ old('order', $landingPage->order) }}"
                            min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">{{ __('messages.lower_numbers_appear_first') }}</p>
                    </div>

                    <!-- Background Color -->
                    <div>
                        <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.background_color') }}
                        </label>
                        <input type="text" 
                            id="background_color"
                            name="background_color"
                            value="{{ old('background_color', $landingPage->background_color) }}"
                            :placeholder="__('messages.hex_or_tailwind')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.text_color') }}
                        </label>
                        <input type="text" 
                            id="text_color"
                            name="text_color"
                            value="{{ old('text_color', $landingPage->text_color) }}"
                            :placeholder="__('messages.hex_or_tailwind_text')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Alignment -->
                <div>
                    <label for="alignment" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ __('messages.alignment') }}
                    </label>
                    <select name="alignment" id="alignment"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="left" {{ old('alignment', $landingPage->alignment ?? 'center') === 'left' ? 'selected' : '' }}>{{ __('messages.left') }}</option>
                        <option value="center" {{ old('alignment', $landingPage->alignment ?? 'center') === 'center' ? 'selected' : '' }}>{{ __('messages.center') }}</option>
                        <option value="right" {{ old('alignment', $landingPage->alignment ?? 'center') === 'right' ? 'selected' : '' }}>{{ __('messages.right') }}</option>
                    </select>
                </div>

                <!-- Valid Period (for promo sections) -->
                <div id="promo-dates" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.valid_from') }}
                        </label>
                        <input type="date" 
                            id="valid_from"
                            name="valid_from"
                            value="{{ old('valid_from', $landingPage->valid_from?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('messages.valid_until') }}
                        </label>
                        <input type="date" 
                            id="valid_until"
                            name="valid_until"
                            value="{{ old('valid_until', $landingPage->valid_until?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" 
                        id="is_active"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $landingPage->is_active) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        {{ __('messages.active_visible_homepage') }}
                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.landing-page.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        {{ __('messages.cancel') }}
                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        {{ __('messages.update_section') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translation strings for JavaScript templates
    const translations = {
        heroSectionContent: @json(__('messages.hero_section_content')),
        primaryButtonText: @json(__('messages.primary_button_text')),
        primaryButtonLink: @json(__('messages.primary_button_link')),
        secondaryButtonTextOptional: @json(__('messages.secondary_button_text_optional')),
        secondaryButtonLinkOptional: @json(__('messages.secondary_button_link_optional')),
        featuresGrid: @json(__('messages.features_grid')),
        addFeature: @json(__('messages.add_feature')),
        featureTitle: @json(__('messages.feature_title')),
        iconSvg: @json(__('messages.icon_svg')),
        description: @json(__('messages.description')),
        howItWorksSteps: @json(__('messages.how_it_works_steps')),
        addStep: @json(__('messages.add_step')),
        stepNumber: @json(__('messages.step_number')),
        stepTitle: @json(__('messages.step_title')),
        premiumBenefits: @json(__('messages.premium_benefits')),
        addBenefit: @json(__('messages.add_benefit')),
        benefitTitle: @json(__('messages.benefit_title')),
        ctaButtonText: @json(__('messages.cta_button_text')),
        trustIndicators: @json(__('messages.trust_indicators')),
        addIndicator: @json(__('messages.add_indicator')),
        text: @json(__('messages.text')),
        testimonials: @json(__('messages.testimonials')),
        addTestimonial: @json(__('messages.add_testimonial')),
        name: @json(__('messages.name')),
        role: @json(__('messages.role')),
        testimonialContent: @json(__('messages.testimonial_content')),
        avatarUrl: @json(__('messages.avatar_url')),
        rating15: @json(__('messages.rating_1_5')),
        promotionalSection: @json(__('messages.promotional_section')),
        promoText: @json(__('messages.promo_text')),
        limitedTimeOffer: @json(__('messages.limited_time_offer')),
        ctaButtonLink: @json(__('messages.cta_button_link')),
        discountCodeOptional: @json(__('messages.discount_code_optional')),
        customSectionJson: @json(__('messages.custom_section_json')),
        enterJsonContent: @json(__('messages.enter_json_content')),
        remove: @json(__('messages.remove')),
        blue: @json(__('messages.blue')),
        green: @json(__('messages.green')),
        purple: @json(__('messages.purple')),
        yellow: @json(__('messages.yellow')),
        getStartedFree: @json(__('messages.get_started_free')),
        signIn: @json(__('messages.sign_in')),
        upgradeToPremium: @json(__('messages.upgrade_to_premium')),
        claimNow: @json(__('messages.claim_now')),
    };
    
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
                    <p class="text-sm font-medium text-gray-700">${translations.heroSectionContent}</p>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.primaryButtonText}</label>
                        <input type="text" name="primary_button_text" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.getStartedFree}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.primaryButtonLink}</label>
                        <input type="text" name="primary_button_link" class="w-full rounded border-gray-300 text-sm" placeholder="/register">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.secondaryButtonTextOptional}</label>
                        <input type="text" name="secondary_button_text" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.signIn}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.secondaryButtonLinkOptional}</label>
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
                    <p class="text-sm font-medium text-gray-700">${translations.featuresGrid}</p>
                    <div id="features-list" class="space-y-3"></div>
                    <button type="button" id="add-feature" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        ${translations.addFeature}
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
                    <p class="text-sm font-medium text-gray-700">${translations.howItWorksSteps}</p>
                    <div id="steps-list" class="space-y-3"></div>
                    <button type="button" id="add-step" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        ${translations.addStep}
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
                    <p class="text-sm font-medium text-gray-700">${translations.premiumBenefits}</p>
                    <div id="benefits-list" class="space-y-3"></div>
                    <button type="button" id="add-benefit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        ${translations.addBenefit}
                    </button>
                    <div class="mt-4">
                        <label class="block text-xs text-gray-600 mb-1">${translations.ctaButtonText}</label>
                        <input type="text" name="cta_text" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.upgradeToPremium}">
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
                    <p class="text-sm font-medium text-gray-700">${translations.trustIndicators}</p>
                    <div id="indicators-list" class="space-y-3"></div>
                    <button type="button" id="add-indicator" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        ${translations.addIndicator}
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
                    <p class="text-sm font-medium text-gray-700">${translations.testimonials}</p>
                    <div id="testimonials-list" class="space-y-3"></div>
                    <button type="button" id="add-testimonial" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                        ${translations.addTestimonial}
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
                    <p class="text-sm font-medium text-gray-700">${translations.promotionalSection}</p>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.promoText}</label>
                        <textarea name="promo_text" rows="3" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.limitedTimeOffer}"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.ctaButtonText}</label>
                        <input type="text" name="cta_text" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.claimNow}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.ctaButtonLink}</label>
                        <input type="text" name="cta_link" class="w-full rounded border-gray-300 text-sm" placeholder="/promo">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.discountCodeOptional}</label>
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
                    <p class="text-sm font-medium text-gray-700">${translations.customSectionJson}</p>
                    <textarea id="custom-json" rows="10" class="w-full rounded border-gray-300 text-sm font-mono" placeholder='{"key": "value"}'></textarea>
                    <p class="text-xs text-gray-500">${translations.enterJsonContent}</p>
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
            contentBuilder.innerHTML = '<p class="text-sm text-gray-500">{{ __('messages.select_section_type') }}</p>';
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
                    <input type="text" name="features[${featureIndex}][title]" placeholder="${translations.featureTitle}" class="text-sm rounded border-gray-300">
                    <input type="text" name="features[${featureIndex}][icon]" placeholder="${translations.iconSvg}" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="features[${featureIndex}][description]" rows="2" placeholder="${translations.description}" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <select name="features[${featureIndex}][color]" class="text-sm rounded border-gray-300">
                    <option value="blue">${translations.blue}</option>
                    <option value="green">${translations.green}</option>
                    <option value="purple">${translations.purple}</option>
                    <option value="yellow">${translations.yellow}</option>
                </select>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">${translations.remove}</button>
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
                    <input type="text" name="steps[${stepIndex}][number]" placeholder="${translations.stepNumber}" class="text-sm rounded border-gray-300">
                    <input type="text" name="steps[${stepIndex}][title]" placeholder="${translations.stepTitle}" class="text-sm rounded border-gray-300">
                    <textarea name="steps[${stepIndex}][description]" rows="2" placeholder="${translations.description}" class="text-sm rounded border-gray-300"></textarea>
                </div>
                <button type="button" class="remove-item text-red-600 text-xs">${translations.remove}</button>
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
                    <input type="text" name="benefits[${benefitIndex}][title]" placeholder="${translations.benefitTitle}" class="text-sm rounded border-gray-300">
                    <input type="text" name="benefits[${benefitIndex}][icon]" placeholder="${translations.iconSvg}" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="benefits[${benefitIndex}][description]" rows="2" placeholder="${translations.description}" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <button type="button" class="remove-item text-red-600 text-xs">${translations.remove}</button>
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
                    <input type="text" name="indicators[${indicatorIndex}][icon]" placeholder="${translations.iconSvg}" class="text-sm rounded border-gray-300">
                    <input type="text" name="indicators[${indicatorIndex}][text]" placeholder="${translations.text}" class="text-sm rounded border-gray-300">
                </div>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">${translations.remove}</button>
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
                    <input type="text" name="testimonials[${testimonialIndex}][name]" placeholder="${translations.name}" class="text-sm rounded border-gray-300">
                    <input type="text" name="testimonials[${testimonialIndex}][role]" placeholder="${translations.role}" class="text-sm rounded border-gray-300">
                </div>
                <textarea name="testimonials[${testimonialIndex}][content]" rows="3" placeholder="${translations.testimonialContent}" class="w-full text-sm rounded border-gray-300 mb-2"></textarea>
                <div class="grid grid-cols-2 gap-2">
                    <input type="url" name="testimonials[${testimonialIndex}][avatar]" placeholder="${translations.avatarUrl}" class="text-sm rounded border-gray-300">
                    <input type="number" name="testimonials[${testimonialIndex}][rating]" placeholder="${translations.rating15}" min="1" max="5" value="5" class="text-sm rounded border-gray-300">
                </div>
                <button type="button" class="remove-item text-red-600 text-xs mt-2">${translations.remove}</button>
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

