<script setup>
defineProps({
    data: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="h-64 flex items-end justify-between space-x-2">
        <div
            v-for="(item, index) in data"
            :key="index"
            class="flex-1 flex flex-col items-center"
        >
            <div
                class="w-full bg-blue-500 rounded-t"
                :style="{ height: `${(item.revenue / Math.max(...data.map(d => d.revenue), 1)) * 100}%` }"
            ></div>
            <p class="text-xs mt-2 text-center">{{ item.date || item.week || item.month }}</p>
            <p class="text-xs text-gray-500">Rp {{ new Intl.NumberFormat('id-ID').format(item.revenue) }}</p>
        </div>
    </div>
</template>

