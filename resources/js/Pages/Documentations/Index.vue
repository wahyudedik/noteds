<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import DocumentationCard from '@/Components/DocumentationCard.vue';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

const props = defineProps({
    documentations: Object,
    searchQuery: String,
});

const search = ref(props.searchQuery || '');

const performSearch = () => {
    if (search.value.trim()) {
        router.get(route('documentations.search'), { q: search.value });
    } else {
        router.get(route('documentations.index'));
    }
};
</script>

<template>
    <Head title="Documentation - Noteds" />

    <Layout>
        <div class="max-w-6xl mx-auto px-4 py-12">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Documentation
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 mb-6">
                    Learn how to use Noteds platform effectively
                </p>

                <!-- Search -->
                <div class="max-w-2xl">
                    <form @submit.prevent="performSearch" class="flex gap-2">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search documentation..."
                            class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <button
                            type="submit"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                        >
                            Search
                        </button>
                    </form>
                </div>
            </div>

            <div v-if="Object.keys(documentations).length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">
                    {{ searchQuery ? 'No documentation found.' : 'No documentation available yet.' }}
                </p>
            </div>

            <div v-else class="space-y-12">
                <div v-for="(categoryDocs, category) in documentations" :key="category">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">
                        {{ category || 'General' }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <DocumentationCard
                            v-for="doc in categoryDocs"
                            :key="doc.id"
                            :documentation="doc"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

