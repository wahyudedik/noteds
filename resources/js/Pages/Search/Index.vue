<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SearchBar from '@/Components/Search/SearchBar.vue';
import SearchResults from '@/Components/Search/SearchResults.vue';
import PostFilters from '@/Components/Search/PostFilters.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    query: {
        type: String,
        default: '',
    },
    results: {
        type: Object,
        default: () => ({
            posts: [],
            users: [],
            products: [],
            articles: [],
        }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    savedSearches: {
        type: Array,
        default: () => [],
    },
    history: {
        type: Array,
        default: () => [],
    },
});

const selectedType = ref(props.filters?.type || 'all');
const selectedDate = ref(props.filters?.date || '');
const selectedCategory = ref(props.filters?.category || '');
const savedName = ref('');

const applyFilters = () => {
    router.get(route('search.index'), {
        q: props.query,
        type: selectedType.value !== 'all' ? selectedType.value : null,
        date: selectedDate.value || null,
        category: selectedCategory.value || null,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const loadSavedSearch = (saved) => {
    router.get(route('search.index'), {
        q: saved.query || props.query,
        ...(saved.filters || {}),
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const saveCurrentSearch = async () => {
    if (!savedName.value.trim()) return;
    const payload = {
        name: savedName.value.trim(),
        q: props.query || '',
        filters: props.filters || {},
    };
    await axios.post(route('search.saved.create'), payload);
    router.reload({ only: ['savedSearches'] });
    savedName.value = '';
};

const executeHistory = (item) => {
    router.get(route('search.index'), {
        q: item.query,
        ...(item.filters || {}),
    }, {
        preserveState: false,
        preserveScroll: false,
    });
};

const deleteSaved = async (id) => {
    await axios.delete(route('search.saved.delete', id));
    router.reload({ only: ['savedSearches'] });
};

const deleteHistoryItem = async (id) => {
    await axios.delete(route('search.history.delete', id));
    router.reload({ only: ['history'] });
};
</script>

<template>
    <Head :title="`Search${query ? `: ${query}` : ''}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Search
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Search Bar -->
                <div class="mb-6">
                    <SearchBar :initial-query="query" />
                </div>

                <!-- Basic Filters -->
                <div v-if="query" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Type Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Type
                            </label>
                            <select
                                id="search-type"
                                name="search_type"
                                v-model="selectedType"
                                @change="applyFilters"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="all">All</option>
                                <option value="posts">Posts</option>
                                <option value="users">Users</option>
                                <option value="products">Products</option>
                                <option value="articles">Articles</option>
                            </select>
                        </div>

                        <!-- Date Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date
                            </label>
                            <select
                                id="search-date"
                                name="search_date"
                                v-model="selectedDate"
                                @change="applyFilters"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">All Time</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>

                        <!-- Category Filter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Category
                            </label>
                            <input
                                id="search-category"
                                name="search_category"
                                v-model="selectedCategory"
                                @input="applyFilters"
                                type="text"
                                placeholder="Category..."
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                autocomplete="off"
                            />
                        </div>
                    </div>
                </div>

                <!-- Advanced Post Filters -->
                <div v-if="query && (selectedType === 'all' || selectedType === 'posts')" class="mb-6">
                    <PostFilters :filters="filters" :query="query" />
                </div>

                <!-- Saved Searches & History -->
                <div v-if="query" class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Saved Searches</h3>
                        </div>
                        <div class="flex gap-2 mb-3">
                            <input
                                v-model="savedName"
                                type="text"
                                placeholder="Name this search..."
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                            />
                            <button
                                @click="saveCurrentSearch"
                                class="px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Save
                            </button>
                        </div>
                        <div v-if="savedSearches.length > 0" class="space-y-2">
                            <div
                                v-for="saved in savedSearches"
                                :key="saved.id"
                                class="flex items-center justify-between"
                            >
                                <button
                                    @click="loadSavedSearch(saved)"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate"
                                >
                                    {{ saved.name }}
                                </button>
                                <button
                                    @click="deleteSaved(saved.id)"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-600 dark:text-gray-400">No saved searches</div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Search History</h3>
                        <div v-if="history.length > 0" class="space-y-2">
                            <div
                                v-for="item in history"
                                :key="item.id"
                                class="flex items-center justify-between"
                            >
                                <button
                                    @click="executeHistory(item)"
                                    class="text-sm text-blue-600 dark:text-blue-400 hover:underline truncate"
                                >
                                    {{ item.query }}
                                </button>
                                <button
                                    @click="deleteHistoryItem(item.id)"
                                    class="text-xs text-red-600 dark:text-red-400 hover:underline"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-600 dark:text-gray-400">No history</div>
                    </div>
                </div>

                <!-- Search Results -->
                <div v-if="query">
                    <SearchResults :results="results" :query="query" />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <svg
                        class="mx-auto h-16 w-16 text-gray-400"
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
                        Search for posts, users, products, and articles
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Enter a search query above to get started.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
