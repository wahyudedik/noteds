<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    initialQuery: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Search posts, users, products...',
    },
});

const query = ref(props.initialQuery || '');
const isSearching = ref(false);
const suggestions = ref([]);
const showSuggestions = ref(false);
const selectedIndex = ref(-1);
const suggestionsContainer = ref(null);
let debounceTimer = null;
let abortController = null;

const fetchSuggestions = async (searchQuery) => {
    if (!searchQuery.trim() || searchQuery.length < 2) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }

    // Cancel previous request if exists
    if (abortController) {
        abortController.abort();
    }
    abortController = new AbortController();

    try {
        const response = await axios.get(route('search.suggestions'), {
            params: { q: searchQuery },
            signal: abortController.signal,
        });
        suggestions.value = response.data.suggestions || [];
        showSuggestions.value = suggestions.value.length > 0;
        selectedIndex.value = -1;
    } catch (error) {
        if (error.name !== 'CanceledError') {
            suggestions.value = [];
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

const selectSuggestion = (suggestion) => {
    query.value = suggestion;
    showSuggestions.value = false;
    performSearch(suggestion);
};

const handleKeyDown = (e) => {
    if (!showSuggestions.value || suggestions.value.length === 0) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex.value = Math.min(selectedIndex.value + 1, suggestions.value.length - 1);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex.value = Math.max(selectedIndex.value - 1, -1);
    } else if (e.key === 'Enter' && selectedIndex.value >= 0) {
        e.preventDefault();
        selectSuggestion(suggestions.value[selectedIndex.value]);
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
        suggestions.value = [];
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
                    @focus="query.length >= 2 && suggestions.length > 0 && (showSuggestions = true)"
                    class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                <button
                    v-if="query"
                    type="button"
                    @click="query = ''; showSuggestions = false; suggestions = []"
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
                v-if="showSuggestions && suggestions.length > 0"
                class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-xl z-50 max-h-96 overflow-y-auto"
            >
                <div class="py-2">
                    <button
                        v-for="(suggestion, index) in suggestions"
                        :key="index"
                        @click="selectSuggestion(suggestion)"
                        :class="[
                            'w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3',
                            selectedIndex === index ? 'bg-gray-100 dark:bg-gray-700' : ''
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
                        <span class="text-sm text-gray-900 dark:text-gray-100 truncate">{{ suggestion }}</span>
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

