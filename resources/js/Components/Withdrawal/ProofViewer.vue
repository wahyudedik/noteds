<script setup>
import { ref } from 'vue';

const props = defineProps({
    proofs: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Transfer Proof',
    },
    uploadedAt: {
        type: String,
        default: null,
    },
});

const selectedImageIndex = ref(null);
const showGallery = ref(false);

const openGallery = (index) => {
    selectedImageIndex.value = index;
    showGallery.value = true;
};

const closeGallery = () => {
    showGallery.value = false;
    selectedImageIndex.value = null;
};

const nextImage = () => {
    if (selectedImageIndex.value < props.proofs.length - 1) {
        selectedImageIndex.value++;
    }
};

const prevImage = () => {
    if (selectedImageIndex.value > 0) {
        selectedImageIndex.value--;
    }
};

const downloadImage = (proof) => {
    const link = document.createElement('a');
    link.href = proof.url;
    link.download = proof.path.split('/').pop() || 'proof.jpg';
    link.target = '_blank';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <div v-if="proofs && proofs.length > 0" class="space-y-3">
        <div>
            <h4 class="font-semibold mb-2">{{ title }}</h4>
            <p v-if="uploadedAt" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                Uploaded: {{ new Date(uploadedAt).toLocaleString() }}
            </p>
        </div>

        <!-- Image Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div
                v-for="(proof, index) in proofs"
                :key="index"
                class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-blue-400 dark:hover:border-blue-500 transition-colors"
                @click="openGallery(index)"
            >
                <img
                    :src="proof.url"
                    :alt="`Proof ${index + 1}`"
                    class="w-full h-full object-cover"
                />
                <!-- Overlay on hover -->
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-opacity flex items-center justify-center">
                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                        <button
                            @click.stop="downloadImage(proof)"
                            class="bg-white text-gray-800 rounded-full p-2 hover:bg-gray-100 shadow-lg"
                            title="Download"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Gallery Modal -->
        <div
            v-if="showGallery"
            class="fixed inset-0 z-50 bg-black bg-opacity-90 flex items-center justify-center p-4"
            @click="closeGallery"
        >
            <div class="relative max-w-7xl max-h-full" @click.stop>
                <!-- Close Button -->
                <button
                    @click="closeGallery"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 z-10 bg-black bg-opacity-50 rounded-full p-2"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>

                <!-- Image -->
                <img
                    :src="proofs[selectedImageIndex]?.url"
                    alt="Transfer proof"
                    class="max-w-full max-h-[90vh] object-contain mx-auto"
                />

                <!-- Navigation Buttons -->
                <button
                    v-if="selectedImageIndex > 0"
                    @click.stop="prevImage"
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 19l-7-7 7-7"
                        />
                    </svg>
                </button>

                <button
                    v-if="selectedImageIndex < proofs.length - 1"
                    @click.stop="nextImage"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                </button>

                <!-- Image Counter -->
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white bg-black bg-opacity-50 px-4 py-2 rounded-full text-sm">
                    {{ selectedImageIndex + 1 }} / {{ proofs.length }}
                </div>
            </div>
        </div>
    </div>
</template>

