<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    clip: Object,
});

const form = useForm({
    content_url: props.clip?.content_url || '',
    platform: props.clip?.platform || 'tiktok',
    platform_content_id: props.clip?.platform_content_id || '',
});

const submit = () => {
    form.put(route('clipper.clips.update', props.clip.id), {
        preserveScroll: true,
    });
};

const canEdit = props.clip?.status === 'pending';
</script>

<template>
    <Head title="Edit Clip" />

    <ClipperLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.clips.show', clip.id)"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit Clip
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <!-- Alert if clip cannot be edited -->
                <div v-if="!canEdit" class="mb-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Cannot Edit Clip
                            </h3>
                            <div class="mt-2 text-sm text-yellow-700 dark:text-yellow-300">
                                <p>This clip cannot be edited because its status is <strong>{{ clip.status }}</strong>. Only pending clips can be edited.</p>
                            </div>
                            <div class="mt-4">
                                <Link
                                    :href="route('clipper.clips.show', clip.id)"
                                    class="text-sm font-medium text-yellow-800 dark:text-yellow-200 hover:text-yellow-900 dark:hover:text-yellow-100 underline"
                                >
                                    View Clip Details →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Campaign Info -->
                <div v-if="clip?.campaign" class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        {{ clip.campaign.title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">
                        {{ clip.campaign.description }}
                    </p>
                    <div class="flex gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">CPM:</span>
                            <span class="ml-2 font-semibold text-gray-900 dark:text-white">
                                Rp {{ new Intl.NumberFormat('id-ID').format(clip.campaign.cpm) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-500 dark:text-gray-400">Max Reward:</span>
                            <span class="ml-2 font-semibold text-gray-900 dark:text-white">
                                <span v-if="clip.campaign.max_reward_per_clipper">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(clip.campaign.max_reward_per_clipper) }}
                                </span>
                                <span v-else>No limit</span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" v-if="canEdit">
                            <div class="space-y-6">
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
                                        :disabled="!canEdit"
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
                                        :disabled="!canEdit"
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
                                        :disabled="!canEdit"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Optional: Enter the content ID from the platform for easier tracking
                                    </p>
                                    <InputError class="mt-2" :message="form.errors.platform_content_id" />
                                </div>

                                <!-- Error Message -->
                                <div v-if="form.errors.error" class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                                    <p class="text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.error }}
                                    </p>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end gap-4">
                                    <Link
                                        :href="route('clipper.clips.show', clip.id)"
                                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton :disabled="form.processing || !canEdit">
                                        Update Clip
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

