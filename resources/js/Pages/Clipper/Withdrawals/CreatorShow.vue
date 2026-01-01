<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    withdrawal: Object,
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <Head title="Creator Withdrawal Details" />

    <ClipperLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Creator Withdrawal Details
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <Link
                    :href="route('clipper.withdrawals.creator.index')"
                    class="mb-4 inline-flex items-center text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                    ← Back to Withdrawals
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white">Withdrawal Request</h3>
                        <span
                            :class="[
                                'inline-block px-3 py-1 rounded-full text-sm font-medium',
                                withdrawal.status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' :
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
                            ]"
                        >
                            {{ withdrawal.status }}
                        </span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Amount</h4>
                            <p class="text-xl text-blue-600 dark:text-blue-400 font-bold">
                                Rp {{ formatCurrency(withdrawal.amount) }}
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Method</h4>
                            <p class="text-gray-900 dark:text-white">{{ withdrawal.method === 'bank_transfer' ? 'Bank Transfer' : 'E-Wallet' }}</p>
                        </div>

                        <div v-if="withdrawal.bank_name">
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Bank Name</h4>
                            <p class="text-gray-900 dark:text-white">{{ withdrawal.bank_name }}</p>
                        </div>

                        <div v-if="withdrawal.ewallet_type">
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">E-Wallet Type</h4>
                            <p class="text-gray-900 dark:text-white">{{ withdrawal.ewallet_type }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Account Number</h4>
                            <p class="text-gray-900 dark:text-white font-mono">{{ withdrawal.account_number }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Account Name</h4>
                            <p class="text-gray-900 dark:text-white">{{ withdrawal.account_name }}</p>
                        </div>

                        <div v-if="withdrawal.admin_notes">
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Admin Notes</h4>
                            <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">{{ withdrawal.admin_notes }}</p>
                        </div>

                        <div>
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Request Date</h4>
                            <p class="text-gray-900 dark:text-white">{{ new Date(withdrawal.created_at).toLocaleString('id-ID') }}</p>
                        </div>

                        <div v-if="withdrawal.processed_at">
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Processed Date</h4>
                            <p class="text-gray-900 dark:text-white">{{ new Date(withdrawal.processed_at).toLocaleString('id-ID') }}</p>
                        </div>

                        <div v-if="withdrawal.admin">
                            <h4 class="font-semibold mb-2 text-gray-700 dark:text-gray-300">Processed By</h4>
                            <p class="text-gray-900 dark:text-white">{{ withdrawal.admin.name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClipperLayout>
</template>

