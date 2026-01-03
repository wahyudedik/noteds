<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

const props = defineProps({
    order: Object,
    snap_token: String,
});

const page = usePage();
const clientKey = page.props.midtrans_client_key;
const isProduction = page.props.midtrans_is_production || false;

onMounted(() => {
    // Load Midtrans script (sandbox or production)
    const script = document.createElement('script');
    script.src = isProduction 
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', clientKey);
    script.async = true;
    
    script.onload = () => {
        if (window.snap && props.snap_token) {
            window.snap.pay(props.snap_token, {
                onSuccess: (result) => {
                    window.location.href = route('marketplace.orders.show', props.order.id);
                },
                onPending: (result) => {
                    window.location.href = route('marketplace.orders.show', props.order.id);
                },
                onError: (result) => {
                    alert('Payment failed. Please try again.');
                    window.location.href = route('marketplace.index');
                },
            });
        }
    };
    
    document.head.appendChild(script);
});
</script>

<template>
    <Head title="Payment" />

    <AuthenticatedLayout>
        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-2xl font-bold mb-4">Processing Payment...</h2>
                <p class="text-gray-600">Please wait while we redirect you to the payment page.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

