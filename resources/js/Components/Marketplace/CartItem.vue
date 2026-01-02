<script setup>
import { router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const quantity = ref(props.item.quantity);
const isUpdating = ref(false);

const remove = () => {
    if (confirm('Are you sure you want to remove this item from cart?')) {
        router.delete(route('marketplace.cart.destroy', props.item.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Item removed
            },
        });
    }
};

const updateQuantity = () => {
    if (quantity.value < 1) {
        quantity.value = 1;
        return;
    }

    isUpdating.value = true;
    
    router.put(route('marketplace.cart.update', props.item.id), {
        quantity: parseInt(quantity.value),
    }, {
        preserveScroll: true,
        onFinish: () => {
            isUpdating.value = false;
        },
    });
};
</script>

<template>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3 sm:p-4 border-b border-gray-200 dark:border-gray-700 gap-3 sm:gap-4">
        <div class="flex items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
            <Link :href="route('marketplace.products.show', props.item.product_id)" class="flex-shrink-0">
                <img
                    v-if="item.product?.image"
                    :src="item.product.image"
                    :alt="item.product.name"
                    class="w-16 h-16 sm:w-20 sm:h-20 object-cover rounded"
                />
                <div v-else class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                    <span class="text-gray-400 dark:text-gray-500 text-xs">No Image</span>
                </div>
            </Link>
            <div class="flex-1 min-w-0">
                <Link :href="route('marketplace.products.show', props.item.product_id)">
                    <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 line-clamp-2">{{ item.product?.name }}</h3>
                </Link>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Rp {{ new Intl.NumberFormat('id-ID').format(item.product?.price || 0) }} each</p>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1 font-medium">
                    Total: Rp {{ new Intl.NumberFormat('id-ID').format((item.product?.price || 0) * item.quantity) }}
                </p>
            </div>
        </div>
        <div class="flex items-center justify-between sm:justify-end space-x-2 sm:space-x-4">
            <div class="flex items-center space-x-2">
                <label class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">Qty:</label>
                <input
                    v-model.number="quantity"
                    @change="updateQuantity"
                    @blur="updateQuantity"
                    type="number"
                    min="1"
                    :max="item.product?.stock || null"
                    :disabled="isUpdating"
                    class="w-16 sm:w-20 px-2 sm:px-3 py-2 text-base border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <button
                @click="remove"
                class="px-3 sm:px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors min-w-[44px] min-h-[44px] flex items-center justify-center"
                title="Remove from cart"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </button>
        </div>
    </div>
</template>
