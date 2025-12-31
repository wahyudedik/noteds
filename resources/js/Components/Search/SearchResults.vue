<script setup>
import { Link } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';

const props = defineProps({
    results: {
        type: Object,
        default: () => ({
            posts: [],
            users: [],
            products: [],
            articles: [],
        }),
    },
    query: {
        type: String,
        default: '',
    },
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};
</script>

<template>
    <div class="space-y-8">
        <!-- Posts Results -->
        <div v-if="results.posts && results.posts.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Posts ({{ results.posts.length }})
            </h3>
            <div class="space-y-4">
                <div
                    v-for="post in results.posts"
                    :key="post.id"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                >
                    <Link :href="route('posts.show', post.id)" class="block">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                            {{ post.title }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                            {{ post.content }}
                        </p>
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-4">
                                <span>{{ post.user?.name }}</span>
                                <span>{{ formatDate(post.created_at) }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span>{{ post.comments_count }} comments</span>
                                <span>{{ post.upvotes_count - post.downvotes_count }} votes</span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Users Results -->
        <div v-if="results.users && results.users.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Users ({{ results.users.length }})
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="user in results.users"
                    :key="user.id"
                    :href="route('profile.show', user.id)"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3">
                        <img
                            :src="user.avatar || '/default-avatar.png'"
                            :alt="user.name"
                            class="w-12 h-12 rounded-full object-cover"
                        />
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-semibold text-gray-900 dark:text-white truncate">
                                {{ user.name }}
                            </h4>
                            <p v-if="user.business_name" class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                {{ user.business_name }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Products Results -->
        <div v-if="results.products && results.products.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Products ({{ results.products.length }})
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="product in results.products"
                    :key="product.id"
                    :href="route('marketplace.products.show', product.id)"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <img
                        v-if="product.image"
                        :src="product.image"
                        :alt="product.name"
                        class="w-full h-48 object-cover"
                    />
                    <div class="p-4">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                            {{ product.name }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                            {{ product.description }}
                        </p>
                        <div class="flex items-center justify-between">
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                            </span>
                        </div>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Articles Results -->
        <div v-if="results.articles && results.articles.length > 0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Articles ({{ results.articles.length }})
            </h3>
            <div class="space-y-4">
                <Link
                    v-for="article in results.articles"
                    :key="article.id"
                    :href="route('explorer.show', article.id)"
                    class="block bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                >
                    <h4 class="text-base font-semibold text-gray-900 dark:text-white mb-2">
                        {{ article.title }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                        {{ article.excerpt || article.content }}
                    </p>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>{{ formatDate(article.created_at) }}</span>
                        <span>{{ article.views_count }} views</span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- No Results -->
        <div
            v-if="
                (!results.posts || results.posts.length === 0) &&
                (!results.users || results.users.length === 0) &&
                (!results.products || results.products.length === 0) &&
                (!results.articles || results.articles.length === 0)
            "
            class="text-center py-12"
        >
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
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
            </svg>
            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                No results found
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Try different keywords or check your spelling.
            </p>
        </div>
    </div>
</template>

