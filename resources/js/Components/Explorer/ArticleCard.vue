<script setup>
import { computed } from 'vue';
import SocialShareButtons from '@/Components/Social/SocialShareButtons.vue';

const props = defineProps({
    article: {
        type: Object,
        required: true,
    },
});

const formattedDate = computed(() => {
    if (!props.article.published_at) return '';
    const date = new Date(props.article.published_at);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(date);
});

const btoaSafe = (s) => {
    try {
        if (typeof window !== 'undefined' && typeof window.btoa === 'function') {
            return window.btoa(String(s));
        }
        if (typeof Buffer !== 'undefined') {
            return Buffer.from(String(s)).toString('base64');
        }
    } catch {}
    return String(s);
};
</script>

<template>
    <a
        :href="article.url"
        target="_blank"
        rel="noopener noreferrer"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow block"
    >
        <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
            <img
                v-if="article.image"
                :src="article.image"
                :alt="article.title"
                class="w-full h-48 object-cover"
            />
            <div v-else class="w-full h-48 flex items-center justify-center text-gray-400 dark:text-gray-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
            </div>
        </div>
        <div class="p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2">
                {{ article.title }}
            </h3>
            <p v-if="article.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                {{ article.description }}
            </p>
            <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400 mb-2">
                <span v-if="article.source" class="font-medium">
                    {{ article.source }}
                </span>
                <span v-if="formattedDate">
                    {{ formattedDate }}
                </span>
            </div>
            <div v-if="article.category" class="mt-2">
                <span class="text-xs px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded">
                    {{ article.category }}
                </span>
            </div>
            <div class="mt-3">
                <SocialShareButtons
                    :url="article.url"
                    :title="article.title"
                    :description="article.description || ''"
                    :hashtags="[]"
                    share-type="external"
                    :share-id="btoaSafe(article.url)"
                />
            </div>
        </div>
    </a>
</template>

