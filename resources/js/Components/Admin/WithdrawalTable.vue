<script setup>
defineProps({
    withdrawals: Array,
});
</script>

<template>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="withdrawal in withdrawals" :key="withdrawal.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                    {{ withdrawal.user?.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    Rp {{ new Intl.NumberFormat('id-ID').format(withdrawal.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span
                        :class="[
                            'px-2 py-1 text-xs rounded-full',
                            withdrawal.status === 'completed' ? 'bg-green-100 text-green-800' :
                            withdrawal.status === 'approved' ? 'bg-blue-100 text-blue-800' :
                            withdrawal.status === 'rejected' ? 'bg-red-100 text-red-800' :
                            'bg-yellow-100 text-yellow-800'
                        ]"
                    >
                        {{ withdrawal.status }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ new Date(withdrawal.created_at).toLocaleDateString() }}
                </td>
            </tr>
        </tbody>
    </table>
</template>

