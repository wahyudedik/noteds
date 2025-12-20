<script setup>
import { Link, router } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';
import { PURPOSE_TYPE_LABELS } from '@/Utils/constants';

const props = defineProps({
    posts: Object,
    filters: Object,
    userVotes: {
        type: Object,
        default: () => ({}),
    },
});

const filterByPurpose = (purposeType) => {
    router.get(route('home'), { purpose_type: purposeType || 'all' }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div>
        <!-- Filters -->
        <div class="mb-4 flex flex-wrap gap-2">
            <button
                @click="filterByPurpose('all')"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    (!filters?.purpose_type || filters.purpose_type === 'all')
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                ]"
            >
                All
            </button>
            <button
                v-for="(label, type) in PURPOSE_TYPE_LABELS"
                :key="type"
                @click="filterByPurpose(type)"
                :class="[
                    'px-4 py-2 rounded-lg text-sm font-medium transition',
                    filters?.purpose_type === type
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                ]"
            >
                {{ label }}
            </button>
        </div>

        <!-- Posts List -->
        <div class="space-y-4">
            <PostCard
                v-for="post in posts.data"
                :key="post.id"
                :post="post"
                :user-vote="userVotes && userVotes[post.id] ? userVotes[post.id] : null"
            />

            <!-- Empty State -->
            <div v-if="posts.data.length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    No posts found. Be the first to share!
                </p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="posts.links && posts.links.length > 3" class="mt-6 flex justify-center">
            <div class="flex gap-2">
                <Link
                    v-for="(link, index) in posts.links"
                    :key="index"
                    :href="link.url || '#'"
                    :class="[
                        'px-4 py-2 rounded-lg text-sm font-medium transition',
                        link.active
                            ? 'bg-indigo-600 text-white'
                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700',
                        !link.url && 'opacity-50 cursor-not-allowed'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>
    </div>
</template>

