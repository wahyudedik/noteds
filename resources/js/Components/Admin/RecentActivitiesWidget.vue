<script setup>
import { Link } from '@inertiajs/vue3';
import { useFeatureGate } from '@/Composables/useFeatureGate';

const props = defineProps({
    activities: {
        type: Array,
        default: () => [],
    },
});
const { can } = useFeatureGate();
const allowed = can('admin');

const getActionLabel = (action) => {
    const labels = {
        moderate_post: 'Moderated Post',
        restore_post: 'Restored Post',
        approve_withdrawal: 'Approved Withdrawal',
        reject_withdrawal: 'Rejected Withdrawal',
        ban_user: 'Banned User',
        unban_user: 'Unbanned User',
        approve_clip: 'Approved Clip',
        reject_clip: 'Rejected Clip',
        suspend_campaign: 'Suspended Campaign',
        approve_brand: 'Approved Brand',
        reject_brand: 'Rejected Brand',
    };
    return labels[action] || action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getActionIcon = (action) => {
    if (action.includes('approve') || action.includes('restore')) {
        return '✓';
    }
    if (action.includes('reject') || action.includes('ban') || action.includes('suspend')) {
        return '✗';
    }
    if (action.includes('moderate')) {
        return '⚠';
    }
    return '•';
};

const getActionColor = (action) => {
    if (action.includes('approve') || action.includes('restore')) {
        return 'text-green-600 dark:text-green-400';
    }
    if (action.includes('reject') || action.includes('ban') || action.includes('suspend')) {
        return 'text-red-600 dark:text-red-400';
    }
    if (action.includes('moderate')) {
        return 'text-yellow-600 dark:text-yellow-400';
    }
    return 'text-blue-600 dark:text-blue-400';
};

const getTargetRoute = (activity) => {
    if (activity.target_type === 'post') {
        return route('admin.posts.show', activity.target_id);
    }
    if (activity.target_type === 'clip') {
        return route('admin.clips.show', activity.target_id);
    }
    if (activity.target_type === 'campaign') {
        return route('admin.campaigns.show', activity.target_id);
    }
    if (activity.target_type === 'user') {
        return route('admin.users.show', activity.target_id);
    }
    if (activity.target_type === 'withdrawal') {
        return route('admin.withdrawals.show', activity.target_id);
    }
    return null;
};

const formatDate = (date) => {
    const d = new Date(date);
    const now = new Date();
    const diffMs = now - d;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    
    return d.toLocaleDateString('id-ID', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <div v-if="allowed" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activities</h3>
        </div>

        <div v-if="activities && activities.length > 0" class="space-y-3 max-h-96 overflow-y-auto">
            <div
                v-for="activity in activities.slice(0, 10)"
                :key="activity.id"
                class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
            >
                <div :class="['text-xl font-bold', getActionColor(activity.action)]">
                    {{ getActionIcon(activity.action) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ activity.admin?.name || 'System' }}
                        </span>
                        <span :class="['text-sm', getActionColor(activity.action)]">
                            {{ getActionLabel(activity.action) }}
                        </span>
                    </div>
                    <div v-if="activity.target_type && activity.target_id" class="mb-1">
                        <Link
                            v-if="getTargetRoute(activity)"
                            :href="getTargetRoute(activity)"
                            class="text-sm text-blue-600 dark:text-blue-400 hover:underline"
                        >
                            {{ activity.target_type }} #{{ activity.target_id.slice(0, 8) }}
                        </Link>
                        <span v-else class="text-sm text-gray-600 dark:text-gray-400">
                            {{ activity.target_type }} #{{ activity.target_id.slice(0, 8) }}
                        </span>
                    </div>
                    <div v-if="activity.notes" class="text-sm text-gray-600 dark:text-gray-400 mb-1">
                        {{ activity.notes }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-500">
                        {{ formatDate(activity.created_at) }}
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
            No recent activities
        </div>
    </div>
</template>

