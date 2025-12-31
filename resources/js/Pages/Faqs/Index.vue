<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';
import FaqAccordion from '@/Components/FaqAccordion.vue';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

defineProps({
    faqs: Object,
});
</script>

<template>
    <Head title="FAQ - Noteds" />

    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <div class="mb-8">
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently Asked Questions
                </h1>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Find answers to common questions about Noteds platform
                </p>
            </div>

            <div v-if="Object.keys(faqs).length === 0" class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400">No FAQs available yet.</p>
            </div>

            <div v-else class="space-y-8">
                <div v-for="(categoryFaqs, category) in faqs" :key="category">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                        {{ category || 'General' }}
                    </h2>
                    <FaqAccordion :faqs="categoryFaqs" :category="category" />
                </div>
            </div>
        </div>
    </Layout>
</template>

