<script setup>
import { computed, ref, watch } from 'vue';

const props = defineProps({
    cpm: {
        type: Number,
        required: true,
    },
    maxRewardPerClipper: {
        type: Number,
        default: null,
    },
    platformFeePercent: {
        type: Number,
        default: 5,
    },
});

const views = ref(0);

const estimatedReward = computed(() => {
    if (!views.value || views.value <= 0) return 0;
    
    // Formula: (views / 1000) × CPM
    let reward = (views.value / 1000) * props.cpm;
    
    // Apply max reward per clipper if set
    if (props.maxRewardPerClipper && reward > props.maxRewardPerClipper) {
        reward = props.maxRewardPerClipper;
    }
    
    return reward;
});

const platformFee = computed(() => {
    return (estimatedReward.value * props.platformFeePercent) / 100;
});

const netReward = computed(() => {
    return estimatedReward.value - platformFee.value;
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

const formatNumber = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
            Reward Calculator
        </h3>
        
        <div class="space-y-4">
            <!-- Input -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Estimated Views
                </label>
                <input
                    v-model.number="views"
                    type="number"
                    min="0"
                    step="100"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Enter estimated views"
                />
            </div>

            <!-- CPM Info -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-600 dark:text-gray-400">CPM (Cost Per Mille):</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        Rp {{ formatCurrency(cpm) }}
                    </span>
                </div>
            </div>

            <!-- Results -->
            <div v-if="views > 0" class="space-y-3">
                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Estimated Reward:</span>
                        <span class="text-xl font-bold text-blue-600 dark:text-blue-400">
                            Rp {{ formatCurrency(estimatedReward) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Based on {{ formatNumber(views) }} views
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Platform Fee ({{ platformFeePercent }}%):</span>
                        <span class="text-gray-900 dark:text-white">
                            - Rp {{ formatCurrency(platformFee) }}
                        </span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700 flex justify-between items-center">
                        <span class="font-semibold text-gray-900 dark:text-white">Net Reward:</span>
                        <span class="text-lg font-bold text-green-600 dark:text-green-400">
                            Rp {{ formatCurrency(netReward) }}
                        </span>
                    </div>
                </div>

                <div v-if="maxRewardPerClipper && estimatedReward >= maxRewardPerClipper" class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 border border-yellow-200 dark:border-yellow-800">
                    <div class="flex items-center gap-2 text-sm text-yellow-800 dark:text-yellow-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Maximum reward per clipper: Rp {{ formatCurrency(maxRewardPerClipper) }}</span>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                <p class="text-sm">Enter estimated views to calculate reward</p>
            </div>
        </div>
    </div>
</template>

