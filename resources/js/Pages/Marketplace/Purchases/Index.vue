<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    purchasedProducts: Array,
});

const downloadFile = (productId) => {
    window.location.href = route('marketplace.products.download', productId);
};
</script>

<template>
    <Head title="My Purchases" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                My Purchases
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <div v-if="purchasedProducts && purchasedProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="order in purchasedProducts"
                        :key="order.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-md transition-shadow"
                    >
                        <!-- Product Image -->
                        <Link :href="route('marketplace.products.show', order.product_id)">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                                <img
                                    v-if="order.product?.image"
                                    :src="order.product.image"
                                    :alt="order.product.name"
                                    class="w-full h-48 object-cover"
                                />
                                <div v-else class="w-full h-48 flex items-center justify-center text-gray-400">
                                    No Image
                                </div>
                            </div>
                        </Link>

                        <!-- Product Info -->
                        <div class="p-4">
                            <Link :href="route('marketplace.products.show', order.product_id)">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-blue-600">
                                    {{ order.product?.name }}
                                </h3>
                            </Link>

                            <div class="space-y-2 mb-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Purchased:</span>
                                    {{ new Date(order.created_at).toLocaleDateString() }}
                                </div>
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Price Paid:</span>
                                    <span class="font-semibold text-blue-600">
                                        Rp {{ new Intl.NumberFormat('id-ID').format(order.total) }}
                                    </span>
                                </div>
                                <div v-if="order.license_key" class="text-sm">
                                    <span class="font-medium text-gray-600 dark:text-gray-400">License Key:</span>
                                    <p class="font-mono text-xs bg-gray-100 dark:bg-gray-700 p-2 rounded mt-1 break-all">
                                        {{ order.license_key }}
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-2">
                                <button
                                    v-if="order.product?.file_download"
                                    @click="downloadFile(order.product_id)"
                                    class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors"
                                >
                                    Download
                                </button>
                                <Link
                                    :href="route('marketplace.orders.show', order.id)"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors"
                                >
                                    View Order
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No purchases yet</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Start shopping to see your purchased products here</p>
                    <Link
                        :href="route('marketplace.index')"
                        class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                    >
                        Browse Products
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

