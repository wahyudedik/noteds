<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    fraudAlertsCount: {
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
    activeCampaigns: {
        type: Number,
        default: 0,
    },
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Clipper System</h3>
            <Link
                :href="route('admin.clips.index')"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
            >
                View All
            </Link>
        </div>

        <!-- Fraud Alerts -->
        <div v-if="fraudAlertsCount > 0" class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="font-semibold text-red-800 dark:text-red-200">
                        {{ fraudAlertsCount }} Fraud Alert{{ fraudAlertsCount > 1 ? 's' : '' }}
                    </span>
                </div>
                <Link
                    :href="route('admin.clips.fraud-alerts')"
                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 underline"
                >
                    View Alerts
                </Link>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pending Clips</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ pendingClips }}
                </div>
                <Link
                    :href="route('admin.clips.index', { status: 'pending' })"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block"
                >
                    Review →
                </Link>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pending Campaigns</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ pendingCampaigns }}
                </div>
                <Link
                    :href="route('admin.campaigns.index', { status: 'draft' })"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block"
                >
                    Review →
                </Link>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Brand Approvals</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ pendingBrandApprovals }}
                </div>
                <Link
                    :href="route('admin.brand-approvals.index')"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block"
                >
                    Review →
                </Link>
            </div>

            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Active Campaigns</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ activeCampaigns }}
                </div>
                <Link
                    :href="route('admin.campaigns.index', { status: 'active' })"
                    class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-1 inline-block"
                >
                    View →
                </Link>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex flex-wrap gap-2">
                <Link
                    :href="route('admin.clips.index')"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                >
                    Manage Clips
                </Link>
                <Link
                    :href="route('admin.campaigns.index')"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                >
                    Manage Campaigns
                </Link>
                <Link
                    :href="route('admin.brand-approvals.index')"
                    class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm"
                >
                    Brand Approvals
                </Link>
            </div>
        </div>
    </div>
</template>

