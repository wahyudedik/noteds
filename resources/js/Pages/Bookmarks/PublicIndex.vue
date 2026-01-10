<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    collections: Object,
});
</script>

<template>
    <Head title="Public Bookmark Collections" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Public Bookmark Collections
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="collections.data && collections.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="collection in collections.data"
                                :key="collection.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition"
                            >
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span v-if="collection.icon" class="text-2xl">{{ collection.icon }}</span>
                                        <h3 class="font-semibold text-lg">
                                            <Link
                                                :href="route('bookmarks.collections.public', collection.public_slug)"
                                                class="hover:text-indigo-600 dark:hover:text-indigo-400"
                                            >
                                                {{ collection.name }}
                                            </Link>
                                        </h3>
                                    </div>
                                </div>
                                
                                <p v-if="collection.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
                                    {{ collection.description }}
                                </p>
                                
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <Link
                                        :href="route('profile.show', collection.user.id)"
                                        class="hover:text-indigo-600 dark:hover:text-indigo-400"
                                    >
                                        {{ collection.user.business_name || collection.user.name }}
                                    </Link>
                                    <span>{{ collection.bookmarks_count }} bookmarks</span>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12 text-gray-500">
                            <p>No public collections found.</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="collections.links && collections.links.length > 3" class="mt-6 flex justify-center">
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in collections.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 rounded-md text-sm',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                ></Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

