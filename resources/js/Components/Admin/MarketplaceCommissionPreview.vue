<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    commissionPercentage: {
        type: Number,
        required: true,
        default: 5,
    },
    commissionFlatFee: {
        type: Number,
        required: true,
        default: 0,
    },
    exampleAmounts: {
        type: Array,
        default: () => [50000, 100000, 250000, 500000, 1000000],
    },
});

const selectedAmount = ref(props.exampleAmounts[1] || 100000);
const customAmount = ref(null);

const displayAmount = computed(() => {
    return customAmount.value !== null ? customAmount.value : selectedAmount.value;
});

const commissionPercentageAmount = computed(() => {
    return (displayAmount.value * props.commissionPercentage) / 100;
});

const totalCommission = computed(() => {
    return commissionPercentageAmount.value + props.commissionFlatFee;
});

const sellerAmount = computed(() => {
    const total = displayAmount.value - totalCommission.value;
    return Math.max(0, total);
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(Math.round(amount || 0));
};

const hasWarning = computed(() => {
    return totalCommission.value > displayAmount.value;
});
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Preview dengan Order Amount:
            </label>
            <div class="flex flex-wrap gap-2 mb-3">
                <button
                    v-for="amount in exampleAmounts"
                    :key="amount"
                    @click="selectedAmount = amount; customAmount = null"
                    :class="[
                        'px-3 py-1 text-sm rounded-lg border transition-colors',
                        displayAmount === amount
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600',
                    ]"
                >
                    Rp {{ formatCurrency(amount) }}
                </button>
            </div>
            <div class="flex gap-2">
                <input
                    v-model.number="customAmount"
                    type="number"
                    min="0"
                    step="1000"
                    placeholder="Custom amount"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                    @input="selectedAmount = null"
                />
                <span class="text-sm text-gray-500 dark:text-gray-400 self-center">atau masukkan custom</span>
            </div>
        </div>

        <div
            v-if="hasWarning"
            class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg
                        class="h-5 w-5 text-yellow-400"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-800 dark:text-yellow-200">
                        Peringatan: Total komisi melebihi order amount. Seller akan menerima Rp 0.
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Order Total:</span>
                <span class="text-lg font-semibold text-gray-900 dark:text-white">
                    Rp {{ formatCurrency(displayAmount) }}
                </span>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-600 pt-3 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Commission ({{ commissionPercentage }}%):</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        - Rp {{ formatCurrency(commissionPercentageAmount) }}
                    </span>
                </div>

                <div v-if="commissionFlatFee > 0" class="flex justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Flat Fee:</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        - Rp {{ formatCurrency(commissionFlatFee) }}
                    </span>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-600 pt-2 flex justify-between">
                    <span class="text-sm font-medium text-red-600 dark:text-red-400">Total Commission:</span>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">
                        - Rp {{ formatCurrency(totalCommission) }}
                    </span>
                </div>
            </div>

            <div class="border-t border-gray-300 dark:border-gray-500 pt-3 flex justify-between items-center">
                <span class="text-base font-semibold text-gray-900 dark:text-white">Seller Receives:</span>
                <span class="text-xl font-bold text-green-600 dark:text-green-400">
                    Rp {{ formatCurrency(sellerAmount) }}
                </span>
            </div>
        </div>
    </div>
</template>

