<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    template: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const createFromTemplate = () => {
    router.visit(route('clipper.campaigns.create', { template_id: props.template.id }));
};

const duplicateForm = useForm({});
const duplicateTemplate = () => {
    duplicateForm.post(route('clipper.campaign-templates.duplicate', props.template.id), {
        preserveScroll: true,
    });
};

const deleteForm = useForm({});
const deleteTemplate = () => {
    if (confirm('Are you sure you want to delete this template?')) {
        deleteForm.delete(route('clipper.campaign-templates.destroy', props.template.id), {
            preserveScroll: true,
            onSuccess: () => {
                router.visit(route('clipper.campaign-templates.index'));
            },
        });
    }
};
</script>

<template>
    <Head :title="`Template: ${template?.name}`" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('clipper.campaign-templates.index')"
                        class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        ← Back to Templates
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Template: {{ template?.name }}
                    </h2>
                </div>
                <div class="flex gap-2">
                    <Link
                        :href="route('clipper.campaign-templates.edit', template.id)"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                    >
                        Edit
                    </Link>
                    <button
                        @click="createFromTemplate"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                    >
                        Use Template
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="space-y-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                        {{ template?.name }}
                                    </h3>
                                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-4">
                                        {{ template?.title }}
                                    </p>
                                    <span
                                        v-if="template?.is_public"
                                        class="px-3 py-1 text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full"
                                    >
                                        Public Template
                                    </span>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <h4 class="text-lg font-semibold mb-4">Description</h4>
                                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                    {{ template?.description }}
                                </p>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <h4 class="text-lg font-semibold mb-4">Campaign Settings</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">CPM:</span>
                                        <p class="text-lg font-semibold">Rp {{ formatCurrency(template?.cpm) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Max Budget:</span>
                                        <p class="text-lg font-semibold">Rp {{ formatCurrency(template?.max_budget) }}</p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Max Reward Per Clipper:</span>
                                        <p class="text-lg font-semibold">
                                            {{ template?.max_reward_per_clipper ? `Rp ${formatCurrency(template.max_reward_per_clipper)}` : 'No limit' }}
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Duration:</span>
                                        <p class="text-lg font-semibold">{{ template?.duration_days }} days</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Times Used:</span>
                                        <p class="text-lg font-semibold">{{ template?.usage_count || 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-6 flex gap-4">
                                <button
                                    @click="createFromTemplate"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                >
                                    Create Campaign from Template
                                </button>
                                <button
                                    @click="duplicateTemplate"
                                    :disabled="duplicateForm.processing"
                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                                >
                                    Duplicate Template
                                </button>
                                <button
                                    @click="deleteTemplate"
                                    :disabled="deleteForm.processing"
                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                >
                                    Delete Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

