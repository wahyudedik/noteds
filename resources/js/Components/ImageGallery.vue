<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        required: true,
    },
    initialIndex: {
        type: Number,
        default: 0,
    },
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const currentIndex = ref(props.initialIndex);
const isZoomed = ref(false);

const currentImage = computed(() => {
    return props.images[currentIndex.value] || null;
});

const hasNext = computed(() => currentIndex.value < props.images.length - 1);
const hasPrev = computed(() => currentIndex.value > 0);

const nextImage = () => {
    if (hasNext.value) {
        currentIndex.value++;
        isZoomed.value = false;
    }
};

const prevImage = () => {
    if (hasPrev.value) {
        currentIndex.value--;
        isZoomed.value = false;
    }
};

const close = () => {
    emit('close');
};

const toggleZoom = () => {
    isZoomed.value = !isZoomed.value;
};

const getImageUrl = (media) => {
    if (media.url) {
        return media.url;
    }
    if (media.file_path) {
        return `/storage/${media.file_path}`;
    }
    return '';
};

const handleKeydown = (e) => {
    if (!props.show) return;
    
    switch (e.key) {
        case 'Escape':
            close();
            break;
        case 'ArrowLeft':
            prevImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    if (props.show) {
        document.body.style.overflow = 'hidden';
    }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    document.body.style.overflow = '';
});

// Watch for show prop changes
watch(() => props.show, (isShow) => {
    if (isShow) {
        currentIndex.value = props.initialIndex;
        isZoomed.value = false;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

// Watch for initialIndex changes
watch(() => props.initialIndex, (newIndex) => {
    currentIndex.value = newIndex;
    isZoomed.value = false;
});
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed inset-0 z-[10000] bg-black bg-opacity-90 flex items-center justify-center"
            @click.self="close"
        >
            <!-- Close Button -->
            <button
                @click="close"
                class="absolute top-4 right-4 z-10 text-white hover:text-gray-300 transition-colors p-2"
                type="button"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Previous Button -->
            <button
                v-if="hasPrev"
                @click="prevImage"
                class="absolute left-4 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black bg-opacity-50 rounded-full"
                type="button"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <!-- Next Button -->
            <button
                v-if="hasNext"
                @click="nextImage"
                class="absolute right-4 z-10 text-white hover:text-gray-300 transition-colors p-2 bg-black bg-opacity-50 rounded-full"
                type="button"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <!-- Image Container -->
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <div
                    class="relative max-w-full max-h-full"
                    :class="{ 'cursor-zoom-in': !isZoomed, 'cursor-zoom-out': isZoomed }"
                    @click="toggleZoom"
                >
                    <img
                        v-if="currentImage"
                        :src="getImageUrl(currentImage)"
                        :alt="currentImage.file_name || 'Image'"
                        class="max-w-full max-h-[90vh] object-contain transition-transform duration-300"
                        :class="{ 'scale-150': isZoomed }"
                    />
                </div>
            </div>

            <!-- Image Counter -->
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-4 py-2 rounded-full text-sm">
                {{ currentIndex + 1 }} / {{ images.length }}
            </div>

            <!-- Zoom Indicator -->
            <div
                v-if="isZoomed"
                class="absolute top-20 left-1/2 transform -translate-x-1/2 text-white bg-black bg-opacity-50 px-4 py-2 rounded-full text-sm"
            >
                Klik untuk zoom out
            </div>
        </div>
    </Transition>
</template>

