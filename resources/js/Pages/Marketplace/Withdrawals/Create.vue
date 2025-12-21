<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import WithdrawalForm from '@/Components/Marketplace/WithdrawalForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    balance: Number,
});
</script>

<template>
    <Head title="Request Withdrawal" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Request Withdrawal
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-2xl">
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm">
                        <strong>Current Balance:</strong> 
                        Rp {{ new Intl.NumberFormat('id-ID').format(balance || 0) }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Minimum withdrawal amount: Rp 50.000
                    </p>
                </div>
                <WithdrawalForm :balance="balance" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

