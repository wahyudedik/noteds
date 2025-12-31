<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    campaign: Object,
    clips: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
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

const canActivate = computed(() => {
    return props.campaign.status === 'draft';
});

const canPause = computed(() => {
    return props.campaign.status === 'active';
});

const canCancel = computed(() => {
    return ['draft', 'active', 'paused'].includes(props.campaign.status);
});

const activateForm = useForm({});
const pauseForm = useForm({});
const cancelForm = useForm({});

const activate = () => {
    if (confirm('Are you sure you want to activate this campaign? The budget will be locked in escrow.')) {
        activateForm.post(route('clipper.campaigns.activate', props.campaign.id), {
            preserveScroll: true,
        });
    }
};

const pause = () => {
    if (confirm('Are you sure you want to pause this campaign?')) {
        pauseForm.post(route('clipper.campaigns.pause', props.campaign.id), {
            preserveScroll: true,
        });
    }
};

const cancel = () => {
    if (confirm('Are you sure you want to cancel this campaign? This action cannot be undone.')) {
        cancelForm.post(route('clipper.campaigns.cancel', props.campaign.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head :title="campaign.title" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('clipper.campaigns.index')"
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
                            <p class="text-gray-600 dark:text-gray-400 whitespace-pre-line">
                                {{ campaign.description }}
                            </p>
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
                            <div class="mt-4">
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                    <div
                                        class="bg-blue-600 h-2 rounded-full transition-all"
                                        :style="{ width: `${(campaign.total_spent / campaign.max_budget) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Campaign Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                        <button
                            v-if="canActivate"
                            @click="activate"
                            :disabled="activateForm.processing"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                        >
                            Activate Campaign
                        </button>
                        <button
                            v-if="canPause"
                            @click="pause"
                            :disabled="pauseForm.processing"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 disabled:opacity-50 transition-colors"
                        >
                            Pause Campaign
                        </button>
                        <button
                            v-if="canCancel"
                            @click="cancel"
                            :disabled="cancelForm.processing"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors"
                        >
                            Cancel Campaign
                        </button>
                        <Link
                            v-if="campaign.status === 'active'"
                            :href="route('clipper.campaigns.analytics.show', campaign.id)"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            View Analytics
                        </Link>
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
                                            :href="route('clipper.clips.show', clip.id)"
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
                                    :href="route('clipper.clips.show', clip.id)"
                                    class="ml-4 px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                                >
                                    View
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
    </AuthenticatedLayout>
</template>

