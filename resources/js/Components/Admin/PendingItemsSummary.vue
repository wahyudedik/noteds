<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useFeatureGate } from '@/Composables/useFeatureGate';

const props = defineProps({
    pendingWithdrawals: {
        type: Object,
        default: () => ({
            clipper: 0,
            creator: 0,
            marketplace: 0,
            total: 0,
        }),
    },
    pendingReports: {
        type: Number,
        default: 0,
    },
    pendingPosts: {
        type: Number,
        default: 0,
    },
    pendingClips: {
        type: Number,
        default: 0,
    },
    pendingCampaigns: {
        type: Number,
        default: 0,
    },
    pendingBrandApprovals: {
        type: Number,
        default: 0,
    },
    fraudAlerts: {
        type: Number,
        default: 0,
    },
});

const totalPending = computed(() => {
    return props.pendingWithdrawals.total +
           props.pendingReports +
           props.pendingPosts +
           props.pendingClips +
           props.pendingCampaigns +
           props.pendingBrandApprovals +
           props.fraudAlerts;
});

const getPriority = (count) => {
    if (count === 0) return 'low';
    if (count <= 5) return 'medium';
    return 'high';
};

const getPriorityColor = (priority) => {
    const colors = {
        high: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 border-red-300 dark:border-red-700',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 border-yellow-300 dark:border-yellow-700',
        low: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-300 dark:border-green-700',
    };
    return colors[priority] || colors.low;
};
const { can } = useFeatureGate();
const allowed = can('admin');
</script>

<template>
    <div v-if="allowed" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Items Summary</h3>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ totalPending }}
            </div>
        </div>

        <div class="space-y-3">
            <!-- Withdrawals -->
            <div
                v-if="pendingWithdrawals.total > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingWithdrawals.total))
                ]"
            >
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">💰</span>
                        <span class="font-semibold">Pending Withdrawals</span>
                    </div>
                    <span class="text-xl font-bold">{{ pendingWithdrawals.total }}</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm mb-2">
                    <div>Clipper: {{ pendingWithdrawals.clipper }}</div>
                    <div>Creator: {{ pendingWithdrawals.creator }}</div>
                    <div>Marketplace: {{ pendingWithdrawals.marketplace }}</div>
                </div>
                <Link
                    :href="route('admin.withdrawals.index')"
                    class="text-sm underline font-medium"
                >
                    Review All →
                </Link>
            </div>

            <!-- Reports -->
            <div
                v-if="pendingReports > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingReports))
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🚨</span>
                        <span class="font-semibold">Pending Reports</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ pendingReports }}</span>
                        <Link
                            :href="route('admin.reports.index')"
                            class="text-sm underline font-medium"
                        >
                            Review →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Posts -->
            <div
                v-if="pendingPosts > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingPosts))
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📝</span>
                        <span class="font-semibold">Posts for Moderation</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ pendingPosts }}</span>
                        <Link
                            :href="route('admin.posts.index', { status: 'moderated' })"
                            class="text-sm underline font-medium"
                        >
                            Review →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Clips -->
            <div
                v-if="pendingClips > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingClips))
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🎬</span>
                        <span class="font-semibold">Pending Clips</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ pendingClips }}</span>
                        <Link
                            :href="route('admin.clips.index', { status: 'pending' })"
                            class="text-sm underline font-medium"
                        >
                            Review →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Campaigns -->
            <div
                v-if="pendingCampaigns > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingCampaigns))
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📢</span>
                        <span class="font-semibold">Pending Campaigns</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ pendingCampaigns }}</span>
                        <Link
                            :href="route('admin.campaigns.index', { status: 'draft' })"
                            class="text-sm underline font-medium"
                        >
                            Review →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Brand Approvals -->
            <div
                v-if="pendingBrandApprovals > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor(getPriority(pendingBrandApprovals))
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🏢</span>
                        <span class="font-semibold">Brand Approvals</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ pendingBrandApprovals }}</span>
                        <Link
                            :href="route('admin.brand-approvals.index')"
                            class="text-sm underline font-medium"
                        >
                            Review →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Fraud Alerts -->
            <div
                v-if="fraudAlerts > 0"
                :class="[
                    'p-4 rounded-lg border-2',
                    getPriorityColor('high')
                ]"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚠️</span>
                        <span class="font-semibold">Fraud Alerts</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xl font-bold">{{ fraudAlerts }}</span>
                        <Link
                            :href="route('admin.clips.fraud-alerts')"
                            class="text-sm underline font-medium"
                        >
                            View Alerts →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- No Pending Items -->
            <div
                v-if="totalPending === 0"
                class="p-4 rounded-lg border-2 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 border-green-300 dark:border-green-700 text-center"
            >
                <span class="text-lg">✅</span>
                <p class="font-semibold mt-2">All clear! No pending items.</p>
            </div>
        </div>
    </div>
</template>

