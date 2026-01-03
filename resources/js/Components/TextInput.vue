<script setup>
import { onMounted, ref, computed } from 'vue';

// Accept both String and Number to support v-model.number modifier
const model = defineModel({
    type: [String, Number],
    required: false,
    default: '',
});

// Convert null/undefined/Number to string for input element
// Input elements always work with strings, so we convert Number to String for display
const modelValue = computed({
    get: () => {
        const value = model.value;
        if (value === null || value === undefined) {
            return '';
        }
        // Convert Number to String for input display
        // v-model.number will handle converting string back to number
        return String(value);
    },
    set: (value) => {
        // Handle v-model.number modifier: preserve number type when appropriate
        // v-model.number converts input strings to numbers, but empty inputs become NaN or ''
        
        // If value is null/undefined, set to null (not empty string) to preserve Number type
        if (value === null || value === undefined) {
            model.value = null;
            return;
        }
        
        // If value is NaN (from empty number input with v-model.number), convert to null
        if (typeof value === 'number' && isNaN(value)) {
            model.value = null;
            return;
        }
        
        // If value is empty string, check if we should preserve as null for Number type
        // or convert to empty string for String type
        if (value === '') {
            // If model currently holds a number, empty input should clear to null
            // Otherwise, use empty string for string inputs
            const currentType = typeof model.value;
            model.value = (currentType === 'number') ? null : '';
            return;
        }
        
        // Preserve the type: numbers stay numbers, strings stay strings
        model.value = value;
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
    <input
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:focus:border-indigo-600 dark:focus:ring-indigo-600"
        v-model="modelValue"
        ref="input"
    />
</template>
