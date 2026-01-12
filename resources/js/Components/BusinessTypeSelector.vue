<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        default: null,
    },
    businessTypes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['update:modelValue']);

const selected = ref(props.modelValue);
const types = ref(props.businessTypes);

// Load business types if not provided
onMounted(async () => {
    if (types.value.length === 0) {
        try {
            const response = await fetch(route('api.suppliers.business-types'));
            const data = await response.json();
            types.value = data;
        } catch (error) {
            console.error('Failed to load business types:', error);
        }
    }
});

const select = (type) => {
    selected.value = type;
    emit('update:modelValue', type);
};

watch(() => props.modelValue, (newValue) => {
    selected.value = newValue;
});
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Tipe Bisnis (Opsional)
            <span class="text-gray-500 text-xs ml-1">- Untuk mendapatkan rekomendasi supplier yang lebih akurat</span>
        </label>
        <div v-if="types.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button
                v-for="type in types"
                :key="type.value"
                type="button"
                @click="select(type.value)"
                :class="[
                    'p-4 rounded-lg border-2 text-left transition-all',
                    selected === type.value
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-400'
                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600',
                ]"
            >
                <div class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ type.label }}
                </div>
                <div v-if="type.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ type.description }}
                </div>
            </button>
        </div>
        <div v-else class="text-sm text-gray-500 dark:text-gray-400">
            Memuat tipe bisnis...
        </div>
        <input type="hidden" :value="selected" />
    </div>
</template>

