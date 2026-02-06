<script setup>
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    initialQuery: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search posts, users...',
    },
});

const query = ref(props.initialQuery || '');
const isSearching = ref(false);
const results = ref({ users: [], posts: [] });
const showSuggestions = ref(false);
const selectedIndex = ref(-1);
const suggestionsContainer = ref(null);
let debounceTimer = null;
let abortController = null;

const usersOffset = computed(() => 1);
const postsOffset = computed(() => 1 + (results.value.users?.length || 0));
const selectableCount = computed(() => 1 + (results.value.users?.length || 0) + (results.value.posts?.length || 0));

const fetchSuggestions = async (searchQuery) => {
    if (!searchQuery.trim() || searchQuery.length < 2) {
        results.value = { users: [], posts: [] };
        showSuggestions.value = false;
        return;
    }

    // Cancel previous request if exists
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    try {
        const response = await axios.get(route('search.quick'), {
            params: { q: searchQuery },
            signal: abortController.signal,
        });
        results.value = {
            users: response.data.users || [],
            posts: response.data.posts || [],
        };
        showSuggestions.value = true;
        selectedIndex.value = -1;
    } catch (error) {
        if (error.name !== 'CanceledError') {
            results.value = { users: [], posts: [] };
            showSuggestions.value = false;
        }
    }
};

const debouncedFetchSuggestions = (searchQuery) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetchSuggestions(searchQuery);
    }, 300); // 300ms debounce
};

const performSearch = (searchQuery = null) => {
    const searchTerm = searchQuery || query.value.trim();
    if (!searchTerm) return;
    
    showSuggestions.value = false;
    isSearching.value = true;
    router.get(route('search.index'), { q: searchTerm }, {
        preserveState: false,
        preserveScroll: false,
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

const handleSubmit = (e) => {
    e.preventDefault();
    performSearch();
};

const selectSearch = () => performSearch();

const selectUser = (user) => {
    showSuggestions.value = false;
    router.get(route('profile.show', user.id), {}, { preserveState: false, preserveScroll: false });
};

const selectPost = (post) => {
    showSuggestions.value = false;
    router.get(route('posts.show', post.id), {}, { preserveState: false, preserveScroll: false });
};

const handleKeyDown = (e) => {
    if (!showSuggestions.value || selectableCount.value === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, selectableCount.value - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        const usersLen = results.value.users?.length || 0;
        if (selectedIndex.value === 0) {
            selectSearch();
            return;
        }
        const userIndex = selectedIndex.value - usersOffset.value;
        if (userIndex >= 0 && userIndex < usersLen) {
            selectUser(results.value.users[userIndex]);
            return;
        }
        const postIndex = selectedIndex.value - postsOffset.value;
        if (postIndex >= 0 && postIndex < (results.value.posts?.length || 0)) {
            selectPost(results.value.posts[postIndex]);
        }
    } else if (e.key === 'Escape') {
        showSuggestions.value = false;
        selectedIndex.value = -1;
    }
};

const handleClickOutside = (event) => {
    if (suggestionsContainer.value && !suggestionsContainer.value.contains(event.target)) {
        showSuggestions.value = false;
    }
};

watch(() => query.value, (newVal) => {
    if (newVal && newVal.length >= 2) {
        debouncedFetchSuggestions(newVal);
    } else {
        results.value = { users: [], posts: [] };
        showSuggestions.value = false;
    }
});

watch(() => props.initialQuery, (newVal) => {
    query.value = newVal || '';
});

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    clearTimeout(debounceTimer);
    if (abortController) {
        abortController.abort();
    }
});
</script>

<template>
    <div ref="suggestionsContainer" class="relative w-full">
        <form @submit.prevent="handleSubmit" class="relative">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg
                        class="w-5 h-5 text-gray-400"
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
                </div>
                <input
                    v-model="query"
                    type="text"
                    :placeholder="placeholder"
                    @keydown="handleKeyDown"
                    @focus="query.length >= 2 && (showSuggestions = true)"
                    class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button
                    v-if="query"
                    type="button"
                    @click="query = ''; showSuggestions = false; results = { users: [], posts: [] }"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                >
                    <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </form>

        <!-- Suggestions Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="showSuggestions && query && query.length >= 2"
                class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"
            >
                <div class="py-2">
                    <button
                        @click="selectSearch"
                        :class="[
                            'w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3',
                            selectedIndex === 0 ? 'bg-gray-100 dark:bg-gray-700' : ''
                        ]"
                    >
                        <svg
                            class="w-5 h-5 text-gray-400 flex-shrink-0"
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
                        <span class="text-sm text-gray-900 dark:text-gray-100 truncate">Search for "{{ query.trim() }}"</span>
                    </button>

                    <div v-if="results.users && results.users.length > 0" class="mt-2">
                        <div class="px-4 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</div>
                        <button
                            v-for="(user, index) in results.users"
                            :key="user.id"
                            @click="selectUser(user)"
                            :class="[
                                'w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3',
                                selectedIndex === (usersOffset + index) ? 'bg-gray-100 dark:bg-gray-700' : ''
                            ]"
                        >
                            <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xs overflow-hidden flex-shrink-0">
                                <img
                                    v-if="user.avatar_url"
                                    :src="user.avatar_url"
                                    :alt="user.business_name || user.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else>{{ (user.business_name || user.name || 'U').charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ user.name }}</div>
                                <div v-if="user.business_name" class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ user.business_name }}</div>
                            </div>
                        </button>
                    </div>

                    <div v-if="results.posts && results.posts.length > 0" class="mt-2">
                        <div class="px-4 py-1 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Posts</div>
                        <button
                            v-for="(post, index) in results.posts"
                            :key="post.id"
                            @click="selectPost(post)"
                            :class="[
                                'w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3',
                                selectedIndex === (postsOffset + index) ? 'bg-gray-100 dark:bg-gray-700' : ''
                            ]"
                        >
                            <svg
                                class="w-5 h-5 text-gray-400 flex-shrink-0"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="min-w-0">
                                <div class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ post.title }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ post.user?.business_name || post.user?.name }}
                                </div>
                            </div>
                        </button>
                    </div>

                    <button
                        v-if="results.users.length === 0 && results.posts.length === 0"
                        disabled
                        class="w-full text-left px-4 py-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        No suggestions
                    </button>
                </div>
            </div>
        </Transition>

        <!-- Loading Indicator -->
        <div v-if="isSearching" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 p-4">
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Searching...</span>
            </div>
        </div>
    </div>
</template>

