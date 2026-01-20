<template>
    <span v-if="readReceipts.length > 0" class="text-xs">
        ✓✓
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    message: Object,
});

const readReceipts = computed(() => {
    return props.message.read_receipts || [];
});
</script>

