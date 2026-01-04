<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    maxImages: {
        type: Number,
        default: 5,
    },
    maxSize: {
        type: Number,
        default: 5120, // 5MB in KB
    },
    label: {
        type: String,
        default: 'Transfer Proof Images',
    },
});

const emit = defineEmits(['update:modelValue']);

const fileInput = ref(null);
const isDragging = ref(false);
const errors = ref([]);

const images = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value),
});

const canAddMore = computed(() => images.value.length < props.maxImages);

const handleFileSelect = (files) => {
    errors.value = [];
    const fileArray = Array.from(files);
    const remainingSlots = props.maxImages - images.value.length;

    if (fileArray.length > remainingSlots) {
        errors.value.push(`You can only add ${remainingSlots} more image(s).`);
        fileArray.splice(remainingSlots);
    }

    fileArray.forEach((file) => {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            errors.value.push(`${file.name} is not a valid image file.`);
            return;
        }

        // Validate file size
        const fileSizeKB = file.size / 1024;
        if (fileSizeKB > props.maxSize) {
            errors.value.push(`${file.name} is too large. Maximum size is ${props.maxSize}KB (${(props.maxSize / 1024).toFixed(1)}MB).`);
            return;
        }

        // Check if already added
        if (images.value.some((img) => img.name === file.name && img.size === file.size)) {
            return;
        }

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            images.value.push({
                file,
                preview: e.target.result,
                name: file.name,
                size: file.size,
            });
        };
        reader.readAsDataURL(file);
    });
};

const handleDrop = (e) => {
    e.preventDefault();
    isDragging.value = false;

    if (!canAddMore.value) {
        return;
    }

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        handleFileSelect(files);
    }
};

const handleDragOver = (e) => {
    e.preventDefault();
    if (canAddMore.value) {
        isDragging.value = true;
    }
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const openFileDialog = () => {
    if (canAddMore.value) {
        fileInput.value?.click();
    }
};

const removeImage = (index) => {
    images.value.splice(index, 1);
    errors.value = [];
};

const handleInputChange = (e) => {
    const files = e.target.files;
    if (files && files.length > 0) {
        handleFileSelect(files);
    }
    // Reset input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <div class="space-y-3">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ label }} (Optional, Max {{ maxImages }} images, {{ (maxSize / 1024).toFixed(1) }}MB each)
        </label>

        <!-- Error Messages -->
        <div v-if="errors.length > 0" class="text-sm text-red-600 dark:text-red-400 space-y-1">
            <div v-for="(error, index) in errors" :key="index">
                {{ error }}
            </div>
        </div>

        <!-- Upload Area -->
        <div
            v-if="canAddMore"
            @drop="handleDrop"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @click="openFileDialog"
            :class="[
                'border-2 border-dashed rounded-lg p-4 text-center cursor-pointer transition-colors',
                isDragging
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                    : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500 hover:bg-gray-50 dark:hover:bg-gray-700/50',
            ]"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                accept="image/*"
                class="hidden"
                @change="handleInputChange"
            />
            <div class="flex flex-col items-center justify-center">
                <svg
                    class="w-8 h-8 text-gray-400 dark:text-gray-500 mb-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                </svg>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium text-blue-600 dark:text-blue-400">Click to upload</span>
                    or drag and drop
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                    PNG, JPG, GIF, WebP up to {{ (maxSize / 1024).toFixed(1) }}MB ({{ maxImages - images.length }} remaining)
                </p>
            </div>
        </div>

        <!-- Image Preview Grid -->
        <div v-if="images.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <div
                v-for="(image, index) in images"
                :key="index"
                class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700"
            >
                <img
                    :src="image.preview"
                    :alt="image.name"
                    class="w-full h-full object-cover"
                />
                <!-- Remove Button -->
                <button
                    @click.stop="removeImage(index)"
                    class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1.5 opacity-0 group-hover:opacity-100 transition-opacity shadow-lg"
                    type="button"
                    title="Remove image"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>

