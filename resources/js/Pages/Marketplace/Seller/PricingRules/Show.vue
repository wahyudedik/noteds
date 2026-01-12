<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PricingRuleCard from '@/Components/Marketplace/Seller/PricingRuleCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    rule: Object,
    applications: Object,
});

const previewPrice = ref(null);
const isPreviewLoading = ref(false);

const testRule = async () => {
    if (!props.rule?.product_id) return;
    
    isPreviewLoading.value = true;
    try {
        const response = await fetch(route('marketplace.products.effective-price', props.rule.product_id));
        const data = await response.json();
        previewPrice.value = data.effective_price;
    } catch (error) {
        console.error('Preview error:', error);
    } finally {
        isPreviewLoading.value = false;
    }
};

const deleteRule = () => {
    if (confirm('Are you sure you want to delete this pricing rule?')) {
        router.delete(route('marketplace.seller.pricing-rules.destroy', props.rule.id));
    }
};
</script>

<template>
    <Head :title="`Pricing Rule: ${rule?.rule_name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Pricing Rule: {{ rule?.rule_name }}
                </h2>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('marketplace.seller.pricing-rules.edit', rule?.id)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <Link
                        :href="route('marketplace.seller.pricing-rules.index')"
                        class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        Back to Rules
                    </Link>
                </div>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Rule Details -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ rule?.rule_name }}
                                </h3>
                                <span
                                    :class="[
                                        'px-3 py-1 text-xs font-semibold rounded-full',
                                        rule?.is_active
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                                    ]"
                                >
                                    {{ rule?.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ rule?.rule_type === 'time_based' ? 'Time-Based' : rule?.rule_type === 'stock_based' ? 'Stock-Based' : 'Demand-Based' }} Rule
                                <span v-if="rule?.priority !== undefined" class="ml-2">
                                    • Priority: {{ rule.priority }}
                                </span>
                            </p>
                        </div>
                        <button
                            @click="deleteRule"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium"
                        >
                            Delete
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Product
                            </p>
                            <p class="text-lg text-gray-900 dark:text-white">
                                {{ rule?.product?.name || 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Adjustment
                            </p>
                            <p class="text-lg text-gray-900 dark:text-white">
                                {{ rule?.adjustment_type === 'percentage' ? rule?.adjustment_value + '%' : 'Rp ' + new Intl.NumberFormat('id-ID').format(rule?.adjustment_value || 0) }}
                            </p>
                        </div>
                    </div>

                    <!-- Rule-Specific Details -->
                    <div v-if="rule?.rule_type === 'time_based'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Time-Based Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.start_date || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.end_date || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Start Time</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.start_time || 'Not set' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">End Time</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.end_time || 'Not set' }}</p>
                            </div>
                            <div v-if="rule?.days_of_week && rule.days_of_week.length > 0" class="md:col-span-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Days of Week</p>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="day in rule.days_of_week"
                                        :key="day"
                                        class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-lg text-sm"
                                    >
                                        {{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][day] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="rule?.rule_type === 'stock_based'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Stock-Based Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Stock Threshold</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.stock_threshold || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Condition</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.stock_condition || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="rule?.rule_type === 'demand_based'" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Demand-Based Settings
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sales Period (Days)</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.sales_period_days || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Sales Threshold</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.sales_threshold || 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Demand Condition</p>
                                <p class="text-gray-900 dark:text-white">{{ rule?.demand_condition || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Price -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                    Current Effective Price
                                </h4>
                                <p v-if="previewPrice" class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(previewPrice) }}
                                </p>
                                <p v-else class="text-gray-500 dark:text-gray-400">
                                    Click "Test Rule" to preview
                                </p>
                            </div>
                            <button
                                @click="testRule"
                                :disabled="isPreviewLoading"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ isPreviewLoading ? 'Loading...' : 'Test Rule' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Application History -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Application History
                    </h3>
                    <div v-if="applications?.data && applications.data.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Original Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Adjusted Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Change</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="application in applications.data"
                                    :key="application.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ new Date(application.applied_at).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(application.original_price || 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(application.adjusted_price || 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        :class="(application.adjusted_price - application.original_price) >= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                                    >
                                        {{ (application.adjusted_price - application.original_price) >= 0 ? '+' : '' }}
                                        Rp {{ new Intl.NumberFormat('id-ID').format(Math.abs(application.adjusted_price - application.original_price)) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No applications yet
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

