<script setup>
import { onMounted, ref, computed } from 'vue';

const model = defineModel({
    type: String,
    required: false,
    default: '',
});

// Exclude class and v-model from attrs to avoid conflicts
defineOptions({
    inheritAttrs: false,
});

// Convert null/undefined to empty string to prevent prop type warnings
const modelValue = computed({
    get: () => model.value ?? '',
    set: (value) => {
        model.value = value ?? '';
    },
});

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <textarea
        v-bind="$attrs"
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        v-model="modelValue"
        ref="input"
    />
</template>

