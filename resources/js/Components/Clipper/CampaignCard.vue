<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    campaign: {
        type: Object,
        required: true,
    },
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
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow">
        <div class="p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between mb-3 sm:mb-4 gap-2">
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 mb-2">
                        <Link
                            :href="route('clipper.campaigns.show', campaign.id)"
                            class="text-base sm:text-lg lg:text-xl font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition line-clamp-2"
                        >
                            {{ campaign.title }}
                        </Link>
                        <span
                            :class="['px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap flex-shrink-0', getStatusBadgeClass(campaign.status)]"
                        >
                            {{ campaign.status }}
                        </span>
                    </div>
                    
                    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mb-3 sm:mb-4 line-clamp-2">
                        {{ campaign.description }}
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mb-3 sm:mb-4">
                        <div>
                            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Budget</div>
                            <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white break-words">
                                Rp {{ formatCurrency(campaign.max_budget) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">CPM</div>
                            <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(campaign.cpm) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Views</div>
                            <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatCurrency(campaign.total_views) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Clips</div>
                            <div class="text-sm sm:text-base lg:text-lg font-semibold text-gray-900 dark:text-white">
                                {{ campaign.total_clips || 0 }}
                            </div>
                        </div>
                    </div>

                    <div v-if="campaign.campaign_wallet" class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center text-sm mb-1">
                            <span class="text-gray-500 dark:text-gray-400">Remaining Budget:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(campaign.campaign_wallet.remaining_budget) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Total Spent:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ formatCurrency(campaign.total_spent) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-2 mt-4">
                <Link
                    :href="route('clipper.campaigns.show', campaign.id)"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm text-center"
                >
                    View Details
                </Link>
                <Link
                    v-if="campaign.status === 'active' || campaign.status === 'draft'"
                    :href="route('clipper.campaigns.analytics.show', campaign.id)"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm"
                >
                    Analytics
                </Link>
            </div>
        </div>
    </div>
</template>

