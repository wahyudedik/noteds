<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: Object,
});

const form = useForm({
    q: props.filters?.q || '',
});

const search = () => {
    form.get(route('marketplace.search'), {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <form @submit.prevent="search" class="flex gap-2">
        <input
            v-model="form.q"
            type="text"
            placeholder="Search products..."
            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
        <button
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
            Search
        </button>
    </form>
</template>

