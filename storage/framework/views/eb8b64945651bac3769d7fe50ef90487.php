<?php
    $sectionType = $section->section_type;
    $content = $section->content ?? [];
    $bgColor = $section->background_color ?? 'transparent';
    $textColor = $section->text_color ?? '#000000';
    $alignment = $section->alignment ?? 'center';
?>

<?php if($section->is_active && $section->isValid()): ?>
    <section class="py-12" style="background-color: <?php echo e($bgColor); ?>; color: <?php echo e($textColor); ?>;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-<?php echo e($alignment); ?>">
                <?php if($section->title): ?>
                    <h2 class="text-3xl font-bold mb-4"><?php echo e($section->title); ?></h2>
                <?php endif; ?>
                
                <?php if($section->subtitle): ?>
                    <p class="text-lg mb-6"><?php echo e($section->subtitle); ?></p>
                <?php endif; ?>

                <?php if($section->image_url): ?>
                    <div class="mb-6">
                        <img src="<?php echo e($section->image_url); ?>" alt="<?php echo e($section->title); ?>" class="mx-auto rounded-lg shadow-lg max-w-full h-auto">
                    </div>
                <?php endif; ?>

                <?php if(is_array($content) && count($content) > 0): ?>
                    <div class="mt-8">
                        <?php if($sectionType === 'features'): ?>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php $__currentLoopData = $content; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        <?php if(isset($feature['icon'])): ?>
                                            <div class="text-4xl mb-4"><?php echo e($feature['icon']); ?></div>
                                        <?php endif; ?>
                                        <?php if(isset($feature['title'])): ?>
                                            <h3 class="text-xl font-semibold mb-2"><?php echo e($feature['title']); ?></h3>
                                        <?php endif; ?>
                                        <?php if(isset($feature['description'])): ?>
                                            <p><?php echo e($feature['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php elseif($sectionType === 'how_it_works'): ?>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php $__currentLoopData = $content; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        <?php if(isset($step['number'])): ?>
                                            <div class="text-3xl font-bold mb-4"><?php echo e($step['number']); ?></div>
                                        <?php endif; ?>
                                        <?php if(isset($step['title'])): ?>
                                            <h3 class="text-xl font-semibold mb-2"><?php echo e($step['title']); ?></h3>
                                        <?php endif; ?>
                                        <?php if(isset($step['description'])): ?>
                                            <p><?php echo e($step['description']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php elseif($sectionType === 'testimonials'): ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <?php $__currentLoopData = $content; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $testimonial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6">
                                        <?php if(isset($testimonial['quote'])): ?>
                                            <p class="italic mb-4">"<?php echo e($testimonial['quote']); ?>"</p>
                                        <?php endif; ?>
                                        <?php if(isset($testimonial['author'])): ?>
                                            <p class="font-semibold">— <?php echo e($testimonial['author']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="prose max-w-none">
                                <?php $__currentLoopData = $content; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(is_string($item)): ?>
                                        <p><?php echo e($item); ?></p>
                                    <?php elseif(is_array($item)): ?>
                                        <div class="mb-4">
                                            <?php if(isset($item['title'])): ?>
                                                <h3 class="text-xl font-semibold mb-2"><?php echo e($item['title']); ?></h3>
                                            <?php endif; ?>
                                            <?php if(isset($item['content'])): ?>
                                                <p><?php echo e($item['content']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\components\landing-section.blade.php ENDPATH**/ ?>