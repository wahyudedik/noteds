<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    refund: Object,
});
</script>

<template>
    <Head title="Refund Details" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Refund Details
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-4xl">
                <Link
                    :href="route('admin.refunds.index')"
                    class="mb-4 inline-flex items-center text-blue-600 hover:text-blue-800"
                >
                    ← Back to Refunds
                </Link>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
                    <!-- Refund Details -->
                    <div>
                        <h3 class="text-2xl font-bold mb-4">Refund Details</h3>
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold mb-2">User</h4>
                                <p>{{ refund.user?.name }} ({{ refund.user?.email }})</p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Wallet Type</h4>
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 rounded-full text-sm',
                                        refund.wallet_type === 'creator'
                                            ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
                                            : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
                                    ]"
                                >
                                    {{ refund.wallet_type === 'creator' ? 'Creator Wallet' : 'Marketplace Wallet' }}
                                </span>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Type</h4>
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 rounded-full text-sm',
                                        refund.type === 'refund'
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
                                    ]"
                                >
                                    {{ refund.type === 'refund' ? 'Refund (Add Balance)' : 'Adjustment (Deduct Balance)' }}
                                </span>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Amount</h4>
                                <p class="text-xl" :class="refund.type === 'refund' ? 'text-green-600' : 'text-red-600'">
                                    {{ refund.type === 'refund' ? '+' : '-' }}Rp {{ new Intl.NumberFormat('id-ID').format(refund.amount) }}
                                </p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Balance Before</h4>
                                <p class="text-lg">Rp {{ new Intl.NumberFormat('id-ID').format(refund.balance_before) }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Balance After</h4>
                                <p class="text-lg">Rp {{ new Intl.NumberFormat('id-ID').format(refund.balance_after) }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Reason</h4>
                                <p class="text-gray-700 dark:text-gray-300">{{ refund.reason || 'No reason provided' }}</p>
                            </div>

                            <div v-if="refund.admin_notes">
                                <h4 class="font-semibold mb-2">Admin Notes</h4>
                                <p class="text-gray-700 dark:text-gray-300">{{ refund.admin_notes }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Created By</h4>
                                <p>{{ refund.admin?.name }}</p>
                            </div>

                            <div>
                                <h4 class="font-semibold mb-2">Created At</h4>
                                <p>{{ new Date(refund.created_at).toLocaleString() }}</p>
                            </div>

                            <div v-if="refund.ledger_entry">
                                <h4 class="font-semibold mb-2">Ledger Entry</h4>
                                <p>
                                    Transaction ID: 
                                    <Link
                                        :href="route('admin.wallets.ledger')"
                                        class="text-blue-600 hover:text-blue-800"
                                    >
                                        {{ refund.ledger_entry.transaction_id }}
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

