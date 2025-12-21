<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    product: Object,
});

const form = useForm({
    quantity: 1,
});

const buyProduct = () => {
    form.post(route('marketplace.orders.store', { product_id: props.product.id }));
};

const canEdit = computed(() => {
    return props.product.user_id === $page.props.auth?.user?.id;
});
</script>

<template>
    <Head :title="product.name" />

    <AuthenticatedLayout>
        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <!-- Back Button -->
                <Link
                    :href="route('marketplace.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to Marketplace
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Product Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                        <img
                            v-if="product.image"
                            :src="product.image"
                            :alt="product.name"
                            class="w-full h-64 object-cover"
                        />
                        <div v-else class="w-full h-64 flex items-center justify-center text-gray-400">
                            No Image
                        </div>
                    </div>

                    <!-- Product Details -->
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                    {{ product.name }}
                                </h1>
                                <p class="text-2xl font-semibold text-blue-600">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                                </p>
                            </div>
                            <div v-if="canEdit" class="flex space-x-2">
                                <Link
                                    :href="route('marketplace.products.edit', product.id)"
                                    class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                                >
                                    Edit
                                </Link>
                            </div>
                        </div>

                        <div class="mb-4">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                {{ product.category || 'Uncategorized' }}
                            </span>
                        </div>

                        <div class="mb-6">
                            <h2 class="text-xl font-semibold mb-2">Description</h2>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                                {{ product.description }}
                            </p>
                        </div>

                        <!-- Product Stats -->
                        <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                            <div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ product.sales_count || 0 }}
                                </div>
                                <div class="text-sm text-gray-500">Sales</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ product.views_count || 0 }}
                                </div>
                                <div class="text-sm text-gray-500">Views</div>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ product.seller?.name || 'Unknown' }}
                                </div>
                                <div class="text-sm text-gray-500">Seller</div>
                            </div>
                        </div>

                        <!-- Buy Button -->
                        <div v-if="!canEdit" class="border-t pt-6">
                            <form @submit.prevent="buyProduct">
                                <div class="flex items-center space-x-4">
                                    <label class="text-sm font-medium">Quantity:</label>
                                    <input
                                        v-model.number="form.quantity"
                                        type="number"
                                        min="1"
                                        :max="product.stock"
                                        class="w-20 px-3 py-2 border border-gray-300 rounded-lg"
                                    />
                                    <button
                                        type="submit"
                                        :disabled="form.processing || (product.stock !== null && product.stock < form.quantity)"
                                        class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                                    >
                                        Buy Now
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

