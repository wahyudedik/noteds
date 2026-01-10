<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { formatDistanceToNow } from 'date-fns';

const props = defineProps({
    drafts: {
        type: Object,
        default: () => ({}),
    },
});

const deleteDraft = (draftId) => {
    if (confirm('Are you sure you want to delete this draft?')) {
        router.delete(route('drafts.destroy', draftId));
    }
};

const publishDraft = (draftId) => {
    router.post(route('drafts.publish', draftId));
};
</script>

<template>
    <Head title="Drafts" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Saved Drafts
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div v-if="drafts.data && drafts.data.length > 0" class="space-y-4">
                    <div
                        v-for="draft in drafts.data"
                        :key="draft.id"
                        class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ draft.title || 'Untitled Draft' }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                        {{ draft.content }}
                                    </p>
                                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                        <span v-if="draft.purpose_type">
                                            {{ draft.purpose_type.replace('_', ' ') }}
                                        </span>
                                        <span v-if="draft.auto_saved_at">
                                            Auto-saved {{ formatDistanceToNow(new Date(draft.auto_saved_at), { addSuffix: true }) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 ml-4">
                                    <button
                                        @click="publishDraft(draft.id)"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium"
                                    >
                                        Publish
                                    </button>
                                    <button
                                        @click="deleteDraft(draft.id)"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div v-if="drafts.links" class="mt-6">
                        <div class="flex justify-center">
                            <nav class="flex gap-2">
                                <a
                                    v-for="(link, index) in drafts.links"
                                    :key="index"
                                    :href="link.url"
                                    @click.prevent="link.url && router.visit(link.url)"
                                    :class="[
                                        'px-3 py-2 rounded-md text-sm font-medium',
                                        link.active
                                            ? 'bg-blue-600 text-white'
                                            : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </nav>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <svg
                        class="mx-auto h-16 w-16 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                        />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">
                        No drafts yet
                    </h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Your saved drafts will appear here.
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>


