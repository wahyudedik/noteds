<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    reposts: {
        type: Array,
        default: () => [],
    },
    postId: {
        type: String,
        required: true,
    },
});

const filterType = ref('all');
const filteredReposts = computed(() => {
    if (filterType.value === 'all') {
        return props.reposts;
    }
    return props.reposts.filter(repost => {
        if (filterType.value === 'quote') {
            return repost.is_quote_repost;
        }
        if (filterType.value === 'with_comment') {
            return repost.comment;
        }
        if (filterType.value === 'regular') {
            return !repost.is_quote_repost && !repost.comment;
        }
        return true;
    });
});

const loadMore = () => {
    router.get(route('reposts.reposters', props.postId), {
        type: filterType.value,
        page: (props.reposts.current_page || 1) + 1,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div class="space-y-4">
        <!-- Filters -->
        <div class="flex gap-2 flex-wrap">
            <button
                v-for="type in [
                    { value: 'all', label: 'All' },
                    { value: 'regular', label: 'Regular' },
                    { value: 'quote', label: 'Quote' },
                    { value: 'with_comment', label: 'With Comment' },
                ]"
                :key="type.value"
                @click="filterType = type.value"
                :class="[
                    'px-3 py-1 text-sm rounded-md transition',
                    filterType === type.value
                        ? 'bg-indigo-600 text-white'
                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
            >
                {{ type.label }}
            </button>
        </div>

        <!-- Reposters List -->
        <div class="space-y-2">
            <div
                v-for="repost in filteredReposts"
                :key="repost.id"
                class="p-3 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700"
            >
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3 flex-1">
                        <Link
                            :href="route('profile.show', repost.user?.id)"
                            class="flex-shrink-0"
                        >
                            <img
                                :src="repost.user?.avatar || '/default-avatar.png'"
                                :alt="repost.user?.name"
                                class="w-10 h-10 rounded-full"
                            />
                        </Link>
                        <div class="flex-1 min-w-0">
                            <Link
                                :href="route('profile.show', repost.user?.id)"
                                class="font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600"
                            >
                                {{ repost.user?.business_name || repost.user?.name }}
                            </Link>
                            <div class="flex items-center gap-2 mt-1">
                                <span
                                    v-if="repost.is_quote_repost"
                                    class="text-xs px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 rounded"
                                >
                                    Quote
                                </span>
                                <span
                                    v-if="repost.comment"
                                    class="text-xs px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded"
                                >
                                    With Comment
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ repost.created_at }}
                                </span>
                            </div>
                            <p v-if="repost.comment" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                {{ repost.comment }}
                            </p>
                            <p v-if="repost.quote_content" class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ repost.quote_content }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="filteredReposts.length === 0" class="text-center py-8 text-gray-500">
                No reposts found.
            </div>
        </div>

        <!-- Load More -->
        <div v-if="reposts.has_more_pages" class="text-center">
            <button
                @click="loadMore"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
            >
                Load More
            </button>
        </div>
    </div>
</template>

