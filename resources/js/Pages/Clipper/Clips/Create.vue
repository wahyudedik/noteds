<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    campaign: Object,
});

const form = useForm({
    campaign_id: props.campaign?.id || '',
    content_url: '',
    platform: 'tiktok',
    platform_content_id: '',
});

const submit = () => {
    form.post(route('clipper.clips.store'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Submit Clip" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.campaigns.available')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Submit Clip
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Campaign Info -->
                <div v-if="campaign" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        {{ campaign.title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ campaign.description }}
                    </p>
                    <div class="flex gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">CPM:</span>
                            <span class="ml-2 font-semibold text-gray-900 dark:text-white">
                                Rp {{ new Intl.NumberFormat('id-ID').format(campaign.cpm) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Max Reward:</span>
                            <span class="ml-2 font-semibold text-gray-900 dark:text-white">
                                <span v-if="campaign.max_reward_per_clipper">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(campaign.max_reward_per_clipper) }}
                                </span>
                                <span v-else>No limit</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <!-- Campaign (if not pre-selected) -->
                                <div v-if="!campaign">
                                    <InputLabel for="campaign_id" value="Campaign" />
                                    <select
                                        id="campaign_id"
                                        v-model="form.campaign_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                        <option value="">Select a campaign</option>
                                        <!-- Options would be populated from backend -->
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.campaign_id" />
                                </div>

                                <!-- Content URL -->
                                <div>
                                    <InputLabel for="content_url" value="Content URL" />
                                    <TextInput
                                        id="content_url"
                                        type="url"
                                        class="mt-1 block w-full"
                                        v-model="form.content_url"
                                        required
                                        placeholder="https://www.tiktok.com/@username/video/..."
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Enter the URL of your content (TikTok, Instagram, YouTube, etc.)
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.content_url" />
                                </div>

                                <!-- Platform -->
                                <div>
                                    <InputLabel for="platform" value="Platform" />
                                    <select
                                        id="platform"
                                        v-model="form.platform"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                                        required
                                    >
                                        <option value="tiktok">TikTok</option>
                                        <option value="instagram">Instagram</option>
                                        <option value="youtube">YouTube</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.platform" />
                                </div>

                                <!-- Platform Content ID (Optional) -->
                                <div>
                                    <InputLabel for="platform_content_id" value="Platform Content ID (Optional)" />
                                    <TextInput
                                        id="platform_content_id"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.platform_content_id"
                                        placeholder="e.g., video ID from platform"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Optional: Enter the content ID from the platform for easier tracking
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.platform_content_id" />
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link
                                        :href="route('clipper.campaigns.available')"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton :disabled="form.processing">
                                        Submit Clip
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

