<script setup>
import { watch, onMounted, onUnmounted, ref, nextTick } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import PostPurposeSelector from '@/Components/PostPurposeSelector.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import LinkPreview from '@/Components/LinkPreview.vue';
import { detectUrl } from '@/Utils/linkDetector';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const titleInput = ref(null);
const images = ref([]);
const linkPreview = ref(null);
const isGeneratingPreview = ref(false);

const form = useForm({
    purpose_type: '',
    title: '',
    content: '',
    images: [],
    link_url: null,
    link_preview_title: null,
    link_preview_description: null,
    link_preview_image: null,
    link_preview_site_name: null,
});

// Debounce function
let debounceTimer = null;
const debounce = (func, delay) => {
    return (...args) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => func(...args), delay);
    };
};

// Watch content for URL detection
watch(() => form.content, debounce((newContent) => {
    if (!newContent) {
        linkPreview.value = null;
        form.link_url = null;
        form.link_preview_title = null;
        form.link_preview_description = null;
        form.link_preview_image = null;
        form.link_preview_site_name = null;
        return;
    }

    const detectedUrl = detectUrl(newContent);
    if (detectedUrl && (!linkPreview.value || linkPreview.value.url !== detectedUrl)) {
        generateLinkPreview(detectedUrl);
    } else if (!detectedUrl && linkPreview.value) {
        linkPreview.value = null;
        form.link_url = null;
        form.link_preview_title = null;
        form.link_preview_description = null;
        form.link_preview_image = null;
        form.link_preview_site_name = null;
    }
}, 1000));

// Generate link preview
const generateLinkPreview = async (url) => {
    if (!url) return;

    // Validate URL format before sending
    try {
        new URL(url);
    } catch (e) {
        console.warn('Invalid URL format:', url);
        linkPreview.value = null;
        return;
    }

    isGeneratingPreview.value = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await fetch(route('link-preview.generate'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ url }),
        });

        const data = await response.json();

        if (!response.ok) {
            // Log validation errors if any
            if (data.errors) {
                console.warn('Validation errors:', data.errors);
            }
            if (data.message) {
                console.warn('Error message:', data.message);
            }
            linkPreview.value = null;
            return;
        }

        if (data.success && data.data) {
            linkPreview.value = data.data;
            form.link_url = data.data.url;
            form.link_preview_title = data.data.title;
            form.link_preview_description = data.data.description;
            form.link_preview_image = data.data.image;
            form.link_preview_site_name = data.data.site_name;
        } else {
            linkPreview.value = null;
        }
    } catch (error) {
        console.error('Error generating link preview:', error);
        linkPreview.value = null;
    } finally {
        isGeneratingPreview.value = false;
    }
};

// Remove link preview
const removeLinkPreview = () => {
    linkPreview.value = null;
    form.link_url = null;
    form.link_preview_title = null;
    form.link_preview_description = null;
    form.link_preview_image = null;
    form.link_preview_site_name = null;
};

const submit = () => {
    if (form.processing) return; // Prevent double submission

    // Set images in form (extract File objects from image data)
    form.images = images.value.map(img => img.file);

    // Submit using Inertia form
    form.post(route('posts.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            images.value = [];
            linkPreview.value = null;
            emit('close');
        },
        onError: (errors) => {
            // Keep modal open if there are validation errors
            // Handle 429 error - when there are no validation errors but request failed
            if (!errors || Object.keys(errors).length === 0) {
                const generalError = form.error('message') || form.error('error');
                if (generalError) {
                    alert(generalError);
                } else {
                    alert('Too many requests. Please wait a moment before creating another post.');
                }
            }
        },
    });
};

const close = () => {
    form.reset();
    images.value = [];
    linkPreview.value = null;
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
        // Focus on title input after modal is shown
        await nextTick();
        if (titleInput.value) {
            setTimeout(() => {
                titleInput.value?.focus();
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
                        class="relative w-full max-w-2xl transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:p-6"
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
                                    Create New Post
                                </h3>

                                <form @submit.prevent="submit" class="space-y-4">
                                    <PostPurposeSelector v-model="form.purpose_type" />
                                    <InputError :message="form.errors.purpose_type" />

                                    <div>
                                        <TextInput
                                            ref="titleInput"
                                            v-model="form.title"
                                            type="text"
                                            class="w-full"
                                            placeholder="Post title (min. 10 characters)"
                                            required
                                        />
                                        <InputError :message="form.errors.title" />
                                    </div>

                                    <div>
                                        <Textarea
                                            v-model="form.content"
                                            class="w-full"
                                            rows="8"
                                            placeholder="Share your thoughts... (min. 50 characters)"
                                            required
                                        />
                                        <InputError :message="form.errors.content" />
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            Focus on business-related content. Personal drama is not allowed.
                                        </p>
                                    </div>

                                    <!-- Image Uploader -->
                                    <div>
                                        <ImageUploader v-model="images" :max-images="10" :max-size="2048" />
                                        <InputError :message="form.errors.images" />
                                    </div>

                                    <!-- Link Preview -->
                                    <div v-if="linkPreview">
                                        <LinkPreview :preview="linkPreview" :show-remove="true" @remove="removeLinkPreview" />
                                    </div>

                                    <!-- Loading indicator for link preview -->
                                    <div v-if="isGeneratingPreview" class="text-sm text-gray-500 dark:text-gray-400">
                                        Generating link preview...
                                    </div>

                                    <div class="flex justify-end gap-3 pt-4">
                                        <button
                                            type="button"
                                            @click="close"
                                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                                        >
                                            Cancel
                                        </button>
                                        <PrimaryButton :disabled="form.processing">
                                            {{ form.processing ? 'Publishing...' : 'Publish' }}
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

