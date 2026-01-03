<script setup>
import { computed } from 'vue';
import FraudDetectionDetails from './FraudDetectionDetails.vue';
import ValidationTimeline from './ValidationTimeline.vue';

const props = defineProps({
    validationData: {
        type: Object,
        required: true,
    },
    showDetails: {
        type: Boolean,
        default: true,
    },
    showTimeline: {
        type: Boolean,
        default: false,
    },
    clipId: {
        type: String,
        default: null,
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const validationRate = computed(() => {
    const total = props.validationData.total_views || 0;
    if (total === 0) return 0;
    const valid = props.validationData.valid_views || 0;
    return Math.round((valid / total) * 100);
});

const stabilityScore = computed(() => {
    return props.validationData.stability_score ?? null;
});

const stabilityColor = computed(() => {
    if (stabilityScore.value === null) return 'gray';
    if (stabilityScore.value <= 0.3) return 'green'; // Stable
    if (stabilityScore.value <= 0.6) return 'yellow'; // Moderate
    return 'red'; // Unstable
});

const stabilityLabel = computed(() => {
    if (stabilityScore.value === null) return 'N/A';
    if (stabilityScore.value <= 0.3) return 'Stable';
    if (stabilityScore.value <= 0.6) return 'Moderate';
    return 'Unstable';
});

const hasFraud = computed(() => {
    return props.validationData.fraud_detected || false;
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white">View Validation Status</h3>
        
        <!-- Fraud Detection Details -->
        <div class="mb-4">
            <FraudDetectionDetails
                :fraud-detected="hasFraud"
                :fraud-reasons="validationData.fraud_reasons || []"
                :stability-score="stabilityScore"
                :clip-id="clipId"
            />
        </div>

        <!-- Validation Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total Views</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ formatCurrency(validationData.total_views || 0) }}
                </div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Valid Views</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ formatCurrency(validationData.valid_views || 0) }}
                </div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Invalid Views</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">
                    {{ formatCurrency((validationData.total_views || 0) - (validationData.valid_views || 0)) }}
                </div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-1">Validation Rate</div>
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ validationRate }}%
                </div>
            </div>
        </div>

        <!-- Validation Rate Bar -->
        <div class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Validation Rate</span>
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ validationRate }}%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div 
                    class="bg-green-600 h-3 rounded-full transition-all duration-500"
                    :style="{ width: validationRate + '%' }"
                ></div>
            </div>
        </div>

        <!-- Stability Score -->
        <div v-if="stabilityScore !== null" class="mb-4">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Stability Score</span>
                <span 
                    :class="[
                        'text-sm font-semibold',
                        stabilityColor === 'green' ? 'text-green-600 dark:text-green-400' :
                        stabilityColor === 'yellow' ? 'text-yellow-600 dark:text-yellow-400' :
                        'text-red-600 dark:text-red-400'
                    ]"
                >
                    {{ stabilityLabel }} ({{ stabilityScore.toFixed(2) }})
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div 
                    :class="[
                        'h-2 rounded-full transition-all duration-500',
                        stabilityColor === 'green' ? 'bg-green-600' :
                        stabilityColor === 'yellow' ? 'bg-yellow-600' :
                        'bg-red-600'
                    ]"
                    :style="{ width: (stabilityScore * 100) + '%' }"
                ></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Lower score indicates more stable view growth (0 = perfectly stable, 1 = very unstable)
            </p>
        </div>

        <!-- Validation Timeline -->
        <div v-if="showTimeline && validationData.validation_history && validationData.validation_history.length > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <ValidationTimeline :validation-history="validationData.validation_history" />
        </div>

        <!-- Validation History (if provided, compact view) -->
        <div v-else-if="showDetails && validationData.validation_history && validationData.validation_history.length > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <h4 class="text-sm font-semibold mb-2 text-gray-700 dark:text-gray-300">Recent Validation History</h4>
            <div class="space-y-2 max-h-48 overflow-y-auto">
                <div
                    v-for="(entry, index) in validationData.validation_history.slice(0, 5)"
                    :key="index"
                    class="flex justify-between items-center text-sm p-2 bg-gray-50 dark:bg-gray-700 rounded"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ new Date(entry.tracked_at).toLocaleString('id-ID') }}
                        </span>
                        <span
                            :class="[
                                'px-2 py-0.5 text-xs rounded',
                                entry.is_valid ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                            ]"
                        >
                            {{ entry.is_valid ? 'Valid' : 'Invalid' }}
                        </span>
                    </div>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ formatCurrency(entry.views_count) }} views
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

