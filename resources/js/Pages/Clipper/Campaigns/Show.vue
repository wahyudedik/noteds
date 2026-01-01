<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import Textarea from '@/Components/Textarea.vue';
import ViewValidationStatus from '@/Components/Clipper/ViewValidationStatus.vue';

const props = defineProps({
    campaign: Object,
    clips: Object,
    availableBalance: {
        type: Number,
        default: 0,
    },
});

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

const canActivate = computed(() => {
    return props.campaign.status === 'draft';
});

const hasInsufficientBalance = computed(() => {
    return props.availableBalance < props.campaign.max_budget;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const canPause = computed(() => {
    return props.campaign.status === 'active';
});

const canResume = computed(() => {
    return props.campaign.status === 'paused';
});

const canCancel = computed(() => {
    return ['draft', 'active', 'paused'].includes(props.campaign.status);
});

const activateForm = useForm({});
const pauseForm = useForm({});
const resumeForm = useForm({});
const cancelForm = useForm({});

const rejectingClipId = ref(null);
const rejectForm = useForm({
    reason: '',
});

const approveClip = (clipId) => {
    if (confirm('Are you sure you want to approve this clip?')) {
        router.post(route('clipper.campaigns.clips.approve', props.campaign.id, clipId), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Success handled by Inertia
            },
        });
    }
};

const startReject = (clipId) => {
    rejectingClipId.value = clipId;
    rejectForm.reset();
};

const cancelReject = () => {
    rejectingClipId.value = null;
    rejectForm.reset();
};

const submitReject = (clipId) => {
    rejectForm.post(route('clipper.campaigns.clips.reject', props.campaign.id, clipId), {
        preserveScroll: true,
        onSuccess: () => {
            rejectingClipId.value = null;
        },
    });
};

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

