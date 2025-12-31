<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

defineProps({
    faq: Object,
    relatedFaqs: Array,
});
</script>

<template>
    <Head :title="`${faq.question} - FAQ`" />

    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <Link :href="route('faqs.index')" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                    ← Back to FAQs
                </Link>
            </nav>

            <!-- FAQ Content -->
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8">
                <div class="mb-4">
                    <span
                        v-if="faq.category"
                        class="px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded"
                    >
                        {{ faq.category }}
                    </span>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ faq.question }}
                </h1>
                <div class="prose dark:prose-invert max-w-none">
                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                        {{ faq.answer }}
                    </p>
                </div>
            </article>

            <!-- Related FAQs -->
            <div v-if="relatedFaqs && relatedFaqs.length > 0" class="mt-8">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Related FAQs
                </h2>
                <div class="space-y-4">
                    <Link
                        v-for="relatedFaq in relatedFaqs"
                        :key="relatedFaq.id"
                        :href="route('faqs.show', relatedFaq.id)"
                        class="block p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ relatedFaq.question }}
                        </h3>
                    </Link>
                </div>
            </div>
        </div>
    </Layout>
</template>

