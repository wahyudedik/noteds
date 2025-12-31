<script setup>
import { computed } from 'vue';

const props = defineProps({
    wallet: {
        type: Object,
        required: true,
    },
    type: {
        type: String,
        default: 'creator', // 'creator' or 'clipper'
        validator: (value) => ['creator', 'clipper'].includes(value),
    },
});

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0);
};

const availableBalance = computed(() => {
    if (props.type === 'creator') {
        return props.wallet?.balance_available || 0;
    } else {
        return props.wallet?.balance_available || 0;
    }
});

const lockedBalance = computed(() => {
    if (props.type === 'creator') {
        return props.wallet?.balance_locked || 0;
    } else {
        return 0;
    }
});

const pendingBalance = computed(() => {
    if (props.type === 'clipper') {
        return props.wallet?.balance_pending || 0;
    } else {
        return 0;
    }
});

const withdrawnBalance = computed(() => {
    if (props.type === 'clipper') {
        return props.wallet?.balance_withdrawn || 0;
    } else {
        return 0;
    }
});

const totalBalance = computed(() => {
    if (props.type === 'creator') {
        return availableBalance.value + lockedBalance.value;
    } else {
        return availableBalance.value + pendingBalance.value;
    }
});
</script>

<template>
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-6 text-white shadow-lg">
        <div class="mb-4">
            <div class="text-sm opacity-90 mb-1">
                {{ type === 'creator' ? 'Creator Wallet' : 'Clipper Wallet' }}
            </div>
            <div class="text-3xl font-bold">
                Rp {{ formatCurrency(totalBalance) }}
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-blue-400 border-opacity-30">
            <div v-if="type === 'creator'">
                <div class="text-xs opacity-75 mb-1">Available</div>
                <div class="text-lg font-semibold">
                    Rp {{ formatCurrency(availableBalance) }}
                </div>
            </div>
            <div v-if="type === 'creator'">
                <div class="text-xs opacity-75 mb-1">Locked</div>
                <div class="text-lg font-semibold">
                    Rp {{ formatCurrency(lockedBalance) }}
                </div>
            </div>
            <div v-if="type === 'clipper'">
                <div class="text-xs opacity-75 mb-1">Available</div>
                <div class="text-lg font-semibold">
                    Rp {{ formatCurrency(availableBalance) }}
                </div>
            </div>
            <div v-if="type === 'clipper'">
                <div class="text-xs opacity-75 mb-1">Pending</div>
                <div class="text-lg font-semibold">
                    Rp {{ formatCurrency(pendingBalance) }}
                </div>
            </div>
        </div>

        <div v-if="type === 'clipper' && withdrawnBalance > 0" class="mt-4 pt-4 border-t border-blue-400 border-opacity-30">
            <div class="text-xs opacity-75 mb-1">Total Withdrawn</div>
            <div class="text-lg font-semibold">
                Rp {{ formatCurrency(withdrawnBalance) }}
            </div>
        </div>
    </div>
</template>

