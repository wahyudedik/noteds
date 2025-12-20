<script setup>
import { PURPOSE_TYPES, PURPOSE_TYPE_LABELS, PURPOSE_TYPE_DESCRIPTIONS } from '@/Utils/constants';

const model = defineModel({
    type: String,
    required: true,
});

const purposes = Object.values(PURPOSE_TYPES);
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            Tujuan Posting <span class="text-red-500">*</span>
        </label>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <button
                v-for="purpose in purposes"
                :key="purpose"
                type="button"
                @click="model = purpose"
                :class="[
                    'p-4 rounded-lg border-2 text-left transition-all',
                    model === purpose
                        ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 dark:border-indigo-400'
                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600',
                ]"
            >
                <div class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ PURPOSE_TYPE_LABELS[purpose] }}
                </div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ PURPOSE_TYPE_DESCRIPTIONS[purpose] }}
                </div>
            </button>
        </div>
    </div>
</template>

