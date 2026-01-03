<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import ProductCard from '@/Components/Marketplace/ProductCard.vue';

const props = defineProps({
    products: Object,
    filters: Object,
    stats: Object,
});

const filterForm = useForm({
    status: props.filters?.status || 'all',
});

const applyFilter = () => {
    filterForm.get(route('marketplace.products.my-products'), {
        preserveState: true,
    });
};
</script>

<template>
    <Head title="My Products" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    My Products
                </h2>
                <Link
                    :href="route('marketplace.products.create')"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                >
                    Add Product
                </Link>
            </div>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-7xl">
                <!-- Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ stats?.total_products || 0 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total Products
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ stats?.total_sales || 0 }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total Sales
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            Rp {{ new Intl.NumberFormat('id-ID').format(stats?.total_earnings || 0) }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Total Earnings
                        </div>
                    </div>
                </div>

                <!-- Filter -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex items-center space-x-4">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</label>
                        <select
                            v-model="filterForm.status"
                            @change="applyFilter"
                            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                        >
                            <option value="all">All</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <div v-if="products && products.data && products.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="product in products.data"
                        :key="product.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <Link :href="route('marketplace.products.show', product.id)">
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100 dark:bg-gray-700">
                                <img
                                    v-if="product.image_url || product.image"
                                    :src="product.image_url || product.image"
                                    :alt="product.name"
                                    class="w-full h-48 object-cover"
                                    @error="$event.target.src = '/images/placeholder.png'"
                                />
                                <div v-else class="w-full h-48 flex items-center justify-center text-gray-400">
                                    No Image
                                </div>
                            </div>
                        </Link>
                        <div class="p-4">
                            <Link :href="route('marketplace.products.show', product.id)">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-blue-600">
                                    {{ product.name }}
                                </h3>
                            </Link>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-xl font-bold text-blue-600">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(product.price) }}
                                </span>
                                <span
                                    :class="[
                                        'px-2 py-1 text-xs rounded-full',
                                        product.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                                    ]"
                                >
                                    {{ product.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-4">
                                <span>Views: {{ product.views_count || 0 }}</span>
                                <span>Sales: {{ product.sales_count || 0 }}</span>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('marketplace.products.edit', product.id)"
                                    class="flex-1 px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-center text-sm"
                                >
                                    Edit
                                </Link>
                                <Link
                                    :href="route('marketplace.products.show', product.id)"
                                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 text-center text-sm"
                                >
                                    View
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
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">No products yet</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Start selling by creating your first product</p>
                    <Link
                        :href="route('marketplace.products.create')"
                        class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition-colors"
                    >
                        Create Product
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="products.links && products.data.length > 0" class="mt-6">
                    <nav class="flex justify-center">
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in products.links"
                                :key="link.label"
                                :href="link.url ?? '#'"
                                :class="[
                                    'px-4 py-2 rounded-lg',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

