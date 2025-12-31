<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const isAuthenticated = !!page.props.auth?.user;
const Layout = isAuthenticated ? AuthenticatedLayout : GuestLayout;

defineProps({
    documentation: Object,
    relatedDocumentations: Array,
});
</script>

<template>
    <Head :title="`${documentation.title} - Documentation`" />

    <Layout>
        <div class="max-w-4xl mx-auto px-4 py-12">
            <!-- Breadcrumb -->
            <nav class="mb-6">
                <Link :href="route('documentations.index')" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">
                    ← Back to Documentation
                </Link>
            </nav>

            <!-- Documentation Content -->
            <article class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-8 mb-8">
                <div class="mb-6">
                    <div class="flex items-center gap-3 mb-4">
                        <span
                            v-if="documentation.category"
                            class="px-3 py-1 text-sm font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 rounded"
                        >
                            {{ documentation.category }}
                        </span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ documentation.views_count || 0 }} views
                        </span>
                    </div>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ documentation.title }}
                    </h1>
                    <p v-if="documentation.excerpt" class="text-lg text-gray-600 dark:text-gray-400">
                        {{ documentation.excerpt }}
                    </p>
                </div>

                <div class="prose dark:prose-invert max-w-none">
                    <div v-html="documentation.content" class="text-gray-700 dark:text-gray-300"></div>
                </div>
            </article>

            <!-- Related Documentations -->
            <div v-if="relatedDocumentations && relatedDocumentations.length > 0" class="mt-8">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Related Documentation
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <Link
                        v-for="relatedDoc in relatedDocumentations"
                        :key="relatedDoc.id"
                        :href="route('documentations.show', relatedDoc.slug)"
                        class="block p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition"
                    >
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
                            {{ relatedDoc.title }}
                        </h3>
                        <p v-if="relatedDoc.excerpt" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                            {{ relatedDoc.excerpt }}
                        </p>
                    </Link>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>
.prose :deep(h1),
.prose :deep(h2),
.prose :deep(h3),
.prose :deep(h4) {
    @apply text-gray-900 dark:text-white font-semibold mt-6 mb-4;
}

.prose :deep(p) {
    @apply text-gray-700 dark:text-gray-300 mb-4;
}

.prose :deep(ul),
.prose :deep(ol) {
    @apply text-gray-700 dark:text-gray-300 mb-4 ml-6;
}

.prose :deep(code) {
    @apply bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-sm;
}

.prose :deep(pre) {
    @apply bg-gray-100 dark:bg-gray-700 p-4 rounded-lg overflow-x-auto mb-4;
}

.prose :deep(a) {
    @apply text-indigo-600 dark:text-indigo-400 hover:underline;
}
</style>

