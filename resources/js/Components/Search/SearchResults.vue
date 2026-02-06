<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    results: {
        type: Object,
        default: () => ({
            posts: null,
            users: null,
            articles: null,
        }),
    },
    query: {
        type: String,
        default: '',
    },
    pagination: {
        type: Object,
        default: () => ({
            posts: null, users: null, articles: null,
            per_page_options: [10, 20, 50, 100],
        }),
    },
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const availableTabs = computed(() => {
    const tabs = [];
    if (props.results.posts?.data?.length) tabs.push('Posts');
    if (props.results.users?.data?.length) tabs.push('Users');
    if (props.results.articles?.data?.length) tabs.push('Articles');
    return tabs.length ? tabs : ['Posts','Users','Articles'];
});
const selectedTab = ref(availableTabs.value[0]);

const getData = (type) => {
    const key = type.toLowerCase();
    const arr = props.results[key]?.data || [];
    if (key === 'posts' || key === 'users') {
        const page = usePage();
        const ids = page.props.blocked_user_ids || [];
        const blocked = Array.isArray(ids) ? ids : Object.values(ids);
        if (key === 'posts') return arr.filter(p => !blocked.includes(p.user?.id || p.user_id));
        if (key === 'users') return arr.filter(u => !blocked.includes(u.id));
    }
    return arr;
};
const getMeta = (type) => {
    const key = type.toLowerCase();
    return props.pagination[key] || null;
};
const getPerPage = (type) => getMeta(type)?.per_page || 20;

const changePage = (type, page) => {
    const paramName = `${type.toLowerCase()}_page`;
    const perName = `${type.toLowerCase()}_per_page`;
    router.get(route('search.index'), {
        q: props.query,
        [paramName]: page,
        [perName]: getPerPage(type),
    }, { preserveState: true, preserveScroll: true });
};
const changePerPage = (type, per) => {
    const paramName = `${type.toLowerCase()}_page`;
    const perName = `${type.toLowerCase()}_per_page`;
    router.get(route('search.index'), {
        q: props.query,
        [paramName]: 1,
        [perName]: per,
    }, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <div class="space-y-8">
        <!-- Tabs -->
        <div class="flex items-center gap-3">
            <button
                v-for="tab in availableTabs"
                :key="tab"
                @click="selectedTab = tab"
                :class="[
                    'px-3 py-1.5 rounded-md text-sm',
                    selectedTab === tab ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300'
                ]"
            >
                {{ tab }}
            </button>
        </div>

        <!-- Posts Results -->
        <div v-if="selectedTab === 'Posts'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Posts ({{ getMeta('Posts')?.total ?? 0 }})
            </h3>
            <div class="space-y-4">
                <div
                    v-for="post in getData('Posts')"
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
            <!-- Pagination controls -->
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Per page:</span>
                    <select :value="getPerPage('Posts')" @change="changePerPage('Posts', parseInt($event.target.value))"
                            class="px-2 py-1 border rounded-md dark:bg-gray-700 dark:text-white">
                        <option v-for="opt in pagination.per_page_options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        class="px-3 py-1.5 border rounded-md"
                        :disabled="(getMeta('Posts')?.current_page ?? 1) <= 1"
                        @click="changePage('Posts', (getMeta('Posts')?.current_page ?? 1) - 1)"
                    >Previous</button>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Page {{ getMeta('Posts')?.current_page ?? 1 }} / {{ getMeta('Posts')?.last_page ?? 1 }}
                    </span>
                    <button
                        class="px-3 py-1.5 border rounded-md"
                        :disabled="(getMeta('Posts')?.current_page ?? 1) >= (getMeta('Posts')?.last_page ?? 1)"
                        @click="changePage('Posts', (getMeta('Posts')?.current_page ?? 1) + 1)"
                    >Next</button>
                </div>
            </div>
        </div>

        <!-- Users Results -->
        <div v-if="selectedTab === 'Users'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Users ({{ getMeta('Users')?.total ?? 0 }})
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Link
                    v-for="user in getData('Users')"
                    :key="user.id"
                    :href="route('profile.show', user.id)"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold overflow-hidden flex-shrink-0">
                            <img
                                v-if="user.avatar_url"
                                :src="user.avatar_url"
                                :alt="user.business_name || user.name"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>
                                {{ (user.business_name || user.name).charAt(0).toUpperCase() }}
                            </span>
                        </div>
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
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Per page:</span>
                    <select :value="getPerPage('Users')" @change="changePerPage('Users', parseInt($event.target.value))"
                            class="px-2 py-1 border rounded-md dark:bg-gray-700 dark:text-white">
                        <option v-for="opt in pagination.per_page_options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1.5 border rounded-md"
                            :disabled="(getMeta('Users')?.current_page ?? 1) <= 1"
                            @click="changePage('Users', (getMeta('Users')?.current_page ?? 1) - 1)">Previous</button>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Page {{ getMeta('Users')?.current_page ?? 1 }} / {{ getMeta('Users')?.last_page ?? 1 }}
                    </span>
                    <button class="px-3 py-1.5 border rounded-md"
                            :disabled="(getMeta('Users')?.current_page ?? 1) >= (getMeta('Users')?.last_page ?? 1)"
                            @click="changePage('Users', (getMeta('Users')?.current_page ?? 1) + 1)">Next</button>
                </div>
            </div>
        </div>

        <!-- Articles Results -->
        <div v-if="selectedTab === 'Articles'">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                Articles ({{ getMeta('Articles')?.total ?? 0 }})
            </h3>
            <div class="space-y-4">
                <Link
                    v-for="article in getData('Articles')"
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
            <div class="mt-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Per page:</span>
                    <select :value="getPerPage('Articles')" @change="changePerPage('Articles', parseInt($event.target.value))"
                            class="px-2 py-1 border rounded-md dark:bg-gray-700 dark:text-white">
                        <option v-for="opt in pagination.per_page_options" :key="opt" :value="opt">{{ opt }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button class="px-3 py-1.5 border rounded-md"
                            :disabled="(getMeta('Articles')?.current_page ?? 1) <= 1"
                            @click="changePage('Articles', (getMeta('Articles')?.current_page ?? 1) - 1)">Previous</button>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Page {{ getMeta('Articles')?.current_page ?? 1 }} / {{ getMeta('Articles')?.last_page ?? 1 }}
                    </span>
                    <button class="px-3 py-1.5 border rounded-md"
                            :disabled="(getMeta('Articles')?.current_page ?? 1) >= (getMeta('Articles')?.last_page ?? 1)"
                            @click="changePage('Articles', (getMeta('Articles')?.current_page ?? 1) + 1)">Next</button>
                </div>
            </div>
        </div>

        <!-- No Results -->
        <div
            v-if="
                (!props.results.posts?.data || props.results.posts.data.length === 0) &&
                (!props.results.users?.data || props.results.users.data.length === 0) &&
                (!props.results.articles?.data || props.results.articles.data.length === 0)
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
