<script setup>
import ClipperLayout from '@/Layouts/ClipperLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    wallet: Object,
    minWithdrawal: {
        type: Number,
        default: 50000,
    },
});

const form = useForm({
    amount: '',
    method: 'bank_transfer',
    account_number: '',
    account_name: '',
    bank_name: '',
    ewallet_type: '',
});

const isBankTransfer = computed(() => form.method === 'bank_transfer');

const submit = () => {
    form.post(route('clipper.withdrawals.store'));
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};
</script>

<template>
    <Head title="Request Clipper Withdrawal" />

    <ClipperLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Request Clipper Withdrawal
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-2xl">
                <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                        <strong>Available Balance:</strong> 
                        Rp {{ formatCurrency(wallet?.balance_available) }}
                    </p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        Minimum withdrawal amount: Rp {{ formatCurrency(minWithdrawal) }}
                    </p>
                </div>

                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Amount (Rp) *</label>
                            <input
                                v-model.number="form.amount"
                                type="number"
                                :min="minWithdrawal"
                                :max="wallet?.balance_available || 0"
                                step="1000"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.amount }"
                            />
                            <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">
                                {{ form.errors.amount }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Withdrawal Method *</label>
                            <select
                                v-model="form.method"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                            >
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="ewallet">E-Wallet</option>
                            </select>
                        </div>

                        <div v-if="isBankTransfer">
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Bank Name *</label>
                            <input
                                v-model="form.bank_name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.bank_name }"
                            />
                            <div v-if="form.errors.bank_name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.bank_name }}
                            </div>
                        </div>

                        <div v-else>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">E-Wallet Type *</label>
                            <select
                                v-model="form.ewallet_type"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.ewallet_type }"
                            >
                                <option value="">Select E-Wallet</option>
                                <option value="OVO">OVO</option>
                                <option value="GoPay">GoPay</option>
                                <option value="DANA">DANA</option>
                                <option value="LinkAja">LinkAja</option>
                            </select>
                            <div v-if="form.errors.ewallet_type" class="text-red-500 text-sm mt-1">
                                {{ form.errors.ewallet_type }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Number *</label>
                            <input
                                v-model="form.account_number"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.account_number }"
                            />
                            <div v-if="form.errors.account_number" class="text-red-500 text-sm mt-1">
                                {{ form.errors.account_number }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">Account Name *</label>
                            <input
                                v-model="form.account_name"
                                type="text"
                                required
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                                :class="{ 'border-red-500': form.errors.account_name }"
                            />
                            <div v-if="form.errors.account_name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.account_name }}
                            </div>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <Link
                                :href="route('clipper.withdrawals.index')"
                                class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </ClipperLayout>
</template>

