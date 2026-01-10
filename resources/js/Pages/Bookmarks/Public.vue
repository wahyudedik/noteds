<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import PostCard from '@/Components/PostCard.vue';

const props = defineProps({
    collection: Object,
    canEdit: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <Head :title="collection.name + ' - Public Collection'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        {{ collection.name }}
                    </h2>
                    <p v-if="collection.description" class="text-sm text-gray-500 mt-1">
                        {{ collection.description }}
                    </p>
                </div>
                <div class="text-sm text-gray-500">
                    by {{ collection.user.name }}
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div v-if="collection.bookmarks && collection.bookmarks.length > 0" class="space-y-4">
                    <PostCard
                        v-for="bookmark in collection.bookmarks"
                        :key="bookmark.id"
                        :post="bookmark.post"
                        :user-vote="null"
                    />
                </div>
                <div v-else class="text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400">
                        This collection is empty.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

