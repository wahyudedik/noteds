<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useFeatureGate } from '@/Composables/useFeatureGate';

const props = defineProps({
    balance: {
        type: Number,
        default: 0,
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
const { can } = useFeatureGate();
const allowed = can('marketplace.seller');

const submit = () => {
    form.post(route('marketplace.withdrawals.store'));
};
</script>

<template>
    <form v-if="allowed" @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium mb-2">Amount (Rp) *</label>
                <input
                    v-model.number="form.amount"
                    type="number"
                    min="50000"
                    :max="balance"
                    step="1000"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
                <div v-if="form.errors.amount" class="text-red-500 text-sm mt-1">
                    {{ form.errors.amount }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Withdrawal Method *</label>
                <select
                    v-model="form.method"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>

            <div v-if="isBankTransfer">
                <label class="block text-sm font-medium mb-2">Bank Name *</label>
                <input
                    v-model="form.bank_name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
            </div>

            <div v-else>
                <label class="block text-sm font-medium mb-2">E-Wallet Type *</label>
                <select
                    v-model="form.ewallet_type"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                >
                    <option value="">Select E-Wallet</option>
                    <option value="OVO">OVO</option>
                    <option value="GoPay">GoPay</option>
                    <option value="DANA">DANA</option>
                    <option value="LinkAja">LinkAja</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Account Number *</label>
                <input
                    v-model="form.account_number"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">Account Name *</label>
                <input
                    v-model="form.account_name"
                    type="text"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                />
            </div>

            <div class="flex justify-end space-x-4 pt-4">
                <Link
                    :href="route('marketplace.withdrawals.index')"
                    class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Cancel
                </Link>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                >
                    Submit Request
                </button>
            </div>
        </div>
    </form>
</template>

