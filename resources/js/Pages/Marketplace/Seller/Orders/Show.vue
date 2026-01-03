<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const statusForm = useForm({
    status: props.order.status,
});

const updateStatus = () => {
    statusForm.put(route('marketplace.seller.orders.update-status', props.order.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Status updated
        },
    });
};
</script>

<template>
    <Head :title="'Order #' + order.order_number" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Order Details
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <Link
                    :href="route('marketplace.seller.orders.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to My Sales
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2">Order #{{ order.order_number }}</h3>
                        <span
                            :class="[
                                'inline-block px-3 py-1 rounded-full text-sm',
                                order.status === 'completed' ? 'bg-green-100 text-green-800' :
                                order.status === 'paid' ? 'bg-blue-100 text-blue-800' :
                                order.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-red-100 text-red-800'
                            ]"
                        >
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div>
                            <h4 class="font-semibold mb-2">Product</h4>
                            <p>{{ order.product?.name }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Buyer</h4>
                            <p>{{ order.buyer?.name }} ({{ order.buyer?.email }})</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Quantity</h4>
                            <p>{{ order.quantity }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Order Total</h4>
                            <p class="text-xl text-blue-600 font-semibold">
                                Rp {{ new Intl.NumberFormat('id-ID').format(order.total) }}
                            </p>
                        </div>

                        <!-- Commission Breakdown -->
                        <div
                            v-if="order.platform_commission_total !== null && order.platform_commission_total > 0"
                            class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4 border border-gray-200 dark:border-gray-600"
                        >
                            <h4 class="font-semibold mb-3 text-gray-900 dark:text-white">Commission Breakdown</h4>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600 dark:text-gray-400">Commission ({{ order.platform_commission_percentage }}%):</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        - Rp {{ new Intl.NumberFormat('id-ID').format(order.platform_commission_percentage ? (order.total * order.platform_commission_percentage / 100) : 0) }}
                                    </span>
                                </div>
                                <div
                                    v-if="order.platform_commission_flat && order.platform_commission_flat > 0"
                                    class="flex justify-between"
                                >
                                    <span class="text-gray-600 dark:text-gray-400">Flat Fee:</span>
                                    <span class="font-medium text-red-600 dark:text-red-400">
                                        - Rp {{ new Intl.NumberFormat('id-ID').format(order.platform_commission_flat) }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-300 dark:border-gray-500 pt-2 flex justify-between font-semibold">
                                    <span class="text-red-600 dark:text-red-400">Total Commission:</span>
                                    <span class="text-red-600 dark:text-red-400">
                                        - Rp {{ new Intl.NumberFormat('id-ID').format(order.platform_commission_total) }}
                                    </span>
                                </div>
                                <div class="border-t border-gray-300 dark:border-gray-500 pt-2 mt-2 flex justify-between items-center">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Amount You Received:</span>
                                    <span class="text-xl font-bold text-green-600 dark:text-green-400">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(order.seller_amount || order.total) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="order.seller_amount && order.seller_amount !== order.total" class="text-sm text-gray-600 dark:text-gray-400">
                            You received: <span class="font-semibold text-green-600 dark:text-green-400">Rp {{ new Intl.NumberFormat('id-ID').format(order.seller_amount) }}</span>
                        </div>

                        <div v-if="order.license_key">
                            <h4 class="font-semibold mb-2">License Key</h4>
                            <p class="font-mono bg-gray-100 dark:bg-gray-700 p-2 rounded">
                                {{ order.license_key }}
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Order Date</h4>
                            <p>{{ new Date(order.created_at).toLocaleString() }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex space-x-3">
                        <a
                            v-if="order.payment_status === 'paid' || order.payment_status === 'completed'"
                            :href="route('marketplace.seller.orders.invoice', order.id)"
                            target="_blank"
                            class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 inline-flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Generate Invoice
                        </a>
                    </div>

                    <!-- Update Status -->
                    <div class="border-t pt-6 mt-6">
                        <h4 class="font-semibold mb-4">Update Order Status</h4>
                        <form @submit.prevent="updateStatus" class="flex items-center space-x-4">
                            <select
                                v-model="statusForm.status"
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button
                                type="submit"
                                :disabled="statusForm.processing || statusForm.status === order.status"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

