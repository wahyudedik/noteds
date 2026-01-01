<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Textarea from '@/Components/Textarea.vue';

const props = defineProps({
    campaign: Object,
    clips: Object,
});

const showSuspendModal = ref(false);
const suspendForm = useForm({
    reason: '',
});

const suspend = () => {
    suspendForm.post(route('admin.campaigns.suspend', props.campaign.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSuspendModal.value = false;
            suspendForm.reset();
        },
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getVideoType = (url) => {
    if (!url) return null;
    const youtubePattern = /^(https?:\/\/)?(www\.)?(youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)/i;
    const drivePattern = /^(https?:\/\/)?(drive|docs)\.google\.com\/(file\/d\/|open\?id=|file\/d\/)/i;
    
    if (youtubePattern.test(url)) return 'youtube';
    if (drivePattern.test(url)) return 'google_drive';
    return null;
};

const getYouTubeThumbnail = (url) => {
    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/);
    if (match) {
        return `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
    }
    return null;
};

const getStatusBadgeClass = (status) => {
    const classes = {
        draft: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    };
    return classes[status] || classes.draft;
};

const getClipStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[status] || classes.pending;
};
</script>

<template>
    <Head :title="`Campaign: ${campaign.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.campaigns.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Campaigns
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Campaign Details
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Campaign Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ campaign.title }}
                                </h1>
                                <span
                                    :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(campaign.status)]"
                                >
                                    {{ campaign.status }}
                                </span>
                            </div>
                            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line mb-4">
                                {{ campaign.description }}
                            </p>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                Creator: <span class="font-medium text-gray-900 dark:text-white">{{ campaign.creator?.name || 'Unknown' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Video References -->
                    <div v-if="campaign.video_references && campaign.video_references.length > 0" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Video References
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="(videoRef, index) in campaign.video_references"
                                :key="index"
                                class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="mb-3">
                                    <div v-if="getVideoType(videoRef.url) === 'youtube' && getYouTubeThumbnail(videoRef.url)" class="relative">
                                        <img
                                            :src="getYouTubeThumbnail(videoRef.url)"
                                            alt="Video thumbnail"
                                            class="w-full h-32 object-cover rounded border border-gray-300 dark:border-gray-600"
                                            @error="$event.target.style.display='none'"
                                        />
                                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded">
                                            <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M8 5v14l11-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div v-else class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                        <span v-if="getVideoType(videoRef.url) === 'google_drive'" class="text-4xl">📁</span>
                                        <span v-else class="text-4xl">🎬</span>
                                    </div>
                                </div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-2 truncate">
                                    {{ videoRef.title || `Video Reference ${index + 1}` }}
                                </h4>
                                <div class="flex items-center justify-between">
                                    <a
                                        :href="videoRef.url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1"
                                    >
                                        <span>View Video</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                    <span
                                        :class="[
                                            'px-2 py-1 text-xs rounded font-medium',
                                            getVideoType(videoRef.url) === 'youtube' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                        ]"
                                    >
                                        {{ getVideoType(videoRef.url) === 'youtube' ? 'YouTube' : 'Google Drive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">CPM</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(campaign.cpm) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Max Budget</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(campaign.max_budget) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(campaign.total_views) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Total Clips</div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ campaign.total_clips || 0 }}
                            </div>
                        </div>
                    </div>

                    <!-- Budget Usage -->
                    <div v-if="campaign.campaign_wallet" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold mb-4">Budget Usage</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Total Budget:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ formatCurrency(campaign.max_budget) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Total Spent:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    Rp {{ formatCurrency(campaign.total_spent) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Remaining Budget:</span>
                                <span class="font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ formatCurrency(campaign.campaign_wallet.remaining_budget) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Admin Actions</h3>
                    <div class="flex gap-4 flex-wrap">
                        <Link
                            :href="route('admin.campaigns.analytics', campaign.id)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            View Analytics
                        </Link>
                        <button
                            v-if="campaign.status === 'active'"
                            @click="showSuspendModal = true"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                        >
                            Suspend Campaign
                        </button>
                    </div>
                </div>

                <!-- Clips List -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-xl font-semibold mb-4">Submitted Clips</h2>
                    
                    <div v-if="clips?.data && clips.data.length > 0" class="space-y-4">
                        <div
                            v-for="clip in clips.data"
                            :key="clip.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                        >
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <Link
                                            :href="route('admin.clips.show', clip.id)"
                                            class="font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400"
                                        >
                                            Clip #{{ clip.id.substring(0, 8) }}
                                        </Link>
                                        <span
                                            :class="['px-2 py-1 text-xs font-medium rounded-full', getClipStatusBadgeClass(clip.status)]"
                                        >
                                            {{ clip.status }}
                                        </span>
                                    </div>
                                    
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        Clipper: <span class="font-medium text-gray-900 dark:text-white">{{ clip.clipper?.name || 'Unknown' }}</span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Platform</div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white capitalize">
                                                {{ clip.platform }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Views</div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ formatCurrency(clip.valid_views) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Reward</div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Submitted</div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ new Date(clip.submitted_at).toLocaleDateString() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <Link
                                    :href="route('admin.clips.show', clip.id)"
                                    class="ml-4 px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                >
                                    Review
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No clips submitted yet.
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspend Modal -->
        <div
            v-if="showSuspendModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showSuspendModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Suspend Campaign</h3>
                <form @submit.prevent="suspend">
                    <div class="mb-4">
                        <InputLabel for="reason" value="Reason for Suspension *" />
                        <Textarea
                            id="reason"
                            class="mt-1 block w-full"
                            v-model="suspendForm.reason"
                            required
                            rows="4"
                            placeholder="Enter reason for suspending this campaign..."
                        />
                        <InputError class="mt-2" :message="suspendForm.errors.reason" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showSuspendModal = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="suspendForm.processing">
                            Suspend
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

