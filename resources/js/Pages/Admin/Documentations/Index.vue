<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    documentations: Object,
    filters: Object,
    categories: Array,
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || 'all');
const categoryFilter = ref(props.filters?.category || '');

const applyFilters = () => {
    router.get(route('admin.documentations.index'), {
        search: search.value,
        status: statusFilter.value,
        category: categoryFilter.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const toggleStatus = (docId) => {
    router.post(route('admin.documentations.toggle-status', docId), {}, {
        preserveScroll: true,
    });
};

const deleteDoc = (docId) => {
    if (confirm('Are you sure you want to delete this documentation?')) {
        router.delete(route('admin.documentations.destroy', docId));
    }
};
</script>

<template>
    <Head title="Manage Documentations" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Manage Documentations
                </h2>
                <Link
                    :href="route('admin.documentations.create')"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                >
                    Create Documentation
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Filters -->
                <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <input
                                v-model="search"
                                @keyup.enter="applyFilters"
                                type="text"
                                placeholder="Search..."
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            />
                        </div>
                        <div>
                            <select
                                v-model="statusFilter"
                                @change="applyFilters"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="all">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>
                        <div>
                            <select
                                v-model="categoryFilter"
                                @change="applyFilters"
                                class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"
                            >
                                <option value="">All Categories</option>
                                <option v-for="cat in categories" :key="cat" :value="cat">
                                    {{ cat }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <button
                                @click="applyFilters"
                                class="w-full px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Slug</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Views</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="doc in documentations.data" :key="doc.id">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ doc.title }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ doc.slug }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ doc.category || '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ doc.order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded-full',
                                            doc.status === 'published' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ doc.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ doc.views_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                    <Link
                                        :href="route('admin.documentations.edit', doc.id)"
                                        class="text-indigo-600 hover:text-indigo-800"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="toggleStatus(doc.id)"
                                        class="text-yellow-600 hover:text-yellow-800"
                                    >
                                        {{ doc.status === 'published' ? 'Unpublish' : 'Publish' }}
                                    </button>
                                    <button
                                        @click="deleteDoc(doc.id)"
                                        class="text-red-600 hover:text-red-800"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="documentations.links && documentations.links.length > 3" class="mt-4 flex justify-center">
                    <div class="flex space-x-2">
                        <Link
                            v-for="link in documentations.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-2 rounded-lg',
                                link.active ? 'bg-indigo-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300',
                                !link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

