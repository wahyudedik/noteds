<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import FaqAccordion from '@/Components/FaqAccordion.vue';

const props = defineProps({
    faqs: Object,
    categories: Array,
});

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);

const searchKnowledgeBase = async () => {
    if (!searchQuery.value.trim()) {
        searchResults.value = [];
        return;
    }

    isSearching.value = true;
    try {
        const response = await fetch(route('support.knowledge-base.search', { q: searchQuery.value }));
        const data = await response.json();
        searchResults.value = data.results;
    } catch (error) {
        console.error('Search error:', error);
    } finally {
        isSearching.value = false;
    }
};
</script>

<template>
    <Head title="Help Center" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Help Center
                </h2>
                <Link
                    :href="route('support.tickets.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Create Support Ticket
                </Link>
            </div>
        </template>

        <div class="px-4 sm:px-6 py-4 sm:py-6">
            <div class="mx-auto max-w-4xl">
                <!-- Search Bar -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Search Knowledge Base
                    </h3>
                    <div class="flex gap-2">
                        <input
                            v-model="searchQuery"
                            @input="searchKnowledgeBase"
                            type="text"
                            placeholder="Search for answers..."
                            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500"
                        />
                    </div>
                    <div v-if="isSearching" class="mt-2 text-sm text-gray-500">Searching...</div>
                    <div v-if="searchResults.length > 0" class="mt-4 space-y-2">
                        <div
                            v-for="result in searchResults"
                            :key="result.id"
                            class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg"
                        >
                            <h4 class="font-medium text-gray-900 dark:text-white">{{ result.question }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ result.answer }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FAQs by Category -->
                <div class="space-y-6">
                    <div v-if="Object.keys(faqs).length === 0 && searchResults.length === 0" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">No FAQs available yet.</p>
                    </div>

                    <div v-for="(categoryFaqs, category) in faqs" :key="category" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                            {{ category || 'General' }}
                        </h2>
                        <FaqAccordion :faqs="categoryFaqs" :category="category" />
                    </div>
                </div>

                <!-- Still Need Help? -->
                <div class="mt-8 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 p-6">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-200 mb-2">
                        Still need help?
                    </h3>
                    <p class="text-blue-800 dark:text-blue-300 mb-4">
                        Can't find what you're looking for? Create a support ticket and our team will help you.
                    </p>
                    <Link
                        :href="route('support.tickets.create')"
                        class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        Create Support Ticket
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

