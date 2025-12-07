<?php $__env->startSection('title', $tutorial->title . ' — ' . __('messages.tuts')); ?>

<?php $__env->startPush('meta'); ?>
        <?php
        use Illuminate\Support\Facades\Storage;
        $shareUrl = route('tuts.show', $tutorial);
        $shareTitle = $tutorial->title;
        $shareDescription = $tutorial->description ?? Str::limit(strip_tags($tutorial->content), 200);
        $shareImage = $tutorial->thumbnail ? url(Storage::url($tutorial->thumbnail)) : null;
    ?>
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo e($shareUrl); ?>">
    <meta property="og:title" content="<?php echo e($shareTitle); ?>">
    <meta property="og:description" content="<?php echo e($shareDescription); ?>">
    <?php if($shareImage): ?>
        <meta property="og:image" content="<?php echo e($shareImage); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    <?php endif; ?>
    <meta property="og:site_name" content="<?php echo e(config('app.name')); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo e($shareUrl); ?>">
    <meta property="twitter:title" content="<?php echo e($shareTitle); ?>">
    <meta property="twitter:description" content="<?php echo e($shareDescription); ?>">
    <?php if($shareImage): ?>
        <meta property="twitter:image" content="<?php echo e($shareImage); ?>">
    <?php endif; ?>

    <!-- Additional Meta -->
    <meta name="description" content="<?php echo e($shareDescription); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="py-8 sm:py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <div class="mb-6">
            <a href="<?php echo e(route('tuts.index')); ?>" class="inline-flex items-center text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Tutorials
            </a>
        </div>

        <!-- Tutorial Header -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="px-3 py-1 text-sm rounded-full 
                    <?php echo e($tutorial->category === 'design' ? 'bg-purple-100 text-purple-800' : ''); ?>

                    <?php echo e($tutorial->category === 'web' ? 'bg-blue-100 text-blue-800' : ''); ?>

                    <?php echo e($tutorial->category === 'photo' ? 'bg-green-100 text-green-800' : ''); ?>

                    <?php echo e($tutorial->category === 'business' ? 'bg-yellow-100 text-yellow-800' : ''); ?>">
                    <?php echo e($tutorial->category_label); ?>

                </span>
                <?php if($tutorial->featured): ?>
                    <span class="px-3 py-1 text-sm rounded-full bg-yellow-100 text-yellow-800">⭐ Featured</span>
                <?php endif; ?>
            </div>

            <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo e($tutorial->title); ?></h1>

            <?php if($tutorial->description): ?>
                <p class="text-lg text-gray-600 mb-4"><?php echo e($tutorial->description); ?></p>
            <?php endif; ?>

            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span><?php echo e($tutorial->author->name); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span><?php echo e(number_format($tutorial->views_count)); ?> views</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span><?php echo e($tutorial->created_at->format('d M Y')); ?></span>
                </div>
            </div>
        </div>

        <!-- Thumbnail -->
        <?php if($tutorial->thumbnail): ?>
            <div class="mb-6 rounded-lg overflow-hidden">
                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($tutorial->thumbnail)); ?>" alt="<?php echo e($tutorial->title); ?>" class="w-full h-auto">
            </div>
        <?php endif; ?>

        <!-- Video -->
        <?php if($tutorial->video_url): ?>
            <div class="mb-6 rounded-lg overflow-hidden bg-gray-900">
                <div class="aspect-video">
                    <?php
                        // Parse YouTube/Vimeo URL
                        $videoId = null;
                        $videoType = null;
                        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/', $tutorial->video_url, $matches)) {
                            $videoId = $matches[1];
                            $videoType = 'youtube';
                        } elseif (preg_match('/(?:vimeo\.com\/|player\.vimeo\.com\/video\/)(\d+)/', $tutorial->video_url, $matches)) {
                            $videoId = $matches[1];
                            $videoType = 'vimeo';
                        }
                    ?>
                    <?php if($videoType === 'youtube'): ?>
                        <iframe src="https://www.youtube.com/embed/<?php echo e($videoId); ?>" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    <?php elseif($videoType === 'vimeo'): ?>
                        <iframe src="https://player.vimeo.com/video/<?php echo e($videoId); ?>" 
                                class="w-full h-full" 
                                frameborder="0" 
                                allow="autoplay; fullscreen; picture-in-picture" 
                                allowfullscreen>
                        </iframe>
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <a href="<?php echo e($tutorial->video_url); ?>" target="_blank" class="text-white hover:text-blue-400">
                                Watch Video
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6 mb-6">
            <div class="prose max-w-none quill-content">
                <?php echo $tutorial->content; ?>

            </div>
        </div>

        <!-- Related Tutorials -->
        <?php if($relatedTutorials->count() > 0): ?>
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Related Tutorials</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php $__currentLoopData = $relatedTutorials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $related): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('tuts.show', $related)); ?>" class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors">
                            <?php if($related->thumbnail): ?>
                                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::url($related->thumbnail)); ?>" alt="<?php echo e($related->title); ?>" class="w-24 h-24 object-cover rounded">
                            <?php else: ?>
                                <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-purple-500 rounded flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 mb-1"><?php echo e($related->title); ?></h3>
                                <p class="text-xs text-gray-500"><?php echo e(number_format($related->views_count)); ?> views • <?php echo e($related->created_at->diffForHumans()); ?></p>
                            </div>
                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
.quill-content {
    line-height: 1.8;
}

.quill-content h1, .quill-content h2, .quill-content h3 {
    margin-top: 1.5em;
    margin-bottom: 0.5em;
    font-weight: 700;
}

.quill-content h1 {
    font-size: 2em;
}

.quill-content h2 {
    font-size: 1.5em;
}

.quill-content h3 {
    font-size: 1.25em;
}

.quill-content p {
    margin-bottom: 1em;
}

.quill-content ul, .quill-content ol {
    margin-bottom: 1em;
    padding-left: 2em;
}

.quill-content li {
    margin-bottom: 0.5em;
}

.quill-content img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1em 0;
}

.quill-content pre {
    background-color: #f3f4f6;
    padding: 1em;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1em 0;
}

.quill-content code {
    background-color: #f3f4f6;
    padding: 0.2em 0.4em;
    border-radius: 0.25rem;
    font-size: 0.9em;
}

.quill-content blockquote {
    border-left: 4px solid #3b82f6;
    padding-left: 1em;
    margin: 1em 0;
    font-style: italic;
    color: #6b7280;
}

.quill-content a {
    color: #3b82f6;
    text-decoration: underline;
}

.quill-content a:hover {
    color: #2563eb;
}
</style>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views\tuts\show.blade.php ENDPATH**/ ?>