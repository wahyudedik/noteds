<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    amount: '',
    payment_method: 'ewallet',
});

const paymentMethods = [
    { value: 'ewallet', label: 'E-Wallet', description: 'GoPay, OVO, DANA, LinkAja' },
    { value: 'virtual_account', label: 'Virtual Account', description: 'Bank Transfer via VA' },
    { value: 'credit_card', label: 'Credit Card', description: 'Visa, Mastercard, JCB' },
];

const quickAmounts = [50000, 100000, 250000, 500000, 1000000, 2500000];

const setQuickAmount = (amount) => {
    form.amount = amount.toString();
};

const submit = () => {
    form.post(route('clipper.top-ups.store'), {
        onError: (errors) => {
            console.error('Top up error:', errors);
        },
    });
};
</script>

<template>
    <Head title="Top Up Wallet" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Top Up Wallet
            </h2>
        </template>

        <div class="px-4 py-6 lg:px-6">
            <div class="mx-auto max-w-2xl">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <form @submit.prevent="submit">
                        <!-- Amount Input -->
                        <div class="mb-6">
                            <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Top Up Amount
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400">Rp</span>
                                <input
                                    id="amount"
                                    v-model="form.amount"
                                    type="number"
                                    min="10000"
                                    step="1000"
                                    required
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-gray-100"
                                    placeholder="Enter amount (min: Rp 10.000)"
                                />
                            </div>
                            <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.amount }}
                            </p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Minimum top up: Rp 10.000
                            </p>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Quick Amount
                            </label>
                            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                                <button
                                    v-for="amount in quickAmounts"
                                    :key="amount"
                                    type="button"
                                    @click="setQuickAmount(amount)"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg border transition-colors',
                                        form.amount === amount.toString()
                                            ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-500 text-blue-700 dark:text-blue-300'
                                            : 'bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    {{ new Intl.NumberFormat('id-ID', { 
                                        style: 'currency', 
                                        currency: 'IDR',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    }).format(amount).replace('Rp', 'Rp') }}
                                </button>
                            </div>
                        </div>

                        <!-- Payment Method Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Payment Method
                            </label>
                            <div class="space-y-3">
                                <label
                                    v-for="method in paymentMethods"
                                    :key="method.value"
                                    :class="[
                                        'flex items-start p-4 border rounded-lg cursor-pointer transition-colors',
                                        form.payment_method === method.value
                                            ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                            : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'
                                    ]"
                                >
                                    <input
                                        v-model="form.payment_method"
                                        type="radio"
                                        :value="method.value"
                                        class="mt-1 mr-3 h-4 w-4 text-blue-600 focus:ring-blue-500"
                                    />
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-gray-100">
                                            {{ method.label }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ method.description }}
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.payment_method }}
                            </p>
                        </div>

                        <!-- Summary -->
                        <div v-if="form.amount" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount:</span>
                                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                    Rp {{ new Intl.NumberFormat('id-ID').format(parseFloat(form.amount) || 0) }}
                                </span>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="form.errors.error" class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <p class="text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.error }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-end space-x-4">
                            <a
                                :href="route('clipper.top-ups.index')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                            >
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.amount || parseFloat(form.amount) < 10000"
                                :class="[
                                    'px-6 py-2 text-sm font-medium text-white rounded-lg transition-colors',
                                    form.processing || !form.amount || parseFloat(form.amount) < 10000
                                        ? 'bg-gray-400 cursor-not-allowed'
                                        : 'bg-blue-600 hover:bg-blue-700'
                                ]"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Continue to Payment</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Info Box -->
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-900 dark:text-blue-200 mb-2">
                        About Top Up
                    </h3>
                    <ul class="text-sm text-blue-800 dark:text-blue-300 space-y-1 list-disc list-inside">
                        <li>Minimum top up amount is Rp 10.000</li>
                        <li>Payment will be processed securely via Midtrans</li>
                        <li>Your balance will be updated immediately after successful payment</li>
                        <li>You can use this balance to create campaigns</li>
                    </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

