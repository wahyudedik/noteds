<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const downloadFile = () => {
    if (props.order.product?.file_download && props.order.payment_status === 'paid') {
        window.location.href = route('marketplace.products.download', props.order.product_id);
    }
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
                    :href="route('marketplace.orders.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to Orders
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
                            <h4 class="font-semibold mb-2">Quantity</h4>
                            <p>{{ order.quantity }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Total</h4>
                            <p class="text-xl text-blue-600">
                                Rp {{ new Intl.NumberFormat('id-ID').format(order.total) }}
                            </p>
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

                    <div v-if="order.payment_status === 'paid' && order.product?.file_download" class="mt-6">
                        <button
                            @click="downloadFile"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                        >
                            Download File
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

