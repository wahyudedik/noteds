<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    product: {
        type: Object,
        default: null,
    },
});

const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes
const MAX_IMAGE_SIZE = 2 * 1024 * 1024; // 2MB in bytes

const form = useForm({
    name: props.product?.name || '',
    description: props.product?.description || '',
    price: props.product?.price || '',
    category: props.product?.category || '',
    image: null,
    file_download: null,
    license_key: props.product?.license_key ? true : false,
    stock: props.product?.stock || null,
    is_active: props.product?.is_active ?? true,
});

const fileSizeError = ref(null);
const imageSizeError = ref(null);
const selectedFileSize = ref(null);
const selectedImageSize = ref(null);

const isEdit = computed(() => !!props.product);

const checkFileSize = (file, maxSize, type = 'file') => {
    if (file && file.size > maxSize) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        const maxMB = (maxSize / 1024 / 1024).toFixed(0);
        return `File terlalu besar (${sizeMB}MB). Maksimal ${maxMB}MB.`;
    }
    return null;
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const error = checkFileSize(file, MAX_FILE_SIZE);
        if (error) {
            fileSizeError.value = error;
            form.file_download = null;
            selectedFileSize.value = null;
            event.target.value = ''; // Clear input
        } else {
            fileSizeError.value = null;
            form.file_download = file;
            selectedFileSize.value = (file.size / 1024 / 1024).toFixed(2);
        }
    }
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const error = checkFileSize(file, MAX_IMAGE_SIZE, 'image');
        if (error) {
            imageSizeError.value = error;
            form.image = null;
            selectedImageSize.value = null;
            event.target.value = ''; // Clear input
        } else {
            imageSizeError.value = null;
            form.image = file;
            selectedImageSize.value = (file.size / 1024 / 1024).toFixed(2);
        }
    }
};

const submit = () => {
    // Clear any previous errors
    fileSizeError.value = null;
    imageSizeError.value = null;

    // Final check before submit
    if (form.file_download && form.file_download.size > MAX_FILE_SIZE) {
        fileSizeError.value = checkFileSize(form.file_download, MAX_FILE_SIZE);
        return;
    }

    if (form.image && form.image.size > MAX_IMAGE_SIZE) {
        imageSizeError.value = checkFileSize(form.image, MAX_IMAGE_SIZE, 'image');
        return;
    }

    if (isEdit.value) {
        form.put(route('marketplace.products.update', props.product.id), {
            forceFormData: true,
        });
    } else {
        form.post(route('marketplace.products.store'), {
            forceFormData: true,
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div class="space-y-4 sm:space-y-6">
            <div>
                <label class="block text-sm sm:text-base font-medium mb-2 text-gray-900 dark:text-white">Product Name *</label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-3 sm:px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <div v-if="form.errors.name" class="text-red-500 dark:text-red-400 text-sm mt-1">
                    {{ form.errors.name }}
                </div>
            </div>

            <div>
                <label class="block text-sm sm:text-base font-medium mb-2 text-gray-900 dark:text-white">Description *</label>
                <textarea
                    v-model="form.description"
                    required
                    rows="5"
                    class="w-full px-3 sm:px-4 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                ></textarea>
                <div v-if="form.errors.description" class="text-red-500 dark:text-red-400 text-sm mt-1">
                    {{ form.errors.description }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Price (Rp) *</label>
                    <input
                        v-model.number="form.price"
                        type="number"
                        min="0"
                        step="1000"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                    <div v-if="form.errors.price" class="text-red-500 text-sm mt-1">
                        {{ form.errors.price }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Category</label>
                    <input
                        v-model="form.category"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Product Image</label>
                <input
                    @change="handleImageChange"
                    type="file"
                    accept="image/*"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <div v-if="selectedImageSize" class="text-sm text-gray-500 mt-1">
                    Ukuran file: {{ selectedImageSize }}MB (Maksimal: 2MB)
                </div>
                <div v-if="imageSizeError" class="text-red-500 text-sm mt-1">
                    {{ imageSizeError }}
                </div>
                <div v-if="form.errors.image" class="text-red-500 text-sm mt-1">
                    {{ form.errors.image }}
                </div>
                <div v-if="product?.image" class="mt-2">
                    <img :src="product.image" alt="Current image" class="h-32 object-cover rounded" />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Digital File (Max 50MB)</label>
                <input
                    @change="handleFileChange"
                    type="file"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <div v-if="selectedFileSize" class="text-sm text-gray-500 mt-1">
                    Ukuran file: {{ selectedFileSize }}MB (Maksimal: 50MB)
                </div>
                <div v-if="fileSizeError" class="text-red-500 text-sm mt-1 font-medium">
                    {{ fileSizeError }}
                </div>
                <div v-if="form.errors.file_download" class="text-red-500 text-sm mt-1">
                    {{ form.errors.file_download }}
                </div>
                <p class="text-sm text-gray-500 mt-1">Upload the digital product file</p>
            </div>

            <div>
                <label class="flex items-center space-x-2">
                    <input
                        v-model="form.license_key"
                        type="checkbox"
                        class="rounded"
                    />
                    <span>Requires License Key</span>
                </label>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Stock (optional, leave empty for unlimited)</label>
                <input
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
            </div>

            <div v-if="isEdit">
                <label class="flex items-center space-x-2">
                    <input
                        v-model="form.is_active"
                        type="checkbox"
                        class="rounded"
                    />
                    <span>Active</span>
                </label>
            </div>

            <div class="flex justify-end space-x-4">
                <Link
                    :href="isEdit ? route('marketplace.products.show', product.id) : route('marketplace.index')"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing || fileSizeError || imageSizeError"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    {{ isEdit ? 'Update' : 'Create' }} Product
                </button>
            </div>
        </div>
    </form>
</template>

