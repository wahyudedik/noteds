<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    hashtag: Object,
    posts: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`#${hashtag.name}`" />

        <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                    #{{ hashtag.name }}
                </h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    {{ hashtag.posts_count }} {{ hashtag.posts_count === 1 ? 'post' : 'posts' }}
                </p>
            </div>

            <div v-if="posts.data && posts.data.length > 0" class="space-y-4">
                <PostCard
                    v-for="post in posts.data"
                    :key="post.id"
                    :post="post"
                />
            </div>

            <div v-else class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">No posts found for this hashtag.</p>
            </div>

            <!-- Pagination -->
            <div v-if="posts.links && posts.links.length > 3" class="mt-6">
                <div class="flex justify-center">
                    <nav class="flex space-x-2">
                        <Link
                            v-for="link in posts.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-4 py-2 rounded-md text-sm font-medium',
                                link.active
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
                                !link.url && 'opacity-50 cursor-not-allowed'
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

