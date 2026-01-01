<script setup>
import { ref, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB

const nameInput = ref(null);

const form = useForm({
    name: '',
    description: '',
    price: '',
    category: '',
    image: null,
    file_download: null,
    license_key: false,
    stock: '',
});

const imagePreview = ref(null);
const fileSizeError = ref(null);

const checkFileSize = (file) => {
    if (file && file.size > MAX_FILE_SIZE) {
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        return `File terlalu besar (${sizeMB}MB). Maksimal 50MB.`;
    }
    return null;
};

const handleImageChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const error = checkFileSize(file);
        if (error) {
            fileSizeError.value = error;
            form.image = null;
            return;
        }
        fileSizeError.value = null;
        form.image = file;
        
        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        const error = checkFileSize(file);
        if (error) {
            fileSizeError.value = error;
            form.file_download = null;
            return;
        }
        fileSizeError.value = null;
        form.file_download = file;
    }
};

const submit = () => {
    form.post(route('marketplace.products.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            imagePreview.value = null;
            fileSizeError.value = null;
            emit('close');
        },
        onError: () => {
            // Keep modal open if there are validation errors
        },
    });
};

const close = () => {
    form.reset();
    imagePreview.value = null;
    fileSizeError.value = null;
    emit('close');
};

// Close on ESC key
const handleEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

watch(() => props.show, async (isShow) => {
    if (isShow) {
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', handleEscape);
        // Focus on name input after modal is shown
        await nextTick();
        if (nameInput.value) {
            setTimeout(() => {
                nameInput.value?.focus();
            }, 100);
        }
    } else {
        document.body.style.overflow = '';
        window.removeEventListener('keydown', handleEscape);
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
    window.removeEventListener('keydown', handleEscape);
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
            class="fixed inset-0 z-[9999] overflow-y-auto"
            @click.self="close"
        >
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <!-- Modal Container -->
            <div class="relative flex min-h-full items-center justify-center p-4 w-full">
                <!-- Modal -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div
                        v-if="show"
                        class="relative w-full max-w-2xl max-h-[90vh] transform overflow-y-auto rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6"
                        @click.stop
                    >
                        <div class="absolute right-0 top-0 pr-4 pt-4">
                            <button
                                @click="close"
                                class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                    Add New Product
                                </h3>

                                <form @submit.prevent="submit" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Product Name *
                                        </label>
                                        <TextInput
                                            ref="nameInput"
                                            v-model="form.name"
                                            type="text"
                                            class="w-full"
                                            placeholder="Enter product name"
                                            required
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Description *
                                        </label>
                                        <Textarea
                                            v-model="form.description"
                                            class="w-full"
                                            rows="4"
                                            placeholder="Describe your product..."
                                            required
                                        />
                                        <InputError :message="form.errors.description" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Price (Rp) *
                                            </label>
                                            <TextInput
                                                v-model.number="form.price"
                                                type="number"
                                                min="0"
                                                step="1000"
                                                class="w-full"
                                                placeholder="0"
                                                required
                                            />
                                            <InputError :message="form.errors.price" />
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Category
                                            </label>
                                            <TextInput
                                                v-model="form.category"
                                                type="text"
                                                class="w-full"
                                                placeholder="e.g. Software, Template"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Product Image
                                        </label>
                                        <input
                                            @change="handleImageChange"
                                            type="file"
                                            accept="image/*"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        />
                                        <InputError :message="form.errors.image" />
                                        <div v-if="fileSizeError" class="text-red-500 text-sm mt-1">
                                            {{ fileSizeError }}
                                        </div>
                                        <div v-if="imagePreview" class="mt-2">
                                            <img :src="imagePreview" alt="Preview" class="h-32 w-32 object-cover rounded" />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Digital File (Max 50MB)
                                        </label>
                                        <input
                                            @change="handleFileChange"
                                            type="file"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                        />
                                        <InputError :message="form.errors.file_download" />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Upload the digital product file
                                        </p>
                                    </div>

                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <input
                                                v-model="form.license_key"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span class="text-sm text-gray-700 dark:text-gray-300">Requires License Key</span>
                                        </label>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Stock (optional, leave empty for unlimited)
                                        </label>
                                        <TextInput
                                            v-model.number="form.stock"
                                            type="number"
                                            min="0"
                                            class="w-full"
                                            placeholder="Leave empty for unlimited"
                                        />
                                    </div>

                                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <button
                                            type="button"
                                            @click="close"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :disabled="form.processing">
                                            {{ form.processing ? 'Creating...' : 'Create Product' }}
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </Transition>
</template>

