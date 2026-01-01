<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Textarea from '@/Components/Textarea.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    clip: Object,
    viewTracking: {
        type: Array,
        default: () => [],
    },
    fraud_detected: {
        type: Boolean,
        default: false,
    },
    stability_score: {
        type: Number,
        default: null,
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const getStatusBadgeClass = (status) => {
    const classes = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        approved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        rejected: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        paid: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    };
    return classes[status] || classes.pending;
};

const approveForm = useForm({});
const rejectForm = useForm({
    rejection_reason: '',
});
const adjustRewardForm = useForm({
    reward_amount: props.clip?.approved_reward || props.clip?.pending_reward || 0,
    reason: '',
});
const overrideValidationForm = useForm({
    valid_views: props.clip?.valid_views || 0,
    reason: '',
});

const showRejectModal = ref(false);
const showAdjustModal = ref(false);
const showOverrideModal = ref(false);

const approve = () => {
    if (confirm('Are you sure you want to approve this clip?')) {
        approveForm.post(route('admin.clips.approve', props.clip.id), {
            preserveScroll: true,
        });
    }
};

const reject = () => {
    rejectForm.post(route('admin.clips.reject', props.clip.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectForm.reset();
        },
    });
};

const adjustReward = () => {
    adjustRewardForm.post(route('admin.clips.adjust-reward', props.clip.id), {
        preserveScroll: true,
        onSuccess: () => {
            showAdjustModal.value = false;
            adjustRewardForm.reset();
        },
    });
};

const manualValidate = () => {
    if (confirm('Are you sure you want to manually validate views for this clip?')) {
        router.post(route('admin.clips.validate', props.clip.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                // Reload page to get updated data
                router.reload();
            },
        });
    }
};

const overrideValidation = () => {
    overrideValidationForm.post(route('admin.clips.override-validation', props.clip.id), {
        preserveScroll: true,
        onSuccess: () => {
            showOverrideModal.value = false;
            overrideValidationForm.reset();
            router.reload();
        },
    });
};

const chartCanvas = ref(null);

onMounted(() => {
    if (props.viewTracking && props.viewTracking.length > 0 && chartCanvas.value) {
        renderChart();
    }
});

