<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import TagList from '@/Components/Bookmarks/TagList.vue';

const props = defineProps({
    tags: Array,
});

const toggleGlobal = async (tag) => {
    try {
        await router.post(route('bookmarks.tags.toggle-global', tag.id), {}, {
            preserveScroll: true,
        });
        router.reload();
    } catch (error) {
        console.error('Error toggling global:', error);
    }
};

const deleteTag = async (tag) => {
    if (!confirm(`Delete tag "${tag.name}"?`)) return;
    
    try {
        await router.delete(route('bookmarks.tags.destroy', tag.id), {
            preserveScroll: true,
        });
        router.reload();
    } catch (error) {
        console.error('Error deleting tag:', error);
    }
};
</script>

<template>
    <Head title="Bookmark Tags" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Bookmark Tags
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div class="space-y-2">
                    <div
                        v-for="tag in tags"
                        :key="tag.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex items-center justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <TagList :tags="[tag]" :clickable="false" />
                            <span class="text-sm text-gray-500">
                                Used {{ tag.usage_count }} times
                            </span>
                        </div>
                        <div class="flex gap-2">
                            <button
                                @click="toggleGlobal(tag)"
                                class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                {{ tag.is_global ? 'Make Private' : 'Make Global' }}
                            </button>
                            <a
                                :href="route('bookmarks.tags.show', tag.id)"
                                class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700"
                            >
                                View
                            </a>
                            <button
                                @click="deleteTag(tag)"
                                class="px-3 py-1 text-sm text-red-600 hover:text-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>

                    <div v-if="tags.length === 0" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">
                            No tags yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

