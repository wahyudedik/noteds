<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import TipTapEditor from '@/Components/RichTextEditor/TipTapEditor.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    postId: {
        type: String,
        required: true,
    },
    originalPost: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close', 'submit']);

const quoteContent = ref('');
const displayMode = ref('embedded');
const originalPostData = ref(props.originalPost);
const isLoading = ref(false);
const fetchError = ref(null);

// Fetch original post when modal is shown
watch(() => props.show, (isVisible) => {
    if (isVisible && !originalPostData.value && props.postId && !isLoading.value) {
        fetchPost();
    }
}, { immediate: true });

const fetchPost = () => {
    if (!props.postId || isLoading.value) return;
    
    isLoading.value = true;
    fetchError.value = null;
    
    router.get(route('posts.show', props.postId), {}, {
        only: ['post'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            if (page.props.post) {
                originalPostData.value = page.props.post;
            } else {
                fetchError.value = 'Post not found or is not accessible.';
            }
            isLoading.value = false;
        },
        onError: (errors) => {
            fetchError.value = 'Unable to load the original post. It may have been deleted or is not accessible.';
            isLoading.value = false;
        },
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

watch(() => props.originalPost, (newVal) => {
    if (newVal) {
        originalPostData.value = newVal;
        fetchError.value = null; // Clear error if post is provided via prop
    }
});

watch(() => props.postId, (newId, oldId) => {
    // Only refetch if postId actually changed, modal is shown, and we don't have the post data
    if (newId && newId !== oldId && props.show && !originalPostData.value && !isLoading.value) {
        fetchPost();
    }
});

const submit = () => {
    if (quoteContent.value.trim()) {
        emit('submit', quoteContent.value, displayMode.value);
        quoteContent.value = '';
    }
};

const close = () => {
    quoteContent.value = '';
    emit('close');
};
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
        @click.self="close"
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full p-6 max-h-[80vh] flex flex-col">
            <h3 class="text-lg font-semibold mb-4">Quote Repost</h3>
            
            <div class="flex-1 overflow-auto space-y-4">
                <!-- Loading State -->
                <div v-if="isLoading" class="p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                    <div class="text-xs text-gray-500 mb-2">Loading original post...</div>
                </div>
                
                <!-- Error State -->
                <div v-else-if="fetchError" class="p-3 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                    <div class="text-xs text-red-600 dark:text-red-400 font-medium mb-1">Error</div>
                    <div class="text-sm text-red-700 dark:text-red-300">{{ fetchError }}</div>
                    <button
                        @click="fetchPost"
                        class="mt-2 text-xs text-red-600 dark:text-red-400 hover:underline"
                    >
                        Try again
                    </button>
                </div>
                
                <!-- Original Post Preview -->
                <div v-else-if="originalPostData" class="p-3 bg-gray-50 dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600">
                    <div class="text-xs text-gray-500 mb-2">Original Post</div>
                    <div class="font-medium text-sm">{{ originalPostData.title }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">
                        {{ originalPostData.content }}
                    </div>
                </div>
                
                <!-- Quote Content Editor -->
                <div>
                    <label class="block text-sm font-medium mb-2">Your Quote</label>
                    <TipTapEditor
                        v-model="quoteContent"
                        :placeholder="'Add your thoughts...'"
                        class="min-h-[200px]"
                    />
                </div>
                
                <!-- Display Mode Selector -->
                <div>
                    <label class="block text-sm font-medium mb-2">Display Mode</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="displayMode"
                                type="radio"
                                value="embedded"
                                class="rounded"
                            />
                            <span class="text-sm">Embedded (like Twitter)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="displayMode"
                                type="radio"
                                value="separate"
                                class="rounded"
                            />
                            <span class="text-sm">Separate Post</span>
                        </label>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ displayMode === 'embedded' 
                            ? 'Original post will be embedded within your quote' 
                            : 'A new post will be created with your quote' }}
                    </p>
                </div>
            </div>
            
            <div class="flex gap-2 mt-4">
                <button
                    @click="close"
                    class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                    Cancel
                </button>
                <button
                    @click="submit"
                    :disabled="!quoteContent.trim() || isLoading || (fetchError && !originalPostData)"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                >
                    Quote Repost
                </button>
            </div>
        </div>
    </div>
</template>

