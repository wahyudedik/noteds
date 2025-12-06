<?php if($images && count($images) > 0): ?>
    <div class="mt-8 mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php echo e($title); ?></h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden group cursor-pointer"
                    onclick="openImageModal('<?php echo e(asset('storage/' . $image)); ?>', <?php echo e($index); ?>, <?php echo e(count($images)); ?>)">
                    <img src="<?php echo e(asset('storage/' . $image)); ?>" alt="<?php echo e($title); ?>"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300">
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Modal Popup -->
    <div id="imageModal" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center" style="display: none;"
        onclick="closeImageModal(event)">
        <div class="relative max-w-4xl w-full mx-4" onclick="event.stopPropagation()">
            <img id="modalImage" src="" alt="Full view"
                class="w-full h-auto rounded-lg max-h-[80vh] object-contain">

            <!-- Close Button -->
            <button onclick="closeImageModal()"
                class="absolute top-4 right-4 bg-white/90 hover:bg-white rounded-full p-2 transition-colors">
                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Navigation -->
            <button id="prevBtn" onclick="prevImage(event)"
                class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full p-2 transition-colors">
                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <button id="nextBtn" onclick="nextImage(event)"
                class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full p-2 transition-colors">
                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Counter -->
            <div
                class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-black/60 text-white px-4 py-2 rounded-full text-sm">
                <span id="imageCounter">1</span> / <?php echo e(count($images)); ?>

            </div>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        const totalImages = <?php echo e(count($images)); ?>;
        const images = [
            <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                '<?php echo e(asset('storage/' . $image)); ?>',
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        ];

        function openImageModal(imageSrc, index, total) {
            currentImageIndex = index;
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('imageCounter').textContent = index + 1;
            document.getElementById('imageModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            updateNavButtons();
        }

        function closeImageModal(event) {
            if (event && event.target.id !== 'imageModal') return;
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function prevImage(event) {
            event.stopPropagation();
            currentImageIndex = (currentImageIndex - 1 + totalImages) % totalImages;
            document.getElementById('modalImage').src = images[currentImageIndex];
            document.getElementById('imageCounter').textContent = currentImageIndex + 1;
            updateNavButtons();
        }

        function nextImage(event) {
            event.stopPropagation();
            currentImageIndex = (currentImageIndex + 1) % totalImages;
            document.getElementById('modalImage').src = images[currentImageIndex];
            document.getElementById('imageCounter').textContent = currentImageIndex + 1;
            updateNavButtons();
        }

        function updateNavButtons() {
            document.getElementById('prevBtn').style.display = currentImageIndex === 0 ? 'none' : 'block';
            document.getElementById('nextBtn').style.display = currentImageIndex === totalImages - 1 ? 'none' : 'block';
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('imageModal').style.display === 'none') return;
            if (e.key === 'ArrowLeft') prevImage({
                stopPropagation: () => {}
            });
            if (e.key === 'ArrowRight') nextImage({
                stopPropagation: () => {}
            });
            if (e.key === 'Escape') closeImageModal();
        });
    </script>
<?php endif; ?>
<?php /**PATH D:\PROJECT\LARAVEL\noteds\resources\views/components/media-gallery.blade.php ENDPATH**/ ?>