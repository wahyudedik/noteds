<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    item: {
        type: Object,
        required: true,
    },
});

const remove = () => {
    router.delete(route('marketplace.cart.remove', item.id));
};

const updateQuantity = (quantity) => {
    router.post(route('marketplace.cart.update'), {
        item_id: item.id,
        quantity,
    });
};
</script>

<template>
    <div class="flex items-center justify-between p-4 border-b">
        <div class="flex items-center space-x-4">
            <img
                v-if="item.product?.image"
                :src="item.product.image"
                :alt="item.product.name"
                class="w-20 h-20 object-cover rounded"
            />
            <div>
                <h3 class="font-semibold">{{ item.product?.name }}</h3>
                <p class="text-gray-500 text-sm">Rp {{ new Intl.NumberFormat('id-ID').format(item.product?.price || 0) }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <input
                :value="item.quantity"
                @change="updateQuantity($event.target.value)"
                type="number"
                min="1"
                class="w-16 px-2 py-1 border rounded"
            />
            <button
                @click="remove"
                class="px-4 py-2 text-red-600 hover:bg-red-50 rounded"
            >
                Remove
            </button>
        </div>
    </div>
</template>