const renderChart = () => {
    const ctx = chartCanvas.value?.getContext('2d');
    if (!ctx || !props.viewTracking || props.viewTracking.length === 0) return;

    const data = props.viewTracking;
    const maxViews = Math.max(...data.map(t => t.views_count), 1);
    const width = chartCanvas.value.width;
    const height = chartCanvas.value.height;
    const padding = 40;
    const chartWidth = width - padding * 2;
    const chartHeight = height - padding * 2;

    ctx.clearRect(0, 0, width, height);

    ctx.strokeStyle = '#9CA3AF';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(padding, padding);
    ctx.lineTo(padding, height - padding);
    ctx.lineTo(width - padding, height - padding);
    ctx.stroke();

    if (data.length > 1) {
        ctx.strokeStyle = '#3B82F6';
        ctx.lineWidth = 2;
        ctx.beginPath();

        data.forEach((track, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = height - padding - (track.views_count / maxViews) * chartHeight;

            if (index === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });

        ctx.stroke();

        ctx.fillStyle = '#3B82F6';
        data.forEach((track, index) => {
            const x = padding + (index / (data.length - 1)) * chartWidth;
            const y = height - padding - (track.views_count / maxViews) * chartHeight;

            ctx.beginPath();
            ctx.arc(x, y, 4, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
};
</script>

<template>
    <Head title="Clip Review" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.clips.index')"
                    class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                >
                    ← Back to Clips
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Clip Review
                </h2>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Clip Info Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    Clip #{{ clip.id.substring(0, 8) }}
                                </h1>
                                <span
                                    :class="['px-3 py-1 text-sm font-medium rounded-full', getStatusBadgeClass(clip.status)]"
                                >
                                    {{ clip.status }}
                                </span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div v-if="clip.campaign">
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Campaign: </span>
                                    <Link
                                        :href="route('admin.campaigns.show', clip.campaign.id)"
                                        class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                                    >
                                        {{ clip.campaign.title }}
                                    </Link>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">Clipper: </span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ clip.clipper?.name || 'Unknown' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clip Details -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Platform</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white capitalize mt-1">
                                {{ clip.platform }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Valid Views</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ formatCurrency(clip.valid_views) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Reward</div>
                            <div class="text-lg font-semibold text-green-600 dark:text-green-400 mt-1">
                                Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Submitted</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white mt-1">
                                {{ new Date(clip.submitted_at).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>

                    <!-- Content URL -->
                    <div v-if="clip.content_url" class="mb-4">
                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Content URL</div>
                        <a
                            :href="clip.content_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-blue-600 dark:text-blue-400 hover:underline break-all"
                        >
                            {{ clip.content_url }}
                        </a>
                    </div>

                    <!-- Rejection Reason -->
                    <div v-if="clip.status === 'rejected' && clip.rejection_reason" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <div class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">Rejection Reason</div>
                        <div class="text-sm text-red-700 dark:text-red-300">
                            {{ clip.rejection_reason }}
                        </div>
                    </div>
                </div>

                <!-- Approval Panel -->
                <div v-if="clip.status === 'pending'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">Review Actions</h3>
                    <div class="flex gap-4">
                        <button
                            @click="approve"
                            :disabled="approveForm.processing"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors"
                        >
                            Approve Clip
                        </button>
                        <button
                            @click="showRejectModal = true"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Reject Clip
                        </button>
                        <button
                            @click="showAdjustModal = true"
                            class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
                        >
                            Adjust Reward
                        </button>
                    </div>
                </div>

                <!-- View Tracking Chart -->
                <div v-if="viewTracking && viewTracking.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold mb-4">View Tracking</h3>
                    <div class="h-64">
                        <canvas ref="chartCanvas" width="800" height="200"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        <div
                            v-for="track in viewTracking"
                            :key="track.id"
                            class="flex justify-between items-center text-sm"
                        >
                            <span class="text-gray-600 dark:text-gray-400">
                                {{ new Date(track.tracked_at).toLocaleString() }}
                            </span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(track.views_count) }} views
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div
            v-if="showRejectModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showRejectModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Reject Clip</h3>
                <form @submit.prevent="reject">
                    <div class="mb-4">
                        <InputLabel for="rejection_reason" value="Rejection Reason" />
                        <Textarea
                            id="rejection_reason"
                            class="mt-1 block w-full"
                            v-model="rejectForm.rejection_reason"
                            required
                            rows="4"
                            placeholder="Enter reason for rejection..."
                        />
                        <InputError class="mt-2" :message="rejectForm.errors.rejection_reason" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showRejectModal = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="rejectForm.processing">
                            Reject
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Adjust Reward Modal -->
        <div
            v-if="showAdjustModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showAdjustModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Adjust Reward</h3>
                <form @submit.prevent="adjustReward">
                    <div class="mb-4">
                        <InputLabel for="reward_amount" value="Reward Amount" />
                        <div class="mt-1 relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <TextInput
                                id="reward_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="block w-full pl-10"
                                v-model="adjustRewardForm.reward_amount"
                                required
                            />
                        </div>
                        <InputError class="mt-2" :message="adjustRewardForm.errors.reward_amount" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="reason" value="Reason for Adjustment" />
                        <Textarea
                            id="reason"
                            class="mt-1 block w-full"
                            v-model="adjustRewardForm.reason"
                            required
                            rows="3"
                            placeholder="Enter reason for adjustment..."
                        />
                        <InputError class="mt-2" :message="adjustRewardForm.errors.reason" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showAdjustModal = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="adjustRewardForm.processing">
                            Adjust
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Override Validation Modal -->
        <div
            v-if="showOverrideModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="showOverrideModal = false"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-semibold mb-4">Override Validation</h3>
                <form @submit.prevent="overrideValidation">
                    <div class="mb-4">
                        <InputLabel for="valid_views" value="Valid Views Count" />
                        <TextInput
                            id="valid_views"
                            type="number"
                            min="0"
                            class="mt-1 block w-full"
                            v-model.number="overrideValidationForm.valid_views"
                            required
                        />
                        <InputError class="mt-2" :message="overrideValidationForm.errors.valid_views" />
                    </div>
                    <div class="mb-4">
                        <InputLabel for="reason" value="Reason for Override *" />
                        <Textarea
                            id="reason"
                            class="mt-1 block w-full"
                            v-model="overrideValidationForm.reason"
                            required
                            rows="4"
                            placeholder="Enter reason for overriding validation..."
                        />
                        <InputError class="mt-2" :message="overrideValidationForm.errors.reason" />
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            type="button"
                            @click="showOverrideModal = false"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </button>
                        <PrimaryButton :disabled="overrideValidationForm.processing">
                            Override
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

