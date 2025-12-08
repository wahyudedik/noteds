<?php $__env->startSection('title', config('app.name', 'Noteds')); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $landingHero = __('landing.hero');
        $landingFeatures = collect(__('landing.features'));
        $landingCta = __('landing.cta');
        $translateOr = fn(string $key, string $fallback) => \Illuminate\Support\Facades\Lang::has($key)
            ? __($key)
            : $fallback;
        $landingStats = collect(
            $landingHero['stats'] ?? [
                ['value' => '10K+', 'label' => $translateOr('messages.users', 'Pengguna Aktif')],
                ['value' => '50K+', 'label' => $translateOr('messages.notes', 'Catatan Dipublikasikan')],
                ['value' => '4.9/5', 'label' => $translateOr('messages.rating', 'Skor Kepuasan')],
            ],
        );
        $featuresIcons = [
            'document-text' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m.75 6H6.75A1.75 1.75 0 0 1 5 16.25V7.75A1.75 1.75 0 0 1 6.75 6h5.086a1.75 1.75 0 0 1 1.237.513l3.914 3.914c.328.328.513.775.513 1.237v4.586A1.75 1.75 0 0 1 16.5 18Z"/></svg>',
            'wallet' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 0 0-3 3v6a3 3 0 0 0 3 3h3.75A2.25 2.25 0 0 0 21 18V12Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 1 1 6 0 3 3 0 0 1-6 0Z"/></svg>',
            'sparkles' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 12h.01M12 6h.01m0 12h.01M18 12h.01M9.172 9.172a4 4 0 0 1 5.656 5.656m-8.486 0a4 4 0 0 1 5.657-5.657"/></svg>',
            'users' =>
                '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11c1.657 0 3-.895 3-2s-1.343-2-3-2m-8 4c1.657 0 3-.895 3-2s-1.343-2-3-2m0 8c3.314 0 6 1.791 6 4H4c0-2.209 2.686-4 6-4Zm8 0c.803 0 1.555.094 2.236.26A3.5 3.5 0 0 0 18 12c-1.306 0-2.418.835-2.829 2"/></svg>',
        ];
    ?>

    <div class="overflow-hidden bg-white dark:bg-gray-900 text-slate-900 dark:text-slate-100">
        <!-- Hero -->
        <section class="relative pt-24 pb-16 sm:pt-28 sm:pb-20 lg:pb-24">
            <div class="absolute inset-0">
                <div class="absolute inset-x-0 top-0 h-64 bg-gradient-to-br from-blue-100 via-white to-white"></div>
                <div class="absolute -top-16 -left-24 h-72 w-72 rounded-full bg-blue-200/40 blur-3xl"></div>
                <div class="absolute -bottom-24 right-10 h-72 w-72 rounded-full bg-indigo-200/40 blur-[160px]"></div>
            </div>
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid lg:grid-cols-[1.05fr,minmax(0,1fr)] gap-16 items-center">
                    <div class="max-w-xl">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-200">
                            <span class="inline-flex h-2 w-2 rounded-full bg-blue-400 animate-pulse"></span>
                            <?php echo e($landingHero['badge'] ?? ''); ?>

                        </span>
                        <h1
                            class="mt-6 text-4xl sm:text-5xl lg:text-6xl font-semibold leading-tight tracking-tight text-slate-900">
                            <?php echo e($landingHero['title'] ?? config('app.name', 'Noteds')); ?>

                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-600">
                            <?php echo e($landingHero['subtitle'] ?? ''); ?>

                        </p>
                        <div class="mt-10 flex flex-wrap items-center gap-4">
                            <a href="<?php echo e(route('marketplace.index')); ?>"
                                class="inline-flex items-center gap-2 rounded-full bg-blue-600 px-6 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                                <?php echo e($landingHero['primary_cta'] ?? $translateOr('messages.explore_marketplace', 'Jelajahi Marketplace')); ?>

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                                </svg>
                            </a>
                            <a href="<?php echo e(auth()->check() ? route('notes.create') : route('register')); ?>"
                                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-slate-300 hover:text-slate-900">
                                <?php echo e($landingHero['secondary_cta'] ?? $translateOr('messages.collection_add_purchased_button', 'Mulai Membuat Catatan')); ?>

                            </a>
                        </div>
                        <?php if($landingStats->isNotEmpty()): ?>
                            <dl class="mt-12 grid gap-4 sm:grid-cols-3">
                                <?php $__currentLoopData = $landingStats->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="rounded-2xl border border-slate-200/80 bg-white/90 px-4 py-5 shadow-sm backdrop-blur">
                                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                            <?php echo e($stat['label']); ?></dt>
                                        <dd class="mt-2 text-2xl font-semibold text-slate-900"><?php echo e($stat['value']); ?></dd>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </dl>
                        <?php endif; ?>
                    </div>

                    <div class="relative">
                        <div class="absolute -top-8 -left-8 h-20 w-20 rounded-full bg-blue-100 blur-2xl"></div>
                        <div class="absolute -bottom-10 -right-6 h-24 w-24 rounded-full bg-indigo-100 blur-2xl"></div>
                        <div
                            class="relative rounded-3xl border border-slate-200 bg-white/80 shadow-xl shadow-blue-100/50 backdrop-blur-xl p-6 sm:p-8">
                            <?php if(isset($featuredHero) && $featuredHero): ?>
                                <?php
                                    $note = $featuredHero->note;
                                ?>
                                <div class="space-y-5">
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-700">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                            <?php echo e($translateOr('messages.featured_note', 'Catatan Unggulan')); ?>

                                        </span>
                                        <span
                                            class="text-xs text-slate-400"><?php echo e($note->created_at->diffForHumans()); ?></span>
                                    </div>
                                    <a href="<?php echo e(route('marketplace.show', $note)); ?>"
                                        class="block space-y-3 featured-click-tracking"
                                        data-featured-id="<?php echo e($featuredHero->id); ?>">
                                        <h3 class="text-xl font-semibold text-slate-900 hover:text-blue-600 transition">
                                            <?php echo e($note->title); ?>

                                        </h3>
                                        <p class="text-sm leading-6 text-slate-600">
                                            <?php echo e(Str::limit($note->summary ?? strip_tags($note->content), 140)); ?>

                                        </p>
                                    </a>
                                    <div class="flex items-center justify-between text-sm text-slate-500">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 text-blue-700 font-semibold">
                                                <?php echo e(substr($note->user->name, 0, 1)); ?>

                                            </span>
                                            <a href="<?php echo e(route('public.profile.show', $note->user->username)); ?>"
                                                class="hover:text-blue-600 transition">
                                                <?php echo e($note->user->name); ?>

                                            </a>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs uppercase tracking-wide text-slate-400">
                                                <?php echo e($translateOr('messages.price_label', 'Harga')); ?>

                                            </p>
                                            <p class="text-sm font-semibold text-slate-900">
                                                <?php echo e($note->price > 0 ? currency($note->price) : $translateOr('messages.free', 'Gratis')); ?>

                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <span
                                        class="inline-flex items-center gap-2 px-2.5 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-700">
                                        <span class="inline-block h-2 w-2 rounded-full bg-blue-400"></span>
                                        <?php echo e($translateOr('messages.discover_premium_notes', 'Temukan Catatan Premium')); ?>

                                    </span>
                                    <p class="text-sm text-slate-600 leading-relaxed">
                                        <?php echo e($landingHero['subtitle'] ?? ''); ?>

                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section class="py-20 lg:py-24 bg-slate-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto">
                    <span class="text-sm font-semibold tracking-wide text-blue-600 uppercase">
                        <?php echo e($translateOr('messages.features', 'Fitur Unggulan')); ?>

                    </span>
                    <h2 class="mt-4 text-3xl sm:text-4xl font-semibold text-slate-900">
                        <?php echo e($translateOr('messages.discover_premium_notes', 'Temukan Catatan Premium')); ?>

                    </h2>
                    <p class="mt-4 text-base text-slate-500 leading-relaxed">
                        <?php echo e($translateOr('landing.features_intro', 'Gabungkan workflow penulisan, kolaborasi, dan monetisasi dalam satu platform yang modern dan aman.')); ?>

                    </p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <?php $__currentLoopData = $landingFeatures; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div
                            class="rounded-3xl border border-white/60 bg-white p-8 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                            <div
                                class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-blue-600 mb-6">
                                <?php echo $featuresIcons[$feature['icon']] ?? $featuresIcons['sparkles']; ?>

                            </div>
                            <h3 class="text-lg font-semibold text-slate-900"><?php echo e($feature['title']); ?></h3>
                            <p class="mt-4 text-sm text-slate-500 leading-relaxed"><?php echo e($feature['description']); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>

        <!-- Featured Carousel -->
        <?php if(isset($featuredCarousel) && $featuredCarousel->count() > 0): ?>
            <section class="py-20 lg:py-24 bg-white">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6 mb-10">
                        <div class="max-w-2xl">
                            <span class="text-sm font-semibold tracking-wide text-blue-600 uppercase">
                                <?php echo e($translateOr('messages.featured_notes', 'Catatan Unggulan')); ?>

                            </span>
                            <h2 class="mt-4 text-3xl font-semibold text-slate-900">
                                <?php echo e($translateOr('messages.discover_premium_notes', 'Temukan Catatan Premium')); ?>

                            </h2>
                            <p class="mt-3 text-sm text-slate-500">
                                <?php echo e($landingHero['subtitle'] ?? ''); ?>

                            </p>
                        </div>
                        <a href="<?php echo e(route('marketplace.index')); ?>"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-700">
                            <?php echo e($translateOr('messages.explore_marketplace', 'Jelajahi Marketplace')); ?>

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        <?php $__currentLoopData = $featuredCarousel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featured): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $note = $featured->note;
                            ?>
                            <a href="<?php echo e(route('marketplace.show', $note)); ?>"
                                class="group block rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg featured-click-tracking"
                                data-featured-id="<?php echo e($featured->id); ?>">
                                <div class="flex items-center justify-between mb-5">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                        <?php echo e($translateOr('messages.featured_badge', 'Unggulan')); ?>

                                    </span>
                                    <span class="text-xs text-slate-400"><?php echo e($note->created_at->diffForHumans()); ?></span>
                                </div>
                                <h3
                                    class="text-lg font-semibold text-slate-900 mb-3 line-clamp-2 group-hover:text-blue-600 transition">
                                    <?php echo e($note->title); ?>

                                </h3>
                                <p class="text-sm text-slate-500 line-clamp-3">
                                    <?php echo e(Str::limit(strip_tags($note->summary ?? $note->content), 120)); ?>

                                </p>
                                <div class="mt-6 flex items-center justify-between text-sm text-slate-500">
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600 font-semibold">
                                            <?php echo e(substr($note->user->name, 0, 1)); ?>

                                        </span>
                                        <span><?php echo e($note->user->name); ?></span>
                                    </div>
                                    <span class="font-semibold text-slate-900">
                                        <?php echo e($note->price > 0 ? currency($note->price) : $translateOr('messages.free', 'Gratis')); ?>

                                    </span>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- CMS Pages Highlight -->
        <?php
            $utilityBgClass = '';
        ?>
        <?php if(isset($cmsHighlightSection, $highlightedCmsPages) && $cmsHighlightSection && $highlightedCmsPages->count() > 0): ?>
            <?php
                $bgClass = $cmsHighlightSection->background_color ?? '';
                $textClass = $cmsHighlightSection->text_color ?? '';
                $alignment = $cmsHighlightSection->alignment ?? 'left';
                $buttonText =
                    data_get($cmsHighlightSection->content, 'button_text') ?:
                    $translateOr('messages.cms_highlight_default_button', 'Lihat Semua Halaman');
                $buttonLink = data_get($cmsHighlightSection->content, 'button_link') ?: route('cms.index');
                $title = $cmsHighlightSection->title ?: $translateOr('messages.cms_pages', 'Pusat Informasi');
                $subtitle =
                    $cmsHighlightSection->subtitle ?:
                    $translateOr(
                        'messages.cms_pages_intro',
                        'Baca kebijakan, panduan, dan pembaruan penting untuk komunitas.',
                    );
                $headingClass = \Illuminate\Support\Str::startsWith((string) $textClass, 'text-')
                    ? $textClass
                    : 'text-slate-900';
                $paragraphClass = \Illuminate\Support\Str::startsWith((string) $textClass, 'text-')
                    ? $textClass
                    : 'text-slate-500';
                $inlineTextColor =
                    $textClass && !\Illuminate\Support\Str::startsWith((string) $textClass, 'text-')
                        ? $textClass
                        : null;
                $sectionAlignmentClass = match ($alignment) {
                    'center' => 'text-center',
                    'right' => 'text-right',
                    default => 'text-left',
                };
                $utilityBgClass =
                    $bgClass && \Illuminate\Support\Str::startsWith((string) $bgClass, 'bg-') ? $bgClass : '';
            ?>
            <section class="py-20 lg:py-24 <?php echo e($utilityBgClass ?: 'bg-slate-50 text-slate-900'); ?>"
                <?php if($bgClass && !$utilityBgClass): ?> style="background-color: <?php echo e($bgClass); ?>; color: <?php echo e($inlineTextColor ?? '#0f172a'); ?>;" <?php endif; ?>>
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div
                        class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12 <?php echo e($sectionAlignmentClass); ?>">
                        <div class="<?php echo e($sectionAlignmentClass); ?> w-full md:w-auto space-y-3">
                            <h2 class="text-3xl font-semibold <?php echo e($headingClass); ?>"
                                <?php if($inlineTextColor && !$utilityBgClass): ?> style="color: <?php echo e($inlineTextColor); ?>;" <?php endif; ?>>
                                <?php echo e($title); ?>

                            </h2>
                            <p class="text-sm <?php echo e($paragraphClass); ?>"
                                <?php if($inlineTextColor && !$utilityBgClass): ?> style="color: <?php echo e($inlineTextColor); ?>;" <?php endif; ?>>
                                <?php echo e($subtitle); ?>

                            </p>
                        </div>
                        <a href="<?php echo e($buttonLink); ?>"
                            class="inline-flex items-center gap-2 text-sm font-semibold <?php echo e($utilityBgClass ? 'text-blue-50 hover:text-white' : 'text-blue-600 hover:text-blue-700'); ?>">
                            <?php echo e($buttonText); ?>

                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                            </svg>
                        </a>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <?php $__currentLoopData = $highlightedCmsPages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article
                                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">
                                        <?php echo e($translateOr('messages.cms_highlight_badge', 'Sorotan CMS')); ?>

                                    </span>
                                    <span class="text-xs text-slate-400"><?php echo e($page->updated_at?->format('M d, Y')); ?></span>
                                </div>
                                <a href="<?php echo e(route('cms.show', $page)); ?>"
                                    class="block space-y-2 hover:text-blue-600 transition">
                                    <h3 class="text-lg font-semibold text-slate-900 line-clamp-2"><?php echo e($page->title); ?></h3>
                                    <p class="text-sm text-slate-500 line-clamp-3">
                                        <?php echo e(\Illuminate\Support\Str::limit(strip_tags($page->content), 160)); ?>

                                    </p>
                                </a>
                                <div class="mt-6">
                                    <a href="<?php echo e(route('cms.show', $page)); ?>"
                                        class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
                                        <?php echo e($translateOr('messages.view', 'Lihat')); ?>

                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Dynamic Landing Page Sections -->
        <?php if(isset($groupedSections) && $groupedSections->count() > 0): ?>
            <?php $__currentLoopData = $groupedSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionType => $sections): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php echo $__env->make('components.landing-section', ['section' => $section], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>

        <!-- Call to Action -->
        <section class="py-20 lg:py-24">
            <div
                class="relative max-w-4xl mx-auto overflow-hidden rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-600 via-blue-500 to-indigo-600 px-6 sm:px-10 lg:px-14 text-center text-white shadow-xl">
                <div class="absolute inset-0">
                    <div class="absolute inset-x-0 top-0 h-1/2 bg-gradient-to-b from-white/15 to-transparent"></div>
                    <div class="absolute -top-20 -left-16 h-40 w-40 rounded-full bg-white/25 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 h-32 w-32 rounded-full bg-sky-200/40 blur-3xl"></div>
                </div>
                <div class="relative py-14 sm:py-16 lg:py-18">
                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1.5 text-xs font-semibold ring-1 ring-white/30">
                        <span class="h-2 w-2 rounded-full bg-white/80 animate-ping"></span>
                        <?php echo e($translateOr('messages.ready_to_start', 'Siap mulai berkarya?')); ?>

                    </span>
                    <h2 class="mt-5 text-3xl sm:text-4xl font-semibold leading-tight">
                        <?php echo e($landingCta['title']); ?>

                    </h2>
                    <p class="mt-4 text-base text-white/80 leading-relaxed max-w-2xl mx-auto">
                        <?php echo e($landingCta['subtitle']); ?>

                    </p>
                    <a href="<?php echo e(route('register')); ?>"
                        class="mt-10 inline-flex items-center gap-3 rounded-full bg-white/95 px-7 py-3 text-base font-semibold text-blue-700 transition hover:bg-white hover:text-blue-800">
                        <?php echo e($landingCta['button']); ?>

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M13.5 4.5 21 12l-7.5 7.5m6-7.5H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Track clicks on featured notes
            document.addEventListener('DOMContentLoaded', function() {
                const featuredLinks = document.querySelectorAll('.featured-click-tracking');
                featuredLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const featuredId = this.getAttribute('data-featured-id');
                        if (featuredId) {
                            // Track click via AJAX
                            fetch(`/api/featured-notes/${featuredId}/click`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]').getAttribute('content')
                                }
                            }).catch(err => console.error('Failed to track click:', err));
                        }
                    });
                });
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/welcome.blade.php ENDPATH**/ ?>