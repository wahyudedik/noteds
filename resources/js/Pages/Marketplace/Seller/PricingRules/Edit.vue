<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    rule: Object,
    products: Array,
});

const form = useForm({
    product_id: props.rule?.product_id || '',
    rule_name: props.rule?.rule_name || '',
    rule_type: props.rule?.rule_type || 'time_based',
    is_active: props.rule?.is_active ?? true,
    priority: props.rule?.priority || 0,
    // Time-based fields
    start_date: props.rule?.start_date || '',
    end_date: props.rule?.end_date || '',
    start_time: props.rule?.start_time || '',
    end_time: props.rule?.end_time || '',
    days_of_week: props.rule?.days_of_week || [],
    // Stock-based fields
    stock_threshold: props.rule?.stock_threshold || '',
    stock_condition: props.rule?.stock_condition || 'below',
    // Demand-based fields
    sales_period_days: props.rule?.sales_period_days || '',
    sales_threshold: props.rule?.sales_threshold || '',
    demand_condition: props.rule?.demand_condition || 'high',
    // Adjustment fields
    adjustment_type: props.rule?.adjustment_type || 'percentage',
    adjustment_value: props.rule?.adjustment_value || '',
    base_price_override: props.rule?.base_price_override || '',
    max_applications: props.rule?.max_applications || '',
});

const daysOfWeekOptions = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const selectedProduct = computed(() => {
    return props.products?.find(p => p.id === form.product_id);
});

const previewPrice = ref(null);
const isPreviewLoading = ref(false);

const calculatePreviewPrice = async () => {
    if (!form.product_id || !form.adjustment_value) return;
    
    isPreviewLoading.value = true;
    try {
        const response = await fetch(route('marketplace.seller.pricing-preview', form.product_id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(form.data()),
        });
        const data = await response.json();
        previewPrice.value = data.effective_price;
    } catch (error) {
        console.error('Preview error:', error);
    } finally {
        isPreviewLoading.value = false;
    }
};

const submit = () => {
    form.put(route('marketplace.seller.pricing-rules.update', props.rule.id), {
        preserveScroll: true,
    });
};

const toggleDay = (day) => {
    const index = form.days_of_week.indexOf(day);
    if (index > -1) {
        form.days_of_week.splice(index, 1);
    } else {
        form.days_of_week.push(day);
    }
};
</script>

<template>
    <Head title="Edit Pricing Rule" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit Pricing Rule
                </h2>
                <Link
                    :href="route('marketplace.seller.pricing-rules.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to Rules
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Basic Information
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Product *
                                </label>
                                <select
                                    v-model="form.product_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Select a product</option>
                                    <option
                                        v-for="product in products"
                                        :key="product.id"
                                        :value="product.id"
                                    >
                                        {{ product.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Rule Name *
                                </label>
                                <input
                                    v-model="form.rule_name"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Rule Type *
                                    </label>
                                    <select
                                        v-model="form.rule_type"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="time_based">Time-Based</option>
                                        <option value="stock_based">Stock-Based</option>
                                        <option value="demand_based">Demand-Based</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Priority (0-100)
                                    </label>
                                    <input
                                        v-model.number="form.priority"
                                        type="number"
                                        min="0"
                                        max="100"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="flex items-center gap-2">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="rounded border-gray-300 dark:border-gray-600"
                                    />
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Active
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Time-Based Fields -->
                    <div v-if="form.rule_type === 'time_based'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Time-Based Settings
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Start Date
                                    </label>
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        End Date
                                    </label>
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        :min="form.start_date"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Start Time
                                    </label>
                                    <input
                                        v-model="form.start_time"
                                        type="time"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        End Time
                                    </label>
                                    <input
                                        v-model="form.end_time"
                                        type="time"
                                        :min="form.start_time"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Days of Week
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="day in daysOfWeekOptions"
                                        :key="day.value"
                                        type="button"
                                        @click="toggleDay(day.value)"
                                        :class="[
                                            'px-3 py-1 rounded-lg text-sm font-medium',
                                            form.days_of_week.includes(day.value)
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                        ]"
                                    >
                                        {{ day.label }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stock-Based Fields -->
                    <div v-if="form.rule_type === 'stock_based'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Stock-Based Settings
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Stock Threshold *
                                    </label>
                                    <input
                                        v-model.number="form.stock_threshold"
                                        type="number"
                                        min="0"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Condition *
                                    </label>
                                    <select
                                        v-model="form.stock_condition"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="below">Below</option>
                                        <option value="above">Above</option>
                                        <option value="equals">Equals</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Demand-Based Fields -->
                    <div v-if="form.rule_type === 'demand_based'" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Demand-Based Settings
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Sales Period (Days) *
                                    </label>
                                    <input
                                        v-model.number="form.sales_period_days"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Sales Threshold
                                    </label>
                                    <input
                                        v-model.number="form.sales_threshold"
                                        type="number"
                                        min="0"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Demand Condition *
                                </label>
                                <select
                                    v-model="form.demand_condition"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="high">High Demand</option>
                                    <option value="low">Low Demand</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Price Adjustment -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            Price Adjustment
                        </h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Adjustment Type *
                                    </label>
                                    <select
                                        v-model="form.adjustment_type"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    >
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Adjustment Value *
                                    </label>
                                    <input
                                        v-model.number="form.adjustment_value"
                                        type="number"
                                        step="0.01"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span v-if="form.adjustment_type === 'percentage'">Percentage (e.g., -20 for 20% discount)</span>
                                        <span v-else>Fixed amount (e.g., -5000 for Rp 5,000 discount)</span>
                                    </p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Base Price Override
                                    </label>
                                    <input
                                        v-model.number="form.base_price_override"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Leave empty to use product base price
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Max Applications
                                    </label>
                                    <input
                                        v-model.number="form.max_applications"
                                        type="number"
                                        min="1"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Limit how many times this rule can apply
                                    </p>
                                </div>
                            </div>
                            <div v-if="selectedProduct && form.adjustment_value" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            Preview Effective Price
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Base price: Rp {{ new Intl.NumberFormat('id-ID').format(selectedProduct.base_price || selectedProduct.price || 0) }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click="calculatePreviewPrice"
                                        :disabled="isPreviewLoading"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 text-sm"
                                    >
                                        {{ isPreviewLoading ? 'Calculating...' : 'Preview' }}
                                    </button>
                                </div>
                                <div v-if="previewPrice" class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-700">
                                    <p class="text-lg font-bold text-blue-600 dark:text-blue-400">
                                        Effective Price: Rp {{ new Intl.NumberFormat('id-ID').format(previewPrice) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4">
                        <Link
                            :href="route('marketplace.seller.pricing-rules.index')"
                            class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Update Rule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

