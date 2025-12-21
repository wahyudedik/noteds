<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    filters: Object,
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="font-semibold mb-4">Filters</h3>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Category</label>
                <select
                    @change="$event.target.value && $inertia.get(route('marketplace.search'), { category: $event.target.value, ...filters })"
                    :value="filters?.category || ''"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="">All Categories</option>
                    <option value="Software">Software</option>
                    <option value="Template">Template</option>
                    <option value="Course">Course</option>
                    <option value="E-book">E-book</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Sort By</label>
                <select
                    @change="$event.target.value && $inertia.get(route('marketplace.search'), { sort: $event.target.value, ...filters })"
                    :value="filters?.sort || 'newest'"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="newest">Newest</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="popularity">Popularity</option>
                </select>
            </div>
        </div>
    </div>
</template>

