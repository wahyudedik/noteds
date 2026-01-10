<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';

const props = defineProps({
    repost: {
        type: Object,
        required: true,
    },
    originalPost: {
        type: Object,
        required: true,
    },
    quotePost: {
        type: Object,
        default: null,
    },
    displayMode: {
        type: String,
        default: 'embedded',
    },
});

const isEmbedded = computed(() => props.displayMode === 'embedded' || !props.quotePost);
const isSeparate = computed(() => props.displayMode === 'separate' && props.quotePost);
</script>

<template>
    <div v-if="isSeparate && quotePost" class="quote-repost-separate">
        <!-- Display as separate post -->
        <PostCard
            :post="quotePost"
            :user-vote="null"
        />
    </div>
    
    <div v-else class="quote-repost-embedded">
        <!-- Display as embedded (Twitter-style) -->
        <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
            <!-- Quote Content -->
            <div class="p-4 bg-white dark:bg-gray-800">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ repost.user?.name || repost.user?.business_name }}
                    </span>
                    <span class="text-xs text-gray-500">quoted</span>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ repost.quote_content }}
                </div>
            </div>
            
            <!-- Original Post Embedded -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900">
                <div class="flex items-center gap-2 mb-2">
                    <Link
                        :href="route('profile.show', originalPost.user?.id)"
                        class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-indigo-600"
                    >
                        {{ originalPost.user?.business_name || originalPost.user?.name }}
                    </Link>
                    <span class="text-xs text-gray-500">·</span>
                    <span class="text-xs text-gray-500">{{ originalPost.created_at }}</span>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    {{ originalPost.title }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                    {{ originalPost.content }}
                </p>
                <Link
                    :href="route('posts.show', originalPost.id)"
                    class="text-xs text-indigo-600 hover:text-indigo-700 mt-2 inline-block"
                >
                    View original post →
                </Link>
            </div>
        </div>
    </div>
</template>

