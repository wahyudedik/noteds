<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import CollectionTree from '@/Components/Bookmarks/CollectionTree.vue';

const props = defineProps({
    shares: Array,
});

const acceptInvitation = async (collectionId) => {
    try {
        await router.post(route('bookmarks.collections.accept', collectionId), {}, {
            preserveScroll: true,
        });
        router.reload();
    } catch (error) {
        console.error('Error accepting invitation:', error);
    }
};

const rejectInvitation = async (collectionId) => {
    try {
        await router.post(route('bookmarks.collections.reject', collectionId), {}, {
            preserveScroll: true,
        });
        router.reload();
    } catch (error) {
        console.error('Error rejecting invitation:', error);
    }
};
</script>

<template>
    <Head title="Shared Collections" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Shared Collections
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div class="space-y-4">
                    <div
                        v-for="share in shares"
                        :key="share.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="font-semibold">{{ share.collection.name }}</h3>
                                <p class="text-sm text-gray-500">
                                    Shared by {{ share.shared_by.name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    Permission: {{ share.permission }} • 
                                    {{ share.accepted_at ? 'Accepted' : 'Pending' }}
                                </p>
                            </div>
                            <div v-if="!share.accepted_at" class="flex gap-2">
                                <button
                                    @click="acceptInvitation(share.collection.id)"
                                    class="px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700"
                                >
                                    Accept
                                </button>
                                <button
                                    @click="rejectInvitation(share.collection.id)"
                                    class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded text-sm hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    Reject
                                </button>
                            </div>
                            <div v-else>
                                <a
                                    :href="route('bookmarks.collections.public', share.collection.public_slug)"
                                    class="px-3 py-1 bg-indigo-600 text-white rounded text-sm hover:bg-indigo-700"
                                >
                                    View
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="shares.length === 0" class="text-center py-12">
                        <p class="text-gray-500 dark:text-gray-400">
                            No shared collections yet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