const resume = () => {
    if (confirm('Are you sure you want to resume this campaign?')) {
        resumeForm.post(route('clipper.campaigns.resume', props.campaign.id), {
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

// Real-time view tracking
const liveViews = ref({
    total_views: props.campaign.total_views || 0,
    valid_views: 0,
    invalid_views: 0,
    last_updated: null,
});
const validationData = ref(null);
const isPolling = ref(false);
const pollInterval = ref(null);
const shouldPoll = computed(() => {
    return ['active', 'paused'].includes(props.campaign.status);
});

const fetchLiveViews = async () => {
    if (!shouldPoll.value) return;
    
    try {
        const response = await fetch(route('clipper.campaigns.analytics.live', props.campaign.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            liveViews.value = {
                total_views: data.total_views || 0,
                valid_views: data.valid_views || 0,
                invalid_views: data.invalid_views || 0,
                last_updated: data.last_updated ? new Date(data.last_updated) : new Date(),
            };
        }
    } catch (error) {
        console.error('Error fetching live views:', error);
    }
};

const fetchValidationDetails = async () => {
    if (!shouldPoll.value) return;
    
    try {
        const response = await fetch(route('clipper.campaigns.analytics.validation', props.campaign.id), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        
        if (response.ok) {
            const data = await response.json();
            validationData.value = {
                total_views: data.total_views || 0,
                valid_views: data.total_valid_views || 0,
                invalid_views: data.total_invalid_views || 0,
                stability_score: data.average_stability_score,
                fraud_detected: data.clips_with_fraud > 0,
                validation_rate: data.validation_rate || 0,
            };
        }
    } catch (error) {
        console.error('Error fetching validation details:', error);
    }
};

onMounted(() => {
    if (shouldPoll.value) {
        isPolling.value = true;
        // Fetch immediately
        fetchLiveViews();
        fetchValidationDetails();
        
        // Then poll every 30 seconds
        pollInterval.value = setInterval(() => {
            fetchLiveViews();
            fetchValidationDetails();
        }, 30000);
    }
});

onUnmounted(() => {
    if (pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
    }
    isPolling.value = false;
});

watch(() => props.campaign.status, (newStatus) => {
    if (['active', 'paused'].includes(newStatus) && !pollInterval.value) {
        isPolling.value = true;
        fetchLiveViews();
        fetchValidationDetails();
        pollInterval.value = setInterval(() => {
            fetchLiveViews();
            fetchValidationDetails();
        }, 30000);
    } else if (!['active', 'paused'].includes(newStatus) && pollInterval.value) {
        clearInterval(pollInterval.value);
        pollInterval.value = null;
        isPolling.value = false;
    }
});
</script>

<template>
    <Head :title="campaign.title" />

    <ClipperLayout>
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
                            <div class="flex items-center gap-2">
                                <div class="text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                                <span
                                    v-if="isPolling"
                                    class="px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded"
                                    title="Auto-refreshing every 30 seconds"
                                >
                                    🔄 Live
                                </span>
                            </div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(liveViews.total_views || campaign.total_views) }}
                            </div>
                            <div v-if="liveViews.last_updated" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Updated: {{ new Date(liveViews.last_updated).toLocaleTimeString('id-ID') }}
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

                    <!-- Available Balance Warning -->
                    <div v-if="canActivate && hasInsufficientBalance" class="mt-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-1">
                                    Insufficient Balance
                                </h3>
                                <p class="text-sm text-red-700 dark:text-red-300 mb-2">
                                    Available balance: <strong>Rp {{ formatCurrency(availableBalance) }}</strong><br>
                                    Campaign budget required: <strong>Rp {{ formatCurrency(campaign.max_budget) }}</strong>
                                </p>
                                <Link
                                    :href="route('clipper.top-ups.create')"
                                    class="inline-flex items-center text-sm font-medium text-red-800 dark:text-red-200 hover:text-red-900 dark:hover:text-red-100 underline"
                                >
                                    Top Up Wallet →
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Available Balance Info -->
                    <div v-if="canActivate && !hasInsufficientBalance" class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-blue-900 dark:text-blue-100">Available Balance:</span>
                            <span class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                Rp {{ formatCurrency(availableBalance) }}
                            </span>
                        </div>
                    </div>

                    <!-- Campaign Actions -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-2">
                        <button
                            v-if="canActivate"
                            @click="activate"
                            :disabled="activateForm.processing || hasInsufficientBalance"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Activate Campaign
                        </button>
                        <button
                            v-if="canResume"
                            @click="resume"
                            :disabled="resumeForm.processing"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                        >
                            Resume Campaign
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

                <!-- View Validation Status -->
                <ViewValidationStatus 
                    v-if="validationData"
                    :validation-data="validationData"
                    :show-details="true"
                />

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

                                    <!-- Reject Form -->
                                    <div v-if="rejectingClipId === clip.id" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                        <form @submit.prevent="submitReject(clip.id)" class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Rejection Reason *
                                                </label>
                                                <Textarea
                                                    v-model="rejectForm.reason"
                                                    class="w-full"
                                                    rows="3"
                                                    placeholder="Please provide a reason for rejecting this clip..."
                                                    required
                                                />
                                                <InputError :message="rejectForm.errors.reason" />
                                            </div>
                                            <div class="flex gap-2">
                                                <button
                                                    type="submit"
                                                    :disabled="rejectForm.processing"
                                                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 transition-colors text-sm"
                                                >
                                                    Confirm Reject
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="cancelReject"
                                                    class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors text-sm"
                                                >
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="ml-4 flex flex-col gap-2">
                                    <Link
                                        :href="route('clipper.clips.show', clip.id)"
                                        class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm text-center"
                                    >
                                        View
                                    </Link>
                                    <button
                                        v-if="clip.status === 'pending'"
                                        @click="approveClip(clip.id)"
                                        class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                                    >
                                        Approve
                                    </button>
                                    <button
                                        v-if="clip.status === 'pending' && rejectingClipId !== clip.id"
                                        @click="startReject(clip.id)"
                                        class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                                    >
                                        Reject
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No clips submitted yet.
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

