<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    rule: {
        type: Object,
        required: true,
    },
});

const ruleTypeLabels = {
    time_based: 'Time-Based',
    stock_based: 'Stock-Based',
    demand_based: 'Demand-Based',
};

const statusBadgeClass = computed(() => {
    return props.rule.is_active
        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
});

const toggleRule = () => {
    router.put(
        route('marketplace.seller.pricing-rules.toggle', props.rule.id),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
};

const deleteRule = () => {
    if (confirm('Are you sure you want to delete this pricing rule?')) {
        router.delete(route('marketplace.seller.pricing-rules.destroy', props.rule.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ rule.name }}
                    </h3>
                    <span
                        :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            statusBadgeClass,
                        ]"
                    >
                        {{ rule.is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Type: {{ ruleTypeLabels[rule.rule_type] || rule.rule_type }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                    Priority: {{ rule.priority }}
                </p>
                <p v-if="rule.product" class="text-sm text-gray-500 dark:text-gray-400">
                    Product: {{ rule.product.name }}
                </p>
            </div>
        </div>
        
        <div class="flex items-center gap-2 mt-4">
            <button
                @click="toggleRule"
                :class="[
                    'px-3 py-1.5 rounded-lg text-sm font-medium transition-colors',
                    rule.is_active
                        ? 'bg-gray-200 text-gray-800 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'
                        : 'bg-green-200 text-green-800 hover:bg-green-300 dark:bg-green-900 dark:text-green-200 dark:hover:bg-green-800',
                ]"
            >
                {{ rule.is_active ? 'Deactivate' : 'Activate' }}
            </button>
            <Link
                :href="route('marketplace.seller.pricing-rules.edit', rule.id)"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors"
            >
                Edit
            </Link>
            <button
                @click="deleteRule"
                class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium transition-colors"
            >
                Delete
            </button>
        </div>
    </div>
</template>

