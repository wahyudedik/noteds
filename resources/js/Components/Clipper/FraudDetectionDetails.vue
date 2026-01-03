<script setup>
import { computed } from 'vue';

const props = defineProps({
    fraudDetected: {
        type: Boolean,
        default: false,
    },
    fraudReasons: {
        type: Array,
        default: () => [],
    },
    stabilityScore: {
        type: Number,
        default: null,
    },
    clipId: {
        type: String,
        default: null,
    },
});

const hasFraudDetails = computed(() => {
    return props.fraudDetected && (props.fraudReasons.length > 0 || props.stabilityScore !== null);
});

const stabilityStatus = computed(() => {
    if (props.stabilityScore === null) return null;
    if (props.stabilityScore <= 0.3) return { label: 'Stable', color: 'green' };
    if (props.stabilityScore <= 0.6) return { label: 'Moderate', color: 'yellow' };
    return { label: 'Unstable', color: 'red' };
});

const recommendedActions = computed(() => {
    const actions = [];
    
    if (props.fraudDetected) {
        actions.push('Review view tracking patterns manually');
        actions.push('Contact support if you believe this is a false positive');
    }
    
    if (props.stabilityScore !== null && props.stabilityScore > 0.6) {
        actions.push('Monitor view growth patterns closely');
        actions.push('Ensure organic view growth');
    }
    
    return actions;
});
</script>

<template>
    <div class="fraud-detection-details">
        <div
            v-if="fraudDetected"
            class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6"
        >
            <div class="flex items-start gap-3 mb-4">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-1">
                        Fraud Detected
                    </h3>
                    <p class="text-sm text-red-700 dark:text-red-300">
                        Suspicious view patterns have been detected for this clip. Manual review may be required.
                    </p>
                </div>
            </div>

            <!-- Detection Reasons -->
            <div v-if="fraudReasons.length > 0" class="mt-4">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">
                    Detection Reasons:
                </h4>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-300">
                    <li v-for="(reason, index) in fraudReasons" :key="index">
                        {{ reason }}
                    </li>
                </ul>
            </div>

            <!-- Stability Score -->
            <div v-if="stabilityScore !== null" class="mt-4 pt-4 border-t border-red-200 dark:border-red-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-red-800 dark:text-red-200">
                        Stability Score
                    </span>
                    <span
                        :class="[
                            'px-3 py-1 text-sm font-semibold rounded-full',
                            stabilityStatus?.color === 'green' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                            stabilityStatus?.color === 'yellow' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                            'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                        ]"
                    >
                        {{ stabilityStatus?.label || 'N/A' }} ({{ stabilityScore.toFixed(2) }})
                    </span>
                </div>
                <div class="w-full bg-red-200 dark:bg-red-800 rounded-full h-2">
                    <div
                        :class="[
                            'h-2 rounded-full transition-all',
                            stabilityStatus?.color === 'green' ? 'bg-green-600' :
                            stabilityStatus?.color === 'yellow' ? 'bg-yellow-600' :
                            'bg-red-600'
                        ]"
                        :style="{ width: (stabilityScore * 100) + '%' }"
                    ></div>
                </div>
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">
                    Lower score indicates more stable view growth (0 = perfectly stable, 1 = very unstable)
                </p>
            </div>

            <!-- Recommended Actions -->
            <div v-if="recommendedActions.length > 0" class="mt-4 pt-4 border-t border-red-200 dark:border-red-700">
                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">
                    Recommended Actions:
                </h4>
                <ul class="list-disc list-inside space-y-1 text-sm text-red-700 dark:text-red-300">
                    <li v-for="(action, index) in recommendedActions" :key="index">
                        {{ action }}
                    </li>
                </ul>
            </div>

            <!-- Admin Link (if clipper) -->
            <div v-if="clipId" class="mt-4 pt-4 border-t border-red-200 dark:border-red-700">
                <a
                    :href="route('admin.clips.show', clipId)"
                    class="inline-flex items-center text-sm font-medium text-red-800 dark:text-red-200 hover:text-red-900 dark:hover:text-red-100 underline"
                >
                    Request Manual Review
                    <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>

        <div
            v-else
            class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-6"
        >
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <div>
                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">
                        No Fraud Detected
                    </h3>
                    <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                        View patterns appear normal. No suspicious activity detected.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

