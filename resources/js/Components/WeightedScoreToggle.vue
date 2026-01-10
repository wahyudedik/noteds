<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    storageKey: {
        type: String,
        default: 'useWeightedScores',
    },
});

const emit = defineEmits(['update:modelValue']);

const useWeighted = ref(props.modelValue);

// Load from localStorage on mount
onMounted(() => {
    const stored = localStorage.getItem(props.storageKey);
    if (stored !== null) {
        useWeighted.value = stored === 'true';
        emit('update:modelValue', useWeighted.value);
    }
});

// Watch for external changes
watch(() => props.modelValue, (newVal) => {
    useWeighted.value = newVal;
});

// Save to localStorage and emit on change
watch(useWeighted, (newVal) => {
    localStorage.setItem(props.storageKey, String(newVal));
    emit('update:modelValue', newVal);
});

const toggle = () => {
    useWeighted.value = !useWeighted.value;
};
</script>

<template>
    <div class="flex items-center gap-2">
        <button
            @click="toggle"
            :class="[
                'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
                useWeighted ? 'bg-indigo-600' : 'bg-gray-200 dark:bg-gray-700'
            ]"
            role="switch"
            :aria-checked="useWeighted"
        >
            <span
                :class="[
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                    useWeighted ? 'translate-x-5' : 'translate-x-0'
                ]"
            />
        </button>
        <span class="text-sm text-gray-600 dark:text-gray-400">
            {{ useWeighted ? 'Weighted' : 'Simple' }}
        </span>
        <span
            v-if="useWeighted"
            class="text-xs text-indigo-600 dark:text-indigo-400"
            title="Verified users' votes count 2x"
        >
            ✓ 2x
        </span>
    </div>
</template>

