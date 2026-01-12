<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    templates: Object,
    filters: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const deleteTemplate = (templateId) => {
    if (confirm('Are you sure you want to delete this template?')) {
        router.delete(route('clipper.campaign-templates.destroy', templateId), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Campaign Templates" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('clipper.campaigns.index')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        ← Back to Campaigns
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Campaign Templates
                    </h2>
                </div>
                <Link
                    :href="route('clipper.campaign-templates.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    + Create Template
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="templates.data && templates.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="template in templates.data"
                                :key="template.id"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-6 hover:shadow-lg transition-shadow"
                            >
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                            {{ template.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ template.title }}
                                        </p>
                                    </div>
                                    <span
                                        v-if="template.is_public"
                                        class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded"
                                    >
                                        Public
                                    </span>
                                </div>

                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                    {{ template.description }}
                                </p>

                                <div class="space-y-2 mb-4 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">CPM:</span>
                                        <span class="font-medium">Rp {{ formatCurrency(template.cpm) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Budget:</span>
                                        <span class="font-medium">Rp {{ formatCurrency(template.max_budget) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Duration:</span>
                                        <span class="font-medium">{{ template.duration_days }} days</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Used:</span>
                                        <span class="font-medium">{{ template.usage_count }} times</span>
                                    </div>
                                </div>

                                <div class="flex gap-2 mt-4">
                                    <Link
                                        :href="route('clipper.campaign-templates.show', template.id)"
                                        class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('clipper.campaign-templates.edit', template.id)"
                                        class="flex-1 text-center px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="deleteTemplate(template.id)"
                                        class="px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-12">
                            <p class="text-gray-500 dark:text-gray-400 mb-4">No templates found.</p>
                            <Link
                                :href="route('clipper.campaign-templates.create')"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                Create Your First Template
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <div v-if="templates.links && templates.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="flex gap-1">
                                <template v-for="(link, index) in templates.links" :key="index">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="px-3 py-2 text-sm leading-4 border rounded hover:bg-gray-50 dark:hover:bg-gray-700"
                                        :class="{
                                            'bg-indigo-600 text-white border-indigo-600': link.active,
                                            'text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600': !link.active
                                        }"
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="px-3 py-2 text-sm leading-4 border rounded text-gray-400 dark:text-gray-600 border-gray-300 dark:border-gray-600"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

