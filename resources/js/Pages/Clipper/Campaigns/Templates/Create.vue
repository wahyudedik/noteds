<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    title: '',
    description: '',
    video_references: [],
    cpm: '',
    max_budget: '',
    max_reward_per_clipper: '',
    duration_days: '',
    is_public: false,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const submit = () => {
    form.post(route('clipper.campaign-templates.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Campaign Template" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.campaign-templates.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Templates
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Campaign Template
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <!-- Template Name -->
                                <div>
                                    <InputLabel for="name" value="Template Name *" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        placeholder="e.g., Monthly Product Launch Template"
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <!-- Campaign Title -->
                                <div>
                                    <InputLabel for="title" value="Campaign Title *" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        placeholder="Enter default campaign title"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description" value="Description *" />
                                    <Textarea
                                        id="description"
                                        class="mt-1 block w-full"
                                        v-model="form.description"
                                        required
                                        rows="5"
                                        placeholder="Describe your campaign template..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- CPM -->
                                <div>
                                    <InputLabel for="cpm" value="CPM (Cost Per 1000 Views) *" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="cpm"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            v-model="form.cpm"
                                            required
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.cpm" />
                                </div>

                                <!-- Max Budget -->
                                <div>
                                    <InputLabel for="max_budget" value="Maximum Budget *" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="max_budget"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            v-model="form.max_budget"
                                            required
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.max_budget" />
                                </div>

                                <!-- Max Reward Per Clipper -->
                                <div>
                                    <InputLabel for="max_reward_per_clipper" value="Max Reward Per Clipper (Optional)" />
                                    <div class="mt-1 relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                                        <TextInput
                                            id="max_reward_per_clipper"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="block w-full pl-10"
                                            v-model="form.max_reward_per_clipper"
                                        />
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.max_reward_per_clipper" />
                                </div>

                                <!-- Duration -->
                                <div>
                                    <InputLabel for="duration_days" value="Campaign Duration (Days) *" />
                                    <TextInput
                                        id="duration_days"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full"
                                        v-model="form.duration_days"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.duration_days" />
                                </div>

                                <!-- Public Template -->
                                <div class="flex items-center">
                                    <input
                                        id="is_public"
                                        type="checkbox"
                                        v-model="form.is_public"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <InputLabel for="is_public" value="Make this template public" class="ml-2" />
                                </div>
                                <InputError class="mt-2" :message="form.errors.is_public" />

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link
                                        :href="route('clipper.campaign-templates.index')"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton :disabled="form.processing">
                                        Create Template
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

