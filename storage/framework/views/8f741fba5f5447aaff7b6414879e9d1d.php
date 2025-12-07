<?php $__env->startSection('title', __('messages.admin_edit_landing_section')); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('messages.admin_edit_landing_section')); ?></h1>
                    <p class="mt-2 text-base text-gray-600"><?php echo e(__('messages.update_section_content')); ?></p>
                </div>
                <a href="<?php echo e(route('admin.landing-page.index')); ?>" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">
                    ← <?php echo e(__('messages.back_to_list')); ?>

                </a>
            </div>
        </div>

        <form action="<?php echo e(route('admin.landing-page.update', $landingPage)); ?>" method="POST" class="space-y-6" id="section-form">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 space-y-6">
                <!-- Section Type -->
                <div>
                    <label for="section_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.section_type')); ?> <span class="text-red-500">*</span>
                    </label>
                    <select name="section_type" id="section_type" required
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['section_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 focus:border-red-500 focus:ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <option value=""><?php echo e(__('messages.select_section_type')); ?></option>
                        <?php $__currentLoopData = $sectionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(old('section_type', $landingPage->section_type) === $key ? 'selected' : ''); ?>>
                                <?php echo e($label); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500"><?php echo e(__('messages.choose_section_type_create')); ?></p>
                    <?php $__errorArgs = ['section_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.title')); ?>

                    </label>
                    <input type="text" 
                        id="title"
                        name="title"
                        value="<?php echo e(old('title', $landingPage->title)); ?>"
                        placeholder="e.g., Welcome to Noteds"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 focus:border-red-500 focus:ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Subtitle -->
                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.subtitle_description')); ?>

                    </label>
                    <textarea name="subtitle" id="subtitle" rows="2"
                        :placeholder="__('messages.brief_description_subtitle')"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['subtitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 focus:border-red-500 focus:ring-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('subtitle', $landingPage->subtitle)); ?></textarea>
                    <?php $__errorArgs = ['subtitle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Content Builder (Dynamic based on section type) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.content')); ?> <span class="text-red-500">*</span>
                    </label>
                    <div id="content-builder" class="border border-gray-300 rounded-lg p-4 bg-gray-50">
                        <p class="text-sm text-gray-500"><?php echo e(__('messages.select_section_type')); ?></p>
                    </div>
                    <input type="hidden" name="content" id="content-input" value="<?php echo e(old('content', json_encode($landingPage->content ?? []))); ?>" required>
                    <?php $__errorArgs = ['content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>

                <!-- Additional Settings -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Image URL -->
                    <div>
                        <label for="image_url" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.image_url')); ?>

                        </label>
                        <input type="url" 
                            id="image_url"
                            name="image_url"
                            value="<?php echo e(old('image_url', $landingPage->image_url)); ?>"
                            placeholder="https://example.com/image.jpg"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Order -->
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.display_order')); ?>

                        </label>
                        <input type="number" 
                            id="order"
                            name="order"
                            value="<?php echo e(old('order', $landingPage->order)); ?>"
                            min="0"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500"><?php echo e(__('messages.lower_numbers_appear_first')); ?></p>
                    </div>

                    <!-- Background Color -->
                    <div>
                        <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.background_color')); ?>

                        </label>
                        <input type="text" 
                            id="background_color"
                            name="background_color"
                            value="<?php echo e(old('background_color', $landingPage->background_color)); ?>"
                            :placeholder="__('messages.hex_or_tailwind')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Text Color -->
                    <div>
                        <label for="text_color" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.text_color')); ?>

                        </label>
                        <input type="text" 
                            id="text_color"
                            name="text_color"
                            value="<?php echo e(old('text_color', $landingPage->text_color)); ?>"
                            :placeholder="__('messages.hex_or_tailwind_text')"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Alignment -->
                <div>
                    <label for="alignment" class="block text-sm font-medium text-gray-700 mb-2">
                        <?php echo e(__('messages.alignment')); ?>

                    </label>
                    <select name="alignment" id="alignment"
                        class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                        <option value="left" <?php echo e(old('alignment', $landingPage->alignment ?? 'center') === 'left' ? 'selected' : ''); ?>><?php echo e(__('messages.left')); ?></option>
                        <option value="center" <?php echo e(old('alignment', $landingPage->alignment ?? 'center') === 'center' ? 'selected' : ''); ?>><?php echo e(__('messages.center')); ?></option>
                        <option value="right" <?php echo e(old('alignment', $landingPage->alignment ?? 'center') === 'right' ? 'selected' : ''); ?>><?php echo e(__('messages.right')); ?></option>
                    </select>
                </div>

                <!-- Valid Period (for promo sections) -->
                <div id="promo-dates" class="hidden grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="valid_from" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.valid_from')); ?>

                        </label>
                        <input type="date" 
                            id="valid_from"
                            name="valid_from"
                            value="<?php echo e(old('valid_from', $landingPage->valid_from?->format('Y-m-d'))); ?>"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label for="valid_until" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('messages.valid_until')); ?>

                        </label>
                        <input type="date" 
                            id="valid_until"
                            name="valid_until"
                            value="<?php echo e(old('valid_until', $landingPage->valid_until?->format('Y-m-d'))); ?>"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" 
                        id="is_active"
                        name="is_active"
                        value="1"
                        <?php echo e(old('is_active', $landingPage->is_active) ? 'checked' : ''); ?>

                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        <?php echo e(__('messages.active_visible_homepage')); ?>

                    </label>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="<?php echo e(route('admin.landing-page.index')); ?>" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                        <?php echo e(__('messages.cancel')); ?>

                    </a>
                    <button type="submit" class="px-6 py-2 border border-transparent rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <?php echo e(__('messages.update_section')); ?>

                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Translation strings for JavaScript templates
    const translations = {
        heroSectionContent: <?php echo json_encode(__('messages.hero_section_content'), 15, 512) ?>,
        primaryButtonText: <?php echo json_encode(__('messages.primary_button_text'), 15, 512) ?>,
        primaryButtonLink: <?php echo json_encode(__('messages.primary_button_link'), 15, 512) ?>,
        secondaryButtonTextOptional: <?php echo json_encode(__('messages.secondary_button_text_optional'), 15, 512) ?>,
        secondaryButtonLinkOptional: <?php echo json_encode(__('messages.secondary_button_link_optional'), 15, 512) ?>,
        featuresGrid: <?php echo json_encode(__('messages.features_grid'), 15, 512) ?>,
        addFeature: <?php echo json_encode(__('messages.add_feature'), 15, 512) ?>,
        featureTitle: <?php echo json_encode(__('messages.feature_title'), 15, 512) ?>,
        iconSvg: <?php echo json_encode(__('messages.icon_svg'), 15, 512) ?>,
        description: <?php echo json_encode(__('messages.description'), 15, 512) ?>,
        howItWorksSteps: <?php echo json_encode(__('messages.how_it_works_steps'), 15, 512) ?>,
        addStep: <?php echo json_encode(__('messages.add_step'), 15, 512) ?>,
        stepNumber: <?php echo json_encode(__('messages.step_number'), 15, 512) ?>,
        stepTitle: <?php echo json_encode(__('messages.step_title'), 15, 512) ?>,
        premiumBenefits: <?php echo json_encode(__('messages.premium_benefits'), 15, 512) ?>,
        addBenefit: <?php echo json_encode(__('messages.add_benefit'), 15, 512) ?>,
        benefitTitle: <?php echo json_encode(__('messages.benefit_title'), 15, 512) ?>,
        ctaButtonText: <?php echo json_encode(__('messages.cta_button_text'), 15, 512) ?>,
        trustIndicators: <?php echo json_encode(__('messages.trust_indicators'), 15, 512) ?>,
        addIndicator: <?php echo json_encode(__('messages.add_indicator'), 15, 512) ?>,
        text: <?php echo json_encode(__('messages.text'), 15, 512) ?>,
        testimonials: <?php echo json_encode(__('messages.testimonials'), 15, 512) ?>,
        addTestimonial: <?php echo json_encode(__('messages.add_testimonial'), 15, 512) ?>,
        name: <?php echo json_encode(__('messages.name'), 15, 512) ?>,
        role: <?php echo json_encode(__('messages.role'), 15, 512) ?>,
        testimonialContent: <?php echo json_encode(__('messages.testimonial_content'), 15, 512) ?>,
        avatarUrl: <?php echo json_encode(__('messages.avatar_url'), 15, 512) ?>,
        rating15: <?php echo json_encode(__('messages.rating_1_5'), 15, 512) ?>,
        promotionalSection: <?php echo json_encode(__('messages.promotional_section'), 15, 512) ?>,
        promoText: <?php echo json_encode(__('messages.promo_text'), 15, 512) ?>,
        limitedTimeOffer: <?php echo json_encode(__('messages.limited_time_offer'), 15, 512) ?>,
        ctaButtonLink: <?php echo json_encode(__('messages.cta_button_link'), 15, 512) ?>,
        discountCodeOptional: <?php echo json_encode(__('messages.discount_code_optional'), 15, 512) ?>,
        customSectionJson: <?php echo json_encode(__('messages.custom_section_json'), 15, 512) ?>,
        enterJsonContent: <?php echo json_encode(__('messages.enter_json_content'), 15, 512) ?>,
        remove: <?php echo json_encode(__('messages.remove'), 15, 512) ?>,
        blue: <?php echo json_encode(__('messages.blue'), 15, 512) ?>,
        green: <?php echo json_encode(__('messages.green'), 15, 512) ?>,
        purple: <?php echo json_encode(__('messages.purple'), 15, 512) ?>,
        yellow: <?php echo json_encode(__('messages.yellow'), 15, 512) ?>,
        getStartedFree: <?php echo json_encode(__('messages.get_started_free'), 15, 512) ?>,
        signIn: <?php echo json_encode(__('messages.sign_in'), 15, 512) ?>,
        upgradeToPremium: <?php echo json_encode(__('messages.upgrade_to_premium'), 15, 512) ?>,
        claimNow: <?php echo json_encode(__('messages.claim_now'), 15, 512) ?>,
        cmsHighlightSettings: <?php echo json_encode(__('messages.cms_highlight_settings'), 15, 512) ?>,
        cmsHighlightLimitLabel: <?php echo json_encode(__('messages.cms_highlight_limit_label'), 15, 512) ?>,
        cmsHighlightButtonTextLabel: <?php echo json_encode(__('messages.cms_highlight_button_text_label'), 15, 512) ?>,
        cmsHighlightButtonLinkLabel: <?php echo json_encode(__('messages.cms_highlight_button_link_label'), 15, 512) ?>,
        cmsHighlightDefaultButton: <?php echo json_encode(__('messages.cms_highlight_default_button'), 15, 512) ?>,
    };
    
    const sectionTypeSelect = document.getElementById('section_type');
    const contentBuilder = document.getElementById('content-builder');
    const contentInput = document.getElementById('content-input');
    const promoDates = document.getElementById('promo-dates');
    
    let contentData = {};
    const initialContent = (() => {
        try {
            return JSON.parse(contentInput.value || '{}');
        } catch (e) {
            return {};
        }
    })();

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
        cms_pages: {
            html: `
                <div class="space-y-4">
                    <p class="text-sm font-medium text-gray-700">${translations.cmsHighlightSettings}</p>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.cmsHighlightLimitLabel}</label>
                        <input type="number" name="cms_limit" class="w-full rounded border-gray-300 text-sm" min="1" max="12" value="${initialContent.limit ?? 3}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.cmsHighlightButtonTextLabel}</label>
                        <input type="text" name="cms_button_text" class="w-full rounded border-gray-300 text-sm" placeholder="${translations.cmsHighlightDefaultButton}" value="${initialContent.button_text ?? translations.cmsHighlightDefaultButton}">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">${translations.cmsHighlightButtonLinkLabel}</label>
                        <input type="text" name="cms_button_link" class="w-full rounded border-gray-300 text-sm" placeholder="/page" value="${initialContent.button_link ?? ''}">
                    </div>
                </div>
            `,
            getData: function() {
                const limitValue = parseInt(document.querySelector('[name="cms_limit"]')?.value, 10);
                return {
                    limit: Number.isFinite(limitValue) && limitValue > 0 ? limitValue : 3,
                    button_text: document.querySelector('[name="cms_button_text"]')?.value || translations.cmsHighlightDefaultButton,
                    button_link: document.querySelector('[name="cms_button_link"]')?.value || '',
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
            contentBuilder.innerHTML = '<p class="text-sm text-gray-500"><?php echo e(__('messages.select_section_type')); ?></p>';
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
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\admin\landing-page\edit.blade.php ENDPATH**/ ?>