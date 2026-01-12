<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    product: Object,
    stockHistory: Object,
});

const stockUpdateForm = useForm({
    quantity: '',
    type: 'adjustment',
    reason: '',
});

const restockForm = useForm({
    quantity: '',
    reason: '',
});

const alertSettingsForm = useForm({
    low_stock_threshold: props.product?.low_stock_threshold || '',
});

const updateStock = () => {
    stockUpdateForm.put(route('marketplace.seller.inventory.stock.update', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            stockUpdateForm.reset();
        },
    });
};

const restock = () => {
    restockForm.post(route('marketplace.seller.inventory.restock', props.product.id), {
        preserveScroll: true,
        onSuccess: () => {
            restockForm.reset();
        },
    });
};

const updateAlertSettings = () => {
    alertSettingsForm.put(route('marketplace.seller.inventory.alert-settings'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Inventory: ${product?.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Inventory: {{ product?.name }}
                </h2>
                <Link
                    :href="route('marketplace.seller.inventory.index')"
                    class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                    Back to Inventory
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl space-y-6">
                <!-- Product Info -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start gap-6">
                        <img
                            v-if="product?.image_url || product?.image"
                            :src="product?.image_url || product?.image"
                            :alt="product?.name"
                            class="h-24 w-24 rounded-lg object-cover"
                        />
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ product?.name }}
                            </h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Current Stock</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                        {{ product?.stock || 0 }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Low Stock Threshold</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                        {{ product?.low_stock_threshold || 'Default' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                                    <span
                                        v-if="(product?.stock || 0) === 0"
                                        class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200"
                                    >
                                        Out of Stock
                                    </span>
                                    <span
                                        v-else-if="product?.has_low_stock"
                                        class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200"
                                    >
                                        Low Stock
                                    </span>
                                    <span
                                        v-else
                                        class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200"
                                    >
                                        In Stock
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Price</p>
                                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(product?.price || 0) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Stock Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Update Stock
                    </h3>
                    <form @submit.prevent="updateStock" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Quantity
                                </label>
                                <input
                                    v-model="stockUpdateForm.quantity"
                                    type="number"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Can be positive or negative
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Type
                                </label>
                                <select
                                    v-model="stockUpdateForm.type"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="adjustment">Adjustment</option>
                                    <option value="restock">Restock</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Reason
                                </label>
                                <input
                                    v-model="stockUpdateForm.reason"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="stockUpdateForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Update Stock
                        </button>
                    </form>
                </div>

                <!-- Restock Form -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Quick Restock
                    </h3>
                    <form @submit.prevent="restock" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Quantity to Add
                                </label>
                                <input
                                    v-model="restockForm.quantity"
                                    type="number"
                                    min="1"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Reason
                                </label>
                                <input
                                    v-model="restockForm.reason"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>
                        <button
                            type="submit"
                            :disabled="restockForm.processing"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50"
                        >
                            Restock
                        </button>
                    </form>
                </div>

                <!-- Alert Settings -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Low Stock Alert Settings
                    </h3>
                    <form @submit.prevent="updateAlertSettings" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Low Stock Threshold
                            </label>
                            <input
                                v-model="alertSettingsForm.low_stock_threshold"
                                type="number"
                                min="0"
                                class="w-full md:w-64 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Leave empty to use default threshold
                            </p>
                        </div>
                        <button
                            type="submit"
                            :disabled="alertSettingsForm.processing"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            Update Settings
                        </button>
                    </form>
                </div>

                <!-- Stock History -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Stock History
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Change</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">After</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Reason</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="history in stockHistory?.data || []"
                                    :key="history.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ new Date(history.created_at).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                            {{ history.type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        :class="history.quantity_change > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                    >
                                        {{ history.quantity_change > 0 ? '+' : '' }}{{ history.quantity_change }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ history.new_stock_level }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        {{ history.reason || '-' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="!stockHistory?.data || stockHistory.data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                        No stock history available
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

