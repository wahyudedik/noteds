<script setup>
import { ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

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

const performSearch = () => {
    if (!query.value.trim()) return;
    
    isSearching.value = true;
    router.get(route('search.index'), { q: query.value.trim() }, {
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

watch(() => props.initialQuery, (newVal) => {
    query.value = newVal || '';
});
</script>

<template>
    <form @submit.prevent="handleSubmit" class="relative w-full">
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
                class="block w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
            <button
                v-if="query"
                type="button"
                @click="query = ''"
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
        <div v-if="isSearching" class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 p-4">
            <div class="flex items-center justify-center">
                <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Searching...</span>
            </div>
        </div>
    </form>
</template>

