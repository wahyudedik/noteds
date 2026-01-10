<script setup>
const props = defineProps({
    category: {
        type: Object,
        required: true,
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value),
    },
    showSource: {
        type: Boolean,
        default: false,
    },
    source: {
        type: String,
        default: null,
    },
});

const sizeClasses = {
    sm: 'text-xs px-2 py-0.5',
    md: 'text-sm px-2.5 py-1',
    lg: 'text-base px-3 py-1.5',
};

const isInferred = computed(() => props.source === 'inferred' || props.category?.source === 'inferred');
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1 rounded-full font-medium',
            sizeClasses[size],
            isInferred
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300'
        ]"
        :title="category.description"
    >
        <span v-if="category.icon" class="text-sm">{{ category.icon }}</span>
        <span>{{ category.name }}</span>
        <span
            v-if="showSource && isInferred"
            class="ml-1 text-xs opacity-75"
            title="Auto-detected category"
        >
            (Auto)
        </span>
    </span>
</template>

