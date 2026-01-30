<script setup>
import { ref, watch } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import PostPurposeSelector from '@/Components/PostPurposeSelector.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import LinkPreview from '@/Components/LinkPreview.vue';
import { detectUrl } from '@/Utils/linkDetector';

const page = usePage();

const showComposer = ref(false);
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
    scheduled_at: null,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
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

// Props: optional shareDraft
const props = defineProps({
    shareDraft: {
        type: Object,
        default: null,
    },
});

// Prefill if shareDraft provided
const prefillFromShareDraft = async () => {
    if (!props.shareDraft) return;
    showComposer.value = true;
    form.title = props.shareDraft.title || '';
    form.content = props.shareDraft.content || '';
    form.link_url = props.shareDraft.link_url || null;
    form.link_preview_title = props.shareDraft.link_preview_title || null;
    form.link_preview_description = props.shareDraft.link_preview_description || null;
    form.link_preview_image = props.shareDraft.link_preview_image || null;
    form.link_preview_site_name = props.shareDraft.link_preview_site_name || null;
    if (form.link_url) {
        await generateLinkPreview(form.link_url);
    }
};

if (page.props.shareDraft && !props.shareDraft) {
    // Safety if passed via page props only
    props.shareDraft = page.props.shareDraft;
}

prefillFromShareDraft();

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
            showComposer.value = false;
        },
        onError: (errors) => {
            // Handle 429 error - when there are no validation errors but request failed
            // This usually means rate limiting
            if (!errors || Object.keys(errors).length === 0) {
                // Check if form has a general error message
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

const toggleComposer = () => {
    showComposer.value = !showComposer.value;
    if (!showComposer.value) {
        form.reset();
        images.value = [];
        linkPreview.value = null;
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 sm:p-6">
        <div v-if="!showComposer" class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold flex-shrink-0 overflow-hidden">
                <img
                    v-if="page.props.auth?.user?.avatar_url"
                    :src="page.props.auth.user.avatar_url"
                    :alt="page.props.auth.user.business_name || page.props.auth.user.name"
                    class="w-full h-full object-cover"
                />
                <span v-else>
                    {{ (page.props.auth?.user?.business_name || page.props.auth?.user?.name || 'U').charAt(0).toUpperCase() }}
                </span>
            </div>
            <button
                @click="toggleComposer"
                class="flex-1 text-left px-4 py-2 bg-gray-50 dark:bg-gray-700 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 transition"
            >
                Share your business idea or question...
            </button>
        </div>

        <div v-else class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Create Post
                </h3>
                <button
                    @click="toggleComposer"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <PostPurposeSelector v-model="form.purpose_type" />
                <InputError :message="form.errors.purpose_type" />

                <div>
                    <TextInput
                        id="post-title"
                        name="title"
                        v-model="form.title"
                        type="text"
                        class="w-full"
                        placeholder="Post title (min. 10 characters)"
                        required
                        autocomplete="off"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div>
                    <Textarea
                        id="post-content"
                        name="content"
                        v-model="form.content"
                        class="w-full"
                        rows="6"
                        placeholder="Share your thoughts... (min. 50 characters)"
                        required
                        autocomplete="off"
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

                <div class="flex items-center justify-end gap-3">
                    <div class="mr-auto flex items-center gap-2">
                        <label class="text-sm text-gray-700 dark:text-gray-300">Schedule</label>
                        <input type="datetime-local" v-model="form.scheduled_at" class="px-2 py-1 border rounded" />
                    </div>
                    <button
                        type="button"
                        @click="toggleComposer"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition"
                    >
                        Cancel
                    </button>
                    <PrimaryButton :disabled="form.processing">
                        Publish
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </div>
</template>

