<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const searchQuery = ref(props.filters?.search || '');
let debounceTimer = null;

// Debounce search input
watch(searchQuery, (value) => {
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }

    debounceTimer = setTimeout(() => {
        if (value.trim()) {
            router.get(route('explorer.index'), { search: value }, {
                preserveState: true,
                preserveScroll: false,
                replace: true,
            });
        } else {
            router.get(route('explorer.index'), {}, {
                preserveState: true,
                preserveScroll: false,
                replace: true,
            });
        }
    }, 500); // 500ms debounce
});

const handleSubmit = (e) => {
    e.preventDefault();
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    
    if (searchQuery.value.trim()) {
        router.get(route('explorer.index'), { search: searchQuery.value }, {
            preserveState: true,
            preserveScroll: false,
        });
    } else {
        router.get(route('explorer.index'), {}, {
            preserveState: true,
            preserveScroll: false,
        });
    }
};
</script>

<template>
    <form @submit.prevent="handleSubmit" class="flex gap-2">
        <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari artikel bisnis, teknologi, entrepreneurship..."
            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
        />
        <button
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
            Cari
        </button>
    </form>
</template>

