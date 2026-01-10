<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import RepostCommentForm from './RepostCommentForm.vue';
import QuoteRepostEditor from './QuoteRepostEditor.vue';

const props = defineProps({
    postId: {
        type: String,
        required: true,
    },
    repostsCount: {
        type: Number,
        default: 0,
    },
    isReposted: {
        type: Boolean,
        default: false,
    },
    canRepost: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['reposted', 'unreposted']);

const showMenu = ref(false);
const showCommentForm = ref(false);
const showQuoteEditor = ref(false);

const repost = () => {
    router.post(route('posts.repost', props.postId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            emit('reposted');
            showMenu.value = false;
        },
    });
};

const repostWithComment = (comment) => {
    router.post(route('posts.repost', props.postId), {
        comment: comment,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('reposted');
            showCommentForm.value = false;
            showMenu.value = false;
        },
    });
};

const quoteRepost = (quoteContent, displayMode) => {
    router.post(route('posts.quote-repost', props.postId), {
        quote_content: quoteContent,
        display_mode: displayMode,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('reposted');
            showQuoteEditor.value = false;
            showMenu.value = false;
        },
    });
};

const unrepost = () => {
    router.delete(route('posts.unrepost', props.postId), {
        preserveScroll: true,
        onSuccess: () => {
            emit('unreposted');
        },
    });
};

const openCommentForm = () => {
    showCommentForm.value = true;
    showMenu.value = false;
};

const openQuoteEditor = () => {
    showQuoteEditor.value = true;
    showMenu.value = false;
};
</script>

<template>
    <div class="relative">
        <button
            v-if="!isReposted"
            @click="showMenu = !showMenu"
            class="flex items-center gap-1 px-2 py-1 text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>{{ repostsCount || 0 }}</span>
        </button>
        
        <button
            v-else
            @click="unrepost"
            class="flex items-center gap-1 px-2 py-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition"
        >
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span>{{ repostsCount || 0 }}</span>
        </button>

        <!-- Dropdown Menu -->
        <div
            v-if="showMenu && !isReposted"
            class="absolute z-50 mt-1 w-48 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md shadow-lg"
            @click.stop
        >
            <div class="py-1">
                <button
                    @click="repost"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Repost
                </button>
                <button
                    @click="openCommentForm"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Repost with Comment
                </button>
                <button
                    @click="openQuoteEditor"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                    Quote Repost
                </button>
            </div>
        </div>

        <!-- Comment Form Modal -->
        <RepostCommentForm
            :show="showCommentForm"
            :post-id="postId"
            @close="showCommentForm = false"
            @submit="repostWithComment"
        />

        <!-- Quote Editor Modal -->
        <QuoteRepostEditor
            :show="showQuoteEditor"
            :post-id="postId"
            @close="showQuoteEditor = false"
            @submit="quoteRepost"
        />

        <!-- Click outside to close -->
        <div
            v-if="showMenu"
            class="fixed inset-0 z-40"
            @click="showMenu = false"
        ></div>
    </div>
</template>

