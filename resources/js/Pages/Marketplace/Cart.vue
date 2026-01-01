<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CartItem from '@/Components/Marketplace/CartItem.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    cartItems: Array,
    subtotal: Number,
});

const isCheckingOut = ref(false);

const checkoutForm = useForm({
    cart_items: null, // null means checkout all items
});

const checkout = () => {
    if (props.cartItems.length === 0) {
        return;
    }

    isCheckingOut.value = true;
    
    checkoutForm.post(route('marketplace.cart.checkout'), {
        preserveScroll: false,
        onSuccess: () => {
            isCheckingOut.value = false;
        },
        onError: () => {
            isCheckingOut.value = false;
        },
        onFinish: () => {
            isCheckingOut.value = false;
        },
    });
};
</script>

<template>
    <Head title="Shopping Cart" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Shopping Cart
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <div v-if="cartItems && cartItems.length > 0" class="space-y-6">
                    <!-- Cart Items -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Cart Items ({{ cartItems.length }})
                            </h3>
                        </div>
                        
                        <div>
                            <CartItem
                                v-for="item in cartItems"
                                :key="item.id"
                                :item="item"
                            />
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Subtotal:</span>
                                <span class="font-semibold">Rp {{ new Intl.NumberFormat('id-ID').format(subtotal || 0) }}</span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                                    <span>Total:</span>
                                    <span>Rp {{ new Intl.NumberFormat('id-ID').format(subtotal || 0) }}</span>
                                </div>
                            </div>
                            <div class="pt-4">
                                <button
                                    @click="checkout"
                                    :disabled="isCheckingOut || checkoutForm.processing"
                                    class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold transition-colors"
                                >
                                    <span v-if="isCheckingOut || checkoutForm.processing">Processing...</span>
                                    <span v-else>Checkout ({{ cartItems.length }} item{{ cartItems.length > 1 ? 's' : '' }})</span>
                                </button>
                                <Link
                                    :href="route('marketplace.index')"
                                    class="block w-full mt-3 text-center px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                >
                                    Continue Shopping
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty Cart -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
                    <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Your cart is empty</h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">Start shopping to add items to your cart</p>
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
