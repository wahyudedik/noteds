<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    topUp: Object,
    snapToken: String,
});

const page = usePage();
const clientKey = page.props.midtrans_client_key;
const isLoading = ref(true);
const error = ref(null);

onMounted(() => {
    if (!props.snapToken) {
        error.value = 'Payment token not available. Please try again.';
        isLoading.value = false;
        return;
    }

    // Load Midtrans script
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', clientKey);
    script.async = true;
    
    script.onload = () => {
        if (window.snap && props.snapToken) {
            isLoading.value = false;
            window.snap.pay(props.snapToken, {
                onSuccess: (result) => {
                    // Redirect to top ups index with success message
                    router.visit(route('clipper.top-ups.index'), {
                        only: ['topUps'],
                        preserveScroll: true,
                        onSuccess: () => {
                            // Flash message will be handled by backend webhook
                        },
                    });
                },
                onPending: (result) => {
                    // Payment pending, redirect to index
                    router.visit(route('clipper.top-ups.index'), {
                        only: ['topUps'],
                        preserveScroll: true,
                    });
                },
                onError: (result) => {
                    error.value = 'Payment failed. Please try again.';
                    // Redirect back after 3 seconds
                    setTimeout(() => {
                        router.visit(route('clipper.top-ups.index'));
                    }, 3000);
                },
                onClose: () => {
                    // User closed payment popup
                    router.visit(route('clipper.top-ups.index'));
                },
            });
        } else {
            error.value = 'Failed to initialize payment gateway. Please refresh the page.';
            isLoading.value = false;
        }
    };
    
    script.onerror = () => {
        error.value = 'Failed to load payment gateway. Please check your internet connection.';
        isLoading.value = false;
    };
    
    document.head.appendChild(script);
});
</script>

<template>
    <Head title="Top Up Payment" />

    <ClipperLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Top Up Payment
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-2xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div v-if="isLoading && !error" class="text-center py-12">
                        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Processing Payment...
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            Please wait while we redirect you to the payment page.
                        </p>
                    </div>

                    <div v-else-if="error" class="text-center py-12">
                        <div class="mb-4">
                            <svg class="mx-auto h-12 w-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Payment Error
                        </h3>
                        <p class="text-red-600 dark:text-red-400 mb-6">
                            {{ error }}
                        </p>
                        <div class="flex space-x-4 justify-center">
                            <button
                                @click="router.visit(route('clipper.top-ups.create'))"
                                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
                            >
                                Try Again
                            </button>
                            <button
                                @click="router.visit(route('clipper.top-ups.index'))"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                            >
                                Back to Top Up History
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <div class="mb-4">
                            <svg class="mx-auto h-12 w-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Payment Gateway Ready
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400">
                            The payment popup should open automatically. If it doesn't, please allow popups for this site.
                        </p>
                    </div>
                </div>

                <!-- Top Up Details -->
                <div v-if="topUp" class="mt-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Top Up Details
                    </h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Amount:</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                Rp {{ new Intl.NumberFormat('id-ID').format(topUp.amount) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Payment Method:</span>
                            <span class="font-semibold text-gray-900 dark:text-white capitalize">
                                {{ topUp.payment_method === 'ewallet' ? 'E-Wallet' : 
                                   topUp.payment_method === 'virtual_account' ? 'Virtual Account' : 
                                   topUp.payment_method === 'credit_card' ? 'Credit Card' : 
                                   topUp.payment_method }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Status:</span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Pending Payment
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

