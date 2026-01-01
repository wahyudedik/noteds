<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Textarea from '@/Components/Textarea.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const isBrand = computed(() => page.props.auth?.user?.clipper_role === 'brand' || page.props.auth?.user?.role === 'brand');

const form = useForm({
    title: '',
    description: '',
    cpm: '',
    max_budget: '',
    max_reward_per_clipper: '',
    duration_days: '',
});

const submit = () => {
    form.post(route('clipper.campaigns.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Create Campaign" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.campaigns.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Campaign
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Alert if not brand -->
                <div v-if="!isBrand" class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Brand Registration Required
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>You must register as a brand first to create campaigns.</p>
                            </div>
                            <div class="mt-4">
                                <Link
                                    :href="route('clipper.brand-registration.create')"
                                    class="text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:text-yellow-900 dark:hover:text-yellow-100 underline"
                                >
                                    Register as Brand →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" v-if="isBrand">
                            <div class="space-y-6">
                                <!-- Title -->
                                <div>
                                    <InputLabel for="title" value="Campaign Title" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        autofocus
                                        placeholder="Enter campaign title"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <!-- Description -->
                                <div>
                                    <InputLabel for="description" value="Description" />
                                    <Textarea
                                        id="description"
                                        class="mt-1 block w-full"
                                        v-model="form.description"
                                        required
                                        rows="5"
                                        placeholder="Describe your campaign..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- CPM (Cost Per Mille) -->
                                <div>
                                    <InputLabel for="cpm" value="CPM (Cost Per 1000 Views)" />
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
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Amount you'll pay per 1000 views
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.cpm" />
                                </div>

                                <!-- Max Budget -->
                                <div>
                                    <InputLabel for="max_budget" value="Maximum Budget" />
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
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Total budget for this campaign (will be locked in escrow)
                                    </p>
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
                                            placeholder="0.00"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Maximum reward a single clipper can earn from this campaign (leave empty for no limit)
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.max_reward_per_clipper" />
                                </div>

                                <!-- Duration -->
                                <div>
                                    <InputLabel for="duration_days" value="Campaign Duration (Days)" />
                                    <TextInput
                                        id="duration_days"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full"
                                        v-model="form.duration_days"
                                        required
                                        placeholder="30"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        How long this campaign will run
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.duration_days" />
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link
                                        :href="route('clipper.campaigns.index')"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton :disabled="form.processing">
                                        Create Campaign
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

