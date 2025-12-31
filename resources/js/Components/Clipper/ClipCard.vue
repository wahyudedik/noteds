<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    clip: {
        type: Object,
        required: true,
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

const getPlatformIcon = (platform) => {
    const icons = {
        tiktok: '🎵',
        instagram: '📷',
        youtube: '▶️',
        other: '🔗',
    };
    return icons[platform] || icons.other;
};
</script>

<template>
    <Link
        :href="route('clipper.clips.show', clip.id)"
        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow block"
    >
        <!-- Content Preview Area -->
        <div v-if="clip.content_url" class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
            <div class="w-full h-48 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <div class="text-4xl mb-2">{{ getPlatformIcon(clip.platform) }}</div>
                    <div class="text-sm capitalize">{{ clip.platform }}</div>
                </div>
            </div>
        </div>

        <div class="p-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-3">
                <span
                    :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusBadgeClass(clip.status)]"
                >
                    {{ clip.status }}
                </span>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ new Date(clip.submitted_at).toLocaleDateString('id-ID') }}
                </span>
            </div>

            <!-- Campaign Title -->
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 line-clamp-2">
                {{ clip.campaign?.title || 'Campaign' }}
            </h3>

            <!-- Stats -->
            <div class="space-y-2 mb-4">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Platform:</span>
                    <span class="font-medium text-gray-900 dark:text-white capitalize flex items-center gap-1">
                        <span>{{ getPlatformIcon(clip.platform) }}</span>
                        {{ clip.platform }}
                    </span>
                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">Views:</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ formatCurrency(clip.valid_views || 0) }}
                                    </span>
                                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Reward:</span>
                    <span class="font-semibold text-green-600 dark:text-green-400">
                        Rp {{ formatCurrency(clip.approved_reward || clip.pending_reward || 0) }}
                    </span>
                </div>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400 text-center">
                    Click to view details
                </div>
            </div>
        </div>
    </Link>
</template>

