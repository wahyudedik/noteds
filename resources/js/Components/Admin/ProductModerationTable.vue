<script setup>
import { Link, router } from '@inertiajs/vue3';

defineProps({
    products: Array,
});

const approve = (productId) => {
    router.post(route('admin.products.approve', productId));
};

const reject = (productId) => {
    router.post(route('admin.products.reject', productId));
};
</script>

<template>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Seller</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="product in products" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                    <Link
                        :href="route('marketplace.products.show', product.id)"
                        class="font-medium text-blue-600 hover:text-blue-800"
                    >
                        {{ product.name }}
                    </Link>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ product.seller?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        :class="[
                            'px-2 py-1 text-xs rounded-full',
                            product.is_active ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                        ]"
                    >
                        {{ product.is_active ? 'Active' : 'Pending' }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <button
                        @click="approve(product.id)"
                        class="text-green-600 hover:text-green-800"
                    >
                        Approve
                    </button>
                    <button
                        @click="reject(product.id)"
                        class="text-red-600 hover:text-red-800"
                    >
                        Reject
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>

