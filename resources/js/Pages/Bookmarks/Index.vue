<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    bookmarks: Object,
});

const page = usePage();

// Extract posts from bookmarks and create userVotes object
const posts = props.bookmarks.data.map(bookmark => bookmark.post);
const userVotes = {};

// If user is authenticated, we would need to fetch votes separately
// For now, we'll assume votes are not included in bookmarks
</script>

<template>
    <Head title="Bookmarks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Bookmarks
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div v-if="bookmarks.data.length === 0" class="text-center py-12">
                    <svg
                        class="mx-auto h-12 w-12 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"
                        />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        No bookmarks yet
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Start bookmarking posts to save them for later reading.
                    </p>
                    <div class="mt-6">
                        <Link
                            :href="route('home')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Browse Posts
                        </Link>
                    </div>
                </div>

                <div v-else>
                    <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                        {{ bookmarks.total }} {{ bookmarks.total === 1 ? 'bookmark' : 'bookmarks' }}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <PostCard
                            v-for="bookmark in bookmarks.data"
                            :key="bookmark.id"
                            :post="bookmark.post"
                            :user-vote="userVotes[bookmark.post.id] || null"
                        />
                    </div>

                    <!-- Pagination -->
                    <div v-if="bookmarks.links && bookmarks.links.length > 3" class="mt-6">
                        <div class="flex justify-center gap-2">
                            <Link
                                v-for="(link, index) in bookmarks.links"
                                :key="index"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 rounded-md text-sm',
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

