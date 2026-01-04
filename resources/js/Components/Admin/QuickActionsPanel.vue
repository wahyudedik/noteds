<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    pendingWithdrawals: {
        type: Number,
        default: 0,
    },
    pendingReports: {
        type: Number,
        default: 0,
    },
    pendingPosts: {
        type: Number,
        default: 0,
    },
    fraudAlerts: {
        type: Number,
        default: 0,
    },
    pendingBrandApprovals: {
        type: Number,
        default: 0,
    },
    pendingClipperApprovals: {
        type: Number,
        default: 0,
    },
});

const quickActions = computed(() => [
    {
        label: 'Review Withdrawals',
        route: 'admin.withdrawals.index',
        count: props.pendingWithdrawals,
        color: 'bg-yellow-600 hover:bg-yellow-700',
        icon: '💰',
    },
    {
        label: 'Review Reports',
        route: 'admin.reports.index',
        count: props.pendingReports,
        color: 'bg-red-600 hover:bg-red-700',
        icon: '🚨',
    },
    {
        label: 'Moderate Posts',
        route: 'admin.posts.index',
        count: props.pendingPosts,
        color: 'bg-orange-600 hover:bg-orange-700',
        icon: '📝',
    },
    {
        label: 'Fraud Alerts',
        route: 'admin.clips.fraud-alerts',
        count: props.fraudAlerts,
        color: 'bg-red-600 hover:bg-red-700',
        icon: '⚠️',
    },
    {
        label: 'Brand Approvals',
        route: 'admin.brand-approvals.index',
        count: props.pendingBrandApprovals,
        color: 'bg-blue-600 hover:bg-blue-700',
        icon: '🏢',
    },
    {
        label: 'Clipper Approvals',
        route: 'admin.clipper-approvals.index',
        count: props.pendingClipperApprovals,
        color: 'bg-indigo-600 hover:bg-indigo-700',
        icon: '🎬',
    },
    {
        label: 'Manage Users',
        route: 'admin.users.index',
        count: null,
        color: 'bg-indigo-600 hover:bg-indigo-700',
        icon: '👥',
    },
    {
        label: 'Support Tickets',
        route: 'admin.support-tickets.index',
        count: null,
        color: 'bg-purple-600 hover:bg-purple-700',
        icon: '🎫',
    },
    {
        label: 'Refunds',
        route: 'admin.refunds.index',
        count: null,
        color: 'bg-pink-600 hover:bg-pink-700',
        icon: '💸',
    },
]);
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">Quick Actions</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <Link
                v-for="action in quickActions"
                :key="action.route"
                :href="route(action.route)"
                :class="[
                    'flex flex-col items-center justify-center p-4 rounded-lg text-white transition-colors relative',
                    action.color
                ]"
            >
                <span class="text-2xl mb-2">{{ action.icon }}</span>
                <span class="text-sm font-medium text-center">{{ action.label }}</span>
                <span
                    v-if="action.count !== null && action.count > 0"
                    class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center"
                >
                    {{ action.count > 99 ? '99+' : action.count }}
                </span>
            </Link>
        </div>
    </div>
</template>

