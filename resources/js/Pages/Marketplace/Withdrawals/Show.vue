<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    withdrawal: Object,
});
</script>

<template>
    <Head title="Withdrawal Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Withdrawal Details
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <Link
                    :href="route('marketplace.withdrawals.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to Withdrawals
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2">Withdrawal Request</h3>
                        <span
                            :class="[
                                'inline-block px-3 py-1 rounded-full text-sm',
                                withdrawal.status === 'completed' ? 'bg-green-100 text-green-800' :
                                withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                                withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800' :
                                'bg-yellow-100 text-yellow-800'
                            ]"
                        >
                            {{ withdrawal.status }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold mb-2">Amount</h4>
                            <p class="text-xl text-blue-600">
                                Rp {{ new Intl.NumberFormat('id-ID').format(withdrawal.amount) }}
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Method</h4>
                            <p>{{ withdrawal.method === 'bank_transfer' ? 'Bank Transfer' : 'E-Wallet' }}</p>
                        </div>

                        <div v-if="withdrawal.bank_name">
                            <h4 class="font-semibold mb-2">Bank Name</h4>
                            <p>{{ withdrawal.bank_name }}</p>
                        </div>

                        <div v-if="withdrawal.ewallet_type">
                            <h4 class="font-semibold mb-2">E-Wallet Type</h4>
                            <p>{{ withdrawal.ewallet_type }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Account Number</h4>
                            <p>{{ withdrawal.account_number }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Account Name</h4>
                            <p>{{ withdrawal.account_name }}</p>
                        </div>

                        <div v-if="withdrawal.admin_notes">
                            <h4 class="font-semibold mb-2">Admin Notes</h4>
                            <p class="text-gray-700 dark:text-gray-300">{{ withdrawal.admin_notes }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2">Request Date</h4>
                            <p>{{ new Date(withdrawal.created_at).toLocaleString() }}</p>
                        </div>

                        <div v-if="withdrawal.processed_at">
                            <h4 class="font-semibold mb-2">Processed Date</h4>
                            <p>{{ new Date(withdrawal.processed_at).toLocaleString() }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

