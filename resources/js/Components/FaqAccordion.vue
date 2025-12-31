<script setup>
import { ref } from 'vue';

const props = defineProps({
    faqs: {
        type: Array,
        required: true,
    },
    category: {
        type: String,
        default: null,
    },
});

const openItems = ref(new Set());

const toggle = (id) => {
    if (openItems.value.has(id)) {
        openItems.value.delete(id);
    } else {
        openItems.value.add(id);
    }
};

const isOpen = (id) => openItems.value.has(id);
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="faq in faqs"
            :key="faq.id"
            class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden"
        >
            <button
                @click="toggle(faq.id)"
                class="w-full px-6 py-4 text-left flex items-center justify-between bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
            >
                <span class="font-semibold text-gray-900 dark:text-gray-100">
                    {{ faq.question }}
                </span>
                <svg
                    :class="[
                        'w-5 h-5 text-gray-500 transition-transform',
                        isOpen(faq.id) ? 'transform rotate-180' : ''
                    ]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div
                v-show="isOpen(faq.id)"
                class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700"
            >
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                    {{ faq.answer }}
                </p>
            </div>
        </div>
    </div>
</template>

